# LinkPoll for Android

Android app for [LinkPoll](https://link-poll.com), a social polling platform: create polls, share them, vote, and see the results.

> **Status:** early-stage Android project, built in Kotlin with Android Studio.

## Tech

- Kotlin, Android SDK
- Gradle (Kotlin DSL)
- Gemini API

## Run locally

**You need:** Android Studio

1. Clone the repo, open Android Studio, choose **Open** and select the project folder.
2. Let Android Studio fix any compatibility issues while it imports the project.
3. Copy `.env.example` to `.env` and set `GEMINI_API_KEY` to your Gemini API key.
4. In `app/build.gradle.kts`, remove `signingConfig = signingConfigs.getByName("debugConfig")`.
5. Run the app on an emulator or a physical device.

## Links

- Web app: [link-poll.com](https://link-poll.com)
