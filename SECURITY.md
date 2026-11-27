# Security policy

## Reporting a vulnerability

Please **do not open a public GitHub issue** for security problems.

Email **info@link-poll.com** with:

- what you found and where (URL, file or screen),
- steps to reproduce,
- the impact you think it has.

We'll reply within 3 working days, keep you updated while we fix it, and credit you if you'd like.

## Scope

- https://link-poll.com and its subdomains
- The LinkPoll Android app
- The code in this repository

Please don't run automated scans that put load on the live site, access other people's data, or test social engineering against users or staff.

## Secrets

Credentials never belong in this repository. The web app reads them from `web/public/config.php` (see `config.sample.php`) and the Android app from `.env` (see `.env.example`); both files are git-ignored.
