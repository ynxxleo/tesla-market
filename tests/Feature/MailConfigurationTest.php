<?php

namespace Tests\Feature;

use Illuminate\Mail\Mailer;
use Illuminate\Mail\MailManager;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Tests\TestCase;

class MailConfigurationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('mail.default', 'smtp');
        config()->set('mail.from.address', 'no-reply@teslapremiums.com');
        config()->set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => 'smtp.provider.com',
            'port' => 587,
            'username' => 'mailer',
            'password' => 'super-secret',
        ]);
    }

    #[DataProvider('nonDeliveringMailers')]
    public function test_local_only_mailers_fail_the_delivery_preflight(string $mailer): void
    {
        config()->set('mail.default', $mailer);

        $this->artisan('mail:check')
            ->expectsOutputToContain('cannot deliver email')
            ->assertFailed();
    }

    public static function nonDeliveringMailers(): array
    {
        return [['log'], ['array']];
    }

    #[DataProvider('invalidSenders')]
    public function test_invalid_or_example_sender_addresses_fail(string $address): void
    {
        config()->set('mail.from.address', $address);

        $this->artisan('mail:check')
            ->expectsOutputToContain('MAIL_FROM_ADDRESS')
            ->assertFailed();
    }

    public static function invalidSenders(): array
    {
        return [['not-an-address'], ['hello@example.com']];
    }

    #[DataProvider('invalidSmtpSettings')]
    public function test_invalid_smtp_configuration_fails_before_connecting(array $configuration, string $message): void
    {
        config()->set('mail.mailers.smtp', array_replace(config('mail.mailers.smtp'), $configuration));

        $this->artisan('mail:check', ['--connect' => true])
            ->expectsOutputToContain($message)
            ->assertFailed();
    }

    public static function invalidSmtpSettings(): array
    {
        return [
            [['host' => 'mailpit'], 'MAIL_HOST'],
            [['host' => 'smtp.example.com'], 'MAIL_HOST'],
            [['port' => 70000], 'MAIL_PORT'],
            [['scheme' => 'tls'], 'MAIL_SCHEME'],
            [['password' => null], 'both SMTP username and password'],
        ];
    }

    public function test_mail_url_overrides_placeholder_smtp_settings_without_connecting(): void
    {
        config()->set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => 'mailpit',
            'port' => 0,
            'username' => null,
            'password' => null,
            'url' => 'smtp://mailer%40teslapremiums.com:super-secret@smtp.provider.com:587',
        ]);

        $this->artisan('mail:check')
            ->expectsOutputToContain('Mail configuration is valid. Delivery has not been tested.')
            ->doesntExpectOutputToContain('super-secret')
            ->assertSuccessful();
    }

    public function test_malformed_mail_url_failure_does_not_print_credentials(): void
    {
        config()->set('mail.mailers.smtp.url', 'smtp://mailer:super-secret@smtp.provider.com:999999');

        $this->artisan('mail:check')
            ->expectsOutputToContain('MAIL_URL is malformed')
            ->doesntExpectOutputToContain('super-secret')
            ->assertFailed();
    }

    public function test_sendmail_can_pass_configuration_check_but_not_smtp_connection_check(): void
    {
        config()->set('mail.default', 'sendmail');

        $this->artisan('mail:check')
            ->expectsOutputToContain('Delivery has not been tested')
            ->assertSuccessful();

        $this->artisan('mail:check', ['--connect' => true])
            ->expectsOutputToContain('supports SMTP only')
            ->assertFailed();
    }

    public function test_builtin_failover_cannot_silently_fall_back_to_logging(): void
    {
        config()->set('mail.default', 'failover');

        $this->artisan('mail:check')
            ->expectsOutputToContain('cannot deliver email')
            ->assertFailed();
    }

    public function test_nested_non_delivering_mailers_are_rejected(): void
    {
        config()->set('mail.default', 'roundrobin');
        config()->set('mail.mailers.roundrobin.mailers', ['smtp', 'failover']);

        $this->artisan('mail:check')
            ->expectsOutputToContain('cannot deliver email')
            ->assertFailed();
    }

    public function test_composites_validate_their_smtp_children(): void
    {
        config()->set('mail.default', 'failover');
        config()->set('mail.mailers.failover.mailers', ['smtp', 'sendmail']);
        config()->set('mail.mailers.smtp.host', 'smtp.example.com');

        $this->artisan('mail:check')
            ->expectsOutputToContain('MAIL_HOST')
            ->assertFailed();
    }

    public function test_composites_require_children_and_reject_circular_references(): void
    {
        config()->set('mail.default', 'failover');
        config()->set('mail.mailers.failover.mailers', []);

        $this->artisan('mail:check')
            ->expectsOutputToContain('at least one configured delivery mailer')
            ->assertFailed();

        config()->set('mail.mailers.failover.mailers', ['roundrobin']);
        config()->set('mail.mailers.roundrobin.mailers', ['failover']);

        $this->artisan('mail:check')
            ->expectsOutputToContain('circular')
            ->assertFailed();
    }

    public function test_composites_with_valid_delivery_transports_pass_without_connecting(): void
    {
        config()->set('mail.default', 'failover');
        config()->set('mail.mailers.failover.mailers', ['smtp', 'sendmail']);

        $this->artisan('mail:check')
            ->expectsOutputToContain('Delivery has not been tested')
            ->assertSuccessful();
    }

    public function test_connection_check_starts_and_stops_smtp_without_sending_a_message(): void
    {
        $stream = (new SocketStream)->setTimeout(42);
        $transport = Mockery::mock(EsmtpTransport::class);
        $transport->shouldReceive('getStream')->andReturn($stream);
        $transport->shouldReceive('start')->once()->andReturnUsing(function () use ($stream) {
            $this->assertSame(10.0, $stream->getTimeout());
        });
        $transport->shouldReceive('stop')->once();
        $transport->shouldNotReceive('send');
        $this->useTransport($transport);

        $this->artisan('mail:check', ['--connect' => true])
            ->expectsOutputToContain('SMTP connection check succeeded. No email was sent.')
            ->assertSuccessful();

        $this->assertSame(42.0, $stream->getTimeout());
    }

    public function test_connection_failure_is_safe_and_closes_the_transport(): void
    {
        $stream = (new SocketStream)->setTimeout(42);
        $transport = Mockery::mock(EsmtpTransport::class);
        $transport->shouldReceive('getStream')->andReturn($stream);
        $transport->shouldReceive('start')->once()->andThrow(new TransportException('Authentication failed: super-secret'));
        $transport->shouldReceive('stop')->once();
        $transport->shouldNotReceive('send');
        $this->useTransport($transport);

        $this->artisan('mail:check', ['--connect' => true])
            ->expectsOutputToContain('SMTP connection or authentication failed')
            ->doesntExpectOutputToContain('super-secret')
            ->assertFailed();

        $this->assertSame(42.0, $stream->getTimeout());
    }

    private function useTransport(EsmtpTransport $transport): void
    {
        $mailer = Mockery::mock(Mailer::class);
        $mailer->shouldReceive('getSymfonyTransport')->once()->andReturn($transport);
        $manager = Mockery::mock(MailManager::class);
        $manager->shouldReceive('mailer')->with('smtp')->once()->andReturn($mailer);
        $this->app->instance('mail.manager', $manager);
    }
}
