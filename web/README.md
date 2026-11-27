# LinkPoll — web platform

The web app and services behind **https://link-poll.com** — the same social polling product as the Android app in this repository.

| Part | Live at | Folder |
|---|---|---|
| Web app (PHP API + single-page app, server-rendered SEO pages) | link-poll.com | `public/` |
| Help Center, About & Policies, Developers, Status | help. / about. / docs. / status.link-poll.com | `lp-site/` + `subdomains/` |

Product, design-system and code guide: **[docs/PLATFORM.md](docs/PLATFORM.md)**.

## Stack

- PHP 8 (no framework, no Composer), MySQL or SQLite (PDO)
- Vanilla JavaScript single-page app (`public/app.js`) and CSS (`public/app.css`)
- Server-side rendering for SEO and share previews (`public/view.php`, `public/seo.php`, `public/og.php`)
- Google Analytics 4 + Microsoft Clarity with Consent Mode v2 (`public/consent.js`)

## Setup

1. Copy `public/config.sample.php` to `public/config.php` and fill in the database, SMTP and Google sign-in values. **Never commit `config.php`.**
2. Point your domain at `public/`. Tables are created automatically on the first request (`public/schema.sql` documents them).
3. Put every other folder **next to** the web root, not inside it.
4. For each of `about`, `help`, `docs`, `status`: create the subdomain and copy `subdomains/.htaccess` and `subdomains/<name>.index.php` (renamed `index.php`) into its root.
5. Status page: add a cron job every 5 minutes — `php ~/lp-site/status_check.php`.

## Not in this repository

`config.php` (secrets), user uploads, logs, caches and backups.
