<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Tests\TestCase;

class MailDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_login_email_returns_a_retryable_error_without_authenticating(): void
    {
        $this->failMailDelivery();
        $user = User::factory()->create(['password' => 'password123']);

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('email')
            ->assertSessionMissing('otp_challenge');

        $this->assertGuest();
        Exceptions::assertReported(TransportException::class);
    }

    public function test_failed_signup_email_does_not_leave_an_account_that_blocks_retry(): void
    {
        $this->failMailDelivery();

        $this->from('/register')->post('/register', [
            'name' => 'Trader',
            'email' => 'trader@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/register')
            ->assertSessionHasErrors('email')
            ->assertSessionMissing('otp_challenge');

        $this->assertDatabaseMissing('users', ['email' => 'trader@example.test']);
        $this->assertGuest();
    }

    public function test_failed_resend_preserves_the_previously_delivered_code(): void
    {
        $this->failMailDelivery();
        $user = User::factory()->create();
        $challenge = $this->challenge($user, 'login');

        $this->withSession(['otp_challenge' => $challenge])
            ->from('/verify-otp')->post('/verify-otp/resend')
            ->assertRedirect('/verify-otp')
            ->assertSessionHasErrors('email')
            ->assertSessionHas('otp_challenge', $challenge)
            ->assertSessionMissing('status');

        $this->post('/verify-otp', ['code' => '123456'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_failed_welcome_email_does_not_interrupt_verified_signup(): void
    {
        $this->failMailDelivery();
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->withSession(['otp_challenge' => $this->challenge($user, 'signup')])
            ->post('/verify-otp', ['code' => '123456'])
            ->assertRedirect('/dashboard')
            ->assertSessionMissing('otp_challenge');

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->email_verified_at);
        Exceptions::assertReported(TransportException::class);
    }

    public function test_failed_investment_email_does_not_authorize_or_create_an_investment(): void
    {
        $this->failMailDelivery();
        $user = User::factory()->create(['cash_balance' => 10000]);

        $this->actingAs($user)->from('/investments')->post('/investments', [
            'plan' => 'model_y',
            'amount' => 5000,
        ])->assertRedirect('/investments')
            ->assertSessionHasErrors('email')
            ->assertSessionMissing('otp_challenge');

        $this->assertDatabaseMissing('investments', ['user_id' => $user->id]);
        $this->assertEquals(10000, (float) $user->fresh()->cash_balance);
    }

    public function test_failed_password_reset_email_keeps_the_generic_response_and_allows_retry(): void
    {
        $this->failMailDelivery();
        $user = User::factory()->create();

        $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect('/forgot-password')
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'If an account exists for that email, we will try to send a password reset link. If it does not arrive, try again shortly.');

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        Exceptions::assertReported(TransportException::class);

        config(['mail.default' => 'array']);
        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'If an account exists for that email, we will try to send a password reset link. If it does not arrive, try again shortly.');

        $this->assertCount(1, Mail::mailer('array')->getSymfonyTransport()->messages());
    }

    public function test_unknown_password_reset_email_keeps_the_generic_response(): void
    {
        $this->failMailDelivery();

        $this->from('/forgot-password')->post('/forgot-password', ['email' => 'unknown@example.test'])
            ->assertRedirect('/forgot-password')
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'If an account exists for that email, we will try to send a password reset link. If it does not arrive, try again shortly.');

        Exceptions::assertNothingReported();
    }

    private function failMailDelivery(): void
    {
        Exceptions::fake([TransportException::class]);
        Mail::extend('unavailable', fn () => new class extends AbstractTransport
        {
            protected function doSend(SentMessage $message): void
            {
                throw new TransportException('SMTP connection unavailable.');
            }

            public function __toString(): string
            {
                return 'unavailable';
            }
        });

        config([
            'mail.default' => 'unavailable',
            'mail.mailers.unavailable' => ['transport' => 'unavailable'],
        ]);
    }

    private function challenge(User $user, string $purpose): array
    {
        return [
            'purpose' => $purpose,
            'user_id' => $user->id,
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'payload' => [],
        ];
    }
}
