# LinkPoll — web platform

The web app and services behind **https://link-poll.com** — the same social polling product as the Android app in this repository.

| Part | Live at | Folder |
|---|---|---|
| Web app (PHP API + single-page app, server-rendered SEO pages) | link-poll.com | `public/` |
| Admin panel (users, behaviour, moderation, site analytics) | admin.link-poll.com | `admin/` + `lp-admin/` |
| Help Center, About & Policies, Developers, Status | help. / about. / docs. / status.link-poll.com | `lp-site/` + `subdomains/` |

Full product, design-system and code guide: **[docs/PLATFORM.md](docs/PLATFORM.md)**.

## Stack

- PHP 8 (no framework, no Composer), MySQL or SQLite (PDO)
- Vanilla JavaScript single-page app (`public/app.js`) and CSS (`public/app.css`)
- Server-side rendering for SEO and share previews (`public/view.php`, `public/seo.php`, `public/og.php`)
- Google Analytics 4 + Microsoft Clarity with Consent Mode v2 (`public/consent.js`)

## Folder layout on the server

```
~/link-poll.com/          ← public/      (document root of link-poll.com)
~/admin.link-poll.com/    ← admin/       (document root of admin.link-poll.com)
~/lp-admin/               ← lp-admin/    (outside the web root; included by both)
~/lp-site/                ← lp-site/     (outside the web root; subdomain engine + content)
~/help.link-poll.com/ …   ← subdomains/  (each has .htaccess + a one-line index.php)
```

## Setup

1. Copy `public/config.sample.php` to `public/config.php` and fill in the database, SMTP and Google sign-in values. **Never commit `config.php`.**
2. Point `link-poll.com` at `public/`. Tables are created automatically on first request (`public/schema.sql` documents them).
3. Put `lp-admin/` and `lp-site/` **next to** the web root, not inside it.
4. Point `admin.link-poll.com` at `admin/`. The first visit runs the owner setup (email code).
5. For each of `about`, `help`, `docs`, `status`: create the subdomain, copy `subdomains/.htaccess` and `subdomains/<name>.index.php` (renamed `index.php`) into its root.
6. Status page: add a cron job every 5 minutes — `php ~/lp-site/status_check.php`.
7. Optional: `lp-admin/seed.php` fills the database with demo content (marked `@demo.linkpoll`, hidden from search engines).

## Not in this repository

`config.php` (secrets), user uploads, logs, caches and backups. Analytics/API keys for the admin "Site analytics" section are entered in the admin panel and stored in the database, never in files.
