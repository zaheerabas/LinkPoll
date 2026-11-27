# LinkPoll — Product & Design System

LinkPoll (https://link-poll.com) is a social polling platform: people create polls, vote, discuss, learn (quizzes, mock papers) and build an audience.
This document is the single reference for **where things go**, **how they look**, and **how the code is organised**.

---

## 1. Product principles

1. **One job per area.** Left = go somewhere. Centre = the content. Right = context about what you're looking at.
2. **One home per destination.** Every page has exactly one place in the menu. Shortcuts (header icons) are allowed; duplicates are not.
3. **Voting is the core loop.** Create → share → vote → see results → insights. Nothing should compete with the poll card in the feed.
4. **Calm, editorial, trustworthy.** Neutral warm surfaces, one accent colour (lime) used sparingly, serif headlines for questions.
5. **Accessible by default.** WCAG 2.2 AA contrast, full keyboard support, 32 px+ touch targets, reduced-motion respected.

---

## 2. Information architecture (the tree)

```
LinkPoll
├── Home                feed: For You · Following · Trending · New · Live
├── Explore             search, trending tags, people   (/discover → /explore)
├── Discussions         community Q&A
├── Communities
├── LEARN
│   ├── Quizzes
│   └── Mock Papers
├── YOU
│   ├── My Polls
│   ├── Insights
│   ├── Saved
│   ├── Network         followers · following · suggested
│   └── Points & badges → Leaderboard
├── CREATOR TOOLS
│   ├── Link in bio
│   ├── Widgets
│   └── Reviews
└── Settings
    ├── Account         Edit profile · Password & security · Account & privacy
    ├── Preferences     Appearance · Notifications & emails
    ├── About & support Help · About · What's new · Send feedback · Privacy · Terms · Guidelines
    └── Log out
```

Sub-pages highlight their parent in the menu (`NAV_ALIAS` in app.js):
`leaderboard → Points & badges`, `tag / discover → Explore`, `c → Communities`, `paper → Mock Papers`.

---

## 3. Layout rules (desktop ≥ 1101 px)

| Zone | Contains | Never contains |
|---|---|---|
| **Header** (top bar, aligned to the 1440 px grid: logo over the menu, search over the feed, actions over the right column) | Logo (→ Home), global search, Notifications, Messages, **Create Poll** (the one lime CTA), avatar | Page-specific actions, a second Home icon |
| **Left sidebar** (264 px) | Navigation tree only (grouped, ≤ 5 items per group), compact Create Poll button, your account chip | Help/legal links, promos, trending lists, ads |
| **Centre** (724 px) | The page: feed, poll, profile, forms | Global navigation |
| **Right column** (344 px, sticky, scrolls on its own) | Context: Trending tags → Live polls → Your points → Invite → footer links (Help · About · What's new · Send feedback · Guidelines · Privacy · Terms · ©) | Navigation, account settings, anything you must see to finish a task |

**Focused pages hide the right column** and use the full width:
Messages, Settings, Link in bio, Reviews, Widgets, Insights.
(Implemented with `body[data-route="…"]`, set automatically from the app's `route` variable.)

Reference patterns: LinkedIn (footer links at the bottom of the right column), Reddit (grouped left nav with section labels), Quora (context cards on the right), Instagram (mobile bottom tab bar with a central create button).

### Mobile (≤ 1100 px)
- **Top bar:** menu (opens the same navigation tree), logo, search, messages.
- **Bottom tab bar:** Home · Explore · **＋ Create** · Alerts · You.
- No right column. Help/legal live in **Settings → About & support**.
- The sidebar Create Poll promo is hidden (the ＋ tab does that job).

---

## 4. Design tokens

All values live as CSS custom properties in `app.css` (`:root`, with a `:root[data-theme="dark"]` override). Never hard-code a colour or size in a component.

### Colour
| Token | Light | Dark | Use |
|---|---|---|---|
| `--bg` | `#f3f1ea` | `#0f0f12` | Page background |
| `--panel` / `--card` | `#ffffff` | (theme) | Cards, menus |
| `--ink` | `#0f0f0d` | (theme) | Primary text, dark buttons, focus ring |
| `--muted` | `#5f5a4b` | `#b4afa1` | Secondary text (≥ 4.5:1) |
| `--soft` | `#6f6a5a` | `#9b968a` | Tertiary text, timestamps, footer links (≥ 4.5:1) |
| `--line` / `--line-2` | `#e7e2d6` / `#efeada` | (theme) | Borders, dividers |
| `--chip` | `#f1ede2` | (theme) | Chips, hover fills |
| `--lime` / `--lime-d` | `#cafc3c` / `#b4ea12` | same | **Accent — primary CTA only** |
| `--red` `--amber` `--green` `--heart` | status colours | | Errors, warnings, success, likes |

### Shape, elevation, size
| Token | Value | Use |
|---|---|---|
| `--r-xs` / `--r-sm` / `--r-md` / `--r-lg` / `--r-pill` | 6 / 10 / 14 / 20 / 999 px | Tooltips / menu items / options, inputs / cards, modals / buttons, chips |
| `--sh-2` | `0 4px 16px rgba(21,20,15,.08)` | Menus, popovers, modals |
| floating | `0 6px 20px rgba(21,20,15,.18)` | Ask AI, back-to-top |

**Elevation rule:** resting surfaces (cards, rail boxes, search, nav items, banners) are **flat** — 1 px border, no shadow. Hover darkens the border instead of lifting. Shadows are only for layers that float above the page: menus, popovers, modals, toasts, Ask AI, back-to-top, pull-to-refresh.

### Type
- **Fraunces** (`--serif`): poll questions, page titles, headings.
- **Hanken Grotesk** (`--sans`): everything else, including tabs, numbers and avatars.
- One scale: 11, 12, 13, 14, 15, 16, 18, 20, 24, 28, 32, 40, 48, 56 px.
- Numbers use `tabular-nums`; headings use `text-wrap: balance`.

---

## 5. Components

| Component | Classes | Rules |
|---|---|---|
| **Primary button** | `.btn.btn-lime` | Lime is reserved for **Create Poll** and the single main action of a screen. One per view. |
| **Secondary button** | `.btn.btn-dark` | Follow, Complete, Send. Ink background, light text. |
| **Follow** | `.follow-btn` | 34 px, pill, ink. Becomes outlined "Following" when active. |
| **Icon button** | `.iconbtn` | ≥ 36 × 36 px hit area, `aria-label` required (shown as a tooltip on desktop). |
| **Card** | `.card` | 20 px radius, 1 px `--line` border, no shadow. Header (avatar, name, time, ⋯ menu) → question → options → meta → action bar. |
| **Poll option** | `.opt[role=button]` | 48 px+, radio dot, hover tint, keyboard focusable (Tab, Enter/Space to vote), shows result bar after voting. |
| **Action bar** | `.act` | Like · Comment · Share · Save · AI on the left, results count on the right. 40 px targets with tooltips. |
| **Tabs** | `.tabs` | Underline style, sans 15 px/600, active tab in `--ink` with a 3 px underline. |
| **Rail card** | `.box.rail-card` | Sans 16 px title + "View all" link top-right, list rows below. Max 5 rows. |
| **Inputs** | `input`, `textarea`, `select` | Visible label, lime focus glow + ink border. |
| **Nav item** | `.nav a` | Icon (20 px line icon) + label; active = lime tint. |
| **Story avatar** | `.story-av` | Avatar with a small lime ＋ badge bottom-right for "Add story". |
| **Empty state** | page-level | Icon + one-line explanation + one CTA (e.g. "Browse polls"). |
| **Footer links** | `.rail-foot` | 12 px, `--soft`, wraps; injected at the bottom of `#rail`. |

**Icons:** line icons from `ic(name, size)` only. Avoid mixing emoji with line icons in the same list.

**Motion** (everything is skipped under `prefers-reduced-motion`):

| Interaction | Behaviour | Timing |
|---|---|---|
| Switch tab | Underline (`.tab-ink`) slides to the new tab | 260 ms |
| Tab content | Slides in from the side you moved toward, with a fade | 280 ms |
| Change page | Centre column fades in and rises 6 px | 240 ms |
| Swipe left/right (touch) | Switches to the next/previous tab | 70 px minimum, < 700 ms |
| Back button | Returns to the exact scroll position you left | restores after render |
| Press | Buttons scale to .97 | 90–160 ms |

Rules: animate only `transform` and `opacity`; nothing over 400 ms; ease-out when entering.

**Loading:** skeleton post cards on first load, cached tabs appear instantly, infinite scroll with a skeleton at the bottom, "You're all caught up!" at the end, inline Retry on failure, a 2 px lime progress bar for requests over 150 ms, images fade in.

**Focus:** every interactive element shows a 2 px ring (ink, lime in dark mode) on keyboard focus.

### Links & titles (shareable URLs)

| Page | URL | Browser-tab title |
|---|---|---|
| Home | `link-poll.com/` | LinkPoll — the world's opinion, live |
| Poll | `link-poll.com/p/<code>` | `<poll question> — LinkPoll` |
| Profile | `link-poll.com/<username>` | `<Name> (@username) — LinkPoll` |
| Community | `link-poll.com/c/<slug>` | `<Community name> — LinkPoll` |

- **Server side:** `.htaccess` sends every non-file path to `view.php`, which prints the right `<title>`, description and Open Graph / Twitter tags, then boots the app.
- **In the app:** names, avatars (`[data-author]`) and poll titles (`[data-open]`) are real `<a href>` links (`a.lp-link`), so Ctrl/Cmd-click, middle-click and *Copy link address* work.

## New users (onboarding)

Most visitors come from **LinkedIn, Reddit and Quora**, usually through a shared poll link. They can read and vote first and sign up later.

| Moment | What the visitor sees | Code |
|---|---|---|
| First visit (guest) | Dismissible **"New to LinkPoll?"** card with **Join free** and **How it works** | `.lp-welcome` |
| Arriving from another site | "👋 Welcome from LinkedIn! Tap an option to vote — no sign-up needed." | `.lp-arrive` |
| After a guest's first vote | "**Vote counted!**" card with **Create free account** / **Keep browsing** | `.lp-after` |
| Guest menu | Member-only items replaced by one "Join LinkPoll" prompt | `body.lp-guest` |
| First days as a member | **"Get started on LinkPoll"** checklist with progress bar (compact next-step view on phones) | `.lp-check` |

## Options

**Poll menu (⋯):** Copy link · Copy as post · Share on LinkedIn / Reddit / WhatsApp · Download results image · Open in new tab · View profile · Follow · Hide posts from @user (with Undo) · Not interested · Report.

**Create Poll:** "Need an idea?" example questions, Quick answers (Yes/No, Agree/Disagree, Rate 1–5, …), image per option, then a **"Your poll is live!"** share sheet.

## SEO

Every URL is rendered by `view.php` + `seo.php` before the app boots, so crawlers get real HTML with one H1, a unique title and description, canonical, Open Graph tags and JSON-LD (Organization, WebSite, DiscussionForumPosting for polls, ProfilePage, CollectionPage, BreadcrumbList). Private pages are `noindex`. Demo accounts (`@demo.linkpoll`) are kept out of search (set `LP_SEO_INDEX_DEMO` in `seo.php` to change). `sitemap.php` lists indexable URLs and updates automatically; each subdomain serves its own `/sitemap.xml`.

---

## 6. Code map

| Path (this repo → server home folder) | What it is |
|---|---|
| `public/index.html` | App shell; loads `app.css?v=N` and `app.js?v=N` (bump N on every deploy) |
| `public/app.js` | Single-page app: routing (`route`), `navList()` + `NAV_GROUPS` + `NAV_ALIAS`, all page renderers; appended layers at the end (motion, loading, links, onboarding, options, growth, sites, design system v3) |
| `public/app.css` | All styles; appended layers at the end, last is *design system v3* |
| `public/api.php` | JSON API (`?path=/...`). Read-only GETs release the session lock early. |
| `public/config.sample.php` | Template for `config.php`: DB connection, mail, Google sign-in, schema/migrations, helpers |
| `public/view.php` | Server-rendered HTML for every URL (titles, meta, OG tags) — boots the app |
| `public/seo.php` | SEO module: robots rules, crawlable content, JSON-LD, demo-content switch |
| `public/sitemap.php` | XML sitemap of indexable URLs |
| `public/og.php` | 1200×630 share and results images (GD) |
| `public/consent.js` | Cookie consent for all LinkPoll sites |
| `lp-site/` | Engine and content for help., about., docs. and status. subdomains |

### Deploy checklist
1. Back up the files you touch.
2. Make the change; run `php -l` on any PHP file.
3. Bump `app.js?v=` / `app.css?v=` in **both** `index.html` and `view.php`.
4. Check: home loads, a poll page loads, no `error_log` appears, browser console has no errors.

### Performance notes
- `init_schema()` runs only when `config.php` changes (stamp file in the system temp dir).
- GET API requests (except `/auth/*`) call `session_write_close()` so parallel requests don't queue.
- Indexes: `votes(user_id,poll_id)`, `votes(option_id)`, `follows(following_id)`, `comments(user_id)`, `likes(poll_id)`, `saves(poll_id)`.

---

## 7. Do / Don't

- ✅ Add a new page → give it **one** place in `navList()` (or reach it from its parent page) and an alias if it's a sub-page.
- ✅ New right-column card → it must relate to what's in the centre, and go below Trending/Live.
- ❌ Don't use lime for anything other than the main action.
- ❌ Don't hard-code colours, radii or shadows — use the tokens.

## Sites (subdomains)

| Site | What | Router |
|---|---|---|
| help.link-poll.com | Help Center: 7 topics, 28 articles, search, contact | `lp-site/sites/help.php` + `content/help.php` |
| about.link-poll.com | About, Policies (Privacy, Terms, Community Guidelines, Enforcement, Cookies, Copyright), Brand, Contact | `sites/about.php` + `content/policies.php` |
| docs.link-poll.com | Developers: embeds, links/UTM, share cards | `sites/docs.php` |
| status.link-poll.com | Live status, 90-day uptime bars, incident history, `/status.json` | `sites/status.php` + `status_check.php` (cron, every 5 min) |

- **Edit content:** articles are entries in `content/help.php` (`cat` must match a topic key). Policies are in `content/policies.php` — change `$UPD` when the text changes.
- Main site: `/help`, `/about`, `/privacy`, `/terms`, `/guidelines`, `/cookies`, `/contact`, `/status`, `/docs`, `/developers` redirect (301) to the subdomains; those words are reserved for usernames at sign-up.
- **Cookie consent:** Google Consent Mode v2 + Clarity `consentv2`. EU/UK/CH visitors are asked first; everyone can use "Cookie settings". Choice stored in `lp_consent` on `.link-poll.com` for 1 year.

## Design system v3 (Nov 2026)

**Tokens (from the logo):** lime `#CAFC3C` (hover `#B4EA12`, tint `#F1FDD0`), ink `#0F0F0D`. Radii 6 / 10 / 14 / 20 / pill. Motion 160 ms, `cubic-bezier(.2,.7,.2,1)`.

| Issue | Fix |
|---|---|
| 40 font sizes | One 14-step scale (420 declarations snapped) |
| Settings rows stacked icon / label / arrow | `button > a.lp-link { display: contents }` |
| AI button covered content on phones | Icon-only, above the tab bar, hidden on detail screens, while typing and while scrolling down |
| Two Create Poll buttons on desktop | Header one only |
| Initials avatars unreadable on some colours | Text colour chosen by contrast |
| Poll answers had no affordance | Radio dot, hover tint, pressed state, 48 px rows; 40 px actions with tooltips; ⋯ menu icon |
| Two onboarding cards stacked | Checklist only; compact on phones |
| Contrast failures | All text ≥ 4.5:1 |
