<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LoginDestinationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_login_renders_verification_then_the_dashboard(): void
    {
        Mail::fake();
        $user = User::factory()->create(['name' => 'Test Trader', 'password' => 'password123']);

        $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertOk()->assertSee('Enter your verification code.');

        $this->assertGuest();
        $code = null;
        Mail::assertSent(OtpMail::class, function (OtpMail $mail) use ($user, &$code) {
            $code = $mail->code;

            return $mail->hasTo($user->email) && $mail->purpose === 'login';
        });

        $this->followingRedirects()->post('/verify-otp', ['code' => $code])
            ->assertOk()
            ->assertSee('PORTFOLIO COMMAND CENTER')
            ->assertSee('Test.')
            ->assertSee('No positions yet.');

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_login_renders_operations_without_sending_an_otp(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['is_admin' => true, 'password' => 'password123']);

        $this->followingRedirects()->post('/login', [
            'email' => $admin->email,
            'password' => 'password123',
        ])->assertOk()->assertSee('ADMIN CONTROL CENTER')->assertSee('Operations.');

        $this->assertAuthenticatedAs($admin);
        Mail::assertNothingSent();
    }
}
