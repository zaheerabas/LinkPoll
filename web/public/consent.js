/* LinkPoll cookie consent — shared by link-poll.com and its subdomains.
   EU/UK/CH visitors (by time zone) are asked before analytics run; everyone
   else can change their choice from any "Cookie settings" link. The choice is
   stored for a year on .link-poll.com so it is asked only once. */
(function () {
  if (window.__lpConsent) return; window.__lpConsent = 1;
  var COOKIE = 'lp_consent', POLICY = 'https://about.link-poll.com/policies/cookies';
  function get() { var m = document.cookie.match(/(?:^|; )lp_consent=(granted|denied)/); return m ? m[1] : ''; }
  function set(v) {
    var dom = /(^|\.)link-poll\.com$/.test(location.hostname) ? '; domain=.link-poll.com' : '';
    document.cookie = COOKIE + '=' + v + '; max-age=31536000; path=/; SameSite=Lax; Secure' + dom;
    try { if (window.gtag) gtag('consent', 'update', {analytics_storage: v}); } catch (e) {}
    try { if (window.clarity) clarity('consentv2', {ad_Storage: 'denied', analytics_Storage: v}); } catch (e) {}
  }
  function inEU() {
    var tz = ''; try { tz = Intl.DateTimeFormat().resolvedOptions().timeZone || ''; } catch (e) {}
    return /^(Europe\/|Atlantic\/(Reykjavik|Canary|Madeira|Azores|Faroe))/.test(tz);
  }
  var css = '.lpc{position:fixed;z-index:2147483000;left:16px;right:16px;bottom:16px;max-width:560px;margin:0 auto;background:#0f0f0d;color:#f3f1ea;border-radius:16px;padding:18px 18px 16px;box-shadow:0 12px 40px rgba(0,0,0,.28);font:15px/1.5 "Hanken Grotesk",system-ui,-apple-system,sans-serif;animation:lpc-in .25s ease-out}' +
    '.lpc h2{font-size:16px;font-weight:700;margin:0 0 4px;color:#fff}.lpc p{margin:0;color:#d9d5c8}.lpc a{color:#fff;text-underline-offset:3px}' +
    '.lpc-b{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}.lpc button{min-height:44px;padding:0 16px;border-radius:12px;border:1px solid #3a382f;background:transparent;color:#fff;font-weight:600;font-size:15px;font-family:inherit;cursor:pointer;flex:1 1 140px}' +
    '.lpc button.y{background:#cafc3c;border-color:#cafc3c;color:#0f0f0d}.lpc button:focus-visible{outline:2px solid #cafc3c;outline-offset:2px}' +
    '@keyframes lpc-in{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}@media (prefers-reduced-motion:reduce){.lpc{animation:none}}';
  function show() {
    if (document.querySelector('.lpc')) return;
    if (!document.getElementById('lpc-css')) { var st = document.createElement('style'); st.id = 'lpc-css'; st.textContent = css; document.head.appendChild(st); }
    var cur = get();
    var w = document.createElement('div'); w.className = 'lpc'; w.setAttribute('role', 'dialog'); w.setAttribute('aria-labelledby', 'lpc-t'); w.setAttribute('aria-live', 'polite');
    w.innerHTML = '<h2 id="lpc-t">Cookies on LinkPoll</h2><p>We use essential cookies to keep you signed in. With your OK we also use Google Analytics and Microsoft Clarity to see how LinkPoll is used and fix problems. No ads, and we never sell your data. <a href="' + POLICY + '">Cookie Notice</a>' +
      (cur ? ' · Current choice: <b>' + (cur === 'granted' ? 'analytics allowed' : 'essential only') + '</b>' : '') + '</p>' +
      '<div class="lpc-b"><button type="button" data-v="denied">Essential only</button><button type="button" class="y" data-v="granted">Allow analytics</button></div>';
    w.addEventListener('click', function (e) { var b = e.target.closest('button[data-v]'); if (!b) return; set(b.getAttribute('data-v')); w.remove(); });
    document.body.appendChild(w);
    var y = w.querySelector('.y'); if (y && cur) y.focus();
  }
  window.lpCookieSettings = show;
  document.addEventListener('click', function (e) { var t = e.target.closest('[data-lp-consent]'); if (t) { e.preventDefault(); show(); } });
  function boot() { if (!get() && inEU()) show(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();
