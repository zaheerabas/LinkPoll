<?php
/* LinkPoll policies. 'updated' is shown on the page; change it whenever the text changes. */
$UPD = '27 November 2026';

$POLICIES = [

'privacy' => ['title' => 'Privacy Policy', 'updated' => $UPD,
'desc' => 'What information LinkPoll collects, why, who we share it with, how long we keep it and your choices.',
'lead' => 'We collect what we need to run LinkPoll, keep it safe and make it better. We don’t sell your personal information and we don’t show ads.',
'body' => <<<'HTML'
<h2 id="who">1. Who we are</h2>
<p>LinkPoll (“LinkPoll”, “we”, “us”) runs the website and app at link-poll.com and its subdomains. Contact us about privacy at <a href="mailto:info@link-poll.com?subject=Privacy">info@link-poll.com</a>.</p>

<h2 id="collect">2. What we collect</h2>
<h3>Information you give us</h3>
<ul>
<li><b>Account:</b> name, username, email address and password (stored only as a secure hash). If you sign in with Google, we receive your name, email address and profile picture from Google.</li>
<li><b>Profile:</b> photo, bio, location, website and colour — whatever you choose to add.</li>
<li><b>Content:</b> polls, answers, images, votes, comments, discussions, reviews, quizzes and mock papers, stories, links and messages.</li>
<li><b>Activity:</b> who you follow, communities you join, polls you like or save, people you block and reports you send.</li>
<li><b>Messages to us:</b> feedback, support emails and appeals.</li>
</ul>
<h3>Information collected automatically</h3>
<ul>
<li><b>Network and device:</b> for signed-in members we record the IP address and browser type used, with first and last seen times. We use this to keep accounts secure and to detect fake or duplicate accounts.</li>
<li><b>Action log:</b> a record of actions like posting, voting, commenting, messaging and following, with the time. We use it to apply speed limits and stop spam and vote-rigging. Entries are deleted after 30 days.</li>
<li><b>Guest votes:</b> if you vote without an account, we store a random identifier in a cookie on your device so the same device can’t vote twice.</li>
<li><b>Analytics:</b> pages viewed, how you arrived, device type, approximate location (country/city) and how you use pages, through Google Analytics and Microsoft Clarity. Clarity can record interactions (clicks, scrolls, mouse movement) to help us find broken or confusing pages; it hides text you type into forms. See <a href="#cookies">Cookies</a> to control this.</li>
<li><b>Server logs:</b> our hosting provider keeps standard web server logs (IP address, page requested, time, browser) for security and troubleshooting.</li>
</ul>

<h2 id="use">3. How we use it</h2>
<table><thead><tr><th>Purpose</th><th>Examples</th><th>Legal basis (EU/UK)</th></tr></thead><tbody>
<tr><td>Run LinkPoll</td><td>Create your account, show your polls, count votes, deliver messages and notifications</td><td>Contract</td></tr>
<tr><td>Keep LinkPoll safe</td><td>Speed limits, spam and fake-account detection, reviewing reports, enforcing our rules</td><td>Legitimate interests</td></tr>
<tr><td>Improve LinkPoll</td><td>Understand which features are used and fix problems</td><td>Consent (analytics cookies) or legitimate interests</td></tr>
<tr><td>Talk to you</td><td>Verification codes, password resets, important account and safety notices, replies to your messages</td><td>Contract / legitimate interests</td></tr>
<tr><td>Legal</td><td>Respond to lawful requests and protect our rights</td><td>Legal obligation / legitimate interests</td></tr>
</tbody></table>

<h2 id="public">4. What other people can see</h2>
<ul>
<li>Your profile, polls, comments, discussions and reviews are <b>public</b> — anyone can see them, including people without an account and search engines.</li>
<li>Depending on a poll’s settings, other people may see that you voted and what you chose. On <b>anonymous</b> polls nobody can see who voted for what.</li>
<li>Messages are visible only to people in the conversation. If a message is reported, our team can review it.</li>
<li>Polls and profiles you embed or share elsewhere are visible wherever you put them.</li>
</ul>

<h2 id="share">5. Who we share it with</h2>
<p>We don’t sell personal information and we don’t share it for advertising. We share it only with:</p>
<ul>
<li><b>Service providers</b> who help us run LinkPoll: our web hosting provider (servers, database and email delivery), Google (Analytics, Google sign-in and web fonts), Microsoft (Clarity) and UptimeRobot (checks that the site is up; it doesn’t receive member data). They may only use the information to provide their service to us.</li>
<li><b>Authorities</b> when the law requires it, or to protect someone from serious harm.</li>
<li><b>A new owner</b> if LinkPoll is ever sold or merged — this policy would continue to apply to your information.</li>
</ul>

<h2 id="cookies">6. Cookies and similar technology</h2>
<p>We use essential cookies to keep you signed in, and — with your OK where required — analytics cookies from Google Analytics and Microsoft Clarity. Visitors in the European Economic Area, the UK and Switzerland are asked before analytics cookies are used. Anyone can change their choice with <b>Cookie settings</b> at the bottom of every page. Details are in our <a href="/policies/cookies">Cookie Notice</a>.</p>

<h2 id="retention">7. How long we keep it</h2>
<ul>
<li><b>Account and content:</b> while your account exists. When you delete your account we delete your profile and content from LinkPoll.</li>
<li><b>Action log:</b> 30 days.</li>
<li><b>Network and device records:</b> while your account exists, so we can protect it.</li>
<li><b>Safety records</b> (reports, warnings, restrictions and suspensions): as long as needed to keep LinkPoll safe — for example, to stop a banned person returning.</li>
<li><b>Analytics:</b> Google Analytics keeps event data for up to 14 months; Clarity keeps recordings for a limited time set by Microsoft.</li>
<li><b>Backups:</b> deleted information may remain in backups for a short time before it’s overwritten.</li>
</ul>

<h2 id="rights">8. Your choices and rights</h2>
<ul>
<li>Edit your profile and content, or delete them, at any time.</li>
<li>Delete your account from <b>Settings</b>.</li>
<li>Change your cookie choice with <b>Cookie settings</b>.</li>
<li>Ask for a copy of your information, a correction, or for us to stop or limit some uses, by emailing <a href="mailto:info@link-poll.com?subject=Data%20request">info@link-poll.com</a> from your account’s email. We’ll reply within 30 days.</li>
</ul>
<p>Depending on where you live you may have further rights, including the right to complain to your data protection authority.</p>

<h2 id="security">9. Security</h2>
<p>We use HTTPS everywhere, store passwords only as secure hashes, limit who on our team can see personal information, and log what our team does in the admin tools. No system is perfectly secure, so please use a strong, unique password.</p>

<h2 id="children">10. Children</h2>
<p>LinkPoll isn’t for children under 13. If you believe a child under 13 has an account, contact us and we’ll remove it. Students under 13 can take a mock paper through a link without creating an account.</p>

<h2 id="transfers">11. International transfers</h2>
<p>Our servers and service providers may be in a different country from yours. When we transfer information we take steps to protect it as this policy describes.</p>

<h2 id="changes">12. Changes</h2>
<p>We’ll update the date at the top when this policy changes, and tell members about important changes in the app or by email.</p>
HTML],

'terms' => ['title' => 'Terms of Service', 'updated' => $UPD,
'desc' => 'The agreement between you and LinkPoll when you use link-poll.com.',
'lead' => 'These terms are the rules for using LinkPoll. We’ve tried to keep them short and clear. By using LinkPoll you agree to them.',
'body' => <<<'HTML'
<h2 id="using">1. Using LinkPoll</h2>
<ul>
<li>You must be at least 13 years old, and old enough to agree to these terms where you live.</li>
<li>Give accurate information when you sign up, keep your password safe, and tell us if your account is used without permission. You’re responsible for what happens on your account.</li>
<li>One person, one account for voting. Extra accounts used to vote or get around our rules aren’t allowed.</li>
</ul>

<h2 id="content">2. Your content</h2>
<ul>
<li>You own what you post. You give LinkPoll a worldwide, non-exclusive, royalty-free licence to host, show, adapt (for example, resize images or create share images and embeds) and share your content so we can run and promote LinkPoll. The licence ends when you delete the content or your account, except where others have already shared or embedded it, or we must keep it for safety or legal reasons.</li>
<li>Only post content you have the right to post. Polls, comments and profiles are public.</li>
<li>Votes and results are opinions of the people who voted. They aren’t scientific surveys — don’t present them as if they were.</li>
</ul>

<h2 id="rules">3. Rules</h2>
<p>Follow our <a href="/policies/community-guidelines">Community Guidelines</a>. In particular, don’t:</p>
<ul>
<li>rig votes, use bots or scripts, or buy, sell or trade votes, followers or accounts;</li>
<li>harass, threaten or impersonate anyone, or post illegal content;</li>
<li>send spam or misleading links, or collect other members’ personal information;</li>
<li>try to break, overload or get around the security or limits of LinkPoll.</li>
</ul>

<h2 id="enforcement">4. What we can do</h2>
<p>To keep LinkPoll safe we may remove content, limit features, warn, restrict or suspend accounts, or close them, as described in <a href="/policies/enforcement">How we enforce our rules</a>. We try to be fair and proportionate, and you can appeal.</p>

<h2 id="teachers">5. Quizzes and mock papers</h2>
<p>If you run a mock paper for students, you’re responsible for its content and for sharing the link only with the people it’s meant for. Scores are for practice and aren’t an official assessment.</p>

<h2 id="service">6. The service</h2>
<p>We’re always improving LinkPoll, so features may change, and occasionally we may need to pause the service for maintenance. LinkPoll is free; if we ever add paid features, we’ll tell you the price before you pay.</p>

<h2 id="ip">7. LinkPoll’s name and code</h2>
<p>The LinkPoll name, logo and software belong to LinkPoll. You can use our name and logo to link to LinkPoll as described in our <a href="/brand">brand guidelines</a>. Don’t copy or reverse-engineer the service.</p>

<h2 id="disclaimers">8. Disclaimers</h2>
<p>LinkPoll is provided “as is”. Content is posted by members, not by us, and we don’t promise it’s accurate. We don’t promise LinkPoll will always be available or error-free.</p>

<h2 id="liability">9. Liability</h2>
<p>To the extent the law allows, LinkPoll isn’t liable for indirect or consequential losses, or for content posted by others. Nothing in these terms limits rights you have under the law that can’t be limited.</p>

<h2 id="ending">10. Ending your account</h2>
<p>You can delete your account at any time from Settings. We may close accounts that seriously or repeatedly break these terms.</p>

<h2 id="changes">11. Changes</h2>
<p>We’ll update the date at the top when these terms change, and tell members about important changes before they take effect. If you keep using LinkPoll after that, the new terms apply.</p>

<h2 id="contact">12. Contact</h2>
<p>Questions? Email <a href="mailto:info@link-poll.com?subject=Terms">info@link-poll.com</a>.</p>
HTML],

'community-guidelines' => ['title' => 'Community Guidelines', 'updated' => $UPD,
'desc' => 'The rules that keep LinkPoll worth being on: be respectful, keep polls honest, no spam.',
'lead' => 'LinkPoll works when people can ask honest questions and get honest answers. These rules apply everywhere on LinkPoll — polls, comments, discussions, messages, profiles and communities.',
'body' => <<<'HTML'
<h2 id="r1">1. Respect people</h2>
<p>Disagree with ideas, not people. No harassment, bullying, threats, or attacks on people for who they are — including race, ethnicity, religion, caste, nationality, gender, sexual orientation, disability or age.</p>

<h2 id="r2">2. Keep polls honest</h2>
<ul><li>One person, one vote. Don’t use extra accounts, bots or scripts to vote.</li><li>Don’t buy, sell or trade votes or followers, or ask groups to vote a particular way to fake a result.</li><li>Don’t present results as something they’re not — a LinkPoll poll isn’t a scientific survey.</li></ul>

<h2 id="r3">3. No spam</h2>
<p>Don’t post the same thing again and again, flood comments or messages, or use misleading links. Share your own links in moderation and where they’re relevant.</p>

<h2 id="r4">4. Respect privacy</h2>
<p>Don’t post someone’s private information — addresses, phone numbers, ID or financial details, private photos or messages — without their permission.</p>

<h2 id="r5">5. Be real</h2>
<p>Don’t pretend to be another person, brand or organisation in a way that misleads people. Parody and fan accounts must say so clearly.</p>

<h2 id="r6">6. Keep it safe and legal</h2>
<ul><li>No content that sexualises minors — we report it to the authorities.</li><li>No sexual content shared without consent, and no explicit sexual content.</li><li>No content that encourages violence, terrorism, self-harm or dangerous acts, or that sells illegal or regulated goods.</li><li>No scams, fraud or malware.</li></ul>

<h2 id="r7">7. Respect creators’ work</h2>
<p>Only post images and content you have the right to use. See <a href="/policies/copyright">Copyright</a>.</p>

<h2 id="r8">8. Communities can add rules</h2>
<p>Community creators can set extra rules for their community, as long as they fit these guidelines.</p>

<h2 id="breaking">If someone breaks the rules</h2>
<p><a href="https://help.link-poll.com/a/report-content">Report it</a>. Our team reviews reports and may remove content, warn, restrict or suspend accounts. Read <a href="/policies/enforcement">How we enforce our rules</a>.</p>
HTML],

'enforcement' => ['title' => 'How we enforce our rules', 'updated' => $UPD,
'desc' => 'Automatic limits, content removal, warnings, restrictions, suspensions and appeals on LinkPoll.',
'lead' => 'We want fewer rules and less enforcement — but when something goes wrong we act consistently, explain what happened and let you appeal.',
'body' => <<<'HTML'
<h2 id="how">How we find problems</h2>
<ul><li><b>Reports</b> from members.</li><li><b>Automatic signals:</b> speed limits, link rules, unusual voting patterns and accounts sharing a network with suspended accounts.</li><li><b>Our team</b> reviewing flagged activity.</li></ul>
<p>Automatic signals help us decide what to look at. A person on our team reviews them before any warning, restriction or suspension, except for the automatic limits below.</p>

<h2 id="automatic">Automatic limits</h2>
<p>Every account has hourly limits on posting, voting, commenting, messaging, following, liking and reporting, plus limits on links in comments and messages. New accounts have lower limits for their first day and can’t post links. When you hit a limit you see a message and can try again later. If an account hits limits repeatedly, that action may be switched off automatically for a time.</p>

<h2 id="actions">What we can do</h2>
<table><thead><tr><th>Action</th><th>When</th><th>What happens</th></tr></thead><tbody>
<tr><td>Remove content</td><td>A poll, comment or message breaks the rules</td><td>The content is taken down</td></tr>
<tr><td>Warning</td><td>A first or minor problem</td><td>Recorded on the account; usually emailed to you</td></tr>
<tr><td>Restriction</td><td>Problems with one kind of action (e.g. spam messages)</td><td>That action is switched off for a set time (up to 30 days, or until lifted for serious cases). You can still sign in and browse</td></tr>
<tr><td>Suspension</td><td>Serious or repeated problems</td><td>You can’t sign in, for a set time or permanently</td></tr>
</tbody></table>
<p>For the most serious harm — like content that sexualises minors or real threats of violence — we suspend permanently and may involve the authorities.</p>

<h2 id="appeals">Appeals</h2>
<p>If you think we made a mistake, email <a href="mailto:info@link-poll.com?subject=Appeal">info@link-poll.com</a> from your account’s email with “Appeal” in the subject, or use <b>Feedback</b> in the app. Tell us what happened and why you think the decision is wrong. A different member of our team reviews it, and we reply as soon as we can.</p>

<h2 id="team">Our team’s actions are logged</h2>
<p>Every moderation action records who took it, when and why, so decisions can be reviewed.</p>
HTML],

'cookies' => ['title' => 'Cookie Notice', 'updated' => $UPD,
'desc' => 'The cookies and browser storage LinkPoll uses, and how to change your choice.',
'lead' => 'Cookies are small files your browser stores for a website. LinkPoll uses as few as possible.',
'body' => <<<'HTML'
<h2 id="essential">Essential (always on)</h2>
<div class="lp-tw"><table><thead><tr><th>Name</th><th>Purpose</th><th>Lasts</th></tr></thead><tbody>
<tr><td><code>linkpoll_sess</code></td><td>Keeps you signed in and protects forms</td><td>30 days</td></tr>
<tr><td><code>lp_remember</code></td><td>Keeps you signed in between visits</td><td>60 days</td></tr>
<tr><td><code>lp_anon</code></td><td>Stops the same device voting twice when you vote without an account</td><td>1 year</td></tr>
<tr><td><code>lp_consent</code></td><td>Remembers your cookie choice</td><td>1 year</td></tr>
<tr><td>Browser storage (localStorage)</td><td>Choices on your device: theme, people whose posts you hid, getting-started progress and reminders</td><td>Until you clear it</td></tr>
</tbody></table></div>

<h2 id="analytics">Analytics (optional where the law requires consent)</h2>
<div class="lp-tw"><table><thead><tr><th>Provider</th><th>Cookies</th><th>Purpose</th><th>Lasts</th></tr></thead><tbody>
<tr><td>Google Analytics</td><td><code>_ga</code>, <code>_ga_*</code></td><td>Counts visits and shows how people find and use LinkPoll</td><td>Up to 2 years</td></tr>
<tr><td>Microsoft Clarity</td><td><code>_clck</code>, <code>_clsk</code></td><td>Heatmaps and session recordings to find broken or confusing pages</td><td>Up to 1 year</td></tr>
</tbody></table></div>
<p>We don’t use advertising cookies.</p>

<h2 id="choice">Your choice</h2>
<p>Visitors in the European Economic Area, the UK and Switzerland are asked before analytics cookies are used. Everyone can change their choice at any time:</p>
<p><button type="button" class="lp-btn pri" data-lp-consent>Open cookie settings</button></p>
<p>You can also block or delete cookies in your browser settings. Blocking essential cookies will stop you from signing in.</p>
HTML],

'copyright' => ['title' => 'Copyright', 'updated' => $UPD,
'desc' => 'How to report content on LinkPoll that uses your copyrighted work.',
'lead' => 'Only post images and content you have the right to use. If someone posted your work without permission, tell us and we’ll review it quickly.',
'body' => <<<'HTML'
<h2 id="report">Report copyright infringement</h2>
<p>Email <a href="mailto:info@link-poll.com?subject=Copyright%20notice">info@link-poll.com</a> with “Copyright notice” in the subject and include:</p>
<ol>
<li>Your name, address, phone number and email.</li>
<li>The work you own (or represent), and where the original can be found.</li>
<li>The link(s) to the content on LinkPoll.</li>
<li>A statement that you believe in good faith the use isn’t authorised by you, your agent or the law.</li>
<li>A statement that the information is accurate and that you are the owner or authorised to act for the owner.</li>
<li>Your signature (typing your full name is fine).</li>
</ol>
<p>We remove content when a notice is valid and tell the person who posted it. Accounts that repeatedly infringe may be suspended.</p>

<h2 id="counter">If your content was removed</h2>
<p>If you believe it was removed by mistake, or you have the right to use it, reply to our email with your explanation and we’ll review it.</p>
HTML],

];
