# Tesla Markets Simulator

A Laravel 12 paper-trading and investment-scenario application. It includes user registration/login, simulated manual trades, modeled investment scenarios, a risk assistant with transparent human-admin escalation, email notifications, an admin queue, SQLite migrations, and scheduled daily compounding.

The funding platform adds encrypted wallet addresses, private KYC document storage, sanctions/KYC status gates, rule-based transaction monitoring, administrator deposit instructions, withdrawal holds, idempotent ledger settlement, hourly reconciliation, immutable application audit events, account restrictions, and a regulatory-license launch registry. It never accepts or stores wallet private keys.

> This is an educational simulation. It does not custody money, execute real securities, guarantee returns, or provide individualized financial advice. The “Grok-style” desk is not affiliated with xAI. Automated and human messages are explicitly labeled.

## Requirements

- PHP 8.2+
- Composer 2
- Node.js 20+

## Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

The seeded development administrator is configured by `ADMIN_NAME`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD` in `.env`. Change all three before any shared deployment. The example defaults are `admin@teslamarkets.test` / `ChangeMe!2026`.

## Email and scheduler

Development uses Laravel’s `log` mailer. For real escalation email, configure `MAIL_MAILER=smtp` and the SMTP variables. Run the scheduler in production:

```bash
php artisan schedule:work
```

Password resets, signup verification, login verification, and investment verification all require working outbound email.

## cPanel deployment

Point the domain's document root at this repository's `public` directory. Keep `.env`, `vendor`, `storage`, and the rest of the Laravel application outside a publicly served directory whenever the host allows it. In the production `.env`, set the real HTTPS URL and SMTP mailbox supplied by the host:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example

MAIL_MAILER=smtp
MAIL_HOST=mail.your-domain.example
MAIL_PORT=465
MAIL_USERNAME=no-reply@your-domain.example
MAIL_PASSWORD=your-mailbox-password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=no-reply@your-domain.example
MAIL_FROM_NAME="Tesla Markets"

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=your-domain.example
```

Do not copy a local `APP_KEY` onto a production application that already contains encrypted wallet records. Set unique `ADMIN_EMAIL` and `ADMIN_PASSWORD` values before deploying. The deployment script synchronizes that administrator account on every deployment, so changing `ADMIN_PASSWORD` in `.env` and deploying is also the supported way to recover cPanel administrator access. For each deployment, run:

```bash
bash deploy/cpanel-deploy.sh
```

If cPanel hosts the application in a subdirectory, include that subdirectory in `APP_URL` and `SESSION_PATH`. After changing `.env`, run `php artisan optimize:clear` followed by `php artisan config:cache`. A `MAIL_MAILER=log` production setting will write verification codes and reset links to `storage/logs/laravel.log` instead of delivering them.

The `investments:accrue` command compounds disclosed simulation rates once daily. These values are modeled scenarios, not actual or promised profit.

## Production checklist

- Replace all example credentials and use a managed database.
- Configure HTTPS, SMTP, queue workers, backups, monitoring, rate limiting, email verification, and MFA for administrators.
- Integrate a licensed broker/custodian and complete legal/regulatory review before adding real-money functionality.
- Replace reference quotes with a licensed market-data source and server-side quote validation.
- Keep `FUNDING_MODE=sandbox` and `LIVE_CRYPTO_TRANSFERS=false` until a qualified attorney and the configured custodian approve every enabled jurisdiction. Code and database records do not constitute a license.
