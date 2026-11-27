# Contributing to LinkPoll

Thanks for helping! This repo holds the **Android app** (`app/`) and the **web platform** (`web/`).

## Before you start

- Bugs and ideas: open an issue using the templates.
- Questions about using LinkPoll: see the [Help Center](https://help.link-poll.com).
- Security problems: follow [SECURITY.md](SECURITY.md), not a public issue.

## Workflow

1. Create a branch from `main`: `feature/short-name` or `fix/short-name`.
2. Keep each pull request to one change, and fill in the PR checklist.
3. Commit messages: short imperative summary with an area prefix, e.g. `web: fix vote count on poll page`, `android: add dark theme`, `docs: update setup`.
4. CI must pass: **Web** (PHP and JavaScript syntax) and **Android** (debug build).

## Web platform

- Setup: [web/README.md](web/README.md). Design rules and code map: [web/docs/PLATFORM.md](web/docs/PLATFORM.md).
- No framework or build step — plain PHP 8 and vanilla JS/CSS.
- Use the design tokens in `app.css`; never hard-code colours, sizes or radii.
- After changing `app.js` or `app.css`, bump `?v=` in both `index.html` and `view.php`.
- Run `php -l` on every PHP file you touch.

## Android app

- Open the project in Android Studio, copy `.env.example` to `.env`, and run on an emulator or device.
- Kotlin + Jetpack Compose; follow the existing package layout (`data/`, `ui/`).

## Style

- UTF-8, LF line endings, 2-space indent (4 for Kotlin) — see `.editorconfig`.
- Write for people who are new to the codebase: clear names, short functions, comments for the *why*.
