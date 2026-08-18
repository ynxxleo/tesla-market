<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Mail\WelcomeMail;
use App\Models\FundingRequest;
use App\Models\InvestmentPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TradingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_signup_requires_emailed_otp_and_sends_welcome_email(): void
    {
        Mail::fake();
        $this->post('/register', ['name' => 'Trader', 'email' => 'trader@example.test', 'password' => 'password123', 'password_confirmation' => 'password123'])->assertRedirect('/verify-otp');
        $this->assertGuest();
        $user = User::where('email', 'trader@example.test')->firstOrFail();
        Mail::assertSent(OtpMail::class, fn ($mail) => $mail->hasTo($user->email) && $mail->purpose === 'signup');
        $otp = null;
        Mail::assertSent(OtpMail::class, function ($mail) use (&$otp) {
            $otp = $mail->code;

            return true;
        });
        $this->post('/verify-otp', ['code' => $otp])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->email_verified_at);
        Mail::assertSent(WelcomeMail::class, fn ($mail) => $mail->hasTo($user->email));
    }

    public function test_login_requires_emailed_otp(): void
    {
        Mail::fake();
        $user = User::factory()->create(['password' => 'password123']);
        $this->post('/login', ['email' => $user->email, 'password' => 'password123'])->assertRedirect('/verify-otp');
        $this->assertGuest();
        $otp = null;
        Mail::assertSent(OtpMail::class, function ($mail) use (&$otp) {
            $otp = $mail->code;

            return $mail->purpose === 'login';
        });
        $this->post('/verify-otp', ['code' => $otp])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_make_paper_trade(): void
    {
        $u = User::factory()->create(['cash_balance' => 100000]);
        $this->actingAs($u)->post('/trade', ['symbol' => 'TSLA', 'side' => 'buy', 'quantity' => 2])->assertSessionHas('status');
        $this->assertDatabaseHas('trades', ['user_id' => $u->id, 'symbol' => 'TSLA', 'side' => 'buy']);
        $this->assertEquals(99108.18, (float) $u->fresh()->cash_balance);
    }

    public function test_user_can_make_crypto_paper_trade(): void
    {
        $u = User::factory()->create(['cash_balance' => 100000]);
        $this->actingAs($u)->post('/trade', ['symbol' => 'ETH', 'side' => 'buy', 'quantity' => 1])->assertSessionHas('status');
        $this->assertDatabaseHas('trades', ['user_id' => $u->id, 'symbol' => 'ETH', 'side' => 'buy']);
    }

    public function test_user_cannot_oversell(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->from('/trade')->post('/trade', ['symbol' => 'TSLA', 'side' => 'sell', 'quantity' => 2])->assertRedirect('/trade')->assertSessionHasErrors('quantity');
    }

    public function test_grok_assistant_answers_before_human_escalation(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->post('/assistant', ['message' => 'How do I deposit?', 'risk_budget' => 200])->assertRedirect();
        $this->assertDatabaseHas('conversations', ['user_id' => $u->id, 'status' => 'automated']);
        $this->assertDatabaseHas('messages', ['author_type' => 'assistant']);
    }

    public function test_grok_assistant_escalates_when_human_is_requested(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->post('/assistant', ['message' => 'I need customer support'])->assertRedirect();
        $this->assertDatabaseHas('conversations', ['user_id' => $u->id, 'status' => 'escalated']);
    }

    public function test_grok_chat_returns_json_and_supports_realtime_polling(): void
    {
        $u = User::factory()->create();
        $response = $this->actingAs($u)->postJson('/assistant', ['message' => 'How do I close a position?']);
        $response->assertOk()->assertJsonCount(2, 'messages')->assertJsonPath('messages.1.author_type', 'assistant');
        $lastId = $response->json('messages.0.id');
        $this->getJson('/assistant/messages?after='.$lastId)->assertOk()->assertJsonCount(1, 'messages');
    }

    public function test_non_admin_cannot_open_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_verified_user_can_request_sandbox_deposit(): void
    {
        $u = User::factory()->create(['kyc_status' => 'verified', 'sanctions_status' => 'clear']);
        $this->actingAs($u)->post('/funding', ['type' => 'deposit', 'asset' => 'BTC', 'network' => 'bitcoin', 'crypto_amount' => '.01', 'usd_amount' => 500])->assertSessionHas('status');
        $this->assertDatabaseHas('funding_requests', ['user_id' => $u->id, 'type' => 'deposit', 'status' => 'awaiting_transfer']);
    }

    public function test_unverified_user_cannot_request_funding(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->from('/funding')->post('/funding', ['type' => 'deposit', 'asset' => 'BTC', 'network' => 'bitcoin', 'crypto_amount' => '.01', 'usd_amount' => 500])->assertRedirect('/funding')->assertSessionHasErrors('funding');
    }

    public function test_user_must_verify_otp_before_adding_tesla_package(): void
    {
        Mail::fake();
        $u = User::factory()->create(['cash_balance' => 10000]);
        $this->actingAs($u)->post('/investments', ['plan' => 'model_y', 'amount' => 5000])->assertRedirect('/investments/verify-otp');
        $this->assertDatabaseMissing('investments', ['user_id' => $u->id, 'plan' => 'model_y']);
        $otp = null;
        Mail::assertSent(OtpMail::class, function ($mail) use (&$otp) {
            $otp = $mail->code;

            return $mail->purpose === 'investment';
        });
        $this->post('/investments/verify-otp', ['code' => $otp])->assertRedirect('/investments')->assertSessionHas('status');
        $this->assertDatabaseHas('investments', ['user_id' => $u->id, 'plan' => 'model_y', 'principal' => 5000]);
    }

    public function test_user_can_close_an_open_position(): void
    {
        $u = User::factory()->create(['cash_balance' => 100000]);
        $this->actingAs($u)->post('/trade', ['symbol' => 'TSLA', 'side' => 'buy', 'quantity' => 2])->assertSessionHas('status');
        $this->post('/trade/TSLA/close')->assertSessionHas('status');
        $position = (float) $u->trades()->where('symbol', 'TSLA')->selectRaw("COALESCE(SUM(CASE WHEN side='buy' THEN quantity ELSE -quantity END),0) as qty")->value('qty');
        $this->assertSame(0.0, $position);
        $this->assertDatabaseHas('trades', ['user_id' => $u->id, 'symbol' => 'TSLA', 'side' => 'sell', 'quantity' => 2]);
    }

    public function test_user_can_update_profile(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->post('/profile', ['name' => 'Updated Trader', 'email' => 'updated@example.test'])->assertSessionHas('status');
        $this->assertSame('Updated Trader', $u->fresh()->name);
    }

    public function test_user_can_upload_profile_picture(): void
    {
        Storage::fake('public');
        $u = User::factory()->create();
        $this->actingAs($u)->post('/profile', ['name' => $u->name, 'email' => $u->email, 'avatar' => UploadedFile::fake()->image('avatar.jpg', 300, 300)])->assertSessionHas('status');
        $this->assertNotNull($u->fresh()->avatar_path);
        Storage::disk('public')->assertExists($u->fresh()->avatar_path);
    }

    public function test_wire_withdrawal_uses_users_bank_details_not_company_instructions(): void
    {
        $u = User::factory()->create(['kyc_status' => 'verified', 'sanctions_status' => 'clear', 'cash_balance' => 5000]);
        $this->actingAs($u)->post('/funding', ['type' => 'withdrawal', 'method' => 'wire', 'usd_amount' => 500, 'beneficiary_name' => 'Test Trader', 'bank_name' => 'User Bank', 'account_number' => 'USER-123'])->assertSessionHas('status');
        $request = FundingRequest::where('user_id', $u->id)->firstOrFail();
        $this->assertNull($request->wire_details_snapshot);
        $this->assertStringContainsString('User Bank', $request->destination_details);
    }

    public function test_funding_and_package_pages_render_for_user(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->get('/funding')->assertOk()->assertSee('Fund your portfolio')->assertSee('Wire transfer')->assertSee('data-wallet-qr', false);
        $this->get('/investments')->assertOk()->assertSee('Choose your drive')->assertSee('Cybertruck');
    }

    public function test_user_can_submit_kyc_document_without_server_error(): void
    {
        Storage::fake('local');
        $u = User::factory()->create();
        $response = $this->actingAs($u)->post('/kyc', ['legal_name' => 'Test Trader', 'date_of_birth' => '1990-01-01', 'country_code' => 'NG', 'document_type' => 'passport', 'document' => UploadedFile::fake()->create('passport.pdf', 100, 'application/pdf')]);
        $response->assertRedirect()->assertSessionHas('status');
        $this->assertDatabaseHas('kyc_profiles', ['user_id' => $u->id, 'legal_name' => 'Test Trader']);
        Storage::disk('local')->assertExists($u->fresh()->kycProfile->document_path);
    }

    public function test_public_legal_pages_render(): void
    {
        foreach (['/terms', '/privacy', '/legal', '/risk-disclosure', '/contact'] as $page) {
            $this->get($page)->assertOk();
        }
    }

    public function test_news_page_renders_with_external_feed_fallback(): void
    {
        Http::fake(['*' => Http::response('', 503)]);
        $this->get('/news')->assertOk()->assertSee('News moving')->assertSee('CNBC');
    }

    public function test_landing_page_has_video_team_and_facebook(): void
    {
        $this->get('/')->assertOk()->assertSee('_OiEwIUQzDk')->assertSee('MEET THE TEAM')->assertSee('Facebook');
        $this->get('/')->assertOk()->assertSee('Elon Musk')->assertSee('Wikimedia Commons')->assertSee('not endorsed by or affiliated with');
    }

    public function test_admin_can_lock_account(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $this->actingAs($admin)->post("/admin/users/{$user->id}/lock", ['lock_reason' => 'Automated monitoring review'])->assertSessionHas('status');
        $this->assertNotNull($user->fresh()->locked_at);
    }

    public function test_verified_user_can_request_wire_deposit_and_admin_can_confirm_it(): void
    {
        $user = User::factory()->create(['kyc_status' => 'verified', 'sanctions_status' => 'clear', 'cash_balance' => 0]);
        $this->actingAs($user)->post('/funding', ['type' => 'deposit', 'method' => 'wire', 'usd_amount' => 250])->assertSessionHas('status');
        $funding = FundingRequest::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('wire', $funding->method);
        $this->assertNotEmpty($funding->wire_details_snapshot);
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->post("/admin/funding/{$funding->id}/approve", ['review_note' => 'Wire reference confirmed'])->assertSessionHas('status');
        $this->assertSame('approved', $funding->fresh()->status);
        $this->assertEquals(250, (float) $user->fresh()->cash_balance);
    }

    public function test_admin_can_edit_package_amount_and_profit_rate(): void
    {
        $package = InvestmentPackage::catalog()->firstWhere('slug', 'cybertruck');
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->post("/admin/packages/{$package->id}", ['minimum' => 200000, 'maximum' => 1500000, 'annual_rate' => 15.25, 'is_active' => 1])->assertSessionHas('status');
        $this->assertEquals(15.25, (float) $package->fresh()->annual_rate);
    }

    public function test_admin_operations_page_renders_package_and_wire_controls(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get('/admin/compliance')->assertOk()->assertSee('Company wire instructions')->assertSee('Cybertruck');
    }
}
