<?php
/* LinkPoll Help Center content. Add an article: copy one block, give it a new slug.
   'cat' must match a key in $HELP_CATS. Bodies are trusted HTML written by the team. */

$APP = 'https://link-poll.com';
$POL = 'https://about.link-poll.com/policies';

$HELP_CATS = [
  'getting-started' => ['🚀', 'Getting started', 'Create an account, find your way around and post your first poll.'],
  'polls' => ['📊', 'Polls & voting', 'Poll types, settings, editing, results and sharing.'],
  'quizzes' => ['🎓', 'Quizzes & mock papers', 'Test knowledge and run mock exams for students.'],
  'communities' => ['👥', 'Communities & discussions', 'Join spaces around a topic and ask the community.'],
  'creators' => ['✨', 'Grow your audience', 'Share polls where your audience is: LinkedIn, Reddit, WhatsApp and more.'],
  'account' => ['⚙️', 'Account & settings', 'Profile, sign-in, notifications, privacy and deleting your account.'],
  'safety' => ['🛡️', 'Safety & reporting', 'Report, block, and how we handle rule breaking.'],
];

$HELP = [

/* ---------------- Getting started ---------------- */
'what-is-linkpoll' => ['cat' => 'getting-started', 'title' => 'What is LinkPoll?', 'pop' => 1,
'desc' => 'LinkPoll is a social network built around polls: ask a question, people vote, and you see the results live.',
'body' => <<<'HTML'
<p>LinkPoll is a social network built around polls. Instead of writing a long post, you ask a question, add a few answers, and people vote. Results update live, so you see what people really think in seconds.</p>
<h2>What you can do</h2>
<ul>
<li><b>Create polls</b> — simple, yes/no, image, rating, this-or-that, quizzes and surveys.</li>
<li><b>Vote and comment</b> on polls from people you follow, communities and the Explore page.</li>
<li><b>Join communities</b> around a topic, and ask or answer questions in <b>Discussions</b>.</li>
<li><b>Run quizzes and mock papers</b> — great for teachers and exam prep.</li>
<li><b>Share anywhere</b> — every poll has its own link and a preview image that looks good on LinkedIn, WhatsApp, Reddit, X and Facebook.</li>
</ul>
<p>LinkPoll is free. People can vote on a shared poll without an account.</p>
HTML],

'create-account' => ['cat' => 'getting-started', 'title' => 'Create an account', 'pop' => 1,
'desc' => 'Sign up with your email or with Google in under a minute.',
'body' => <<<'HTML'
<ol class="lp-steps">
<li>Open <a href="https://link-poll.com">link-poll.com</a> and choose <b>Join free</b> (or <b>Sign up</b>).</li>
<li>Continue with <b>Google</b>, or enter your name, a username, your email and a password.</li>
<li>If you used email, enter the code we send you to confirm your address. Check spam or promotions if it doesn’t arrive within a few minutes, or ask for a new code.</li>
<li>Pick a few topics and people to follow, so your Home feed has polls you care about.</li>
</ol>
<div class="lp-note">You need to be at least 13 years old to create an account. See our <a href="https://about.link-poll.com/policies/terms">Terms of Service</a>.</div>
<h2>Choosing a username</h2>
<p>Your username becomes your profile address, for example <code>link-poll.com/yourname</code>. Some words are reserved for LinkPoll pages (like <i>help</i>, <i>about</i> or <i>privacy</i>), so you may need to try another.</p>
HTML],

'find-your-way' => ['cat' => 'getting-started', 'title' => 'Find your way around', 'pop' => 0,
'desc' => 'What each part of LinkPoll is for: Home, Explore, Discussions, Communities and more.',
'body' => <<<'HTML'
<table><thead><tr><th>Where</th><th>What it’s for</th></tr></thead><tbody>
<tr><td><b>Home</b></td><td>Polls from people and communities you follow.</td></tr>
<tr><td><b>Explore</b></td><td>Trending polls, topics and people to follow.</td></tr>
<tr><td><b>Discussions</b></td><td>Ask a question and get written answers from the community.</td></tr>
<tr><td><b>Communities</b></td><td>Spaces around a shared interest.</td></tr>
<tr><td><b>Quizzes &amp; Mock Papers</b></td><td>Test yourself, or run a test for students.</td></tr>
<tr><td><b>My Polls &amp; Insights</b></td><td>Your polls and how they’re doing.</td></tr>
<tr><td><b>Saved</b></td><td>Polls you bookmarked.</td></tr>
<tr><td><b>Network</b></td><td>Followers, following and suggestions.</td></tr>
<tr><td><b>Points &amp; badges</b></td><td>Your level, streak and the leaderboard.</td></tr>
<tr><td><b>Link in bio, Widgets, Reviews</b></td><td>Creator tools for sharing LinkPoll outside the app.</td></tr>
</tbody></table>
<p>On a phone these are in the bottom bar and the menu. You can also install LinkPoll from your browser’s menu (“Add to Home screen” or “Install app”).</p>
HTML],

'vote-without-account' => ['cat' => 'getting-started', 'title' => 'Vote without an account', 'pop' => 1,
'desc' => 'Anyone with a poll link can vote. An account lets you post, comment and follow.',
'body' => <<<'HTML'
<p>If someone shares a LinkPoll link with you, you can open it and vote straight away — no sign-up needed. You’ll see the results once your vote is counted (unless the creator chose to hide results).</p>
<h2>Why create an account?</h2>
<ul><li>Post your own polls and quizzes.</li><li>Comment, follow people and join communities.</li><li>Keep your votes, saved polls and points in one place.</li></ul>
<p>To stop people voting twice, we remember guest votes with a cookie on your device. Clearing your browser data resets this, so a poll’s creator may see an occasional duplicate guest vote.</p>
HTML],

/* ---------------- Polls ---------------- */
'create-a-poll' => ['cat' => 'polls', 'title' => 'Create a poll', 'pop' => 1,
'desc' => 'Write a question, add answers, choose the poll type and settings, then post and share.',
'body' => <<<'HTML'
<ol class="lp-steps">
<li>Select <b>Create Poll</b>.</li>
<li>Type your question. Short, specific questions get the most votes. Stuck? Use <b>Need an idea?</b> for suggestions.</li>
<li>Add at least two answers, or pick a ready-made set under <b>Quick answers</b> (like Yes / No or Agree / Disagree). You can add an image to an answer with the image button.</li>
<li>Choose where to post it: your followers or a community.</li>
<li>Check the settings (see below) and select <b>Post</b>.</li>
</ol>
<p>Right after posting you’ll see <b>Your poll is live!</b> with one-tap sharing to WhatsApp, LinkedIn, Reddit, X and Facebook, plus Copy link and Save image.</p>
<h2>Poll types</h2>
<p>Simple, yes/no, image, rating, this-or-that, quiz (with correct answers) and survey.</p>
<h2>Settings</h2>
<table><thead><tr><th>Setting</th><th>What it does</th></tr></thead><tbody>
<tr><td>Multiple choice</td><td>Voters can pick more than one answer.</td></tr>
<tr><td>Duration</td><td>The poll closes automatically after the time you choose.</td></tr>
<tr><td>Show results</td><td>Hide results until the poll closes, or show them after voting.</td></tr>
<tr><td>Anonymous</td><td>Nobody — including you — can see who voted for what.</td></tr>
<tr><td>Allow sharing</td><td>Turn off if you don’t want the poll shared outside LinkPoll.</td></tr>
</tbody></table>
HTML],

'edit-close-delete-poll' => ['cat' => 'polls', 'title' => 'Edit, close or delete a poll', 'pop' => 0,
'desc' => 'You can edit a poll for 5 minutes after posting. Close or delete it any time.',
'body' => <<<'HTML'
<h2>Edit</h2>
<p>You can fix a poll for <b>5 minutes</b> after posting — open the poll’s <b>⋯</b> menu and choose <b>Edit</b>. After that the poll is locked so votes can’t be changed by editing the question underneath them.</p>
<h2>Close</h2>
<p>Open the <b>⋯</b> menu and choose <b>Close poll</b>. Voting stops and the results stay visible.</p>
<h2>Delete</h2>
<p>Open the <b>⋯</b> menu and choose <b>Delete</b>. This removes the poll, its votes and comments. It can’t be undone.</p>
HTML],

'results-and-insights' => ['cat' => 'polls', 'title' => 'Results, insights and results images', 'pop' => 1,
'desc' => 'See live results, who your poll reached, and download a ready-to-post results image.',
'body' => <<<'HTML'
<p>Results update live as people vote. Open a poll to see the full breakdown.</p>
<h2>Insights</h2>
<p>Go to <b>Insights</b> to see how your polls are doing over time: votes, reach and your most popular polls.</p>
<h2>Download a results image</h2>
<p>Open the poll’s <b>⋯</b> menu and choose <b>Download results image</b>. On a phone this opens your share sheet so you can post it straight to LinkedIn, Instagram or WhatsApp. Results images are great for a follow-up post: “62% said hybrid — here’s what surprised me.”</p>
HTML],

'share-a-poll' => ['cat' => 'polls', 'title' => 'Share a poll', 'pop' => 1,
'desc' => 'Every poll has its own link and preview image. Share it anywhere, people can vote without signing up.',
'body' => <<<'HTML'
<p>Each poll has its own address, like <code>link-poll.com/p/AbC123</code>, and a preview image showing the question and answers — so it looks good wherever you share it.</p>
<h2>Ways to share</h2>
<ul>
<li>Right after posting, from <b>Your poll is live!</b></li>
<li>From the poll’s <b>⋯</b> menu: Share on WhatsApp, LinkedIn or Reddit, <b>Copy link</b>, or <b>Copy as post</b> (question, answers and link, ready to paste).</li>
<li>On your website, with an <a href="https://docs.link-poll.com/embeds">embedded poll</a>.</li>
</ul>
<p>See <a href="/a/share-on-linkedin">Share on LinkedIn</a> and <a href="/a/share-on-reddit">Share on Reddit</a> for tips that get more votes.</p>
HTML],

'save-and-mute' => ['cat' => 'polls', 'title' => 'Save polls and hide people’s posts', 'pop' => 0,
'desc' => 'Bookmark polls to find later, or hide posts from someone without blocking them.',
'body' => <<<'HTML'
<h2>Save</h2>
<p>Select the bookmark icon on any poll. Find everything you saved under <b>Saved</b>.</p>
<h2>Hide posts from someone</h2>
<p>Open a poll’s <b>⋯</b> menu and choose <b>Hide posts from @name</b>. You’ll stop seeing their polls in your feeds. They aren’t told. Use <b>Undo</b> straight away if you change your mind. To stop someone contacting you, <a href="/a/block-someone">block them</a> instead.</p>
HTML],

/* ---------------- Quizzes ---------------- */
'quizzes' => ['cat' => 'quizzes', 'title' => 'Create a quiz', 'pop' => 0,
'desc' => 'Quiz polls have a correct answer, so voters find out instantly whether they were right.',
'body' => <<<'HTML'
<ol class="lp-steps"><li>Select <b>Create Poll</b> and choose the <b>Quiz</b> type.</li><li>Add your question and answers, and mark the correct one.</li><li>Post it. Voters see whether they were right as soon as they answer, and earn points for correct answers.</li></ol>
<p>Find quizzes from others under <b>Quizzes</b>.</p>
HTML],

'mock-papers' => ['cat' => 'quizzes', 'title' => 'Run a mock paper for students', 'pop' => 1,
'desc' => 'Build a multiple-choice test, share one link, and see ranked results and per-question stats.',
'body' => <<<'HTML'
<p>Mock Papers let teachers and tutors build a multiple-choice test and share it with one link. Students don’t need an account to take it.</p>
<ol class="lp-steps"><li>Go to <b>Mock Papers</b> and create a new paper.</li><li>Add your questions, answers and the correct option for each.</li><li>Share the link with your class — in WhatsApp groups, Google Classroom or by email.</li><li>Students take the test and get their score instantly.</li><li>You see ranked results and stats for each question, so you know which topics need more work.</li></ol>
<p>Close the paper when the test is over so no more answers come in.</p>
HTML],

/* ---------------- Communities ---------------- */
'communities' => ['cat' => 'communities', 'title' => 'Join or create a community', 'pop' => 0,
'desc' => 'Communities are spaces around a shared interest. Some are open; private ones need approval.',
'body' => <<<'HTML'
<p>Communities bring people together around a topic — an exam, a profession, a city, a hobby. Members post polls to the community and vote together.</p>
<h2>Join</h2>
<p>Open <b>Communities</b>, pick one and select <b>Join</b>. Open communities let you in straight away. Private communities need the creator to approve your request.</p>
<h2>Create</h2>
<p>Select <b>Create community</b>, give it a name, a short description and choose whether it’s open or private. As the creator you approve join requests for private communities.</p>
<p>Communities can have their own rules, as long as they fit our <a href="https://about.link-poll.com/policies/community-guidelines">Community Guidelines</a>.</p>
HTML],

'discussions' => ['cat' => 'communities', 'title' => 'Ask and answer in Discussions', 'pop' => 0,
'desc' => 'Discussions are for questions that need written answers rather than a vote.',
'body' => <<<'HTML'
<p>Some questions need more than a vote. In <b>Discussions</b> you can ask a question and the community answers in writing — like a Q&amp;A.</p>
<ul><li>Ask a clear question and add context so people can help.</li><li>Answer from experience, and be kind — someone asked because they don’t know.</li><li>You can embed a discussion on your website from <b>Widgets</b>.</li></ul>
HTML],

'messages' => ['cat' => 'communities', 'title' => 'Messages and group chats', 'pop' => 0,
'desc' => 'Send private messages, accept message requests and chat in groups.',
'body' => <<<'HTML'
<p>Open <b>Messages</b> to chat one-to-one or in a group. You can share a poll into a chat.</p>
<h2>Message requests</h2>
<p>Messages from people you don’t know arrive as requests. You choose whether to accept. If someone bothers you, <a href="/a/block-someone">block</a> and <a href="/a/report-content">report</a> them.</p>
HTML],

/* ---------------- Creators ---------------- */
'share-on-linkedin' => ['cat' => 'creators', 'title' => 'Share on LinkedIn (and get more votes)', 'pop' => 1,
'desc' => 'Post the poll image natively, put the link in the first comment, and follow up with results.',
'body' => <<<'HTML'
<p>LinkedIn shows image posts to more people than posts with links. This approach works best:</p>
<ol class="lp-steps">
<li>Open your poll’s <b>⋯</b> menu and choose <b>Download results image</b> (or Save image from <b>Your poll is live!</b>).</li>
<li>Post the image on LinkedIn with your question in the text.</li>
<li>Add your LinkPoll link in the <b>first comment</b>: “Vote here 👇”.</li>
<li>Reply to comments in the first hour — it helps your post reach more people.</li>
<li>Two days later, post the results image with two or three lines on what you learned.</li>
</ol>
<div class="lp-note">Running a series (for example “Work in Pakistan 2027”, five polls over two weeks) builds an audience that comes back.</div>
HTML],

'share-on-reddit' => ['cat' => 'creators', 'title' => 'Share on Reddit and Quora', 'pop' => 0,
'desc' => 'Join the conversation first, share a poll only where it genuinely helps, and follow each community’s rules.',
'body' => <<<'HTML'
<ul>
<li><b>Contribute first.</b> Communities welcome people who take part, not drive-by links.</li>
<li><b>Check the rules.</b> Many subreddits limit links or polls — read the sidebar before posting.</li>
<li><b>Use Copy as post</b> from the poll’s <b>⋯</b> menu: it gives you the question, answers and link in one go.</li>
<li><b>On Quora</b>, answer with real data: “I asked 300 people — here’s what they said,” then link the poll.</li>
</ul>
HTML],

'link-in-bio-widgets' => ['cat' => 'creators', 'title' => 'Link in bio, widgets and reviews', 'pop' => 0,
'desc' => 'One link for your bio, plus embeddable polls, bio cards, discussions and a reviews badge.',
'body' => <<<'HTML'
<h2>Link in bio</h2>
<p>Build a single page with your links and latest polls, then put it in your Instagram, LinkedIn or TikTok bio.</p>
<h2>Widgets</h2>
<p>Embed a poll, a discussion, your bio card or your reviews badge on any website. Open <b>Widgets</b>, choose what to embed and copy the code. Developers can find details on <a href="https://docs.link-poll.com/embeds">docs.link-poll.com</a>.</p>
<h2>Reviews</h2>
<p>Collect star ratings and short reviews on your profile and show them with the reviews badge.</p>
HTML],

'points-badges' => ['cat' => 'creators', 'title' => 'Points, levels, streaks and badges', 'pop' => 0,
'desc' => 'Earn points for creating, voting and quizzes. Keep a daily streak and unlock badges.',
'body' => <<<'HTML'
<p>You earn points for creating polls, voting, getting votes on your polls, keeping a daily streak and completing quizzes. Points decide your level and your place on the leaderboard.</p>
<p><b>Streaks</b> count the days in a row you’re active. <b>Badges</b> are achievements — like your first poll — and you can turn any badge into a shareable image with <b>Celebrate</b> on your profile.</p>
HTML],

/* ---------------- Account ---------------- */
'edit-profile' => ['cat' => 'account', 'title' => 'Edit your profile', 'pop' => 0,
'desc' => 'Change your photo, name, bio, location, website and colour.',
'body' => <<<'HTML'
<p>Open <b>Settings → Edit profile</b>, or select your photo on your profile. You can change your photo, name, bio, location, website and profile colour. Your profile is public, so only add what you’re happy for anyone to see.</p>
HTML],

'sign-in-password' => ['cat' => 'account', 'title' => 'Sign-in problems and changing your password', 'pop' => 1,
'desc' => 'Reset a forgotten password, change it in Settings, or sign in with Google.',
'body' => <<<'HTML'
<h2>Forgot your password</h2>
<ol class="lp-steps"><li>On the sign-in screen choose <b>Forgot password?</b></li><li>Enter your email and we’ll send a code.</li><li>Enter the code and choose a new password.</li></ol>
<h2>Change your password</h2>
<p>Go to <b>Settings</b> and choose <b>Change password</b>.</p>
<h2>Signed up with Google?</h2>
<p>Use <b>Continue with Google</b> to sign in. You can also set a password with Forgot password, using the same email address.</p>
<h2>Still stuck?</h2>
<p>If you see “This account has been suspended”, read <a href="/a/warnings-restrictions-suspensions">Warnings, restrictions and suspensions</a>. Otherwise <a href="/contact">contact us</a> from the email on your account.</p>
HTML],

'appearance-notifications' => ['cat' => 'account', 'title' => 'Dark mode and notifications', 'pop' => 0,
'desc' => 'Switch between light and dark, and control what you’re notified about.',
'body' => <<<'HTML'
<p>Open <b>Settings → Appearance</b> to turn on dark mode, or let LinkPoll follow your device. Notification and media options are under <b>Settings → Preferences</b>.</p>
HTML],

'privacy-controls' => ['cat' => 'account', 'title' => 'Your privacy on LinkPoll', 'pop' => 0,
'desc' => 'What’s public, anonymous polls, cookies, and how to get a copy of your data.',
'body' => <<<'HTML'
<ul>
<li><b>Public:</b> your profile, polls, comments and answers can be seen by anyone, including people without an account and search engines.</li>
<li><b>Votes:</b> depending on a poll’s settings, other people may see that you voted and what you chose. On <b>anonymous</b> polls nobody can see who voted for what.</li>
<li><b>Private:</b> messages are only visible to the people in the conversation (and to our team if a message is reported).</li>
<li><b>Cookies:</b> change your choice any time with <b>Cookie settings</b> at the bottom of this page.</li>
<li><b>Your data:</b> email <a href="mailto:info@link-poll.com?subject=Data%20request">info@link-poll.com</a> from your account’s email to ask for a copy or a correction.</li>
</ul>
<p>Read the full <a href="https://about.link-poll.com/policies/privacy">Privacy Policy</a>.</p>
HTML],

'delete-account' => ['cat' => 'account', 'title' => 'Delete your account', 'pop' => 0,
'desc' => 'Deleting removes your profile and content. It can’t be undone.',
'body' => <<<'HTML'
<ol class="lp-steps"><li>Go to <b>Settings</b>.</li><li>Choose <b>Delete account</b> and confirm.</li></ol>
<p>Your profile, polls, votes, comments and messages are removed from LinkPoll. This can’t be undone. Some records may stay in backups for a short time, or where we must keep them for safety or legal reasons — see the <a href="https://about.link-poll.com/policies/privacy#retention">Privacy Policy</a>.</p>
HTML],

/* ---------------- Safety ---------------- */
'report-content' => ['cat' => 'safety', 'title' => 'Report a poll, comment or person', 'pop' => 1,
'desc' => 'Reports are private. Our team reviews them against the Community Guidelines.',
'body' => <<<'HTML'
<ol class="lp-steps"><li>Open the <b>⋯</b> menu on the poll, comment or profile.</li><li>Choose <b>Report</b> and pick the reason that fits best.</li><li>Add details if you can — it helps us act faster.</li></ol>
<p>The person isn’t told who reported them. We review reports against our <a href="https://about.link-poll.com/policies/community-guidelines">Community Guidelines</a> and may remove content, warn, restrict or suspend accounts.</p>
<div class="lp-note">If someone is in immediate danger, contact your local emergency services first.</div>
HTML],

'block-someone' => ['cat' => 'safety', 'title' => 'Block someone', 'pop' => 0,
'desc' => 'Blocking stops someone from messaging you, without telling them.',
'body' => <<<'HTML'
<p>Open their profile, select <b>⋯</b> and choose <b>Block</b>. They can’t message you, and you won’t see their activity. They aren’t notified. Unblock them the same way.</p>
<p>Just want to see less of someone? <a href="/a/save-and-mute">Hide their posts</a> instead.</p>
HTML],

'doing-that-too-fast' => ['cat' => 'safety', 'title' => '“You’re doing that too fast” and other limits', 'pop' => 1,
'desc' => 'Hourly limits and link rules stop spam. Here’s what they are and what to do.',
'body' => <<<'HTML'
<p>To stop spam and vote-rigging, LinkPoll limits how many times an account can post, vote, comment, message, follow, like or report in an hour. Almost nobody reaches these limits by normal use.</p>
<h2>If you see a limit message</h2>
<ul><li><b>“You’re doing that too fast”</b> — wait a little and try again.</li>
<li><b>“New accounts can’t post links yet”</b> — new accounts can’t put links in comments or messages for their first day.</li>
<li><b>“Too many links in one message”</b> — use fewer links.</li></ul>
<p>New accounts have lower limits for their first day. If you think a limit is stopping normal use, <a href="/contact">let us know</a>.</p>
HTML],

'warnings-restrictions-suspensions' => ['cat' => 'safety', 'title' => 'Warnings, restrictions and suspensions', 'pop' => 0,
'desc' => 'How we act on rule breaking, what each step means, and how to appeal.',
'body' => <<<'HTML'
<table><thead><tr><th>Action</th><th>What it means</th></tr></thead><tbody>
<tr><td><b>Content removed</b></td><td>A poll, comment or message broke the rules and was taken down.</td></tr>
<tr><td><b>Warning</b></td><td>A note on your account, often sent by email. Repeated warnings lead to stronger action.</td></tr>
<tr><td><b>Restriction</b></td><td>You can still sign in and browse, but one thing — like messaging or posting — is switched off for a time. You’ll see a message explaining it.</td></tr>
<tr><td><b>Suspension</b></td><td>You can’t sign in, for a set time or permanently.</td></tr>
</tbody></table>
<h2>Appeal</h2>
<p>If you think we got it wrong, email <a href="mailto:info@link-poll.com?subject=Appeal">info@link-poll.com</a> from your account’s email with “Appeal” in the subject, or send a note from <b>Feedback</b>. A different team member reviews it.</p>
<p>More detail: <a href="https://about.link-poll.com/policies/enforcement">How we enforce our rules</a>.</p>
HTML],

'spot-fake-polls' => ['cat' => 'safety', 'title' => 'Fake accounts and rigged polls', 'pop' => 0,
'desc' => 'What we do about vote manipulation, and how you can help.',
'body' => <<<'HTML'
<p>Polls only mean something if the votes are real. We watch for signs of rigging — sudden vote spikes, many accounts on one network, and accounts that hit speed limits — and act on them.</p>
<ul><li>Don’t create extra accounts to vote, and don’t buy or trade votes.</li><li>If a poll looks rigged, <a href="/a/report-content">report it</a>.</li></ul>
HTML],

];
