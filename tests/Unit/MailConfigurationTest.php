<?php

namespace Tests\Unit;

use Tests\TestCase;

class MailConfigurationTest extends TestCase
{
    public function test_legacy_encryption_settings_use_supported_smtp_schemes(): void
    {
        $this->assertSame('smtps', $this->smtpConfiguration([
            'MAIL_SCHEME' => 'null',
            'MAIL_ENCRYPTION' => 'ssl',
        ])['scheme']);

        $this->assertSame('smtp', $this->smtpConfiguration([
            'MAIL_SCHEME' => 'null',
            'MAIL_ENCRYPTION' => 'tls',
        ])['scheme']);

        $this->assertSame('smtp', $this->smtpConfiguration([
            'MAIL_SCHEME' => 'smtp',
            'MAIL_ENCRYPTION' => 'ssl',
        ])['scheme']);
    }

    public function test_smtp_connection_timeout_is_bounded_and_configurable(): void
    {
        $this->assertSame(10.0, $this->smtpConfiguration(['MAIL_TIMEOUT' => '10'])['timeout']);
        $this->assertSame(4.0, $this->smtpConfiguration(['MAIL_TIMEOUT' => '4'])['timeout']);
    }

    private function smtpConfiguration(array $values): array
    {
        $previousEnv = $_ENV;
        $previousServer = $_SERVER;

        try {
            foreach ($values as $key => $value) {
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }

            return (require config_path('mail.php'))['mailers']['smtp'];
        } finally {
            $_ENV = $previousEnv;
            $_SERVER = $previousServer;
        }
    }
}
