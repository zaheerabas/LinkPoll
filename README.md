# LinkPoll

[![Web](https://github.com/zaheerabas/LinkPoll/actions/workflows/web.yml/badge.svg)](https://github.com/zaheerabas/LinkPoll/actions/workflows/web.yml) [![Android](https://github.com/zaheerabas/LinkPoll/actions/workflows/android.yml/badge.svg)](https://github.com/zaheerabas/LinkPoll/actions/workflows/android.yml)

[LinkPoll](https://link-poll.com) is a social polling platform: create polls, share them anywhere, vote, see live results and discuss.

This repository holds both apps:

| Part | What | Folder |
|---|---|---|
| **Android app** | Native app in Kotlin | `app/` (+ Gradle files at the root) |
| **Web platform** | link-poll.com and the Help, About, Developers and Status sites | `web/` |

## Live

| Site | Address |
|---|---|
| Web app | [link-poll.com](https://link-poll.com) |
| Help Center | [help.link-poll.com](https://help.link-poll.com) |
| About & Policies | [about.link-poll.com](https://about.link-poll.com) |
| Developers | [docs.link-poll.com](https://docs.link-poll.com) |
| Status | [status.link-poll.com](https://status.link-poll.com) |

## Repository layout

```
poll/
├── app/                    Android app (Kotlin)
├── gradle/, *.gradle.kts   Android build
├── .env.example            Android API key template
├── .github/                CI workflows, issue and PR templates
└── web/                    Web platform
    ├── README.md           Web setup guide
    ├── docs/PLATFORM.md    Product, design system and code guide
    ├── public/             link-poll.com (PHP API, single-page app, SEO pages)
    ├── lp-site/            Engine and content for the help/about/docs/status sites
    └── subdomains/         Entry points for each subdomain
```

## Android app

**Tech:** Kotlin, Android SDK, Gradle (Kotlin DSL), Gemini API.

**Run locally** (you need Android Studio):

1. Clone the repo, open Android Studio, choose **Open** and select the project folder.
2. Let Android Studio fix any compatibility issues while it imports the project.
3. Copy `.env.example` to `.env` and set `GEMINI_API_KEY` to your Gemini API key.
4. In `app/build.gradle.kts`, remove `signingConfig = signingConfigs.getByName("debugConfig")`.
5. Run the app on an emulator or a physical device.

Every push that touches the app is built by the **Android** workflow; the debug APK is attached to each run under *Artifacts*.

## Web platform

**Tech:** PHP 8 (no framework), MySQL or SQLite via PDO, vanilla JavaScript single-page app, server-rendered pages for SEO and share previews, Google Analytics 4 and Microsoft Clarity with Consent Mode v2.

**Features:** feeds (For You, Following, Trending, New, Live), polls with images and live results, comments, messages, communities, quizzes and mock papers, points and badges, link in bio, embeddable widgets, insights, guest voting and cookie consent.

**Run it:** copy `web/public/config.sample.php` to `config.php`, fill in your own values and follow **[web/README.md](web/README.md)**. Design rules and the code map are in **[web/docs/PLATFORM.md](web/docs/PLATFORM.md)**.

## Contributing

Issues and pull requests are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md). Questions about using LinkPoll: [Help Center](https://help.link-poll.com).

## Security

Please report vulnerabilities privately as described in [SECURITY.md](SECURITY.md). Never commit secrets: `.env` and `web/public/config.php` are git-ignored.
