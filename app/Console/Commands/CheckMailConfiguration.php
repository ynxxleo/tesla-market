<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ConfigurationUrlParser;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Throwable;

class CheckMailConfiguration extends Command
{
    protected $signature = 'mail:check {--connect : Connect to SMTP and authenticate without sending an email}';

    protected $description = 'Validate delivery configuration and optionally check the SMTP connection';

    public function handle(MailManager $manager): int
    {
        $name = config('mail.default');

        if (! is_string($name) || $name === '') {
            $this->error('The default mailer is not configured. Set MAIL_MAILER.');

            return self::FAILURE;
        }

        $configuration = $this->validateMailer($name);

        if ($configuration === null) {
            return self::FAILURE;
        }

        $driver = $configuration['transport'] ?? null;
        $from = $configuration['from']['address'] ?? config('mail.from.address');

        if (! is_string($from) || ! filter_var($from, FILTER_VALIDATE_EMAIL) || $this->isPlaceholderHost(substr(strrchr($from, '@'), 1))) {
            $this->error('Set MAIL_FROM_ADDRESS to a valid sender address on your verified sending domain.');

            return self::FAILURE;
        }

        if ($this->option('connect') && $driver !== 'smtp') {
            $this->error('The --connect check supports SMTP only. Check delivery through the configured provider.');

            return self::FAILURE;
        }

        try {
            // Resolving the actual transport also catches unsupported drivers or missing provider packages.
            $transport = $manager->mailer($name)->getSymfonyTransport();
        } catch (Throwable) {
            $this->error('The mail transport could not be initialized. Check the mailer settings and required provider package.');

            return self::FAILURE;
        }

        if (! $this->option('connect')) {
            $this->info('Mail configuration is valid. Delivery has not been tested.');

            return self::SUCCESS;
        }

        if (! $transport instanceof SmtpTransport || ! $transport->getStream() instanceof SocketStream) {
            $this->error('The configured transport does not support an SMTP connection check.');

            return self::FAILURE;
        }

        $stream = $transport->getStream();
        $previousTimeout = $stream->getTimeout();
        $stream->setTimeout(10);

        try {
            // Symfony negotiates TLS and authentication during start(), without MAIL FROM or RCPT TO.
            $transport->start();
            $this->info('SMTP connection check succeeded. No email was sent.');

            return self::SUCCESS;
        } catch (Throwable) {
            // SMTP exceptions can include usernames, server responses, or connection URLs.
            $this->error('SMTP connection or authentication failed. Check server access, TLS settings, and credentials.');

            return self::FAILURE;
        } finally {
            try {
                $transport->stop();
            } catch (Throwable) {
                // Do not expose server responses if the connection closes during QUIT.
            } finally {
                $stream->setTimeout($previousTimeout);
            }
        }
    }

    private function validateMailer(string $name, array $parents = []): ?array
    {
        if (in_array($name, $parents, true)) {
            $this->error('Mail configuration contains a circular failover or roundrobin reference.');

            return null;
        }

        $configuration = config("mail.mailers.{$name}");

        if (! is_array($configuration)) {
            $this->error('A selected mailer is not configured. Check MAIL_MAILER and config/mail.php.');

            return null;
        }

        try {
            // Match Laravel's precedence, including credentials and options in MAIL_URL.
            if (isset($configuration['url'])) {
                $configuration = array_merge($configuration, (new ConfigurationUrlParser)->parseConfiguration($configuration));
                $configuration['transport'] = $configuration['driver'] ?? null;
            }
        } catch (Throwable) {
            $this->error('MAIL_URL is malformed. Check its scheme, host, port, and URL-encoded credentials.');

            return null;
        }

        $driver = $configuration['transport'] ?? null;

        if (in_array($driver, ['log', 'array'], true)) {
            $this->error('A selected mailer cannot deliver email. Remove log/array transports and configure SMTP or a delivery provider.');

            return null;
        }

        if ($driver === 'smtp' && ! $this->validateSmtp($configuration)) {
            return null;
        }

        if (in_array($driver, ['failover', 'roundrobin'], true)) {
            $children = $configuration['mailers'] ?? null;

            if (! is_array($children) || $children === []) {
                $this->error('Failover and roundrobin mailers require at least one configured delivery mailer.');

                return null;
            }

            foreach ($children as $child) {
                if (! is_string($child) || $child === '') {
                    $this->error('Failover and roundrobin children must be configured mailer names.');

                    return null;
                }

                if ($this->validateMailer($child, [...$parents, $name]) === null) {
                    return null;
                }
            }
        }

        return $configuration;
    }

    private function validateSmtp(array $configuration): bool
    {
        $host = $configuration['host'] ?? null;

        if (! is_string($host) || $host === '' || $this->isPlaceholderHost($host)
            || (! filter_var(trim($host, '[]'), FILTER_VALIDATE_IP) && ! filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME))) {
            $this->error('Set MAIL_HOST or the MAIL_URL host to a valid SMTP server, replacing any example or development mail server.');

            return false;
        }

        if (! filter_var($configuration['port'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]])) {
            $this->error('Set MAIL_PORT or the MAIL_URL port to an integer from 1 to 65535.');

            return false;
        }

        if (! in_array($configuration['scheme'] ?? null, [null, '', 'smtp', 'smtps'], true)) {
            $this->error('MAIL_SCHEME must be smtp or smtps.');

            return false;
        }

        $hasUsername = ($configuration['username'] ?? '') !== '' && ($configuration['username'] ?? null) !== null;
        $hasPassword = ($configuration['password'] ?? '') !== '' && ($configuration['password'] ?? null) !== null;

        if ($hasUsername !== $hasPassword) {
            $this->error('Configure both SMTP username and password, or leave both empty for an unauthenticated relay.');

            return false;
        }

        return true;
    }

    private function isPlaceholderHost(string $host): bool
    {
        $host = strtolower(rtrim($host, '.'));

        return in_array($host, ['mailpit', 'mailhog', 'example.com', 'example.net', 'example.org'], true)
            || preg_match('/\.(?:example\.(?:com|net|org)|test|invalid)$/', $host) === 1;
    }
}
