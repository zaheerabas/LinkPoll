/* =====================================================================
   LinkPoll — app.js   (live version; talks to api.php / MySQL)
   ===================================================================== */
'use strict';
const API = '/api.php';

/* ---------- reactions ---------- */
const REACTIONS=[['like','👍','Like'],['love','❤️','Love'],['haha','😂','Haha'],['wow','😮','Wow'],['sad','😢','Sad'],['angry','😡','Angry']];
const REMOJI=Object.fromEntries(REACTIONS.map((r)=>[r[0],r[1]]));
const RLABEL=Object.fromEntries(REACTIONS.map((r)=>[r[0],r[2]]));
function reactionSummary(poll){ const c=poll.reactionCounts||{}; const keys=Object.keys(c).filter((k)=>c[k]>0).sort((a,b)=>c[b]-c[a]).slice(0,3); return keys.map((k)=>REMOJI[k]||'👍').join(''); }

/* ---------- icons ---------- */
const P = {
  home:'<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
  explore:'<circle cx="12" cy="12" r="10"/><polygon points="16.2 7.8 14.1 14.1 7.8 16.2 9.9 9.9"/>',
  following:'<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.1a4 4 0 0 1 0 7.75"/>',
  mypolls:'<path d="M21.2 15.9A10 10 0 1 1 8 2.8"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>',
  gear:'<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 0 1-4 0v-.1A1.7 1.7 0 0 0 9 19.4a1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 0 1 0-4h.1A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 0 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 0 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
  heart:'<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>',
  comment:'<path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z"/>',
  lines:'<line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/>',
  lines2:'<line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="13" y2="15"/>',
  arrow:'<line x1="5" y1="12" x2="19" y2="12"/><polyline points="13 6 19 12 13 18"/>',
  trash:'<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>',
  download:'<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
  paper:'<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="13" y2="17"/>',
  camera:'<path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/>',
  refresh:'<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>',
  more:'<circle cx="5" cy="12" r="1.7" fill="currentColor" stroke="none"/><circle cx="12" cy="12" r="1.7" fill="currentColor" stroke="none"/><circle cx="19" cy="12" r="1.7" fill="currentColor" stroke="none"/>',
  share:'<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.6" y1="13.5" x2="15.4" y2="17.5"/><line x1="15.4" y1="6.5" x2="8.6" y2="10.5"/>',
  bars:'<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
  plus:'<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
  search:'<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.7" y2="16.7"/>',
  signin:'<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>',
  clock:'<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
  rocket:'<path d="M4.5 16.5c-1.5 1.3-2 5-2 5s3.7-.5 5-2c.7-.8.7-2.1-.1-2.9a2.2 2.2 0 0 0-2.9 0z"/><path d="M12 15l-3-3a22 22 0 0 1 2-4A12.9 12.9 0 0 1 22 2c0 2.7-.8 7.5-6 11a22 22 0 0 1-4 2z"/><path d="M9 12H4s.6-3 2-4c1.6-1.1 5 0 5 0"/><path d="M12 15v5s3-.6 4-2c1.1-1.6 0-5 0-5"/>',
  eye:'<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
  out:'<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
  user:'<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
  info:'<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
  edit:'<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4z"/>',
  target:'<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.4" fill="currentColor"/>',
  compass:'<circle cx="12" cy="12" r="9"/><polygon points="16.2,7.8 10.8,10.8 7.8,16.2 13.2,13.2" fill="currentColor" stroke="none"/>',
  sparkle:'<path d="M12 3l1.6 4.9L18.5 9.5l-4.9 1.6L12 16l-1.6-4.9L5.5 9.5l4.9-1.6z"/><path d="M18 15l.7 2.1 2.1.7-2.1.7L18 20.7l-.7-2.2-2.1-.7 2.1-.7z"/>',
  shield:'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
  trophy:'<path d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0V4z"/><path d="M17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/>',
  users:'<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
  globe:'<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
  lockc:'<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
  flag:'<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>',
  link:'<path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/>',
  loc:'<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
  bookmark:'<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>',
  bell:'<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
  mail:'<path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="m22 6-10 7L2 6"/>',
  send:'<path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/>',
  lock:'<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
  saved:'<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>'
};
const ic = (n, sz = 20) => `<svg class="ico" width="${sz}" height="${sz}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${P[n]||''}</svg>`;
/* verified checkmark badge (shown next to names) */
const vbadge = (v) => v ? `<svg class="vbadge" width="15" height="15" viewBox="0 0 24 24" aria-label="Verified"><path fill="currentColor" d="M12 1.6l2.3 1.9 3 .1.9 2.9 2.4 1.8-.9 2.9.9 2.9-2.4 1.8-.9 2.9-3 .1L12 22.4l-2.3-1.9-3-.1-.9-2.9L3.4 15.7l.9-2.9-.9-2.9 2.4-1.8.9-2.9 3-.1z"/><path fill="#fff" d="M10.6 14.6l-2-2-1.2 1.2 3.2 3.2 5.4-5.4-1.2-1.2z"/></svg>` : '';

const CATEGORIES = ['Technology','Food','Lifestyle','Sports','Politics','Entertainment','Business','Science','Other'];
const DURATIONS = [['6 hours',6],['12 hours',12],['24 hours',24],['3 days',72],['1 week',168],['No limit',null]];
const COLORS = ['#15140f','#1f4d35','#3c2a4d','#3a1d1d','#2e2416','#142036'];

/* ---------- helpers ---------- */
const $  = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];
const esc = (s) => String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const initials = (s) => (s || '?').trim().split(/\s+/).map((w)=>w[0]).join('').slice(0,2).toUpperCase();
function fmt(n){ n=Number(n)||0; if(n>=1e6)return(n/1e6).toFixed(1).replace(/\.0$/,'')+'M'; if(n>=1e3)return(n/1e3).toFixed(1).replace(/\.0$/,'')+'K'; return''+n; }
function timeAgo(ts){ const s=Math.floor((Date.now()-ts)/1000); if(s<60)return Math.max(1,s)+'s ago'; const m=(s/60)|0; if(m<60)return m+'m ago'; const h=(m/60)|0; if(h<24)return h+'h ago'; const d=(h/24)|0; if(d<7)return d+'d ago'; return((d/7)|0)+'w ago'; }
function timeLeft(p){ if(p.closed)return{closed:true,text:'Closed'}; if(p.durationH==null)return{live:true,text:'Live'}; const rem=p.createdAt+p.durationH*3600e3-Date.now();
  if(rem<=0)return{closed:true,text:'Closed'}; const h=Math.floor(rem/3600e3);
  if(h>=24)return{text:Math.floor(h/24)+'d left'}; if(h>=1)return{text:h+'h left'}; return{text:Math.max(1,Math.floor(rem/60e3))+'m left'}; }
function toast(m){ const t=$('#toast'); t.textContent=m; t.classList.add('show'); clearTimeout(toast._t); toast._t=setTimeout(()=>t.classList.remove('show'),2200); }
function avatar(u, size='s34', link=false){ const col=(u&&u.color)||'#15140f';
  const inner=u&&u.avatar?`<img src="${esc(u.avatar)}" alt="" loading="lazy">`:esc(u?(u.initials||initials(u.name)):'?');
  if(u&&u.hasStory&&u.username){ return `<span class="av ${size} has-story" data-story="${esc(u.username)}" style="background:${col}">${inner}</span>`; }
  const cls=link?'av '+size+' av-link" data-author="'+esc(u.username):'av '+size;
  return `<span class="${cls}" style="background:${col}">${inner}</span>`; }

/* shrink an uploaded image to a small square-ish JPEG data URL so it fits in the DB */
function downscaleImage(dataUrl, max, cb){
  try{
    const img=new Image();
    img.onload=()=>{ try{ let w=img.width||max, h=img.height||max; const s=Math.min(max/w,max/h,1);
      const cw=Math.max(1,Math.round(w*s)), ch=Math.max(1,Math.round(h*s));
      const cv=document.createElement('canvas'); cv.width=cw; cv.height=ch;
      const ctx=cv.getContext('2d'); ctx.drawImage(img,0,0,cw,ch);
      cb(cv.toDataURL('image/jpeg',0.85)); }catch(e){ cb(dataUrl); } };
    img.onerror=()=>cb(dataUrl);
    img.src=dataUrl;
  }catch(e){ cb(dataUrl); }
}
/* hex SHA-256 (Gravatar accepts the SHA-256 of the lowercased email) */
async function sha256hex(str){
  const buf=await crypto.subtle.digest('SHA-256', new TextEncoder().encode(str));
  return [...new Uint8Array(buf)].map((b)=>b.toString(16).padStart(2,'0')).join('');
}
/* turn @username into a profile link inside already-escaped text */
function linkifyMentions(escaped){
  return escaped.replace(/@([A-Za-z0-9_]{2,30})/g,'<a class="mention" data-author="$1">@$1</a>');
}
function linkifyHashtags(escaped){
  return escaped.replace(/#([\p{L}0-9_]{1,50})/gu,(m,t)=>`<a class="tag" data-tag="${t.toLowerCase()}">#${t}</a>`);
}
/* lightweight @mention autocomplete for a textarea */
function attachMentions(ta){
  if(!ta || ta._mentions) return; ta._mentions=true;
  let pop=null, items=[], active=-1, token='', timer=null;
  const close=()=>{ if(pop){ pop.remove(); pop=null; } items=[]; active=-1; };
  const place=()=>{ if(!pop) return; const wrap=ta.closest('.cbox')||ta.parentElement;
    if(wrap && getComputedStyle(wrap).position==='static') wrap.style.position='relative';
    (wrap||ta.parentElement).appendChild(pop); };
  const render=()=>{ if(!pop) return;
    pop.innerHTML=items.map((u,i)=>`<button type="button" class="mrow ${i===active?'on':''}" data-pick="${esc(u.username)}">
      ${avatar(u,'s26',false)}<span><b>${esc(u.name)}</b> <i>@${esc(u.username)}</i></span></button>`).join('');
    $$('[data-pick]',pop).forEach((b)=>b.onmousedown=(e)=>{ e.preventDefault(); pick(b.dataset.pick); }); };
  const pick=(uname)=>{ const pos=ta.selectionStart, pre=ta.value.slice(0,pos), post=ta.value.slice(pos);
    const np=pre.replace(/@([A-Za-z0-9_]{0,30})$/,'@'+uname+' '); ta.value=np+post;
    const caret=np.length; ta.setSelectionRange(caret,caret); ta.focus(); close(); };
  ta.addEventListener('input',()=>{ const pos=ta.selectionStart, pre=ta.value.slice(0,pos);
    const m=pre.match(/@([A-Za-z0-9_]{1,30})$/); if(!m){ close(); return; }
    token=m[1]; clearTimeout(timer); timer=setTimeout(async()=>{
      try{ const {users}=await api('/search',{query:{q:token}});
        items=(users||[]).slice(0,6); active=-1;
        if(!items.length){ close(); return; }
        if(!pop){ pop=document.createElement('div'); pop.className='mention-pop'; place(); }
        render();
      }catch(e){ close(); }
    },160); });
  ta.addEventListener('keydown',(e)=>{ if(!pop) return;
    if(e.key==='ArrowDown'){ e.preventDefault(); active=(active+1)%items.length; render(); }
    else if(e.key==='ArrowUp'){ e.preventDefault(); active=(active-1+items.length)%items.length; render(); }
    else if(e.key==='Enter'&&active>=0){ e.preventDefault(); pick(items[active].username); }
    else if(e.key==='Escape'){ close(); } });
  ta.addEventListener('blur',()=>setTimeout(close,150));
}
/* followers / following list modal */
async function openUserList(username, type){
  closeMenus(); closeDrawer(); lockScroll();
  const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim" data-scrim><div class="modal" data-keepmenu style="max-width:440px">
    <div class="mhead"><h3>${type==='followers'?'Followers':'Following'}</h3><button class="close" data-x>✕</button></div>
    <div id="ulist" class="ulist"><div class="sk" style="height:54px;border-radius:12px;margin:6px 0"></div><div class="sk" style="height:54px;border-radius:12px;margin:6px 0"></div></div>
  </div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  try{
    const {users}=await api('/'+type,{query:{username}});
    const box=$('#ulist');
    box.innerHTML = users.length ? users.map((u)=>`<div class="urow">${avatar(u,'s40',true)}
        <div class="uwho" data-author="${esc(u.username)}"><b>${esc(u.name)}${vbadge(u.verified)}</b><span>@${esc(u.username)}</span></div>
        ${(ME&&!u.isMe)?`<button class="btn btn-sm ${u.isFollowing?'btn-ghost':'btn-dark'}" data-follow="${esc(u.username)}">${u.isFollowing?'Following':'Follow'}</button>`:''}
      </div>`).join('') : '<p class="empty" style="padding:26px">No one here yet.</p>';
  }catch(e){ const box=$('#ulist'); if(box) box.innerHTML=`<p class="empty" style="padding:20px">${esc(e.message)}</p>`; }
}
function closeMenus(){ $$('.menu').forEach((n)=>n.remove()); $$('.react-pop').forEach((n)=>n.remove()); }
document.addEventListener('click',(e)=>{ const s=e.target.closest('[data-story]'); if(s){ e.preventDefault(); e.stopPropagation(); openProfileStory(s.dataset.story); } }, true);
document.addEventListener('click',(e)=>{ if(!e.target.closest('[data-keepmenu]'))closeMenus(); });
/* ---------- clean URL routing (History API) ----------
   Internally the app still talks in "#/u/name" style routes; these helpers
   translate to and from the pretty public URLs (/name, /p/CODE, /c/slug). */
const LP_RESERVED=['p','c','t','u','b','r','faq','faqs','widgets','links','reviews','api','app','assets','static','admin','about','privacy','terms','help','support','discover',
  'feedback','updates','guidelines','cookies','changelog',
  'explore','quiz','quizzes','leaderboard','communities','community','saved','mypolls','notifications',
  'settings','messages','points','network','insights','following','followers','home','feed','poll','polls',
  'tag','tags','search','login','signin','signup','register','logout','sitemap','robots','index','view',
  'paper','papers',
  'embed','favicon','manifest','sw','icon','og-image','uploads','data','config','new','create','me','contact','status','docs','developers','rules'];
function hashToPath(h){
  h=String(h||'#/'); if(h[0]!=='#') return h;
  const seg=h.slice(1).split('/').filter(Boolean);
  if(!seg.length) return '/';
  if(seg[0]==='u'   && seg[1]) return '/'+seg.slice(1).join('/');
  if(seg[0]==='poll'&& seg[1]) return '/p/'+seg.slice(1).join('/');
  if(seg[0]==='tag' && seg[1]) return '/t/'+seg.slice(1).join('/');
  return '/'+seg.join('/');
}
function pathToParts(p){
  const seg=String(p||'/').split('?')[0].split('/').filter(Boolean);
  if(!seg.length) return [];
  const a=seg[0].toLowerCase();
  if(a==='p' && seg[1]) return ['poll'].concat(seg.slice(1));
  if(a==='t' && seg[1]) return ['tag'].concat(seg.slice(1));
  if(a==='u' && seg[1]) return ['u'].concat(seg.slice(1));
  if(LP_RESERVED.includes(a)) return seg;
  return ['u'].concat(seg);          // /zaheerabbas -> profile
}
function partsToPath(parts){ return hashToPath('#/'+parts.join('/')); }
const go = (h)=>{
  const path=hashToPath(h);
  if(location.pathname===path){ router(); return; }
  try{ history.pushState({},'',path); }catch(e){ location.href=path; return; }
  router();
};
const goPath = (p)=>go(p);

/* ---------- state ---------- */
let ME = null, route = 'home', feedTab = 'foryou', settingsTab = 'profile', query = '', UNREAD = 0, STATS = null, NOTIF_SEEN = 0, MSG_UNREAD = 0, MSG_SEEN = 0;
const POLLS = {};                                  // code -> poll (for action handlers)
const FAQS = {};                                   // id -> faq (for action handlers)
let FAQ_QUEUE=[], FAQ_CURSOR=null, FAQ_DONE=false, FAQ_SHOWN=new Set(), FAQ_NEXTAT=3;
function cachePolls(arr){ (arr||[]).forEach((p)=>POLLS[p.code]=p); }

/* =====================================================================
   API client
   ===================================================================== */
async function api(path, { method='GET', query=null, body=null } = {}){
  let url = API + '?path=' + encodeURIComponent(path);
  if (query) for (const [k,v] of Object.entries(query)) if (v!=null && v!=='') url += '&'+encodeURIComponent(k)+'='+encodeURIComponent(v);
  const o = { method, headers:{ 'X-Requested-With':'linkpoll' } };
  if (body){ o.headers['Content-Type']='application/json'; o.body=JSON.stringify(body); }
  const r = await fetch(url, o); let d = {}; try { d = await r.json(); } catch(e){}
  if (!r.ok) throw new Error(d.error || ('Error '+r.status));
  return d;
}

/* =====================================================================
   LinkPoll Assistant — chat panel (answers from live poll data)
   ===================================================================== */
let AI_HISTORY=[];
function aiMsgHTML(m){
  if(m.role==='typing') return `<div class="ai-msg bot"><div class="ai-bubble typing"><span></span><span></span><span></span></div></div>`;
  const polls=(m.polls&&m.polls.length)?`<div class="ai-polls">${m.polls.map((p)=>
    `<a class="ai-poll" href="#/poll/${esc(p.code)}"><span class="ai-poll-q">${esc(p.question)}</span><span class="ai-poll-v">${ic('bars',12)} ${fmt(p.votes)} vote${p.votes===1?'':'s'}</span></a>`).join('')}</div>`:'';
  const text=esc(m.text||'').replace(/\n/g,'<br>');
  return `<div class="ai-msg ${m.role}"><div class="ai-bubble">${text}</div>${polls}</div>`;
}
function aiRender(){
  const list=$('#ai-list'); if(!list) return;
  if(!AI_HISTORY.length){
    list.innerHTML=`<div class="ai-welcome">
      <div class="ai-ava">${ic('sparkle',26)}</div>
      <b>LinkPoll Assistant</b>
      <p>Ask me anything about the polls — I read the live data, so I always know what's new.</p></div>`;
  } else {
    list.innerHTML=AI_HISTORY.map(aiMsgHTML).join('');
  }
  // chips (suggestions) after the latest bot reply, or on the welcome screen
  const last=AI_HISTORY[AI_HISTORY.length-1];
  const chips=(last&&last.chips)||(!AI_HISTORY.length
    ?['🔥 Trending','🆕 Newest','⏳ Ending soon','🤔 Controversial','💡 Recommend a poll','🏆 Leaderboard']:null);
  if(chips){ list.insertAdjacentHTML('beforeend',
    `<div class="ai-chips">${chips.map((c)=>`<button class="ai-chip" data-aichip="${esc(c.replace(/^[^\w]+\s*/,''))}">${esc(c)}</button>`).join('')}</div>`); }
  list.scrollTop=list.scrollHeight;
}
async function aiSend(text){
  text=(text||'').trim(); if(!text) return;
  AI_HISTORY.push({role:'user',text});
  AI_HISTORY.push({role:'typing'});
  aiRender();
  const inp=$('#ai-input'); if(inp){ inp.value=''; inp.style.height='auto'; }
  try{
    const r=await api('/assistant',{method:'POST',body:{message:text}});
    AI_HISTORY.pop(); // remove typing
    AI_HISTORY.push({role:'bot',text:r.reply,polls:r.polls||[],chips:r.chips||null});
  }catch(e){
    AI_HISTORY.pop();
    AI_HISTORY.push({role:'bot',text:e.message||'Something went wrong — please try again.'});
  }
  aiRender();
}
function openAssistant(){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim ai-scrim" data-scrim data-keepmenu>
    <div class="sheet ai-sheet" data-keepmenu>
      <div class="sheet-grab"></div>
      <div class="ai-head"><div class="ai-head-l"><span class="ai-badge">${ic('sparkle',18)}</span>
        <div><b>LinkPoll Assistant</b><span class="ai-sub">Answers from live poll data</span></div></div>
        <div class="ai-head-r">
          <button class="ai-reset" id="ai-reset" aria-label="New chat" title="New chat">${ic('refresh',17)}</button>
          <button class="close" data-x aria-label="Close">✕</button></div></div>
      <div class="ai-list" id="ai-list"></div>
      <div class="ai-bar">
        <textarea id="ai-input" class="field" rows="1" placeholder="Ask about polls…"></textarea>
        <button class="ai-send" id="ai-send" aria-label="Send">${ic('send',18)}</button>
      </div></div></div>`;
  $('[data-x]',ov).onclick=closeOverlay;
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget) closeOverlay(); };
  const rst=$('#ai-reset',ov); if(rst) rst.onclick=()=>{ AI_HISTORY=[]; aiRender(); const i=$('#ai-input'); if(i) i.focus(); };
  aiRender();
  const list=$('#ai-list',ov);
  list.addEventListener('click',(e)=>{
    const chip=e.target.closest('[data-aichip]'); if(chip){ aiSend(chip.dataset.aichip); return; }
    const pl=e.target.closest('.ai-poll'); if(pl){ closeOverlay(); } // href handles navigation
  });
  const inp=$('#ai-input',ov);
  const grow=()=>{ inp.style.height='auto'; inp.style.height=Math.min(inp.scrollHeight,110)+'px'; };
  inp.addEventListener('input',grow);
  inp.addEventListener('keydown',(e)=>{ if(e.key==='Enter'&&!e.shiftKey){ e.preventDefault(); aiSend(inp.value); } });
  $('#ai-send',ov).onclick=()=>aiSend(inp.value);
}

/* =====================================================================
   SHELL
   ===================================================================== */
function renderShell(){
  $('#root').innerHTML = `
    <div class="nav-scrim" id="navScrim"></div>
    <header class="topbar" id="topbar"><meta charset="utf-8"></header>
    <header class="dtop" id="dtop"></header>
    <div class="app"><aside class="side" id="side"></aside><main class="main" id="main"></main><aside class="rail" id="rail"></aside></div>
    <button class="fab-create" id="fabCreate" aria-label="Create poll">${ic('plus',26)}</button>
    <button class="ai-fab" id="aiFab" aria-label="Ask the LinkPoll assistant">${ic('sparkle',22)}<span class="ai-fab-lbl">Ask AI</span></button>
    <div class="ptr" id="ptr"><span class="ptr-spin"></span></div>`;
  const fab=$('#fabCreate'); if(fab) fab.onclick=()=>{ if(ME) openCreate(); else openAuth(); };
  const aif=$('#aiFab'); if(aif) aif.onclick=()=>openAssistant();
  $('#navScrim').onclick = closeDrawer;
  // any tap on a link/button inside the drawer closes it
  $('#side').addEventListener('click', (e)=>{ if(e.target.closest('a,button')) closeDrawer(); });
}

/* ---------- desktop top bar (logo · search · actions) ---------- */
function renderDesktopTop(){
  const el=$('#dtop'); if(!el) return;
  const alerts=UNREAD?`<span class="dt-badge">${UNREAD>9?'9+':UNREAD}</span>`:'';
  const msgs=MSG_UNREAD?`<span class="dt-badge">${MSG_UNREAD>9?'9+':MSG_UNREAD}</span>`:'';
  el.innerHTML=`
    <a href="#/" class="dt-brand"><img class="logo" src="logo-icon.png" alt=""><b>LinkPoll</b></a>
    <div class="dt-search"><span class="dt-mag">${ic('search',18)}</span>
      <input id="dtop-search" placeholder="Search polls, people, communities…" value="${esc(query)}" autocomplete="off">
      <span class="dt-kbd">Ctrl /</span></div>
    <div class="dt-actions">${ME?`
      <a href="#/" class="dt-ic ${route==='home'?'on':''}" aria-label="Home">${ic('home',21)}</a>
      <a href="#/notifications" class="dt-ic ${route==='notifications'?'on':''}" aria-label="Notifications">${ic('bell',21)}${alerts}</a>
      <a href="#/messages" class="dt-ic ${route==='messages'?'on':''}" aria-label="Messages">${ic('mail',21)}${msgs}</a>
      <button class="btn btn-lime dt-create" data-create>${ic('plus',16)} Create Poll</button>
      <button class="dt-ava" data-usermenu data-keepmenu aria-label="Your account">${avatar(ME,'s34')}</button>`
    :`<a class="btn btn-lime dt-create" data-auth>${ic('signin',16)} Sign In</a>`}</div>`;
  const si=$('#dtop-search');
  if(si){ si.addEventListener('input',()=>{ query=si.value; clearTimeout(si._t); si._t=setTimeout(()=>{ if(route!=='explore') go('#/explore'); else exResults&&exResults(); },250); });
    si.addEventListener('keydown',(e)=>{ if(e.key==='Enter'){ query=si.value; go('#/explore'); } }); }
}
/* ---------- mobile top bar + hamburger drawer ---------- */
function renderTopbar(){
  $('#topbar').innerHTML = `
    <button class="burger" id="burger" aria-label="Open menu"><span></span><span></span><span></span></button>
    <a href="#/" class="tb-brand"><img class="logo" src="logo-icon.png" alt="LinkPoll" /> LinkPoll</a>
    <div class="tb-right">${ME
      ? `<button class="tb-icon" data-go-explore aria-label="Search">${ic('search',20)}</button>
         <button class="tb-icon tb-msg" data-go-messages aria-label="Messages">${ic('mail',20)}${MSG_UNREAD?`<span class="tb-badge">${MSG_UNREAD>9?'9+':MSG_UNREAD}</span>`:''}</button>`
      : `<button class="btn btn-lime btn-sm" data-auth>Sign In</button>`}</div>`;
  $('#burger').onclick = (e)=>{ e.stopPropagation(); toggleDrawer(); };
  const se=$('[data-go-explore]'); if(se) se.onclick=()=>go('#/explore');
  const sm=$('[data-go-messages]'); if(sm) sm.onclick=()=>go('#/messages');
}
/* Scroll lock that PRESERVES the page position (body gets position:fixed, so we
   remember where we were and restore it — otherwise opening any sheet jumps to top). */
let LOCK_Y=0;
/* Jump straight to a position with no smooth-scroll animation. */
function jumpTo(y){
  const el=document.documentElement, prev=el.style.scrollBehavior;
  el.style.scrollBehavior='auto';
  try{ window.scrollTo({top:y,left:0,behavior:'instant'}); }catch(e){ window.scrollTo(0,y); }
  el.style.scrollBehavior=prev||'';
}
function lockScroll(){
  if(document.body.classList.contains('locked')) return;
  LOCK_Y = window.scrollY || document.scrollingElement?.scrollTop || document.documentElement.scrollTop || document.body.scrollTop || 0;
  document.body.style.top = (-LOCK_Y)+'px';
  document.body.classList.add('locked');
}
function unlockScroll(){
  if(!document.body.classList.contains('locked')) return;
  document.body.classList.remove('locked');
  document.body.style.top='';
  jumpTo(LOCK_Y);
}
function openDrawer(){ $('#side')?.classList.add('open'); $('#navScrim')?.classList.add('show'); $('#burger')?.classList.add('on'); lockScroll(); }
function closeDrawer(){ $('#side')?.classList.remove('open'); $('#navScrim')?.classList.remove('show'); $('#burger')?.classList.remove('on'); if(!$('#overlay').innerHTML) unlockScroll(); }
function toggleDrawer(){ $('#side')?.classList.contains('open') ? closeDrawer() : openDrawer(); }
/* ---------- mobile bottom navigation bar ---------- */
function renderTabbar(){
  const tb=$('#tabbar'); if(!tb) return;
  const item=(r,icon,label)=>`<a href="#/${r}" class="tb-item ${(route===(r||'home')||NAV_ALIAS[route]===r)?'active':''}">${ic(icon,24)}<span>${label}</span></a>`;
  const badge=UNREAD?`<span class="tb-badge">${UNREAD>9?'9+':UNREAD}</span>`:'';
  tb.innerHTML = `
    ${item('','home','Home')}
    ${item('explore','explore','Explore')}
    <button class="tb-create" ${ME?'data-create':'data-auth'} aria-label="Create poll">${ic('plus',26)}</button>
    <a href="#/notifications" class="tb-item ${route==='notifications'?'active':''}">${ic('bell',24)}${badge}<span>Alerts</span></a>
    ${ME
      ? `<a href="#/u/${ME.username}" class="tb-item prof ${route==='profile'?'active':''}">${avatar(ME,'s26')}<span>You</span></a>`
      : `<a class="tb-item" data-auth>${ic('user',24)}<span>You</span></a>`}`;
}
const NAV_GROUPS={quiz:'Learn',mypolls:'You',links:'Creator tools',settings:' '};
const NAV_ALIAS={leaderboard:'points',discover:'explore',tag:'explore',c:'communities',paper:'papers'};
function navList(){ const n=[['','home','Home'],['explore','explore','Explore'],['faqs','comment','Discussions'],['communities','globe','Communities'],['quiz','target','Quizzes'],['papers','paper','Mock Papers'],['mypolls','mypolls','My Polls'],['insights','bars','Insights'],['saved','saved','Saved'],['network','users','Network'],['points','trophy','Points & badges'],['links','link','Link in bio'],['widgets','share','Widgets'],['reviews','heart','Reviews'],['settings','gear','Settings']];
  return n; }
function renderLinksSide(){
  const u=ME?esc(ME.username||''):'';
  $('#side').innerHTML = `
    <div class="brand"><img class="logo" src="logo-icon.png" alt="LinkPoll" /><b>LinkPoll</b></div>
    <a class="lm-snav lm-snav-back" href="#/">‹<span>Back to LinkPoll</span></a>
    <div class="lm-snav-title">${ic('link',15)} Link in bio</div>
    <nav class="nav lm-sidenav">
      <button class="lm-snav active" data-lmside="content">${ic('paper')}<span>Content</span></button>
      <button class="lm-snav" data-lmside="design">${ic('sparkle')}<span>Design &amp; theme</span></button>
      <button class="lm-snav" data-lmside="insights">${ic('target')}<span>Insights</span></button>
      <a class="lm-snav" href="#/b/${u}">${ic('compass')}<span>View live card</span></a>
      <button class="lm-snav" data-lmside-share>${ic('share')}<span>Share card</span></button>
    </nav>
    ${ME?`<div class="userchip" data-usermenu data-keepmenu>${avatar(ME,'s34')}
      <div class="who"><b>${esc(ME.name)}</b><span>@${esc(ME.username)}</span></div><span class="dots">⋯</span></div>`:''}`;
  $$('[data-lmside]').forEach((b)=>b.onclick=()=>{ const t=$('[data-letab="'+b.dataset.lmside+'"]'); if(t) t.click();
    $$('[data-lmside]').forEach((x)=>x.classList.toggle('active',x===b)); });
  const sh=$('[data-lmside-share]'); if(sh) sh.onclick=()=>{ if(ME) shareBio(ME.username, ME.name); };
}
function renderSide(){
  if(route==='links'){ return renderLinksSide(); }
  if(route==='myreviews'){ return renderReviewsSide(); }
  const item=([r,icon,label])=>`<a href="#/${r}" class="${(route===(r||'home')||NAV_ALIAS[route]===r)?'active':''}">${ic(icon)}<span>${label}</span>${(r==='notifications'&&UNREAD)?`<span class="nbadge badge">${UNREAD}</span>`:''}${(r==='messages'&&MSG_UNREAD)?`<span class="nbadge badge">${MSG_UNREAD}</span>`:''}</a>`;
  $('#side').innerHTML = `
    <div class="brand"><img class="logo" src="logo-icon.png" alt="LinkPoll" /><b>LinkPoll</b></div>
    <nav class="nav">${navList().map(x=>(NAV_GROUPS[x[0]]?'<div class="nav-group">'+NAV_GROUPS[x[0]]+'</div>':'')+item(x)).join('')}
      <div class="side-foot">
        <a href="#/help">Help</a><a href="#/about">About</a><a href="#/updates">What's New</a>
        <a href="#/feedback">Feedback</a><a href="#/privacy">Privacy</a><a href="#/terms">Terms</a>
        <a href="#/guidelines">Guidelines</a>
        <span class="side-foot-copy">© ${new Date().getFullYear()} LinkPoll · link-poll.com</span>
      </div>
    </nav>
    ${ME?`<div class="side-promo">
        <b>Your opinion<br><span class="sp-hi">matters.</span></b>
        <p>Create a poll and see what the world thinks.</p>
        <button class="btn btn-lime btn-block" data-create>${ic('plus',15)} Create Poll</button>
      </div>`
      :`<div class="cta"><button class="btn btn-lime btn-block" data-auth>${ic('signin')} Sign In</button></div>`}
    ${ME?`<div class="userchip" data-usermenu data-keepmenu>${avatar(ME,'s34')}
      <div class="who"><b>${esc(ME.name)}</b><span>@${esc(ME.username)}</span></div><span class="dots">⋯</span></div>`:''}`;
}
let RAIL=null;
async function renderRail(){
  let s=STATS; if(!s){ try{ s=await loadStats(); }catch(e){} } s=s||{trending:[],live:[]};
  const trendHtml=(s.trending&&s.trending.length)
    ? s.trending.map((t,i)=>`<div class="trend" data-search="${esc(t.tag)}"><span class="rank">${i+1}</span>
        <div class="t"><b>#${esc(t.tag)}</b><span>${fmt(t.count)} votes</span></div></div>`).join('')
    : '<p class="rail-empty">No trends yet — create the first poll!</p>';
  const liveHtml=(s.live&&s.live.length)
    ? s.live.map((l)=>`<a class="live-item" href="#/poll/${esc(l.code)}"><span class="live-ic">${ic('bars',15)}</span>
        <span class="li-t"><b>${esc(l.question)}</b><span>${fmt(l.votes)} vote${l.votes===1?'':'s'}</span></span></a>`).join('')
    : '<p class="rail-empty">No live polls right now.</p>';
  // points widget (signed-in only)
  let pointsHtml='';
  if(ME){ const pts=ME.points||0, floor=ME.levelFloor||0, next=ME.nextAt||null;
    const prog=next?Math.min(100,Math.round(((pts-floor)/(next-floor))*100)):100;
    pointsHtml=`<div class="box rail-card points-card">
      <div class="rc-head"><h4>Your Points</h4><a href="#/leaderboard" class="rc-link">View leaderboard</a></div>
      <div class="pc-row"><span class="pc-fire">🔥</span><div class="pc-n"><b>${fmt(pts)}</b><span>Total Points</span></div>
        <div class="pc-prog"><div class="pc-next">${next?`Next Level <b>${fmt(next)} pts</b>`:'Top tier 👑'}</div>
          <div class="pc-bar"><span style="width:${prog}%"></span></div></div></div></div>`;
  }
  $('#rail').innerHTML = `
    <div class="box rail-card"><div class="rc-head"><h4>Trending</h4><a href="#/explore" class="rc-link">View all</a></div>
      <div class="rc-body">${trendHtml}</div></div>
    <div class="box rail-card"><div class="rc-head"><h4><span class="live-dot"></span> Live Polls</h4><a href="#/?tab=live" class="rc-link" data-livetab>View all</a></div>
      <div class="rc-body">${liveHtml}</div></div>
    <div class="box rail-card invite-card">
      <div class="ic-txt"><b>Invite friends, get rewards!</b><p>Invite your friends and earn points when they join LinkPoll.</p>
        <button class="btn btn-ghost btn-sm" data-invite>Invite Now</button></div>
      <div class="ic-gift">🎁</div></div>
    ${pointsHtml}`;
  $('#rail').onclick=(e)=>{
    const lt=e.target.closest('[data-livetab]'); if(lt){ e.preventDefault(); feedTab='live'; go('#/'); return; }
    const iv=e.target.closest('[data-invite]'); if(iv){ if(ME) shareProfile(ME.username, ME.name); else openAuth(); return; }
    const vrr=e.target.closest('[data-results]'); if(vrr){ const p=POLLS[vrr.dataset.results]; if(p) openResults(p); return; }
    const oc=e.target.closest('[data-comments]'); if(oc){ openComments(oc.dataset.comments, oc); return; }
    const o=e.target.closest('[data-open]'); if(o){ go('#/poll/'+o.dataset.open); }
    const t=e.target.closest('[data-search]'); if(t){ query=t.dataset.search; go('#/explore'); } };
}
/* ---------- full-size profile photo viewer ---------- */
function openPhotoViewer(url, name){
  if(!url){ return; }
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim photo-scrim" data-scrim data-keepmenu>
    <button class="photo-x" data-x aria-label="Close">✕</button>
    <img class="photo-full" src="${esc(url)}" alt="${esc(name||'')}">
  </div>`;
  $('[data-x]',ov).onclick=closeOverlay;
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
}

/* Change the signed-in user's avatar or cover directly (pick → downscale → upload → refresh).
   kind = 'avatar' | 'cover'. Lets people edit their photos by tapping them, no settings detour. */
function uploadUserImage(kind, onDone){
  if(!ME) return openAuth();
  const inp=document.createElement('input'); inp.type='file'; inp.accept='image/*';
  inp.onchange=()=>{ const f=inp.files&&inp.files[0]; if(!f) return;
    toast('Uploading…');
    const rd=new FileReader();
    rd.onload=()=>downscaleImage(rd.result, kind==='cover'?1280:256, async(out)=>{
      const body={ name:ME.name, username:ME.username, bio:ME.bio||'', location:ME.location||'',
        website:ME.website||'', profession:ME.profession||'', color:ME.color||'#15140f',
        avatar:ME.avatar||'', cover:ME.cover||'' };
      if(ME.covers&&ME.covers.length) body.covers=ME.covers.slice();
      if(kind==='cover'){
        let covers=(ME.covers&&ME.covers.length)?ME.covers.slice():[];
        if(covers.length) covers[0]=out; else covers=[out];
        body.cover=out; body.covers=covers;
      } else { body.avatar=out; }
      try{ const {user}=await api('/user/update',{method:'POST',body}); ME=user;
        toast(kind==='cover'?'Cover photo updated ✓':'Profile photo updated ✓');
        chrome(); if(onDone) onDone();
      }catch(e){ toast(e.message); }
    }); rd.readAsDataURL(f); };
  inp.click();
}
function removeUserImage(kind, onDone){
  if(!ME) return;
  if(!confirm(kind==='cover'?'Remove your cover photo?':'Remove your profile photo?')) return;
  const body={ name:ME.name, username:ME.username, bio:ME.bio||'', location:ME.location||'',
    website:ME.website||'', profession:ME.profession||'', color:ME.color||'#15140f',
    avatar:ME.avatar||'', cover:ME.cover||'' };
  if(kind==='cover'){ body.cover=''; body.covers=[]; }
  else { body.avatar=''; if(ME.covers&&ME.covers.length) body.covers=ME.covers.slice(); }
  api('/user/update',{method:'POST',body}).then(({user})=>{ ME=user;
    toast(kind==='cover'?'Cover removed':'Photo removed'); chrome(); if(onDone) onDone();
  }).catch((e)=>toast(e.message));
}
let STORY_TRAYS=[];
async function loadStoryTray(){
  const el=$('#story-tray'); if(!el) return;
  let trays=[]; try{ trays=(await api('/stories')).trays||[]; }catch(e){ el.innerHTML=''; return; }
  STORY_TRAYS=trays;
  const myTray=trays.find((t)=>t.isMe);
  const others=trays.filter((t)=>!t.isMe);
  let html='';
  // "Your story" bubble — add, or open if you have one
  if(ME){
    if(myTray){ html+=`<button class="story-item ${myTray.seen?'seen':''}" data-stray="${myTray.uid}">
        <span class="story-ring"><span class="story-av">${avatar(ME,'s56',false)}</span></span>
        <span class="story-add-badge">+</span><span class="story-name">Your story</span></button>`; }
    else { html+=`<button class="story-item add" data-add-story>
        <span class="story-ring dashed"><span class="story-av">${avatar(ME,'s56',false)}<span class="story-plus">+</span></span></span>
        <span class="story-name">Add story</span></button>`; }
  }
  html+=others.map((t)=>`<button class="story-item ${t.seen?'seen':''}" data-stray="${t.uid}">
      <span class="story-ring"><span class="story-av">${avatar(t.user,'s56',false)}</span></span>
      <span class="story-name">${esc(t.user.username)}</span></button>`).join('');
  if(!ME && !others.length){ el.innerHTML=''; return; }
  el.innerHTML=html;
  el.querySelectorAll('[data-stray]').forEach((b)=>b.onclick=()=>openStoryViewer(+b.dataset.stray));
  const add=el.querySelector('[data-add-story]'); if(add) add.onclick=openStoryComposer;
}

function openStoryComposer(){
  if(!ME){ openAuth(); return; }
  const inp=document.createElement('input'); inp.type='file'; inp.accept='image/*';
  inp.onchange=()=>{ const f=inp.files&&inp.files[0]; if(!f) return;
    if(f.size>12*1024*1024){ toast('Image is too large (max ~12MB).'); return; }
    const rd=new FileReader(); rd.onload=()=>downscaleImage(rd.result,1080,(out)=>openStoryPreview(out)); rd.readAsDataURL(f);
  };
  inp.click();
}
function openStoryPreview(dataUrl){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim" data-scrim><div class="modal" data-keepmenu style="max-width:420px">
    <div class="mhead"><h3>New story</h3><button class="close" data-x>✕</button></div>
    <div class="story-prev"><img src="${dataUrl}" alt=""></div>
    <input class="field" id="story-cap" placeholder="Add a caption… (optional)" maxlength="200" style="margin-top:12px">
    <div class="mfoot"><button class="btn btn-ghost" data-x>Cancel</button>
      <button class="btn btn-dark" id="story-post"><span style="color:var(--lime)">Share to story</span></button></div>
  </div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  $('#story-post').onclick=async()=>{ const btn=$('#story-post'); btn.disabled=true;
    try{ await api('/stories',{method:'POST',body:{image:dataUrl,caption:$('#story-cap').value.trim()}});
      closeOverlay(); toast('Story shared! It disappears in 24 hours.'); loadStoryTray(); }
    catch(e){ toast(e.message); btn.disabled=false; }
  };
}

let STORY_VIEW=null;
function openStoryViewer(startUid){ openStoryTrays(STORY_TRAYS.slice(), startUid); }
async function openProfileStory(username){
  let tray; try{ tray=(await api('/stories/user',{query:{username}})).tray; }catch(e){ toast(e.message); return; }
  if(!tray || !tray.stories.length){ toast('No active story.'); return; }
  openStoryTrays([tray], tray.uid);
}
function openStoryTrays(order, startUid){
  let ti=order.findIndex((t)=>t.uid===startUid); if(ti<0) ti=0;
  let si=0;
  const ov=$('#overlay'); lockScroll();
  STORY_VIEW={timer:null};
  function clearTimer(){ if(STORY_VIEW&&STORY_VIEW.timer){ clearTimeout(STORY_VIEW.timer); STORY_VIEW.timer=null; } }
  function holdPause(){ if(!STORY_VIEW||STORY_VIEW.paused) return; STORY_VIEW.paused=true; clearTimer();
    const f=STORY_VIEW.fill; if(f){ const w=getComputedStyle(f).width; f.style.transition='none'; f.style.width=w; }
    STORY_VIEW.elapsed=(STORY_VIEW.elapsed||0)+(Date.now()-STORY_VIEW.start); }
  function holdResume(){ if(!STORY_VIEW||!STORY_VIEW.paused) return; STORY_VIEW.paused=false;
    const remain=Math.max(400,(STORY_VIEW.dur||5200)-(STORY_VIEW.elapsed||0)); const f=STORY_VIEW.fill;
    if(f){ requestAnimationFrame(()=>{ f.style.transition=`width ${remain}ms linear`; f.style.width='100%'; }); }
    STORY_VIEW.start=Date.now(); STORY_VIEW.timer=setTimeout(()=>{ STORY_VIEW.elapsed=0; next(); }, remain); }
  function close(){ clearTimer(); STORY_VIEW=null; closeOverlay(); loadStoryTray(); }
  async function markViewed(st){ try{ await api('/stories/view',{method:'POST',body:{storyId:st.id}}); }catch(e){} }
  function render(){
    clearTimer();
    const tray=order[ti]; if(!tray){ close(); return; } const sts=tray.stories; const st=sts[si];
    if(!st){ next(); return; }
    const dur=(st.poll||st.community)?16000:5200;
    ov.innerHTML=`<div class="story-screen" data-keepmenu>
      <div class="story-progress">${sts.map((_,i)=>`<span class="sp"><span class="sp-fill" style="${i<si?'width:100%':i===si?'':'width:0'}"></span></span>`).join('')}</div>
      <div class="story-top">
        <span class="story-who">${avatar(tray.user,'s34',false)}<b>${esc(tray.user.username)}</b><span class="story-ago">${timeAgo(st.createdAt)}</span></span>
        <span class="story-top-r">${tray.isMe?`<button class="story-del" data-del="${st.id}" aria-label="Delete">🗑</button>`:''}<button class="story-x" data-close aria-label="Close">✕</button></span>
      </div>
      <div class="story-img ${(st.poll||st.community)?'haspoll':''}" data-tapwrap>${st.poll
        ? `<div class="story-pollwrap">${chatPollCard(st.poll, 'story-'+st.id)}</div>`
        : st.community
        ? `<div class="story-pollwrap"><a class="story-commcard" href="#/c/${esc(st.community.slug)}" data-commgo="${esc(st.community.slug)}" style="background:linear-gradient(150deg, ${esc(st.community.color||'#15140f')}, ${esc(st.community.color||'#15140f')}cc)">
             <span class="scc-badge">${st.community.privacy==='private'?ic('lockc',13):ic('users',13)} Community</span>
             <b class="scc-name">${esc(st.community.name)}</b>
             ${st.community.description?`<span class="scc-desc">${esc(st.community.description)}</span>`:''}
             <span class="scc-meta">${fmt(st.community.members)} member${st.community.members===1?'':'s'}</span>
             <span class="scc-cta">Tap to view →</span></a></div>`
        : `<img src="${esc(st.image)}" alt="">`}
        ${st.caption?`<div class="story-cap">${esc(st.caption)}</div>`:''}
        <button class="story-nav left" data-prev aria-label="Previous"></button>
        <button class="story-nav right" data-next aria-label="Next"></button>
      </div>
      <div class="story-foot">${tray.isMe
        ? `<button class="story-viewers" data-viewers="${st.id}">👁 <span>See viewers</span></button>`
        : `<div class="story-reacts">${['❤️','😂','😮','😢','👏','🔥','👍'].map((em)=>`<button class="story-react" data-react="${em}">${em}</button>`).join('')}</div>
           ${ME?`<div class="story-reply"><input id="story-reply-in" placeholder="Send message…" autocomplete="off"><button class="story-reply-send" data-replysend aria-label="Send">${ic('send',18)}</button></div>`:''}`}
      </div></div>`;
    // animate current progress bar
    const fill=ov.querySelectorAll('.sp-fill')[si]; if(fill){ requestAnimationFrame(()=>{ fill.style.transition=`width ${dur}ms linear`; fill.style.width='100%'; }); }
    STORY_VIEW.start=Date.now(); STORY_VIEW.dur=dur; STORY_VIEW.fill=fill; STORY_VIEW.elapsed=0; STORY_VIEW.paused=false;
    markViewed(st);
    ov.querySelector('[data-close]').onclick=close;
    ov.querySelector('[data-next]').onclick=next;
    ov.querySelector('[data-prev]').onclick=prev;
    // hold anywhere on the image to pause
    const imgWrap=ov.querySelector('[data-tapwrap]');
    if(imgWrap){ const pause=(e)=>{ if(e.target.closest('button,input,a,.cp-card'))return; holdPause(); };
      imgWrap.addEventListener('pointerdown',pause); imgWrap.addEventListener('pointerup',holdResume); imgWrap.addEventListener('pointerleave',holdResume); }
    ov.querySelectorAll('[data-react]').forEach((b)=>b.onclick=async(e)=>{ e.stopPropagation();
      const em=b.dataset.react;
      try{ await api('/stories/react',{method:'POST',body:{storyId:st.id,emoji:em}});
        ov.querySelectorAll('[data-react]').forEach((x)=>x.classList.remove('reacted')); b.classList.add('reacted'); toast('Reacted '+em); }catch(err){ toast(err.message); } });
    const rin=ov.querySelector('#story-reply-in');
    if(rin){ rin.addEventListener('focus',holdPause);
      const send=async()=>{ const v=rin.value.trim(); if(!v)return; rin.value='';
        try{ await api('/messages',{method:'POST',body:{to:tray.user.username,body:v}}); toast('Sent to @'+tray.user.username); }catch(err){ toast(err.message); } };
      rin.addEventListener('keydown',(e)=>{ if(e.key==='Enter'){ e.preventDefault(); send(); } });
      const rb=ov.querySelector('[data-replysend]'); if(rb) rb.onclick=send; }
    const vb=ov.querySelector('[data-viewers]'); if(vb) vb.onclick=(e)=>{ e.stopPropagation(); clearTimer(); openStoryViewers(st.id, ()=>render()); };
    const cg=ov.querySelector('[data-commgo]'); if(cg) cg.onclick=(e)=>{ e.preventDefault(); e.stopPropagation(); close(); go('#/c/'+cg.dataset.commgo); };
    ov.querySelectorAll('[data-cvote]').forEach((b)=>b.onclick=async(e)=>{ e.stopPropagation(); clearTimer();
      const card=b.closest('[data-pollcard]'); const pid=+card.dataset.pollcard; const oid=+b.dataset.cvote;
      try{ const r=await api('/vote',{method:'POST',body:{pollId:pid,optionIds:[oid]}}); st.poll=r.poll; render(); }
      catch(err){ toast(err.message); render(); } });
    const del=ov.querySelector('[data-del]'); if(del) del.onclick=async()=>{ clearTimer();
      if(!confirm('Delete this story?')) { render(); return; }
      try{ await api('/stories/delete',{method:'POST',body:{storyId:st.id}}); toast('Story deleted');
        sts.splice(si,1); if(!sts.length){ order.splice(ti,1); si=0; if(ti>=order.length){ close(); return; } } else if(si>=sts.length){ si=sts.length-1; }
        render(); }catch(e){ toast(e.message); render(); } };
    STORY_VIEW.timer=setTimeout(next, dur);
  }
  function next(){ const tray=order[ti]; if(tray && si<tray.stories.length-1){ si++; render(); }
    else { ti++; si=0; if(ti>=order.length){ close(); } else render(); } }
  function prev(){ if(si>0){ si--; render(); } else if(ti>0){ ti--; si=order[ti].stories.length-1; render(); } }
  render();
}
function openStoryViewers(storyId, onClose){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim data-keepmenu><div class="sheet" style="max-height:70vh">
    <div class="sheet-grab"></div>
    <div class="sheet-head"><h3>Viewers</h3><button class="close" data-x>✕</button></div>
    <div id="viewers-list" class="viewers-list">${skList(3)}</div></div></div>`;
  const done=()=>{ closeOverlay(); if(onClose) onClose(); };
  $$('[data-x]',ov).forEach((b)=>b.onclick=done);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)done(); };
  (async()=>{ let d; try{ d=await api('/stories/viewers',{query:{storyId}}); }catch(e){ $('#viewers-list').innerHTML=empt('🙈',e.message); return; }
    const box=$('#viewers-list'); if(!box) return;
    if(!d.viewers.length){ box.innerHTML=`<div class="ins-empty"><p>No views yet.</p></div>`; return; }
    box.innerHTML=`<div class="viewers-count">👁 ${fmt(d.count)} ${d.count===1?'view':'views'}</div>`+
      d.viewers.map((v)=>`<div class="viewer-row">${avatar(v,'s40',true)}
        <div class="vr-info"><b data-author="${esc(v.username)}">${esc(v.name)}${vbadge(v.verified)}</b><span>@${esc(v.username)}</span></div>
        ${v.emoji?`<span class="vr-emoji">${v.emoji}</span>`:''}</div>`).join('');
  })();
}

async function loadSuggested(){
  const box=$('#sugg'); if(!box)return;
  try{
    const { users } = await api('/suggested');
    const list=(users||[]).slice(0,4);
    if(!list.length){ box.innerHTML='<p style="color:var(--soft);font-size:13px;padding:6px 8px">No one new to suggest right now.</p>'; return; }
    box.innerHTML=list.map((u)=>`<div class="sug">${avatar(u,'s40',true)}
      <div class="who"><b data-author="${esc(u.username)}" style="cursor:pointer">${esc(u.name)}${vbadge(u.verified)}</b><span>${u.followers?fmt(u.followers)+' followers':'@'+esc(u.username)}</span></div>
      <button class="btn btn-dark btn-sm" data-sfollow="${esc(u.username)}">Follow</button></div>`).join('');
    box.querySelectorAll('[data-sfollow]').forEach((b)=>b.onclick=async(e)=>{ e.stopPropagation();
      const un=b.dataset.sfollow;
      try{ const r=await api('/follow',{method:'POST',body:{username:un}}); afterFollow(un, r.following); }
      catch(err){ if(!ME) openAuth(); else toast(err.message); } });
  }catch(e){ box.innerHTML=''; }
}
function chrome(){ try{ document.body.dataset.route=route; }catch(e){} if(route!=='biocard'){ document.body.style.background=''; } document.body.classList.remove('nav-hidden'); if(route!=='explore') document.body.classList.remove('ex-searching'); renderSide(); renderTopbar(); renderDesktopTop(); renderTabbar(); renderRail(); }

/* =====================================================================
   POLL CARD
   ===================================================================== */
function faqAnswerHTML(a, faqId){
  const reps=(a.replies||[]);
  const repHTML = reps.map((r)=>`<div class="faq-rep"><a href="#/u/${esc(r.author.username)}" class="fr-av">${avatar(r.author,'s24')}</a>
    <div class="fr-body"><a href="#/u/${esc(r.author.username)}" class="fr-name">${esc(r.author.name)}${r.author.verified?vbadge(1):''}</a><span class="fr-txt">${esc(r.body)}</span></div></div>`).join('');
  const more = (a.replyCount||0)>reps.length ? `<a class="faq-morereplies" href="#/faq/${faqId}">View ${a.replyCount-reps.length} more →</a>` : '';
  return `<div class="faq-ans" data-ans="${a.id}">
    <a href="#/u/${esc(a.author.username)}" class="fa-av">${avatar(a.author,'s28')}</a>
    <div class="fa-body">
      <a href="#/u/${esc(a.author.username)}" class="fa-name">${esc(a.author.name)}${a.author.verified?vbadge(1):''}</a>
      <p>${esc(a.body)}</p>
      <div class="fa-actions">${ME?`<button class="fa-replybtn" data-reply-toggle="${a.id}">↳ Reply</button>`:''}${a.replyCount?`<span class="fa-repcount">${a.replyCount} repl${a.replyCount===1?'y':'ies'}</span>`:''}</div>
      ${(repHTML||more)?`<div class="fa-replies">${repHTML}${more}</div>`:''}
      <div class="fa-replybox" data-replybox="${a.id}" hidden>
        <input class="faq-input sm" data-reply-input="${a.id}" placeholder="Reply to ${esc(a.author.name)}…" maxlength="1000">
        <button class="btn btn-lime btn-sm" data-reply-send="${a.id}" data-faq="${faqId}">Reply</button>
      </div>
    </div>
  </div>`;
}
function wireFaqReplies(scope, onUpdate){
  scope.querySelectorAll('[data-reply-toggle]').forEach((b)=>b.onclick=()=>{
    const box=scope.querySelector(`[data-replybox="${b.dataset.replyToggle}"]`);
    if(box){ box.hidden=!box.hidden; if(!box.hidden){ const i=box.querySelector('input'); if(i) i.focus(); } }
  });
  scope.querySelectorAll('[data-reply-send]').forEach((b)=>{
    const inp=scope.querySelector(`[data-reply-input="${b.dataset.replySend}"]`);
    const submit=async()=>{ if(!inp) return; const body=inp.value.trim(); if(!body) return; b.disabled=true;
      try{ const d=await api('/faqs/answer',{method:'POST',body:{faqId:+b.dataset.faq,parentId:+b.dataset.replySend,body}}); inp.value=''; onUpdate(d.faq); toast('Reply posted!'); }
      catch(e){ toast(e.message); } finally{ b.disabled=false; } };
    b.onclick=submit; if(inp) inp.onkeydown=(e)=>{ if(e.key==='Enter'){ e.preventDefault(); submit(); } };
  });
}
function faqCardHTML(f){
  FAQS[f.id]=f; const a=f.author;
  return `<article class="card faq-card" data-faq="${f.id}">
    <div class="faq-cardtop"><div class="faq-badge">${ic('sparkle',13)} Discussion</div>
      <button class="iconbtn faq-menu" data-faq-menu="${f.id}" aria-label="More" data-keepmenu>${ic('lines2',18)}</button></div>
    <header class="card-head">
      <a href="#/u/${esc(a.username)}" class="ch-av">${avatar(a,'s40')}</a>
      <div class="ch-meta"><a href="#/u/${esc(a.username)}" class="ch-name">${esc(a.name)}${a.verified?vbadge(1):''}</a>
        <span class="ch-sub">@${esc(a.username)} · ${timeAgo(f.at*1000)} · asked</span></div>
    </header>
    <a class="faq-q" href="#/faq/${f.id}">${esc(f.question)}</a>
    <div class="faq-answers" data-faq-answers="${f.id}"></div>
    <div class="faq-reply">
      ${ME?`<input class="faq-input" data-faq-input="${f.id}" placeholder="Share your answer or opinion…" maxlength="1000">
        <button class="btn btn-lime btn-sm" data-faq-send="${f.id}">Answer</button>`
      :`<button class="btn btn-ghost btn-sm btn-block" data-auth>Sign in to join the discussion</button>`}
    </div>
  </article>`;
}
function faqUrl(id){ return location.origin+'/faq/'+id; }
function shareFaq(f){ openShareSheet({url:faqUrl(f.id), title:f.question, subtitle:'Discussion on LinkPoll'}); }
function openFaqMenu(f, anchor){
  if(!f) return; const a=f.author||{};
  const isOwner=!!(ME && a.username && ME.username===a.username); const canMod=!!(ME&&ME.admin);
  const actions=[
    {label:'Share', icon:ic('share',18), onClick:()=>shareFaq(f)},
    {label:'Copy link', icon:ic('link',18), onClick:async()=>{ try{ await navigator.clipboard.writeText(faqUrl(f.id)); toast('Link copied!'); }catch(e){ prompt('Copy:',faqUrl(f.id)); } }},
    (!isOwner&&a.username) && {label:'View profile', icon:ic('user',18), onClick:()=>go('#/u/'+a.username)},
    (ME&&!isOwner) && {label:'Report', icon:ic('flag',18), danger:true, onClick:()=>openReport('faq', f.id)},
    (isOwner||canMod) && {label:'Delete'+(!isOwner&&canMod?' (admin)':''), icon:ic('out',18), danger:true, onClick:async()=>{
      if(!confirm(isOwner?'Delete this discussion permanently?':'Remove this discussion as moderator?')) return;
      try{ await api('/faqs/delete',{method:'POST',body:{id:f.id}}); try{ delete FAQS[f.id]; }catch(_e){}
        const n=document.querySelector(`.faq-card[data-faq="${f.id}"]`); if(n) n.remove(); invalidateViews&&invalidateViews(true);
        toast('Discussion deleted'); if(route==='faq') go('#/faqs'); }catch(e){ toast(e.message); } }},
  ];
  const head=a.username?`<div class="as-person" data-author="${esc(a.username)}">${avatar(a,'s44',true)}<div class="as-pwho"><b>${esc(a.name||a.username)}${vbadge(a.verified)}</b><span>@${esc(a.username)}</span></div>${!isOwner?'<span class="as-pgo">›</span>':''}</div>`:'';
  actionSheet(actions, anchor, '', head);
}
// Render the card's answers area: collapsed shows just the first answer + a "Show more"
// button; expanded (after a click) shows every answer and its replies inline.
function renderFaqCardAnswers(node, f){
  FAQS[f.id]=f;
  const ansBox=node.querySelector('[data-faq-answers]'); if(!ansBox) return;
  const expanded=!!node._faqExpanded;
  const shown = expanded ? (f.answers||[]) : (f.answers||[]).slice(0,1);
  ansBox.innerHTML = shown.length ? shown.map((x)=>faqAnswerHTML(x,f.id)).join('')
    : '<div class="faq-noans">No answers yet — share your take!</div>';
  wireFaqReplies(ansBox,(faq)=>renderFaqCardAnswers(node,faq));
  const oldMore=node.querySelector('.faq-more'); if(oldMore) oldMore.remove();
  const remaining=(f.answerCount||0)-shown.length;
  if(!expanded && remaining>0){
    const mb=htmlToNode(`<button class="faq-more" data-faq-more="${f.id}">↓ Show ${fmt(remaining)} more answer${remaining===1?'':'s'} &amp; discussion</button>`);
    ansBox.after(mb);
    mb.onclick=async()=>{ mb.disabled=true; mb.textContent='Loading…';
      try{ const full=await api('/faq',{query:{id:f.id}}); node._faqExpanded=true; renderFaqCardAnswers(node,full); }
      catch(e){ toast(e.message); mb.disabled=false; mb.textContent=`↓ Show ${fmt(remaining)} more`; } };
  }
}
function updateFaqCard(node, f){ renderFaqCardAnswers(node, f); }
function wireFaqCard(node){
  const f=FAQS[node.dataset.faq]; if(f) renderFaqCardAnswers(node, f);
  const menu=node.querySelector('[data-faq-menu]');
  if(menu) menu.onclick=(e)=>{ e.preventDefault(); e.stopPropagation(); openFaqMenu(FAQS[node.dataset.faq], menu); };
  const send=node.querySelector('[data-faq-send]'), input=node.querySelector('[data-faq-input]');
  if(send && input){
    const submit=async()=>{ const id=+send.dataset.faqSend, body=input.value.trim(); if(!body) return;
      send.disabled=true;
      try{ const d=await api('/faqs/answer',{method:'POST',body:{faqId:id,body}}); input.value=''; renderFaqCardAnswers(node,d.faq); toast('Answer posted!'); }
      catch(e){ toast(e.message); } finally{ send.disabled=false; } };
    send.onclick=submit;
    input.onkeydown=(e)=>{ if(e.key==='Enter'){ e.preventDefault(); submit(); } };
  }
}
async function injectFeedFaqs(feed){
  if(route!=='home' || !feed) return;
  if(FAQ_QUEUE.length<2 && !FAQ_DONE){
    try{ const d=await api('/faqs',{query:{cursor:FAQ_CURSOR}}); (d.faqs||[]).forEach((f)=>{ if(!FAQ_SHOWN.has(f.id)) FAQ_QUEUE.push(f); }); FAQ_CURSOR=d.nextCursor; if(!FAQ_CURSOR) FAQ_DONE=true; }catch(e){}
  }
  while(FAQ_QUEUE.length){
    const cards=[...feed.children].filter((n)=>n.classList&&n.classList.contains('card')&&!n.classList.contains('faq-card'));
    if(cards.length<FAQ_NEXTAT) break;
    const anchor=cards[FAQ_NEXTAT-1]; if(!anchor) break;
    const f=FAQ_QUEUE.shift(); if(FAQ_SHOWN.has(f.id)) continue; FAQ_SHOWN.add(f.id);
    const node=htmlToNode(faqCardHTML(f)); anchor.after(node); wireFaqCard(node);
    FAQ_NEXTAT+=6;
  }
}
function openFaqComposer(){
  if(!ME){ openAuth(); return; }
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim><div class="sheet faq-composer">
    <div class="sheet-head"><h3>${ic('sparkle',18)} Start a discussion</h3><button class="close" data-x>✕</button></div>
    <p class="sheet-sub">Ask the community anything to get quick answers and opinions. It appears in the feed with polls — everyone can answer and reply.</p>
    <textarea id="faqc-q" placeholder="Your question — e.g. What’s the best courier for shipping to the UK?" maxlength="280" rows="3"></textarea>
    <div class="faqc-row"><span class="faqc-count" id="faqc-count">0/280</span></div>
    <label class="faqc-lbl">Your answer / opinion <span>(optional)</span></label>
    <textarea id="faqc-a" placeholder="Kick off the discussion with your own take…" maxlength="1000" rows="2"></textarea>
    <div class="faqc-foot"><button class="btn btn-lime" id="faqc-post">Post</button></div>
  </div></div>`;
  $('[data-x]',ov).onclick=closeOverlay; $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget) closeOverlay(); };
  const ta=$('#faqc-q',ov), cnt=$('#faqc-count',ov);
  ta.oninput=()=>cnt.textContent=`${ta.value.length}/280`; ta.focus();
  $('#faqc-post',ov).onclick=async()=>{
    const q=ta.value.trim(); if(q.length<5){ toast('Please write a longer question.'); return; }
    const answer=$('#faqc-a',ov).value.trim();
    try{ const d=await api('/faqs',{method:'POST',body:{question:q, answer}}); closeOverlay(); toast('Posted!');
      const feed=$('#feed');
      if(feed){ const node=htmlToNode(faqCardHTML(d.faq)); const first=feed.querySelector('.card'); if(first) first.before(node); else feed.prepend(node); wireFaqCard(node); FAQ_SHOWN.add(d.faq.id); window.scrollTo({top:0,behavior:'smooth'}); }
      else go('#/faq/'+d.faq.id);
    }catch(e){ toast(e.message); }
  };
}
async function renderFaqsPage(){
  route='faqs'; chrome();
  setMain(`<div class="faqs-page">
    <div class="faqs-head"><h1 class="faqs-title">${ic('sparkle',22)} Discussions</h1>${ME?`<button class="btn btn-lime btn-sm" data-ask>${ic('plus',14)} Ask a question</button>`:''}</div>
    <p class="faqs-intro">Open questions from the community — answer, reply, and share what you think.</p>
    <div id="faqs-list" class="faqs-list">${skCards(3)}</div>
    <div class="feed-more" id="faqs-more" style="text-align:center"></div>
  </div>`);
  const ask=$('[data-ask]'); if(ask) ask.onclick=openFaqComposer;
  let cursor=null, done=false, loading=false, first=true;
  const list=$('#faqs-list'), more=$('#faqs-more');
  async function load(){ if(loading||done) return; loading=true; if(!first) more.innerHTML=skCards(1);
    try{ const d=await api('/faqs',{query:{cursor}});
      if(first){ list.innerHTML=''; first=false; }
      (d.faqs||[]).forEach((f)=>{ const node=htmlToNode(faqCardHTML(f)); list.appendChild(node); wireFaqCard(node); });
      cursor=d.nextCursor; more.innerHTML='';
      if(!cursor){ done=true; if(!list.children.length) list.innerHTML=empt('💬','No discussions yet. Be the first to ask the community something!'); }
    }catch(e){ more.innerHTML=`<button class="btn btn-ghost btn-sm">Retry</button>`; const b=more.querySelector('button'); if(b) b.onclick=()=>{ more.innerHTML=''; load(); }; }
    loading=false;
  }
  await load();
  if('IntersectionObserver' in window){ const io=new IntersectionObserver((es)=>{ if(es.some((x)=>x.isIntersecting)) load(); },{rootMargin:'500px 0px'}); io.observe(more); more._lpIO=io; more._lpLoad=load; }
}
async function renderFaqPage(id){
  route='faq'; chrome();
  setMain(`<div class="faqpage"><div class="rvp-load">Loading…</div></div>`);
  let f; try{ f=await api('/faq',{query:{id}}); }catch(e){ setMain(`<div class="faqpage"><div class="rvp-nf"><div class="rvp-nf-em">🔍</div><h2>Question not found</h2><a class="btn btn-lime" href="#/">Back to feed</a></div></div>`); return; }
  drawFaqPage(f);
}
function drawFaqPage(f){
  FAQS[f.id]=f; const a=f.author;
  const answers=(f.answers||[]).slice().reverse(); // oldest first for a readable thread
  const list = answers.length? answers.map((x)=>faqAnswerHTML(x,f.id)).join('') : '<div class="faq-noans">No answers yet — share what you know!</div>';
  setMain(`<div class="faqpage">
    <div class="faqp-top"><button class="lm-back" data-back title="Back">‹</button><span class="faqp-badge">${ic('sparkle',13)} Discussion</span>
      <div class="faqp-actions"><button class="iconbtn" id="faqp-share" aria-label="Share">${ic('share',18)}</button><button class="iconbtn" id="faqp-menu" aria-label="More" data-keepmenu>${ic('lines2',18)}</button></div></div>
    <div class="faqp-card">
      <header class="card-head"><a href="#/u/${esc(a.username)}" class="ch-av">${avatar(a,'s40')}</a>
        <div class="ch-meta"><a href="#/u/${esc(a.username)}" class="ch-name">${esc(a.name)}${a.verified?vbadge(1):''}</a>
          <span class="ch-sub">@${esc(a.username)} · ${timeAgo(f.at*1000)}</span></div></header>
      <h1 class="faqp-q">${esc(f.question)}</h1>
    </div>
    ${ME?`<div class="faqp-reply"><input class="faq-input" id="faqp-input" placeholder="Share your answer or opinion…" maxlength="1000"><button class="btn btn-lime" id="faqp-send">Answer</button></div>`
      :`<button class="btn btn-lime btn-block" data-auth style="margin:14px 0">Sign in to join the discussion</button>`}
    <div class="faqp-count">${fmt(f.answerCount)} answer${f.answerCount===1?'':'s'}</div>
    <div class="faqp-answers" id="faqp-answers">${list}</div>
    <div class="rvp-foot"><a href="#/">✦ Ask, answer &amp; discuss on LinkPoll</a></div>
  </div>`);
  const back=$('[data-back]'); if(back) back.onclick=()=>{ if(history.length>1) history.back(); else go('#/'); };
  const psh=$('#faqp-share'); if(psh) psh.onclick=()=>shareFaq(f);
  const pmn=$('#faqp-menu'); if(pmn) pmn.onclick=(e)=>{ e.stopPropagation(); openFaqMenu(f, pmn); };
  const send=$('#faqp-send'), input=$('#faqp-input');
  if(send && input){
    const submit=async()=>{ const body=input.value.trim(); if(!body) return; send.disabled=true;
      try{ const d=await api('/faqs/answer',{method:'POST',body:{faqId:f.id,body}}); input.value=''; drawFaqPage(d.faq); }
      catch(e){ toast(e.message); send.disabled=false; } };
    send.onclick=submit; input.onkeydown=(e)=>{ if(e.key==='Enter'){ e.preventDefault(); submit(); } };
  }
  const ansBox=$('#faqp-answers'); if(ansBox) wireFaqReplies(ansBox,(faq)=>drawFaqPage(faq));
}
const WIDGET_TYPES=[
  {k:'bio', emoji:'🔗', name:'Bio card', desc:'Your profile + links'},
  {k:'reviews', emoji:'⭐', name:'Reviews badge', desc:'Star rating badge'},
  {k:'poll', emoji:'📊', name:'Poll', desc:'A votable poll'},
  {k:'faq', emoji:'💬', name:'Discussion', desc:'A Q&A discussion'},
];
function widgetMeta(W){
  const u=encodeURIComponent(ME?ME.username:''); const t=(W.theme==='dark')?'&theme=dark':''; const o=location.origin;
  if(W.type==='bio') return {url:`${o}/embed.php?type=bio&u=${u}${t}`, h:540, max:380, needsItem:false, theme:true, label:'Bio card'};
  if(W.type==='reviews') return {url:`${o}/embed.php?type=reviews&u=${u}`, h:150, max:340, needsItem:false, theme:false, label:'Reviews badge'};
  if(W.type==='poll') return {url:W.item?`${o}/embed.php?c=${encodeURIComponent(W.item)}`:'', h:340, max:560, needsItem:true, theme:false, label:'Poll'};
  return {url:W.item?`${o}/embed.php?type=faq&id=${W.item}${t}`:'', h:360, max:560, needsItem:true, theme:true, label:'Discussion'};
}
function widgetCode(m){ return `<iframe src="${m.url}" width="100%" height="${m.h}" style="border:0;max-width:${m.max}px" frameborder="0" loading="lazy" title="LinkPoll ${m.label}"></iframe>`; }
async function renderWidgetsPage(){
  route='widgets'; chrome();
  if(!ME){ setMain(`<div class="wgt-page"><div class="rvp-nf"><div class="rvp-nf-em">🔗</div><h2>Sign in to create widgets</h2><p class="muted">Embed your polls, discussions, reviews and bio on any website.</p><button class="btn btn-lime" data-auth>Sign in</button></div></div>`); return; }
  setMain(`<div class="wgt-page">
    <div class="wgt-head"><h1>${ic('bars',22)} Widgets</h1><p>Embed your LinkPoll content on any website, blog or store. Pick a widget, customize it, then copy the code.</p></div>
    <div class="wgt-types">${WIDGET_TYPES.map((t)=>`<button class="wgt-type${t.k==='bio'?' active':''}" data-wtype="${t.k}"><span class="wt-em">${t.emoji}</span><span class="wt-nm">${t.name}</span><span class="wt-ds">${t.desc}</span></button>`).join('')}</div>
    <div class="wgt-body">
      <div class="wgt-left">
        <div class="wgt-config" id="wgt-config"></div>
        <label class="wgt-lbl">Embed code</label>
        <textarea class="wgt-code" id="wgt-code" readonly rows="3" onclick="this.select()"></textarea>
        <button class="btn btn-lime" id="wgt-copy">${ic('link',15)} Copy embed code</button>
        <p class="wgt-hint">Paste this into your website’s HTML where you want the widget to appear.</p>
      </div>
      <div class="wgt-right"><div class="wgt-prevlbl">Live preview</div><div class="wgt-preview" id="wgt-preview"></div></div>
    </div>
  </div>`);
  const W={ type:'bio', item:null, theme:'light', polls:null, faqs:null };
  const cfg=$('#wgt-config'), prev=$('#wgt-preview'), codeEl=$('#wgt-code');
  function build(){
    const m=widgetMeta(W);
    if(m.needsItem && !W.item){ prev.innerHTML=`<div class="wgt-empty">Choose one of your ${W.type==='poll'?'polls':'discussions'} above to preview.</div>`; codeEl.value=''; return; }
    prev.innerHTML=`<iframe src="${esc(m.url)}" style="width:100%;max-width:${m.max}px;height:${m.h}px;border:0" loading="lazy" title="preview"></iframe>`;
    codeEl.value=widgetCode(m);
  }
  async function renderConfig(){
    const m=widgetMeta(W); let html='';
    if(W.type==='poll'){
      if(W.polls===null){ try{ const d=await api('/mine'); W.polls=d.polls||[]; }catch(e){ W.polls=[]; } }
      html += W.polls.length
        ? `<label class="wgt-lbl">Choose a poll</label><select class="wgt-select" id="wgt-item">${W.polls.map((p)=>`<option value="${esc(p.code)}"${W.item===p.code?' selected':''}>${esc(p.question.slice(0,70))}</option>`).join('')}</select>`
        : `<div class="wgt-none">You haven’t created any polls yet. <a href="#/">Create one</a> first.</div>`;
      if(W.polls.length && !W.item) W.item=W.polls[0].code;
    } else if(W.type==='faq'){
      if(W.faqs===null){ try{ const d=await api('/faqs',{query:{mine:1}}); W.faqs=d.faqs||[]; }catch(e){ W.faqs=[]; } }
      html += W.faqs.length
        ? `<label class="wgt-lbl">Choose a discussion</label><select class="wgt-select" id="wgt-item">${W.faqs.map((f)=>`<option value="${f.id}"${String(W.item)===String(f.id)?' selected':''}>${esc(f.question.slice(0,70))}</option>`).join('')}</select>`
        : `<div class="wgt-none">You haven’t started any discussions yet. <a href="#/faqs">Start one</a> first.</div>`;
      if(W.faqs.length && !W.item) W.item=W.faqs[0].id;
    } else {
      html += `<div class="wgt-forwhom">This widget shows <b>@${esc(ME.username)}</b>’s ${W.type==='bio'?'profile and links':'review rating'}.</div>`;
    }
    if(m.theme){
      html += `<label class="wgt-lbl">Theme</label><div class="wgt-theme">
        <button class="wgt-th${W.theme==='light'?' on':''}" data-theme="light">☀️ Light</button>
        <button class="wgt-th${W.theme==='dark'?' on':''}" data-theme="dark">🌙 Dark</button></div>`;
    }
    cfg.innerHTML=html;
    const sel=$('#wgt-item'); if(sel) sel.onchange=()=>{ W.item=sel.value; build(); };
    $$('[data-theme]',cfg).forEach((b)=>b.onclick=()=>{ W.theme=b.dataset.theme; renderConfig(); build(); });
    build();
  }
  $$('.wgt-type').forEach((b)=>b.onclick=()=>{ $$('.wgt-type').forEach((x)=>x.classList.remove('active')); b.classList.add('active');
    W.type=b.dataset.wtype; W.item=null; renderConfig(); });
  $('#wgt-copy').onclick=async()=>{ const m=widgetMeta(W); if(m.needsItem&&!W.item){ toast('Choose an item first'); return; }
    const snip=widgetCode(m); try{ await navigator.clipboard.writeText(snip); toast('Embed code copied!'); }catch(e){ codeEl.select(); toast('Selected — press Ctrl/Cmd+C'); } };
  renderConfig();
}
function cardHTML(poll){
  POLLS[poll.code]=poll;
  const a=poll.author, my=poll.myVotes||[], voted=poll.hasVoted||timeLeft(poll).closed;
  const total=poll.totalVotes, tl=timeLeft(poll);
  const leadId = (voted && total>0) ? poll.options.reduce((a,b)=>b.votes>a.votes?b:a, poll.options[0]).id : null;
  const grid = poll.layout==='grid';
  const cid = poll.correctOptionId||null;
  const qz = (id)=> (poll.isQuiz && cid) ? (id===cid?' correct':(my.includes(id)?' wrong':'')) : '';
  const opts=poll.options.map((o)=>{
    const im=o.image?`<img class="opt-img" src="${esc(o.image)}" alt="" loading="lazy">`:'';
    if(grid){ const pct=total?Math.round(o.votes/total*100):0, mine=my.includes(o.id);
      if(voted) return `<div class="gopt voted ${mine?'mine':''} ${o.id===leadId&&total>0?'lead':''} ${o.image?'has-img':''}${qz(o.id)}" data-oid="${o.id}">
        ${o.image?`<span class="gopt-img">${im}</span>`:''}
        <span class="gopt-fill" data-pct="${pct}"></span>
        <span class="gopt-body"><span class="gopt-lbl">${qz(o.id)===' correct'?'<span class="tick">✓ </span>':(qz(o.id)===' wrong'?'<span class="cross">✗ </span>':(mine?'<span class="tick">✓ </span>':''))}${esc(o.label)}</span><span class="gopt-pct">${pct}%</span></span></div>`;
      return `<div class="gopt ${o.image?'has-img':''}" role="button" tabindex="0" data-vote="${poll.code}|${o.id}">
        ${o.image?`<span class="gopt-img">${im}</span>`:''}
        <span class="gopt-body"><span class="gopt-lbl">${esc(o.label)}</span></span></div>`;
    }
    if(voted){ const pct=total?Math.round(o.votes/total*100):0, mine=my.includes(o.id);
      return `<div class="opt voted ${mine?'mine':''} ${o.id===leadId&&total>0?'lead':''} ${o.image?'has-img':''}${qz(o.id)}" data-oid="${o.id}"><span class="fill" data-pct="${pct}"></span>
        <span class="lbl">${im}<span class="otext">${qz(o.id)===' correct'?'<span class="tick">✓ </span>':(qz(o.id)===' wrong'?'<span class="cross">✗ </span>':(mine?'<span class="tick">✓ </span>':''))}${esc(o.label)}</span><span class="pct">${pct}%</span></span></div>`; }
    return `<div class="opt ${o.image?'has-img':''}" role="button" tabindex="0" data-vote="${poll.code}|${o.id}">${im}<span class="lbl"><span class="otext">${esc(o.label)}</span></span></div>`;
  }).join('');
  const quizBanner = (poll.isQuiz && voted && cid) ? `<div class="quiz-result ${my.includes(cid)?'ok':'no'}">${my.includes(cid)?'🎯 Correct! Nice one.':'❌ Not quite — the right answer is highlighted.'}${poll.quizExplain?`<span class="qr-explain">${esc(poll.quizExplain)}</span>`:''}<a class="qr-more" href="#/quiz">Play Quiz Mode →</a></div>` : '';
  const isOwner = ME && (a.isMe || ME.username===a.username);
  const followBtn = (ME && !isOwner)
    ? `<button class="follow-btn" data-cfollow="${esc(a.username)}" ${a.following?'style="display:none"':''}>+ Follow</button>` : '';
  return `<div class="card ${poll.bgColor?'has-bg':''}" id="card-${poll.code}" data-v="${total}:${voted?1:0}"${poll.bgColor?` style="background:${esc(poll.bgColor)}"`:''}>
    <div class="phead">${avatar(a,'s40',true)}
      <div class="who"><b data-author="${esc(a.username)}">${esc(a.name)}${vbadge(a.verified)}${a.flair?'<span class="flair" title="Pro member">✨</span>':''}</b>
        <div class="sub">${timeAgo(poll.createdAt)}${poll.community?` · <a class="comm-chip" href="#/c/${esc(poll.community.slug)}">${poll.community.privacy==='private'?ic('lockc',11):ic('users',11)} ${esc(poll.community.name)}</a>`:''}</div></div>
      <div class="phead-right">
        ${followBtn}
        <button class="iconbtn pmenu-btn" data-pmenu="${poll.code}" aria-label="Menu" data-keepmenu>${ic('lines2',20)}</button></div></div>
    ${poll.seriesTotal>1?`<div class="series-bar"><div class="series-steps">${Array.from({length:poll.seriesTotal},(_,i)=>`<span class="series-step ${i<=poll.seriesPos?'on':''}"></span>`).join('')}</div><span class="series-label">${poll.seriesPos+1} of ${poll.seriesTotal}</span>${(ME&&poll.author&&ME.username===poll.author.username)?`<button class="series-results-btn" data-sresults="${poll.code}" title="View all results">📊</button>`:''}</div>`:''}
    <div class="q" data-open="${poll.code}" style="cursor:pointer">${poll.isQuiz?'<span class="quiz-chip">🎯 Quiz</span> ':''}${poll.seriesTotal>1?'<span class="series-chip">📋 Survey</span> ':''}${linkifyHashtags(esc(poll.question))}</div>
    ${(poll.tags&&poll.tags.length)?`<div class="ptags">${poll.tags.map((t)=>`<a class="tag" data-tag="${esc(t)}">#${esc(t)}</a>`).join('')}</div>`:''}
    ${poll.image?`<div class="pimg" data-open="${poll.code}"><img src="${esc(poll.image)}" alt="" loading="lazy"></div>`:''}
    <div class="opts ${grid?'grid':''}">${opts}</div>${quizBanner}${(!voted && poll.seriesNextCode)?`<button class="series-skip" data-sskip="${poll.code}">Skip this question →</button>`:''}
    <div class="pmeta">
      ${tl.live?'<span class="live">Live</span>':tl.closed?'<span class="tag-closed">Closed</span>':`<span class="clock">${ic('clock',13)} ${tl.text}</span>`}
      <span class="dot">•</span><span class="vcount">${fmt(total)} votes</span>${poll.edited?'<span class="dot">•</span><span class="edited-tag">edited</span>':''}${poll.multiple?'<span class="dot">•</span><span>multi-choice</span>':''}</div>
    <div class="pacts">
      <button class="act react ${poll.myReaction?'reacted r-'+poll.myReaction:''}" data-react="${poll.code}" data-keepmenu aria-label="React">
        <span class="rico">${poll.myReaction?REMOJI[poll.myReaction]:ic('heart',16)}</span>
        <span class="rsum">${reactionSummary(poll)}</span>
        <span class="n">${fmt(poll.likes)}</span></button>
      <button class="act cmt" data-comments="${poll.code}">${ic('comment',16)} <span class="n">${fmt(poll.comments)}</span></button>
      <button class="act" data-share="${poll.code}" data-keepmenu aria-label="Share">${ic('share',16)} <span class="n">${poll.shares?fmt(poll.shares):''}</span></button>
      <button class="act ${poll.saved?'on-save':''}" data-save="${poll.code}" aria-label="Save">${ic('bookmark',16)}</button>
      <button class="act act-ai" data-ai="${poll.code}" aria-label="AI summary">${ic('sparkle',16)}<span class="ai-lbl">AI</span></button>
      <span class="spacer"></span><button class="act count" data-results="${poll.code}" title="See results" aria-label="See results">${ic('bars',16)} <span class="n">${fmt(total)}</span></button></div>
  </div>`;
}
function paintFills(scope){ $$('.opt .fill, .gopt-fill',scope||document).forEach((f)=>{ f.style.width='0'; requestAnimationFrame(()=>{ f.style.width=(f.dataset.pct||0)+'%'; }); }); }
function replaceCard(poll){ const n=$('#card-'+poll.code); if(!n)return; const t=document.createElement('div'); t.innerHTML=cardHTML(poll); n.replaceWith(t.firstElementChild); paintFills($('#card-'+poll.code)); }

/* feed delegation */
function wireFeed(c){
  c.addEventListener('click',(e)=>{
    const v=e.target.closest('[data-vote]'); if(v){ const[code,oid]=v.dataset.vote.split('|'); doVote(code,+oid); return; }
    const vr=e.target.closest('[data-results]'); if(vr){ const p=POLLS[vr.dataset.results]; if(p) openResults(p); return; }
    const cm=e.target.closest('[data-comments]'); if(cm){ openComments(cm.dataset.comments, cm); return; }
    const op=e.target.closest('[data-open]'); if(op){ go('#/poll/'+op.dataset.open); return; }
    const tg=e.target.closest('[data-tag]'); if(tg){ go('#/tag/'+tg.dataset.tag); return; }
    const au=e.target.closest('[data-author]'); if(au){ go('#/u/'+au.dataset.author); return; }
    const rx=e.target.closest('[data-react]'); if(rx){
      const onCount=e.target.closest('.rsum')||e.target.closest('.n');
      const p=POLLS[rx.dataset.react];
      if(onCount && p && (p.likes>0)){ openReactors(rx.dataset.react); }
      else { openReactionPicker(rx.dataset.react, rx); }
      return; }
    const sv=e.target.closest('[data-save]'); if(sv){ doSave(sv.dataset.save); return; }
    const aq=e.target.closest('[data-ai]'); if(aq){ openAiSummary(aq.dataset.ai); return; }
    const sk=e.target.closest('[data-sskip]'); if(sk){ const p=POLLS[sk.dataset.sskip]; if(p&&p.seriesNextCode) loadSeriesNext(sk.dataset.sskip, p.seriesNextCode); return; }
    const sr=e.target.closest('[data-sresults]'); if(sr){ openSurveyResults(sr.dataset.sresults); return; }
    const sh=e.target.closest('[data-share]'); if(sh){ openShareMenu(sh.dataset.share, sh); return; }
    const cf=e.target.closest('[data-cfollow]'); if(cf){ doCardFollow(cf.dataset.cfollow); return; }
    const pm=e.target.closest('[data-pmenu]'); if(pm){ e.stopPropagation(); openPollMenu(pm.dataset.pmenu, pm); return; }
  });
}
async function doCardFollow(username){
  if(!ME){ openAuth(); return; }
  try{ const r=await api('/follow',{method:'POST',body:{username}});
    afterFollow(username, r.following);
    toast(r.following?'Following @'+username:'Unfollowed @'+username);
  }catch(e){ toast(e.message); }
}

/* =====================================================================
   REAL-TIME LIVE UPDATES (polling — works on shared hosting)
   ===================================================================== */
let LIVE_TIMER=null;
/* ---------- device notifications (Notifications API + service worker) ---------- */
function registerSW(){ if('serviceWorker' in navigator){ navigator.serviceWorker.register('/sw.js').catch(()=>{}); } }
function notifySupported(){ return 'Notification' in window; }
async function ensureNotifyPermission(loud){
  if(!notifySupported()){ if(loud) toast('Your browser does not support notifications'); return false; }
  if(Notification.permission==='granted') return true;
  if(Notification.permission==='denied'){ if(loud) toast('Notifications are blocked — enable them in your browser settings'); return false; }
  try{ const p=await Notification.requestPermission(); try{ localStorage.setItem('lp_notify_asked','1'); }catch(e){} return p==='granted'; }
  catch(e){ return false; }
}
async function maybeAskNotify(){
  if(!notifySupported() || Notification.permission!=='default') return;
  let asked=false; try{ asked=localStorage.getItem('lp_notify_asked')==='1'; }catch(e){}
  if(!asked) ensureNotifyPermission(false);
}
function showDeviceNotif(n){
  if(!notifySupported() || Notification.permission!=='granted') return;
  const title='LinkPoll';
  const body=`${n.name} ${notifText(n.type)}`;
  const url=location.origin+location.pathname+(n.code?('#/poll/'+n.code):'#/notifications');
  try{
    if('serviceWorker' in navigator && navigator.serviceWorker.controller){
      navigator.serviceWorker.controller.postMessage({type:'notify',title,body,url,tag:'lp-'+n.id});
    } else {
      const no=new Notification(title,{body,icon:'icon.svg'});
      no.onclick=()=>{ window.focus(); go(n.code?('#/poll/'+n.code):'#/notifications'); no.close(); };
    }
  }catch(e){}
}

function startLive(){ stopLive(); LIVE_TIMER=setInterval(liveTick, 10000); }
function stopLive(){ if(LIVE_TIMER){ clearInterval(LIVE_TIMER); LIVE_TIMER=null; } }
async function liveTick(){
  const hidden=document.hidden;
  const blocked = !!$('#overlay').innerHTML || $('#side')?.classList.contains('open') || !!document.querySelector('.menu');
  const codes = (hidden||blocked) ? [] : [...document.querySelectorAll('[id^="card-"]')].map((n)=>n.id.slice(5));
  const payload={codes}; if(ME){ payload.sinceNotif=NOTIF_SEEN; payload.sinceMsg=MSG_SEEN; }
  try{ const r=await api('/live',{method:'POST',body:payload});
    if(!hidden && !blocked && r.polls) for(const code of codes){ const d=r.polls[code]; if(d) livePatch(code,d); }
    if(typeof r.unread==='number' && r.unread!==UNREAD){ UNREAD=r.unread; renderSide(); renderTabbar(); }
    if(r.notifs && r.notifs.length){ for(const n of r.notifs){ showDeviceNotif(n); if(n.id>NOTIF_SEEN) NOTIF_SEEN=n.id; } }
    if(r.newMessages && r.newMessages.length){
      const mp=pathToParts(location.pathname); const openWith = (route==='messages' && mp[1]) ? decodeURIComponent(mp[1]) : null;
      for(const m of r.newMessages){
        if(m.id>MSG_SEEN) MSG_SEEN=m.id;
        if(openWith && m.username===openWith){ appendChatMessage({id:m.id,fromMe:false,body:m.body,poll:m.poll||null,createdAt:Date.now()}); api('/messages/read',{method:'POST',body:{with:openWith}}).catch(()=>{}); }
        else showMessageNotif(m);
      }
    }
    if(typeof r.unreadMessages==='number' && r.unreadMessages!==MSG_UNREAD){ MSG_UNREAD=r.unreadMessages; renderSide(); renderTabbar(); renderTopbar(); }
  }catch(e){}
}
function showMessageNotif(m){
  if(!notifySupported() || Notification.permission!=='granted') return;
  const title=m.name||'New message'; const body=(m.body||'').slice(0,120);
  const url=location.origin+location.pathname+'#/messages/'+m.username;
  try{
    if('serviceWorker' in navigator && navigator.serviceWorker.controller) navigator.serviceWorker.controller.postMessage({type:'notify',title,body,url,tag:'lpm-'+m.id});
    else { const no=new Notification(title,{body,icon:'icon.svg'}); no.onclick=()=>{ window.focus(); go('#/messages/'+m.username); no.close(); }; }
  }catch(e){}
}
function livePatch(code,d){
  const poll=POLLS[code], card=$('#card-'+code); if(!poll||!card) return;
  const byId={}; d.options.forEach((o)=>{ byId[o.id]=o.votes; });
  const wasClosed=poll.closed;
  poll.options.forEach((o)=>{ if(byId[o.id]!=null) o.votes=byId[o.id]; });
  poll.totalVotes=d.totalVotes; poll.voters=d.voters; poll.likes=d.likes; poll.comments=d.comments; poll.shares=d.shares; poll.closed=d.closed;
  if(d.closed && !wasClosed){ replaceCard(poll); return; }      // switch to closed/results view
  const total=poll.totalVotes;
  let leadId=null,leadV=-1; poll.options.forEach((o)=>{ if((byId[o.id]??o.votes)>leadV){ leadV=byId[o.id]??o.votes; leadId=o.id; } });
  card.querySelectorAll('.opt[data-oid]').forEach((el)=>{ const oid=+el.dataset.oid, v=byId[oid]??0, pct=total?Math.round(v/total*100):0;
    const f=el.querySelector('.fill'); if(f){ f.dataset.pct=pct; f.style.width=pct+'%'; }
    const p=el.querySelector('.pct'); if(p) p.textContent=pct+'%';
    el.classList.toggle('lead', total>0 && oid===leadId); });
  card.querySelectorAll('.vcount').forEach((s)=>s.textContent=fmt(total)+' votes');
  const ln=card.querySelector('[data-react] .n'); if(ln) ln.textContent=fmt(poll.likes);
  const rs=card.querySelector('[data-react] .rsum'); if(rs) rs.textContent=reactionSummary(poll);
  const cn=card.querySelector('.act.cmt .n'); if(cn) cn.textContent=fmt(poll.comments);
  const bn=card.querySelector('.act.count .n'); if(bn) bn.textContent=fmt(total);
}
async function doVote(code,oid){
  const poll=POLLS[code]; if(!poll||poll.hasVoted)return;
  try{ const{poll:fresh}=await api('/vote',{method:'POST',body:{pollId:poll.id,optionIds:[oid]}}); Object.assign(poll,fresh); replaceCard(poll);
    if(fresh.seriesNextCode){ toast('Answer saved — next question…'); loadSeriesNext(code, fresh.seriesNextCode); }
    else if(fresh.seriesId && fresh.seriesTotal>1){ toast('Survey complete ✓'); }
    else toast(ME?'Vote counted!':'Vote counted! Sign up to create your own polls.'); }
  catch(e){ toast(e.message); }
}
async function loadSeriesNext(prevCode, nextCode){
  const card=$('#card-'+prevCode); if(!card) return;
  card.classList.add('series-advancing');
  try{ const{poll}=await api('/poll',{query:{code:nextCode}}); POLLS[poll.code]=poll;
    setTimeout(()=>{
      const t=document.createElement('div'); t.innerHTML=cardHTML(poll);
      const el=t.firstElementChild; card.replaceWith(el); paintFills(el); el.classList.add('series-in');
    }, 900);
  }catch(e){ card.classList.remove('series-advancing'); }
}
async function openReactors(code){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim data-keepmenu><div class="sheet" style="max-height:72vh">
    <div class="sheet-grab"></div>
    <div class="sheet-head"><h3>Reactions</h3><button class="close" data-x>✕</button></div>
    <div id="reactors-tabs" class="react-tabs"></div>
    <div id="reactors-list" class="viewers-list">${skList(3)}</div></div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  let d; try{ d=await api('/poll/reactors',{query:{code}}); }catch(e){ $('#reactors-list').innerHTML=empt('⚠️',e.message); return; }
  if(!d.reactors.length){ $('#reactors-list').innerHTML=`<div class="ins-empty"><p>No reactions yet.</p></div>`; $('#reactors-tabs').innerHTML=''; return; }
  let filter='all';
  const tabs=$('#reactors-tabs');
  const render=()=>{
    const list=filter==='all'?d.reactors:d.reactors.filter((r)=>r.reaction===filter);
    tabs.innerHTML=`<button class="rt ${filter==='all'?'on':''}" data-rt="all">All ${fmt(d.total)}</button>`+
      REACTIONS.filter((r)=>d.counts[r[0]]).map((r)=>`<button class="rt ${filter===r[0]?'on':''}" data-rt="${r[0]}">${r[1]} ${fmt(d.counts[r[0]])}</button>`).join('');
    tabs.querySelectorAll('[data-rt]').forEach((b)=>b.onclick=()=>{ filter=b.dataset.rt; render(); });
    $('#reactors-list').innerHTML=list.map((v)=>`<div class="viewer-row">${avatar(v,'s40',true)}
      <div class="vr-info"><b data-author="${esc(v.username)}">${esc(v.name)}${vbadge(v.verified)}</b><span>@${esc(v.username)}</span></div>
      <span class="vr-emoji">${REMOJI[v.reaction]||'👍'}</span></div>`).join('');
  };
  render();
}
function openReactionPicker(code, anchor){
  closeMenus(); if(!ME){ openAuth(); return; }
  const poll=POLLS[code]; const r=anchor.getBoundingClientRect();
  const m=document.createElement('div'); m.className='react-pop'; m.dataset.keepmenu='1';
  m.style.left=Math.max(8,Math.min(window.innerWidth-280, r.left))+'px';
  m.style.top=(r.top-52)+'px';
  m.innerHTML=REACTIONS.map((x)=>`<button class="rbtn ${poll.myReaction===x[0]?'on':''}" data-r="${x[0]}" title="${x[2]}">${x[1]}</button>`).join('');
  document.body.appendChild(m);
  m.querySelectorAll('[data-r]').forEach((b)=>b.onclick=()=>{ closeMenus(); doReact(code, b.dataset.r); });
}
async function doReact(code, type){
  if(!ME){ openAuth(); return; }
  const poll=POLLS[code];
  try{ const r=await api('/react',{method:'POST',body:{pollId:poll.id, type}});
    poll.myReaction=r.myReaction; poll.liked=r.liked; poll.likes=r.likes; poll.reactionCounts=r.reactionCounts; replaceCard(poll); }
  catch(e){ toast(e.message); }
}
async function doSave(code){
  if(!ME){ openAuth(); return; }
  const poll=POLLS[code];
  try{ const r=await api('/save',{method:'POST',body:{pollId:poll.id}}); poll.saved=r.saved; replaceCard(poll); toast(r.saved?'Saved to your bookmarks':'Removed from saved'); if(route==='saved'&&!r.saved){ const n=$('#card-'+code); if(n)n.remove(); } }
  catch(e){ toast(e.message); }
}
/* share menu: WhatsApp / X / Facebook / Copy / native */
function openEmbedModal(poll){
  const embedUrl=location.origin+'/embed.php?c='+poll.code;
  const snippet=`<iframe src="${embedUrl}" width="100%" height="340" style="border:0;max-width:560px" frameborder="0" title="LinkPoll poll" loading="lazy"></iframe>`;
  lockScroll();
  const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim" data-scrim><div class="modal" data-keepmenu style="max-width:600px">
    <div class="mhead"><h3>Embed this poll</h3><button class="close" data-x>✕</button></div>
    <div class="desc">Paste this code into any website, blog or forum. People can <b>vote right there</b> — no LinkPoll account needed.</div>
    <div class="embed-prev"><iframe src="${esc(embedUrl)}" style="width:100%;height:300px;border:0" title="preview" loading="lazy"></iframe></div>
    <span class="f-lab" style="margin-top:12px">Embed code</span>
    <textarea class="field code" id="embed-code" readonly rows="3" onclick="this.select()">${esc(snippet)}</textarea>
    <div class="mfoot"><button class="btn btn-ghost" data-x>Close</button>
      <button class="btn btn-dark" id="embed-copy"><span style="color:var(--lime)">${ic('link',15)} Copy embed code</span></button></div>
  </div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  $('#embed-copy').onclick=async()=>{ try{ await navigator.clipboard.writeText(snippet); toast('Embed code copied!'); }catch(e){ const t=$('#embed-code'); t.select(); toast('Selected — press Ctrl/Cmd+C'); } };
}
function openSendPollModal(poll, linkOpts){
  if(!ME){ openAuth(); return; }
  // linkOpts = {url,title,subtitle} when sending a profile/community link instead of a poll
  const L = linkOpts||null;
  const sendUrl = L ? L.url : (poll&&poll.shareUrl) || '';
  const headTitle = L ? 'Send' : 'Send poll';
  const itemText = L ? (L.title||sendUrl) : (poll?poll.question:'');
  const qrSub = L ? (L.subtitle||'Scan to open this link') : 'Scan to open this poll';
  lockScroll(); const ov=$('#overlay');
  const sent={};
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim><div class="sheet" data-keepmenu>
    <div class="sheet-grab"></div>
    <div class="sheet-head"><h3>${esc(headTitle)}</h3><button class="close" data-x>✕</button></div>
    <div class="sheet-poll">${ic('bars',15)} <span>${esc(itemText)}</span></div>
    <div class="qr-wrap">${qrSVG(sendUrl,180)}<div class="qr-sub">${esc(qrSub)}</div></div>
    <div class="qr-link" id="qr-link">${esc(sendUrl)}</div>
    <div class="sheet-search"><span class="ss-ic">${ic('search',16)}</span><input id="sp-search" placeholder="Search people…" autocomplete="off"></div>
    <div id="sp-results" class="sp-grid">${skList(2)}</div>
    <div class="sheet-foot"><input id="sp-note" class="field" placeholder="Write a message… (optional)" maxlength="500">
      <button class="btn btn-dark" id="sp-done">Done</button></div>
  </div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('#sp-done').onclick=closeOverlay;
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  const box=$('#sp-results'), inp=$('#sp-search');
  const card=(u)=>`<button class="sp-card ${sent[u.username]?'sent':''}" data-u="${esc(u.username)}">
      ${avatar(u,'s48',false)}
      <span class="sp-name">${esc(u.name)}${vbadge(u.verified)}</span>
      <span class="sp-handle">@${esc(u.username)}</span>
      <span class="sp-btn">${sent[u.username]?'Sent ✓':'Send'}</span></button>`;
  const wire=()=>{ box.querySelectorAll('[data-u]').forEach((b)=>b.onclick=async()=>{
      const un=b.dataset.u; if(sent[un]) return;
      b.classList.add('sending');
      const note=$('#sp-note').value.trim();
      const payload = L ? {to:un, body:(note?note+'\n':'')+sendUrl} : {to:un, pollId:poll.id, body:note};
      try{ await api('/messages',{method:'POST',body:payload});
        sent[un]=1; b.classList.remove('sending'); b.classList.add('sent'); b.querySelector('.sp-btn').textContent='Sent ✓'; toast('Sent to @'+un); }
      catch(e){ b.classList.remove('sending'); toast(e.message); }
    }); };
  const renderList=(users)=>{ if(!users.length){ box.innerHTML='<div class="sp-hint">No people found.</div>'; return; }
    box.innerHTML=users.map(card).join(''); wire(); };
  // initial: suggested targets
  (async()=>{ try{ const d=await api('/share/targets'); renderList(d.targets||[]); if(!(d.targets||[]).length) box.innerHTML='<div class="sp-hint">Search for people to send this to.</div>'; }
    catch(e){ box.innerHTML='<div class="sp-hint">'+esc(e.message)+'</div>'; } })();
  let t=null;
  inp.addEventListener('input',()=>{ clearTimeout(t); t=setTimeout(async()=>{
    const q=inp.value.trim();
    if(q.length<1){ try{ const d=await api('/share/targets'); renderList(d.targets||[]); }catch(e){} return; }
    try{ const d=await api('/search',{query:{q}}); renderList((d.users||[]).filter((u)=>!u.isMe).slice(0,12)); }catch(e){}
  },220); });
  inp.focus();
}
/* Minimal QR Code encoder (byte mode, EC level M, versions 1–10).
   Returns a square array of 0/1 rows. Self-contained, no dependencies. */
function qrMatrix(text, forceMask){
  const EC=[ /* v1..v10 @ level M: [ecPerBlock, [ [blocks, dataPerBlock], ... ] ] */
    [10,[[1,16]]], [16,[[1,28]]], [26,[[1,44]]], [18,[[2,32]]], [24,[[2,43]]],
    [16,[[4,27]]], [18,[[4,31]]], [22,[[2,38],[2,39]]], [22,[[3,36],[2,37]]], [26,[[4,43],[1,44]]]
  ];
  const ALIGN=[[],[6,18],[6,22],[6,26],[6,30],[6,34],[6,22,38],[6,24,42],[6,26,46],[6,28,50]];

  // ---- utf-8 bytes ----
  const bytes=[]; for(const ch of unescape(encodeURIComponent(text))) bytes.push(ch.charCodeAt(0));

  // ---- pick the smallest version that fits ----
  let ver=-1, ecPer=0, groups=null, dataCW=0;
  for(let v=1; v<=10; v++){
    const [e,g]=EC[v-1]; let d=0; for(const [b,c] of g) d+=b*c;
    const countBits = v<10?8:16;
    if(4+countBits+8*bytes.length <= d*8){ ver=v; ecPer=e; groups=g; dataCW=d; break; }
  }
  if(ver<0) throw new Error('Text too long for QR');
  const size=17+4*ver, countBits=ver<10?8:16;

  // ---- bit stream ----
  const bits=[]; const put=(val,len)=>{ for(let i=len-1;i>=0;i--) bits.push((val>>i)&1); };
  put(4,4); put(bytes.length,countBits); for(const b of bytes) put(b,8);
  for(let i=0;i<4 && bits.length<dataCW*8;i++) bits.push(0);       // terminator
  while(bits.length%8) bits.push(0);
  const data=[]; for(let i=0;i<bits.length;i+=8){ let b=0; for(let j=0;j<8;j++) b=(b<<1)|bits[i+j]; data.push(b); }
  const PAD=[0xEC,0x11]; let pi=0; while(data.length<dataCW) data.push(PAD[pi++%2]);

  // ---- GF(256) ----
  const EXP=new Array(512), LOG=new Array(256);
  for(let i=0,x=1;i<255;i++){ EXP[i]=x; LOG[x]=i; x<<=1; if(x&0x100) x^=0x11D; }
  for(let i=255;i<512;i++) EXP[i]=EXP[i-255];
  const mul=(a,b)=> (a===0||b===0)?0:EXP[LOG[a]+LOG[b]];
  const genPoly=(n)=>{ let g=[1];
    for(let i=0;i<n;i++){ const ng=new Array(g.length+1).fill(0);
      for(let j=0;j<g.length;j++){ ng[j]^=mul(g[j],1); ng[j+1]^=mul(g[j],EXP[i]); } g=ng; }
    return g; };
  const rs=(blk,n)=>{ const g=genPoly(n), res=blk.concat(new Array(n).fill(0));
    for(let i=0;i<blk.length;i++){ const c=res[i]; if(!c) continue;
      for(let j=0;j<g.length;j++) res[i+j]^=mul(g[j],c); }
    return res.slice(blk.length); };

  // ---- split into blocks, compute EC, interleave ----
  const dBlocks=[], eBlocks=[]; let off=0;
  for(const [nb,cw] of groups) for(let i=0;i<nb;i++){ const blk=data.slice(off,off+cw); off+=cw; dBlocks.push(blk); eBlocks.push(rs(blk,ecPer)); }
  const final=[]; const maxD=Math.max(...dBlocks.map((b)=>b.length));
  for(let i=0;i<maxD;i++) for(const b of dBlocks) if(i<b.length) final.push(b[i]);
  for(let i=0;i<ecPer;i++) for(const b of eBlocks) final.push(b[i]);

  // ---- module grid ----
  const m=Array.from({length:size},()=>new Array(size).fill(null));   // null = free
  const setF=(r,c,v)=>{ if(r>=0&&r<size&&c>=0&&c<size) m[r][c]=v; };
  const finder=(r,c)=>{ for(let i=-1;i<=7;i++) for(let j=-1;j<=7;j++){
      const rr=r+i, cc=c+j; if(rr<0||rr>=size||cc<0||cc>=size) continue;
      const on=(i>=0&&i<=6&&(j===0||j===6))||(j>=0&&j<=6&&(i===0||i===6))||(i>=2&&i<=4&&j>=2&&j<=4);
      setF(rr,cc,on?1:0); } };
  finder(0,0); finder(0,size-7); finder(size-7,0);
  for(let i=8;i<size-8;i++){ const v=(i%2===0)?1:0; setF(6,i,v); setF(i,6,v); }   // timing
  const ap=ALIGN[ver-1];
  for(const r of ap) for(const c of ap){
    if((r<=8&&c<=8)||(r<=8&&c>=size-9)||(r>=size-9&&c<=8)) continue;
    for(let i=-2;i<=2;i++) for(let j=-2;j<=2;j++)
      setF(r+i,c+j,(Math.abs(i)===2||Math.abs(j)===2||(i===0&&j===0))?1:0); }
  setF(size-8,8,1);                                                    // dark module
  const reserve=()=>{ for(let i=0;i<9;i++){ if(m[8][i]===null) setF(8,i,0); if(m[i][8]===null) setF(i,8,0); }
    for(let i=0;i<8;i++){ if(m[8][size-1-i]===null) setF(8,size-1-i,0); if(m[size-1-i][8]===null) setF(size-1-i,8,0); }
    if(ver>=7){ for(let i=0;i<6;i++) for(let j=0;j<3;j++){ setF(size-11+j,i,0); setF(i,size-11+j,0); } } };
  reserve();

  // ---- place data (zigzag, right to left, skipping column 6) ----
  const free=Array.from({length:size},()=>new Array(size).fill(false));
  for(let r=0;r<size;r++) for(let c=0;c<size;c++) free[r][c]=(m[r][c]===null);
  let bit=0; const nextBit=()=>{ const byteI=bit>>3; const b=byteI<final.length?final[byteI]:0; const v=(b>>(7-(bit&7)))&1; bit++; return v; };
  let up=true;
  for(let c=size-1;c>0;c-=2){
    if(c===6) c--;
    for(let k=0;k<size;k++){ const r=up?size-1-k:k;
      for(let z=0;z<2;z++){ const cc=c-z; if(free[r][cc]){ m[r][cc]=nextBit(); } } }
    up=!up;
  }

  // ---- masking ----
  const maskFn=[ (r,c)=>(r+c)%2===0, (r,c)=>r%2===0, (r,c)=>c%3===0, (r,c)=>(r+c)%3===0,
    (r,c)=>(Math.floor(r/2)+Math.floor(c/3))%2===0, (r,c)=>((r*c)%2)+((r*c)%3)===0,
    (r,c)=>((((r*c)%2)+((r*c)%3))%2)===0, (r,c)=>((((r+c)%2)+((r*c)%3))%2)===0 ];
  const fmtBits=(mask)=>{ // EC level M = 0b00
    let v=(0<<3)|mask, d=v<<10;
    for(let i=4;i>=0;i--) if(d&(1<<(i+10))) d^=0x537<<i;
    return ((v<<10)|d)^0x5412; };
  const applyFmt=(g,mask)=>{ const f=fmtBits(mask);
    // k = 0..14 walking the 15-bit string from its most significant bit
    for(let k=0;k<15;k++){ const b=(f>>(14-k))&1;
      if(k<6) g[8][k]=b; else if(k===6) g[8][7]=b; else if(k===7) g[8][8]=b;
      else if(k===8) g[7][8]=b; else g[14-k][8]=b;
      if(k<7) g[size-1-k][8]=b; else g[8][size-15+k]=b; }
    g[size-8][8]=1; };
  const applyVer=(g)=>{ if(ver<7) return; let d=ver<<12;
    for(let i=5;i>=0;i--) if(d&(1<<(i+12))) d^=0x1F25<<i;
    const bitsV=(ver<<12)|d;
    for(let i=0;i<18;i++){ const b=(bitsV>>i)&1; const a=Math.floor(i/3), bb=i%3;
      g[size-11+bb][a]=b; g[a][size-11+bb]=b; } };
  const penalty=(g)=>{ let p=0;
    for(let r=0;r<size;r++){ let run=1; for(let c=1;c<size;c++){ if(g[r][c]===g[r][c-1]) run++; else { if(run>=5) p+=3+(run-5); run=1; } } if(run>=5) p+=3+(run-5); }
    for(let c=0;c<size;c++){ let run=1; for(let r=1;r<size;r++){ if(g[r][c]===g[r-1][c]) run++; else { if(run>=5) p+=3+(run-5); run=1; } } if(run>=5) p+=3+(run-5); }
    for(let r=0;r<size-1;r++) for(let c=0;c<size-1;c++){ const v=g[r][c]; if(v===g[r][c+1]&&v===g[r+1][c]&&v===g[r+1][c+1]) p+=3; }
    const pat=[1,0,1,1,1,0,1,0,0,0,0], pat2=[0,0,0,0,1,0,1,1,1,0,1];
    const has=(arr,i,pt)=>{ for(let k=0;k<pt.length;k++) if(arr[i+k]!==pt[k]) return false; return true; };
    for(let r=0;r<size;r++){ const row=g[r]; for(let c=0;c+11<=size;c++){ if(has(row,c,pat)) p+=40; if(has(row,c,pat2)) p+=40; } }
    for(let c=0;c<size;c++){ const col=[]; for(let r=0;r<size;r++) col.push(g[r][c]);
      for(let r=0;r+11<=size;r++){ if(has(col,r,pat)) p+=40; if(has(col,r,pat2)) p+=40; } }
    let dark=0; for(let r=0;r<size;r++) for(let c=0;c<size;c++) dark+=g[r][c];
    p += Math.floor(Math.abs(dark*100/(size*size)-50)/5)*10;
    return p; };

  let best=null, bestP=Infinity;
  for(let mask=0;mask<8;mask++){
    if(forceMask!=null && mask!==forceMask) continue;
    const g=m.map((row)=>row.slice());
    for(let r=0;r<size;r++) for(let c=0;c<size;c++) if(free[r][c] && maskFn[mask](r,c)) g[r][c]^=1;
    applyFmt(g,mask); applyVer(g);
    const p=penalty(g);
    if(p<bestP){ bestP=p; best=g; }
  }
  return best;
}

/* Render a QR matrix as a crisp, scalable SVG. */
function qrSVG(text, px){
  px=px||220;
  let m; try{ m=qrMatrix(text); }catch(e){ return '<div class="qr-err">Link too long for a QR code</div>'; }
  const n=m.length, quiet=4, total=n+quiet*2;
  let d='';
  for(let r=0;r<n;r++){ let c=0; while(c<n){ if(m[r][c]){ let w=1; while(c+w<n && m[r][c+w]) w++;
    d+='M'+(c+quiet)+' '+(r+quiet)+'h'+w+'v1h-'+w+'z'; c+=w; } else c++; } }
  return '<svg class="qr-svg" viewBox="0 0 '+total+' '+total+'" width="'+px+'" height="'+px+'" shape-rendering="crispEdges" xmlns="http://www.w3.org/2000/svg">'
    +'<rect width="'+total+'" height="'+total+'" fill="#fff"/><path d="'+d+'" fill="#15140f"/></svg>';
}
/* Download the QR as a PNG (works offline; no server round-trip). */
function downloadQR(text, name){
  let m; try{ m=qrMatrix(text); }catch(e){ toast('Link too long for a QR code'); return; }
  const n=m.length, quiet=4, scale=12, total=(n+quiet*2)*scale;
  const cv=document.createElement('canvas'); cv.width=cv.height=total;
  const g=cv.getContext('2d'); g.fillStyle='#fff'; g.fillRect(0,0,total,total); g.fillStyle='#15140f';
  for(let r=0;r<n;r++) for(let c=0;c<n;c++) if(m[r][c]) g.fillRect((c+quiet)*scale,(r+quiet)*scale,scale,scale);
  try{ const a=document.createElement('a'); a.download=(name||'linkpoll-qr')+'.png'; a.href=cv.toDataURL('image/png');
    document.body.appendChild(a); a.click(); a.remove(); toast('QR code saved'); }
  catch(e){ toast('Could not save the QR code'); }
}
/* Generic share sheet with QR — used for profiles, communities and polls. */
/* =====================================================================
   CELEBRATE — turn any achievement into a shareable image. Draws a branded
   card on a <canvas>, then lets the user download it or share via the native
   share sheet (with the image file) to any other app/platform.
   ===================================================================== */
function rrect(ctx,x,y,w,h,r){ ctx.beginPath(); ctx.moveTo(x+r,y);
  ctx.arcTo(x+w,y,x+w,y+h,r); ctx.arcTo(x+w,y+h,x,y+h,r); ctx.arcTo(x,y+h,x,y,r); ctx.arcTo(x,y,x+w,y,r); ctx.closePath(); }
function wrapText(ctx,text,maxW){ const words=String(text).split(/\s+/); const lines=[]; let line='';
  for(const w of words){ const t=line?line+' '+w:w; if(ctx.measureText(t).width>maxW && line){ lines.push(line); line=w; } else line=t; }
  if(line) lines.push(line); return lines; }
function celebLoadImg(src, cross){
  return new Promise((res)=>{ if(!src) return res(null); const img=new Image();
    if(cross) img.crossOrigin='anonymous';
    img.onload=()=>res(img); img.onerror=()=>res(null);
    try{ img.src=src; }catch(_){ res(null); } });
}
async function makeCelebrationCanvas(opts, useAvatarImg){
  const S=1080, c=document.createElement('canvas'); c.width=S; c.height=S; const x=c.getContext('2d');
  // ---- light background with soft lime glow ----
  const bg=x.createLinearGradient(0,0,0,S); bg.addColorStop(0,'#ffffff'); bg.addColorStop(1,'#f0eee5');
  x.fillStyle=bg; x.fillRect(0,0,S,S);
  const glow=x.createRadialGradient(S/2,300,30,S/2,300,540); glow.addColorStop(0,'rgba(194,242,58,.30)'); glow.addColorStop(1,'rgba(194,242,58,0)');
  x.fillStyle=glow; x.fillRect(0,0,S,640);
  // ---- confetti ----
  const cols=['#c2f23a','#a7d825','#7c8cff','#ff8a5c','#f5b301','#15140f']; let seed=97;
  const rnd=()=>{ seed=(seed*9301+49297)%233280; return seed/233280; };
  for(let i=0;i<44;i++){ const px=rnd()*S, py=rnd()*S, r=6+rnd()*10;
    x.globalAlpha=0.10+rnd()*0.20; x.fillStyle=cols[i%cols.length];
    if(rnd()>0.5){ x.beginPath(); x.arc(px,py,r,0,7); x.fill(); } else { x.save(); x.translate(px,py); x.rotate(rnd()*3); x.fillRect(-r/2,-r/2,r,r*1.6); x.restore(); } }
  x.globalAlpha=1;
  // ---- brand: logo + wordmark ----
  x.textAlign='center';
  x.font='800 42px "Hanken Grotesk", system-ui, sans-serif';
  const bw=x.measureText('LinkPoll').width;
  const logo=await celebLoadImg('logo-icon.png', false); // same-origin, safe
  if(logo){ const lsz=54, gap=14, gw=lsz+gap+bw, gx=S/2-gw/2;
    x.drawImage(logo, gx, 60, lsz, lsz);
    x.textAlign='left'; x.fillStyle='#15140f'; x.fillText('LinkPoll', gx+lsz+gap, 100); x.textAlign='center';
  } else {
    x.fillStyle='#a7d825'; x.beginPath(); x.arc(S/2-bw/2-24,84,10,0,7); x.fill();
    x.fillStyle='#15140f'; x.fillText('LinkPoll',S/2,98);
  }
  // ---- avatar (hero) with lime ring ----
  const acx=S/2, acy=304, ar=104;
  x.fillStyle='#c2f23a'; x.beginPath(); x.arc(acx,acy,ar+11,0,7); x.fill();
  x.fillStyle='#ffffff'; x.beginPath(); x.arc(acx,acy,ar+5,0,7); x.fill();
  x.save(); x.beginPath(); x.arc(acx,acy,ar,0,7); x.clip();
  let drew=false;
  if(useAvatarImg && ME && ME.avatar){ const img=await celebLoadImg(ME.avatar, /^https?:/i.test(ME.avatar));
    if(img){ // cover-fit
      const iw=img.width||1, ih=img.height||1, scale=Math.max((ar*2)/iw,(ar*2)/ih);
      const dw=iw*scale, dh=ih*scale; x.drawImage(img, acx-dw/2, acy-dh/2, dw, dh); drew=true; } }
  if(!drew){ x.fillStyle=(ME&&ME.color)||'#15140f'; x.fillRect(acx-ar,acy-ar,ar*2,ar*2);
    x.fillStyle='#ffffff'; x.font='800 82px "Hanken Grotesk", system-ui, sans-serif'; x.textBaseline='middle';
    x.fillText(initials((ME&&ME.name)||'?'), acx, acy+4); x.textBaseline='alphabetic'; }
  x.restore();
  // achievement emoji badge bottom-right of avatar
  const bx=acx+ar-4, by=acy+ar-4, br=50;
  x.fillStyle='#ffffff'; x.beginPath(); x.arc(bx,by,br+7,0,7); x.fill();
  const bgr=x.createLinearGradient(0,by-br,0,by+br); bgr.addColorStop(0,'#c2f23a'); bgr.addColorStop(1,'#a7d825');
  x.fillStyle=bgr; x.beginPath(); x.arc(bx,by,br,0,7); x.fill();
  x.font='52px "Segoe UI Emoji","Apple Color Emoji","Noto Color Emoji", sans-serif'; x.textBaseline='middle';
  x.fillText(opts.icon||'🏆', bx, by+3); x.textBaseline='alphabetic';
  // ---- eyebrow ----
  x.font='800 28px "Hanken Grotesk", system-ui, sans-serif'; x.fillStyle='#6f9412';
  x.fillText((opts.eyebrow||'ACHIEVEMENT').toUpperCase(), S/2, 512);
  // ---- title (wrapped, max 2 lines) ----
  x.fillStyle='#15140f'; x.font='800 60px "Hanken Grotesk", system-ui, sans-serif';
  const lines=wrapText(x, opts.title||'', S-150).slice(0,2); let ty=534;
  lines.forEach((ln)=>{ x.fillText(ln, S/2, ty+=68); });
  // ---- stat pill (dark bg, lime text) ----
  if(opts.stat){ x.font='800 36px "Hanken Grotesk", system-ui, sans-serif';
    const pw=Math.min(S-120, x.measureText(opts.stat).width+64), ph=70, pxx=S/2-pw/2, pyy=ty+26;
    x.fillStyle='#15140f'; rrect(x,pxx,pyy,pw,ph,35); x.fill();
    x.fillStyle='#c2f23a'; x.textBaseline='middle'; x.fillText(opts.stat, S/2, pyy+ph/2+2); x.textBaseline='alphabetic'; }
  // ---- divider above the person band ----
  x.strokeStyle='rgba(21,20,15,.09)'; x.lineWidth=2;
  x.beginPath(); x.moveTo(S/2-150,892); x.lineTo(S/2+150,892); x.stroke();
  // ---- name + handle (fixed bottom band — no overlap with footer) ----
  if(ME){
    x.fillStyle='#15140f'; x.font='800 34px "Hanken Grotesk", system-ui, sans-serif';
    x.fillText((ME.name||('@'+(opts.handle||''))), S/2, 946);
    if(opts.handle){ x.fillStyle='#8a8578'; x.font='600 26px "Hanken Grotesk", system-ui, sans-serif';
      x.fillText('@'+opts.handle, S/2, 986); }
  }
  // ---- footer (lime dot + wordmark colour to match the header) ----
  x.font='700 24px "Hanken Grotesk", system-ui, sans-serif';
  const fw=x.measureText('link-poll.com').width;
  x.fillStyle='#a7d825'; x.beginPath(); x.arc(S/2-fw/2-16,1032,6,0,7); x.fill();
  x.fillStyle='#a9a496'; x.fillText('link-poll.com', S/2, 1040);
  return c;
}
async function openCelebrate(opts){
  if(!ME) return openAuth();
  opts=opts||{}; if(!opts.handle && ME) opts.handle=ME.username;
  const inviteLink='https://link-poll.com/?ref='+encodeURIComponent(ME.username);
  const caption=`🎉 ${opts.title||'New achievement'}${opts.stat?' — '+opts.stat:''} on LinkPoll!\n\nJoin me on LinkPoll and see what the world thinks 👇\n${inviteLink}\n\n#LinkPoll #Polls`;
  const shareText=caption;
  const pageUrl=inviteLink;
  // open the sheet immediately with a loading state
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim data-keepmenu>
    <div class="sheet celebrate-sheet" role="dialog" aria-label="Share achievement">
      <div class="sheet-grab"></div>
      <div class="sheet-head"><h3>Share your achievement 🎉</h3><button class="close" data-x aria-label="Close">✕</button></div>
      <div id="celeb-body"><div class="ais-loading"><span class="ais-dot"></span><span class="ais-dot"></span><span class="ais-dot"></span><p>Creating your card…</p></div></div>
    </div></div>`;
  $('[data-x]',ov).onclick=closeOverlay;
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  // build the canvas (with the user's photo; retry without it if a cross-origin photo taints the canvas)
  let canvas, dataUrl;
  try{ canvas=await makeCelebrationCanvas(opts, true); dataUrl=canvas.toDataURL('image/png'); }
  catch(e){ try{ canvas=await makeCelebrationCanvas(opts, false); dataUrl=canvas.toDataURL('image/png'); }
    catch(e2){ const b=$('#celeb-body'); if(b) b.innerHTML=empt('⚠️','Could not create the image','Please try again.'); return; } }
  const body=$('#celeb-body'); if(!body) return; // sheet was closed while generating
  body.innerHTML=`<div class="celeb-preview"><img src="${dataUrl}" alt="Achievement card" class="celeb-img"></div>
    <div class="celeb-actions">
      <button class="btn btn-lime btn-block" id="celeb-share">${ic('share',16)} Share image</button>
      <div class="celeb-plats">
        <button class="cplat" data-plat="instagram"><span class="cplat-ic ig">📸</span>Instagram</button>
        <button class="cplat" data-plat="whatsapp"><span class="cplat-ic wa">💬</span>WhatsApp</button>
        <button class="cplat" data-plat="facebook"><span class="cplat-ic fb">f</span>Facebook</button>
        <button class="cplat" data-plat="x"><span class="cplat-ic xx">𝕏</span>X</button>
        <button class="cplat" data-plat="linkedin"><span class="cplat-ic li">in</span>LinkedIn</button>
        <button class="cplat" data-plat="reddit"><span class="cplat-ic rd">r/</span>Reddit</button>
      </div>
      <div class="celeb-dl-row">
        <span class="celeb-dl-lbl">${ic('download',15)} Save to device</span>
        <button class="btn btn-ghost btn-sm" id="celeb-png">PNG</button>
        <button class="btn btn-ghost btn-sm" id="celeb-jpg">JPG</button>
      </div>
      <div class="celeb-caption">
        <div class="cap-head"><span>Post caption</span><button class="cap-copy" data-cap>${ic('link',13)} Copy caption</button></div>
        <textarea class="cap-text" readonly rows="5">${esc(caption)}</textarea>
      </div>
      <div class="celeb-invite">
        <span class="inv-lbl">🔗 Your invite link</span>
        <span class="inv-url">${esc(inviteLink)}</span>
        <button class="inv-copy" data-invcopy>Copy</button>
      </div>
      <p class="celeb-hint">“Share image” posts the actual picture to any app (Instagram, Stories, WhatsApp…). Copy the caption to paste your invite link alongside it.</p>
    </div>`;
  const getBlob=(type,q)=>new Promise((res)=>{ try{ canvas.toBlob((b)=>res(b), type||'image/png', q==null?0.95:q); }catch(_){ res(null); } });
  const downloadAs=async(fmt)=>{
    const type=fmt==='jpg'?'image/jpeg':'image/png', ext=fmt==='jpg'?'jpg':'png';
    let b=await getBlob(type, fmt==='jpg'?0.92:0.95);
    const a=document.createElement('a');
    a.href=b?URL.createObjectURL(b):(fmt==='jpg'?canvas.toDataURL('image/jpeg',0.92):dataUrl);
    a.download='linkpoll-achievement.'+ext; document.body.appendChild(a); a.click(); a.remove();
    if(b) setTimeout(()=>URL.revokeObjectURL(a.href),1500); toast(ext.toUpperCase()+' downloaded ✓');
  };
  const shareNative=async(hintOnDesktop)=>{
    try{ const b=await getBlob('image/png',0.95); const file=b?new File([b],'linkpoll-achievement.png',{type:'image/png'}):null;
      if(file && navigator.canShare && navigator.canShare({files:[file]})){
        await navigator.share({files:[file], title:'My LinkPoll achievement', text:shareText+' '+pageUrl}); return true;
      } else if(navigator.share){ await navigator.share({title:'My LinkPoll achievement', text:shareText, url:pageUrl}); return true; }
      await downloadAs('png'); if(hintOnDesktop) toast('Image saved — open the app and upload it 📸'); return false;
    }catch(e){ return false; }
  };
  const platShare=(plat)=>{
    if(plat==='instagram'){ shareNative(true); return; } // Instagram has no web post URL — use native share / download
    const u=encodeURIComponent(pageUrl), t=encodeURIComponent(shareText);
    const urls={
      whatsapp:'https://wa.me/?text='+encodeURIComponent(shareText+' '+pageUrl),
      facebook:'https://www.facebook.com/sharer/sharer.php?u='+u,
      x:'https://twitter.com/intent/tweet?text='+t+'&url='+u,
      linkedin:'https://www.linkedin.com/sharing/share-offsite/?url='+u,
      reddit:'https://www.reddit.com/submit?url='+u+'&title='+t,
      telegram:'https://t.me/share/url?url='+u+'&text='+t };
    if(urls[plat]) window.open(urls[plat],'_blank','noopener');
  };
  $('#celeb-png',ov).onclick=()=>downloadAs('png');
  $('#celeb-jpg',ov).onclick=()=>downloadAs('jpg');
  $('#celeb-share',ov).onclick=()=>shareNative(true);
  ov.querySelectorAll('[data-plat]').forEach((b)=>b.onclick=()=>platShare(b.dataset.plat));
  const copyText=async(txt,okMsg)=>{
    try{ await navigator.clipboard.writeText(txt); toast(okMsg); }
    catch(_){ const ta=document.createElement('textarea'); ta.value=txt; ta.style.position='fixed'; ta.style.opacity='0';
      document.body.appendChild(ta); ta.select(); try{ document.execCommand('copy'); toast(okMsg); }catch(e){ toast('Copy not supported — select and copy manually'); } ta.remove(); }
  };
  const capBtn=$('[data-cap]',ov); if(capBtn) capBtn.onclick=()=>copyText(caption,'Caption copied ✓');
  const invBtn=$('[data-invcopy]',ov); if(invBtn) invBtn.onclick=()=>copyText(inviteLink,'Invite link copied ✓');
  const capTa=$('.cap-text',ov); if(capTa) capTa.onclick=()=>{ capTa.select(); };
}
/* Build the list of the user's achievements and let them pick one to celebrate. */
async function openCelebratePicker(){
  if(!ME) return openAuth();
  // open the sheet immediately with a loading state (so the click always does something)
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim data-keepmenu><div class="sheet celeb-picker">
    <div class="sheet-grab"></div>
    <div class="cpick-head"><span class="cpick-emoji">🎉</span><div><h3>Celebrate an achievement</h3><span>Pick one to make a shareable card</span></div>
      <button class="close" data-x aria-label="Close">✕</button></div>
    <div id="cpick-list"><div class="ais-loading"><span class="ais-dot"></span><span class="ais-dot"></span><span class="ais-dot"></span><p>Gathering your achievements…</p></div></div>
  </div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  let pts=null, bd=null;
  try{ pts=await api('/me/points'); }catch(e){}
  try{ bd=await api('/badges'); }catch(e){}
  const items=[];
  if(pts&&pts.level){ items.push({icon:pts.level.icon||'🌱', eyebrow:'Level reached',
    title:`Level ${pts.level.level}: ${pts.level.name}`, stat:`${fmt(pts.points)} points`}); }
  else if(ME){ items.push({icon:ME.levelIcon||'🌱', eyebrow:'My rank', title:ME.levelName||'On LinkPoll', stat:`${fmt(ME.points||0)} points`}); }
  if(pts&&pts.rank){ items.push({icon:'🏆', eyebrow:'Leaderboard', title:`Ranked #${fmt(pts.rank)} on LinkPoll`, stat:`${fmt(pts.points)} points`}); }
  if(bd&&bd.streak>0){ items.push({icon:'🔥', eyebrow:'On a streak', title:`${bd.streak}-day streak!`, stat:`${bd.streak} days`}); }
  if(bd&&bd.quizTotal>0){ const acc=Math.round(bd.quizCorrect/bd.quizTotal*100);
    items.push({icon:'🎯', eyebrow:'Quiz master', title:`${acc}% quiz accuracy`, stat:`${bd.quizCorrect}/${bd.quizTotal} correct`}); }
  if(bd&&bd.badges){ bd.badges.filter((b)=>b.earned).forEach((b)=>items.push({icon:b.icon||'🏅', eyebrow:'Badge earned', title:b.label, stat:b.desc||''})); }
  const list=$('#cpick-list'); if(!list) return; // sheet was closed
  if(!items.length){
    list.innerHTML=`<div class="cpick-empty">${empt('✨','Nothing to celebrate yet','Earn points, keep a streak, or unlock badges — then come back to make a card.',
      )}</div>`;
    return;
  }
  list.innerHTML=items.map((it,i)=>`<button class="cpick-item" data-ci="${i}">
      <span class="cpick-ic">${it.icon}</span>
      <span class="cpick-txt"><span class="cpick-eyebrow">${esc(it.eyebrow||'')}</span><span class="cpick-title">${esc(it.title)}</span>${it.stat?`<span class="cpick-stat">${esc(it.stat)}</span>`:''}</span>
      <span class="cpick-go">${ic('arrow',16)}</span></button>`).join('');
  list.querySelectorAll('[data-ci]').forEach((b)=>b.onclick=()=>{ const it=items[+b.dataset.ci]; closeOverlay(); openCelebrate(it); });
}

function openShareSheet(opts){
  const url=opts.url, title=opts.title||'', sub=opts.subtitle||'', poll=opts.poll||null;
  closeMenus(); lockScroll(); const ov=$('#overlay');
  const eu=encodeURIComponent(url), et=encodeURIComponent(title||'Check this out on LinkPoll');
  const sent={};
  // single-row apps (horizontal scroll)
  const apps=[
    {k:'copy',label:'Copy link',html:ic('link',22)},
    {k:'qr',label:'QR code',html:'<span class="aemoji">▦</span>'},
    {k:'wa',label:'WhatsApp',html:'<span class="aemoji">🟢</span>',href:'https://wa.me/?text='+et+'%20'+eu},
    {k:'x',label:'X',html:'<span class="aemoji">𝕏</span>',href:'https://twitter.com/intent/tweet?text='+et+'&url='+eu},
    {k:'fb',label:'Facebook',html:'<span class="aemoji">📘</span>',href:'https://www.facebook.com/sharer/sharer.php?u='+eu},
    {k:'tg',label:'Telegram',html:'<span class="aemoji">✈️</span>',href:'https://t.me/share/url?url='+eu+'&text='+et},
    {k:'mail',label:'Email',html:'<span class="aemoji">✉️</span>',href:'mailto:?subject='+et+'&body='+eu},
  ];
  if(poll) apps.push({k:'story',label:'Add to story',html:'<span class="aemoji">⊕</span>'},{k:'embed',label:'Embed',html:ic('bars',22)});
  if(navigator.share) apps.splice(1,0,{k:'native',label:'More…',html:ic('share',22)});
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim><div class="sheet share-sheet" data-keepmenu>
    <div class="sheet-grab"></div>
    <div class="sheet-head"><h3>Share</h3><button class="close" data-x>✕</button></div>
    ${title?`<div class="sheet-poll"><span>${esc(title)}</span></div>`:''}
    ${ME?`<div class="share-people-wrap"><div id="share-people" class="share-people">${skRow()}</div></div>`:''}
    <div class="share-apps" id="share-apps">${apps.map((a,i)=>`<button class="share-app" data-i="${i}"><span class="sa-ic">${a.html}</span><span class="sa-lbl">${esc(a.label)}</span></button>`).join('')}</div>
    <div class="qr-panel" id="qr-panel" ${opts.showQR?'':'hidden'}>
      <div class="qr-wrap">${qrSVG(url,200)}${sub?`<div class="qr-sub">${esc(sub)}</div>`:''}</div>
      <div class="qr-link" id="qr-link">${esc(url)}</div>
      <button class="btn btn-dark btn-sm qr-save" id="qr-save">${ic('plus',15)} Save QR image</button>
    </div>
  </div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  const bump=poll?async()=>{ try{ const x=await api('/share',{method:'POST',body:{pollId:poll.id}}); poll.shares=x.shares; replaceCard(poll); }catch(e){} }:()=>{};
  const lk=$('#qr-link'); if(lk) lk.onclick=async()=>{ try{ await navigator.clipboard.writeText(url); toast('Link copied!'); }catch(e){ prompt('Copy:',url); } };
  const qs=$('#qr-save'); if(qs) qs.onclick=()=>downloadQR(url, opts.fileName||'linkpoll-qr');

  // people row — send inside LinkPoll (poll → send poll; profile/community → send the link)
  if(ME){
    (async()=>{ let targets=[]; try{ targets=(await api('/share/targets')).targets||[]; }catch(e){}
      const box=$('#share-people'); if(!box) return;
      const moreBtn=`<button class="sp-person more" data-more><span class="sp-pav search">${ic('search',22)}</span><span class="sp-pname">Search</span></button>`;
      box.innerHTML=(targets.length?targets.slice(0,12).map((u)=>`<button class="sp-person" data-u="${esc(u.username)}"><span class="sp-pav">${avatar(u,'s56',false)}</span><span class="sp-pname">${esc(u.username)}</span></button>`).join(''):'')+moreBtn;
      box.querySelectorAll('[data-u]').forEach((b)=>b.onclick=async()=>{ const un=b.dataset.u; if(sent[un])return; b.classList.add('sending');
        try{ await api('/messages',{method:'POST',body: poll?{to:un,pollId:poll.id,body:''}:{to:un,body:url}}); sent[un]=1;
          b.classList.remove('sending'); b.classList.add('sent'); b.querySelector('.sp-pname').textContent='Sent ✓'; bump(); }
        catch(e){ b.classList.remove('sending'); toast(e.message); } });
      const more=box.querySelector('[data-more]'); if(more) more.onclick=()=>{ closeOverlay();
        if(poll) openSendPollModal(poll);
        else openSendPollModal(null,{url,title:title||'LinkPoll',subtitle:opts.subtitle||'Scan to open this link'}); };
    })();
  }

  ov.querySelectorAll('.share-app').forEach((b)=>b.onclick=async()=>{
    const a=apps[+b.dataset.i];
    if(a.k==='qr'){ const panel=$('#qr-panel'); if(panel){ panel.hidden=!panel.hidden; if(!panel.hidden && panel.scrollIntoView) try{ panel.scrollIntoView({behavior:'smooth',block:'nearest'}); }catch(e){} } return; }
    if(a.k==='copy'){ try{ await navigator.clipboard.writeText(url); toast('Link copied!'); }catch(e){ prompt('Copy:',url); } bump(); return; }
    if(a.k==='native'){ try{ await navigator.share({title:title||'LinkPoll',url}); bump(); }catch(e){} return; }
    if(a.k==='story' && poll){ try{ await api('/stories',{method:'POST',body:{pollId:poll.id}}); bump(); closeOverlay(); toast('Added to your story! It disappears in 24h.'); if(route===''||route==='home'||route==='profile') loadStoryTray&&loadStoryTray(); }catch(e){ toast(e.message); } return; }
    if(a.k==='embed' && poll){ closeOverlay(); openEmbedModal(poll); bump(); return; }
    window.open(a.href,'_blank','noopener'); bump();
  });
}
function shareProfile(username, name){
  openShareSheet({url:location.origin+'/'+username, title:(name||'@'+username), subtitle:'Scan to open this profile', fileName:'linkpoll-'+username});
}
function shareBio(username, name){
  openShareSheet({url:location.origin+'/b/'+username, title:(name?name+' · Link in bio':'@'+username+' · Link in bio'), subtitle:'Scan to open this link-in-bio', fileName:'linkpoll-bio-'+username});
}
function qrBio(username, name){
  openShareSheet({url:location.origin+'/b/'+username, title:(name?name+' · Link in bio':'@'+username+' · Link in bio'), subtitle:'Scan to open this link-in-bio', fileName:'linkpoll-bio-'+username, showQR:true});
}
function shareCommunity(slug, name){
  openShareSheet({url:location.origin+'/c/'+slug, title:name||slug, subtitle:'Scan to open this community', fileName:'linkpoll-'+slug});
}
function openShareMenu(code, anchor){
  const poll=POLLS[code]; if(!poll) return;
  openShareSheet({url:poll.shareUrl, title:poll.question, subtitle:'Scan to open this poll', fileName:'linkpoll-'+code, poll});
}

function skRow(){ return '<div class="sp-skel">'+Array(5).fill('<span></span>').join('')+'</div>'; }
/* owner menu: copy link / close / delete */
/* Reusable action menu: bottom sheet on mobile, anchored dropdown on desktop.
   actions = [{label, icon, danger, onClick}] */
function actionSheet(actions, anchor, title, head){
  closeMenus(); actions=actions.filter(Boolean);
  if(window.innerWidth<=820){
    lockScroll(); const ov=$('#overlay');
    ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim data-keepmenu><div class="sheet action-sheet">
      <div class="sheet-grab"></div>${head?`<div class="as-head">${head}</div>`:''}${title?`<div class="as-title">${esc(title)}</div>`:''}
      <div class="as-list">${actions.map((a,i)=>`<button class="as-item ${a.danger?'danger':''}" data-i="${i}">${a.icon?`<span class="as-ic">${a.icon}</span>`:''}<span>${esc(a.label)}</span></button>`).join('')}</div>
      <button class="as-item as-cancel" data-cancel>Cancel</button></div></div>`;
    $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
    $('[data-cancel]',ov).onclick=closeOverlay;
    const hp=ov.querySelector('.as-head [data-author]'); if(hp) hp.onclick=()=>{ closeOverlay(); go('#/u/'+hp.dataset.author); };
    actions.forEach((a,i)=>{ const b=ov.querySelector(`.as-item[data-i="${i}"]`); if(b) b.onclick=()=>{ closeOverlay(); a.onClick(); }; });
  } else {
    const r=(anchor||document.body).getBoundingClientRect();
    const m=document.createElement('div'); m.className='menu'; m.dataset.keepmenu='1';
    m.style.right=Math.max(10,window.innerWidth-r.right)+'px'; m.style.top=(r.bottom+6)+'px';
    m.innerHTML=actions.map((a,i)=>`<button class="${a.danger?'danger':''}" data-i="${i}">${a.icon?a.icon+' ':''}${esc(a.label)}</button>`).join('');
    document.body.appendChild(m);
    actions.forEach((a,i)=>{ const b=m.querySelector(`[data-i="${i}"]`); if(b) b.onclick=()=>{ closeMenus(); a.onClick(); }; });
  }
}
function openPollMenu(code, anchor){
  const poll=POLLS[code]; if(!poll) return;
  const a=poll.author||{}; const isOwner=!!(a.isMe || (ME&&ME.username===a.username)); const canMod=ME&&ME.admin;
  const actions=[
    isOwner && {label:'Who voted', icon:'👥', onClick:()=>openVoters(poll)},
    isOwner && {label:'Audience insights', icon:'📈', onClick:()=>openInsights(poll)},
    {label:'Copy link', icon:ic('link',18), onClick:async()=>{ try{ await navigator.clipboard.writeText(poll.shareUrl); toast('Link copied!'); }catch(e){ prompt('Copy:',poll.shareUrl); } }},
    (!isOwner&&a.username) && {label:'View profile', icon:ic('user',18), onClick:()=>go('#/u/'+a.username)},
    (ME&&!isOwner&&a.username) && {label:(a.following?'Unfollow ':'Follow ')+'@'+a.username, icon:ic('user',18), onClick:async()=>{ try{ const r=await api('/follow',{method:'POST',body:{username:a.username}}); a.following=r.following; replaceCard(poll); toast(r.following?('Following @'+a.username):('Unfollowed @'+a.username)); }catch(e){ toast(e.message); } }},
    (ME&&!isOwner) && {label:'Not interested', icon:ic('eye',18), onClick:()=>{ const n=$('#card-'+code); if(n){ n.style.transition='opacity .22s ease,transform .22s ease'; n.style.opacity='0'; n.style.transform='scale(.96)'; setTimeout(()=>n.remove(),220);} toast('Thanks — we’ll show less like this'); }},
    ((isOwner&&poll.canEdit)||canMod) && {label:'Edit poll'+(!isOwner&&canMod?' (admin)':''), icon:ic('edit',18), onClick:()=>openEditPoll(code)},
    ((isOwner||canMod)&&!poll.closed) && {label:'Close poll'+(!isOwner&&canMod?' (admin)':''), icon:ic('clock',18), onClick:async()=>{ try{ const{poll:fresh}=await api('/poll/close',{method:'POST',body:{pollId:poll.id}}); Object.assign(poll,fresh); replaceCard(poll); toast('Poll closed'); }catch(e){ toast(e.message); } }},
    (ME&&!isOwner) && {label:'Report', icon:ic('flag',18), danger:true, onClick:()=>openReport('poll', poll.id)},
    (isOwner||canMod) && {label:'Delete poll'+(!isOwner&&canMod?' (admin)':''), icon:ic('out',18), danger:true, onClick:async()=>{ if(!confirm(isOwner?'Delete this poll permanently?':'Remove this poll as moderator?'))return; try{ await api('/poll/delete',{method:'POST',body:{pollId:poll.id}}); try{ delete POLLS[code]; }catch(_e){} invalidateViews(true); const n=$('#card-'+code); if(n)n.remove(); toast('Poll deleted'); if(route==='poll')go('#/'); }catch(e){ toast(e.message); } }},
  ];
  const head=a.username?`<div class="as-person" data-author="${esc(a.username)}">${avatar(a,'s44',true)}<div class="as-pwho"><b>${esc(a.author||a.name||a.username)}${vbadge(a.verified)}</b><span>@${esc(a.username)}</span></div>${!isOwner?'<span class="as-pgo">›</span>':''}</div>`:'';
  actionSheet(actions, anchor, '', head);
}
async function doShare(code){ openShareMenu(code, $('#card-'+code+' [data-share]')||document.body); }
async function openSurveyResults(code){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim data-keepmenu><div class="sheet" style="max-height:84vh;overflow-y:auto">
    <div class="sheet-grab"></div>
    <div class="sheet-head"><h3>📊 Survey results</h3><button class="close" data-x>✕</button></div>
    <div id="sr-body">${skList(3)}</div></div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  try{
    const r=await api('/series/results',{query:{code}});
    const body=$('#sr-body'); if(!body) return;
    body.innerHTML=`<div class="sr-title">${esc(r.title)}</div>` + r.questions.map((qq,qi)=>`
      <div class="sr-q">
        <div class="sr-qhead"><span class="sr-num">Q${qi+1}</span> ${esc(qq.question)} ${qq.isQuiz?'<span class="quiz-chip">🎯 Quiz</span>':''}</div>
        <div class="sr-total">${fmt(qq.total)} response${qq.total===1?'':'s'}</div>
        ${qq.options.map((o)=>`<div class="sr-opt ${o.correct?'correct':''}">
          <div class="sr-oprow"><span class="sr-olbl">${o.correct?'✓ ':''}${esc(o.label)}</span><span class="sr-opct">${o.pct}% (${fmt(o.votes)})</span></div>
          <div class="sr-bar"><span style="width:${o.pct}%"></span></div></div>`).join('')}
      </div>`).join('');
  }catch(e){ const body=$('#sr-body'); if(body) body.innerHTML=empt('⚠️',e.message); }
}
async function openAiSummary(code){
  const poll=POLLS[code];
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim data-keepmenu><div class="sheet ais-modal">
    <div class="sheet-grab"></div>
    <div class="ais-head">
      <span class="ais-badge">${ic('sparkle',20)}</span>
      <div class="ais-htxt"><h3>AI Summary</h3><span>Live poll analysis</span></div>
      <button class="close" data-x aria-label="Close">✕</button>
    </div>
    <div id="ai-body"><div class="ais-loading"><span class="ais-dot"></span><span class="ais-dot"></span><span class="ais-dot"></span><p>Reading the poll…</p></div></div>
  </div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  try{
    const r=await api('/poll/summary',{query:{code}});
    const body=$('#ai-body'); if(!body) return;
    body.innerHTML=`${poll?`<div class="ais-q">${esc(poll.question)}</div>`:''}
      <div class="ais-para">${esc(r.summary)}</div>
      ${r.insights&&r.insights.length?`<div class="ais-stats">${r.insights.map((s)=>`<div class="ais-stat">${esc(s)}</div>`).join('')}</div>`:''}
      <p class="ais-foot">${ic('sparkle',13)} Generated from this poll's live results.</p>`;
  }catch(e){ const body=$('#ai-body'); if(body) body.innerHTML=empt('⚠️','Couldn’t generate a summary', e.message); }
}

/* =====================================================================
   VIEWS
   ===================================================================== */
function setMain(html){ if(route!=='biocard'){ try{ document.body.style.background=''; }catch(e){} } $('#main').innerHTML=html; }
/* ---- skeleton loaders ---- */
function skCard(){ return `<div class="sk-card">
    <div class="sk-row"><span class="sk sk-av"></span><span class="sk sk-line" style="width:42%"></span></div>
    <span class="sk sk-line lg" style="width:82%"></span>
    <span class="sk sk-opt"></span><span class="sk sk-opt"></span><span class="sk sk-opt"></span>
    <div class="sk-row" style="margin-top:14px"><span class="sk sk-pill"></span><span class="sk sk-pill"></span><span class="sk sk-pill"></span></div></div>`; }
function skCards(n=3){ return `<div class="sk-wrap">${Array.from({length:n},skCard).join('')}</div>`; }
function skList(n=5){ return `<div class="sk-wrap">${Array.from({length:n},()=>`<div class="sk-card srow"><span class="sk sk-av"></span>
    <div style="flex:1"><span class="sk sk-line" style="width:55%"></span><span class="sk sk-line" style="width:32%;margin-top:9px"></span></div></div>`).join('')}</div>`; }
function skProfile(){ return `<div class="sk-card" style="margin-bottom:16px"><div class="sk-row"><span class="sk sk-av lg"></span>
    <div style="flex:1"><span class="sk sk-line" style="width:40%"></span><span class="sk sk-line" style="width:26%;margin-top:9px"></span></div></div>
    <span class="sk sk-line" style="width:70%;margin-top:14px"></span></div>${skCards(2)}`; }
/* Empty / not-found state. Signature is backward compatible:
   empt(emoji, title)                       → just a heading
   empt(emoji, title, true)                 → + Sign In button
   empt(emoji, title, 'subtitle')           → + subtitle line
   empt(emoji, title, {sub, auth, actions}) → subtitle + buttons ({label, href} or {label, onClick}) */
let EMPT_ACTS={}, EMPT_SEQ=0;
function empt(emoji, title, opt){
  let sub='', auth=false, actions=[];
  if(opt===true) auth=true;
  else if(typeof opt==='string') sub=opt;
  else if(opt && typeof opt==='object'){ sub=opt.sub||''; auth=!!opt.auth; actions=opt.actions||[]; }
  const btns=[];
  if(auth) btns.push(`<button class="btn btn-lime" data-auth>${ic('signin',16)} Sign In</button>`);
  actions.forEach((a)=>{
    const cls='btn '+(a.primary?'btn-lime':'btn-ghost');
    if(a.href){ btns.push(`<a class="${cls}" href="${esc(a.href)}">${esc(a.label)}</a>`); }
    else if(a.onClick){ const id='ea'+(++EMPT_SEQ); EMPT_ACTS[id]=a.onClick; btns.push(`<button class="${cls}" data-emact="${id}">${esc(a.label)}</button>`); }
  });
  return `<div class="empty"><div class="big">${emoji}</div><h3 class="empty-title">${title}</h3>`+
    `${sub?`<p class="empty-sub">${sub}</p>`:''}`+
    `${btns.length?`<div class="empty-acts">${btns.join('')}</div>`:''}</div>`;
}
// delegated handler for empt() onClick actions
document.addEventListener('click',(e)=>{ const b=e.target.closest&&e.target.closest('[data-emact]');
  if(b && EMPT_ACTS[b.dataset.emact]){ e.preventDefault(); EMPT_ACTS[b.dataset.emact](); } });
/* Full-page friendly not-found / error view with a back button + actions. */
function notFound(opts){
  opts=opts||{}; chrome();
  const emoji=opts.emoji||'🧭';
  const title=opts.title||'Page not found';
  const sub=opts.sub||"The page you're looking for doesn't exist or may have moved.";
  const actions=opts.actions||[{label:'Go home',href:'#/',primary:true},{label:'Explore',href:'#/explore'}];
  setMain(`${opts.back===false?'':`<button class="back-btn" data-back>← Back</button>`}
    <div class="notfound-wrap">${empt(emoji,title,{sub,actions})}</div>`);
  const b=$('[data-back]'); if(b) b.onclick=()=>{ if(history.length>1) history.back(); else go('#/'); };
}
function renderNotFound(){ route='notfound'; notFound({
  emoji:'🧭', title:'This page doesn’t exist',
  sub:'The link may be broken, or the page may have been moved or removed.',
  actions:[{label:'Go home',href:'#/',primary:true},{label:'Explore polls',href:'#/explore'}] }); }

/* =====================================================================
   INFO PAGES — Help, About, Policies, Updates (crawlable content pages)
   ===================================================================== */
const APP_VERSION='2.4';
const INFO_PAGES={
  help:{ icon:'💡', title:'Help Center', sub:'Everything you need to get the most out of LinkPoll.',
    sections:[
      {h:'Getting started', faq:[
        ['What is LinkPoll?','LinkPoll is a social network built entirely around polls. Instead of long posts, you ask a question, add a few options, and the world votes — so you see what people really think in seconds.'],
        ['How do I create a poll?','Tap <b>Create Poll</b>, type your question, and add 2 or more options. You can choose a poll type (simple, yes/no, quiz, image, rating, this-or-that, or a survey), set how long it stays open, and post it to your followers or a community.'],
        ['How do I vote?','Just tap the option you agree with. Results update live as more people vote. On most polls you can change or see results right after voting.'],
        ['Is LinkPoll free?','Yes. Creating polls, voting, quizzes, communities and messaging are all free.'] ]},
      {h:'Points, levels & badges', faq:[
        ['How do I earn points?','You earn points for creating polls, voting, getting votes on your polls, keeping a daily streak, and completing quizzes. Your points determine your level and your place on the leaderboard.'],
        ['What are badges?','Badges are achievements you unlock as you use LinkPoll — like creating your first poll or reaching a streak. You can turn any achievement into a shareable image from your profile with the 🎉 Celebrate button.'],
        ['What is a streak?','Your streak counts consecutive days you\u2019re active on LinkPoll. Keep it going to earn bonus points.'] ]},
      {h:'Communities, quizzes & mock papers', faq:[
        ['What are communities?','Communities are spaces built around a shared interest where members post and vote on polls together. Some are open to everyone; others are private and need approval to join.'],
        ['What are Mock Papers?','Mock Papers let teachers build multiple-choice tests and share a link. Students (even without an account) can take the test, get scored instantly, and teachers see ranked results and per-question stats.'] ]},
      {h:'Your account & privacy', faq:[
        ['How do I change my photo or details?','Go to your profile and tap your photo, or open <b>Settings → Edit profile</b>.'],
        ['How do I switch to dark mode?','Open <b>Settings → Appearance</b> and toggle Dark mode. The app can also follow your device\u2019s light/dark setting automatically.'],
        ['How is my data handled?','Read our <a href="#/privacy">Privacy Policy</a> for exactly what we collect and why. In short: we only use your data to run LinkPoll, and we never sell it.'] ]} ]},
  about:{ icon:'🌐', title:'About LinkPoll', sub:'The world\u2019s opinion, live.',
    sections:[
      {h:'Our mission', body:['LinkPoll exists to make it effortless to ask a question and hear what people really think. Every major platform treats polls as an afterthought — we built an entire social network around them.'] },
      {h:'What makes it different', list:[
        'Polls are the main event, not a hidden feature — with rich types like quizzes, image polls, ratings, and surveys.',
        'Live results you can watch update in real time.',
        'Communities, mock papers for teachers, points, levels, badges and streaks that make participating fun.',
        'A clean, fast experience that works right in your browser and installs as an app.'] },
      {h:'Get in touch', body:['Have a question, idea, or found a bug? We\u2019d love to hear from you on our <a href="#/feedback">feedback page</a>, or email <a href="mailto:info@link-poll.com">info@link-poll.com</a>.'] } ]},
  updates:{ icon:'🚀', title:'What\u2019s New', sub:'The latest features and improvements on LinkPoll.',
    changelog:[
      {v:'2.4', date:'2026', items:['Full-width poll cards on mobile with a cleaner feed layout.','The app now follows your device\u2019s light/dark theme, with a matching status bar when installed.','Brand-new Help Center, About, Feedback and Policy pages.','Search-engine improvements so LinkPoll is easier to find.'] },
      {v:'2.3', date:'2026', items:['🎉 Celebrate: turn any achievement into a shareable image and post it to Instagram, WhatsApp, Facebook, X, LinkedIn or Reddit — with your personal invite link.','Redesigned AI Summary for polls.','Welcome tour for new members and a polished splash screen.'] },
      {v:'2.2', date:'2026', items:['Mock Papers — teachers can build MCQ tests, share a link, and see ranked student results.','Direct profile & cover photo editing.','Dark mode across the whole app.'] },
      {v:'2.1', date:'2026', items:['Communities, quizzes, points, levels, badges and streaks.','Live results and a faster, smoother feed.'] } ]},
  privacy:{ icon:'🔒', title:'Privacy Policy', sub:'Last updated: 2026. How we handle your data.',
    sections:[
      {h:'What we collect', list:['Account details you provide: name, username, email, and anything you add to your profile.','Content you create: polls, votes, comments, messages and community activity.','Basic technical data (like device/browser type) needed to run and secure the service.'] },
      {h:'How we use it', list:['To operate LinkPoll — showing your polls, tallying votes, and personalising your feed.','To keep the platform safe and prevent abuse.','To send essential account emails, and (only if you opt in) product updates.'] },
      {h:'What we don\u2019t do', body:['We do not sell your personal data. We don\u2019t show third-party ads that track you across the web.'] },
      {h:'Your choices', list:['Edit or delete your profile details any time in Settings.','Control activity and promotional emails in Settings.','Contact us to request deletion of your account and associated data.'] },
      {h:'Contact', body:['Questions about privacy? Email <a href="mailto:info@link-poll.com">info@link-poll.com</a>.'] } ]},
  terms:{ icon:'📜', title:'Terms of Service', sub:'Last updated: 2026. The rules for using LinkPoll.',
    sections:[
      {h:'Using LinkPoll', body:['By using LinkPoll you agree to these terms and to our <a href="#/guidelines">Community Guidelines</a>. You must be old enough to use the service in your country and provide accurate account information.'] },
      {h:'Your content', body:['You own the content you create. By posting, you grant LinkPoll the licence needed to display and distribute it within the service. You\u2019re responsible for what you post and must have the right to share it.'] },
      {h:'Acceptable use', list:['No illegal, hateful, harassing, or misleading content.','No spam, scraping, or attempts to break or overload the service.','No impersonation or infringing others\u2019 rights.'] },
      {h:'Availability', body:['LinkPoll is provided \u201cas is\u201d. We work hard to keep it running but can\u2019t guarantee it will always be available or error-free, and we may update or change features over time.'] },
      {h:'Contact', body:['Questions about these terms? Email <a href="mailto:info@link-poll.com">info@link-poll.com</a>.'] } ]},
  guidelines:{ icon:'🤝', title:'Community Guidelines', sub:'Keeping LinkPoll a place worth being.',
    sections:[
      {h:'Be respectful', body:['Debate ideas, not people. Harassment, hate speech, threats and bullying are not allowed.'] },
      {h:'Keep it honest', body:['Don\u2019t spread misinformation, manipulate votes, or use fake accounts. Polls should be genuine questions, not scams.'] },
      {h:'Keep it safe', list:['No sexual content involving minors — ever.','No content that promotes self-harm, violence, or illegal activity.','No sharing others\u2019 private information.'] },
      {h:'Reporting', body:['See something that breaks these rules? Use the report option on any poll or comment. Our team reviews reports and may remove content or suspend accounts.'] } ]},
  cookies:{ icon:'🍪', title:'Cookie Notice', sub:'How LinkPoll uses cookies and local storage.',
    sections:[
      {h:'What we use', list:['A secure sign-in cookie to keep you logged in.','Local storage on your device to remember preferences like your theme and whether you\u2019ve seen the welcome tour.'] },
      {h:'What we don\u2019t use', body:['We don\u2019t use third-party advertising or cross-site tracking cookies.'] } ]} };

function infoFooterLinks(cur){
  const links=[['help','Help'],['about','About'],['updates','What\u2019s New'],['feedback','Feedback'],['privacy','Privacy'],['terms','Terms'],['guidelines','Guidelines']];
  return `<nav class="info-links">${links.filter(([k])=>k!==cur).map(([k,l])=>`<a href="#/${k}">${l}</a>`).join('')}</nav>
    <p class="info-copy">\u00A9 ${new Date().getFullYear()} LinkPoll \u00B7 link-poll.com</p>`;
}
function renderInfoPage(key){
  const p=INFO_PAGES[key]; if(!p) return renderNotFound();
  chrome();
  let inner='';
  if(p.changelog){
    inner=p.changelog.map((c)=>`<section class="info-sec"><div class="chlog-head"><span class="chlog-v">v${esc(c.v)}</span><span class="chlog-date">${esc(c.date)}</span></div>
      <ul>${c.items.map((i)=>`<li>${i}</li>`).join('')}</ul></section>`).join('');
  } else {
    inner=(p.sections||[]).map((s)=>`<section class="info-sec"><h2>${esc(s.h)}</h2>
      ${(s.body||[]).map((para)=>`<p>${para}</p>`).join('')}
      ${s.list?`<ul>${s.list.map((li)=>`<li>${li}</li>`).join('')}</ul>`:''}
      ${(s.faq||[]).map(([q,a])=>`<details class="info-faq"><summary>${esc(q)}</summary><div class="info-faq-a">${a}</div></details>`).join('')}
    </section>`).join('');
  }
  setMain(`<button class="back-btn" data-back>${ic('arrow',16)} Back</button>
    <article class="info-page">
      <header class="info-head"><div class="info-emoji">${p.icon}</div><h1>${esc(p.title)}</h1><p class="info-sub">${esc(p.sub)}</p></header>
      ${inner}
      <footer class="info-foot">${infoFooterLinks(key)}</footer>
    </article>`);
  const b=$('[data-back]'); if(b) b.onclick=()=>{ if(history.length>1) history.back(); else go('#/'); };
}
function renderFeedback(){
  chrome();
  const types=['Idea','Bug','Question','Other'];
  setMain(`<button class="back-btn" data-back>${ic('arrow',16)} Back</button>
    <article class="info-page">
      <header class="info-head"><div class="info-emoji">💬</div><h1>Send us feedback</h1><p class="info-sub">Found a bug or have an idea? We read every message.</p></header>
      <div class="fb-form">
        <label class="fb-label">What\u2019s this about?</label>
        <div class="fb-types">${types.map((t,i)=>`<button class="fb-type ${i===0?'on':''}" data-fbtype="${t}">${t}</button>`).join('')}</div>
        ${ME?'':`<label class="fb-label" for="fb-email">Your email <span class="fb-opt">(optional, so we can reply)</span></label><input id="fb-email" class="fb-input" type="email" placeholder="you@example.com" autocomplete="email">`}
        <label class="fb-label" for="fb-msg">Your message</label>
        <textarea id="fb-msg" class="fb-input" rows="6" placeholder="Tell us what\u2019s on your mind\u2026"></textarea>
        <button class="btn btn-lime btn-block" id="fb-send">${ic('send',16)} Send feedback</button>
        <p class="info-sub" style="text-align:center;margin-top:14px">Prefer email? Write to <a href="mailto:info@link-poll.com">info@link-poll.com</a></p>
      </div>
      <footer class="info-foot">${infoFooterLinks('feedback')}</footer>
    </article>`);
  const b=$('[data-back]'); if(b) b.onclick=()=>{ if(history.length>1) history.back(); else go('#/'); };
  let fbType='Idea';
  $$('[data-fbtype]').forEach((btn)=>btn.onclick=()=>{ fbType=btn.dataset.fbtype; $$('[data-fbtype]').forEach((x)=>x.classList.toggle('on',x===btn)); });
  $('#fb-send').onclick=async()=>{
    const msg=($('#fb-msg').value||'').trim(); if(msg.length<5){ toast('Please write a little more.'); return; }
    const email=ME?'':(($('#fb-email')||{}).value||'').trim();
    const btn=$('#fb-send'); btn.disabled=true; btn.textContent='Sending\u2026';
    try{ await api('/feedback',{method:'POST',body:{category:fbType,message:msg,email,page:location.pathname}});
      setMain(`<article class="info-page"><header class="info-head"><div class="info-emoji">🎉</div><h1>Thank you!</h1>
        <p class="info-sub">Your feedback has been sent. We really appreciate you helping make LinkPoll better.</p></header>
        <div style="text-align:center;margin-top:8px"><a class="btn btn-lime" href="#/">Back to home</a></div>
        <footer class="info-foot">${infoFooterLinks('feedback')}</footer></article>`);
    }catch(e){ toast(e.message||'Could not send — please try again.'); btn.disabled=false; btn.innerHTML=`${ic('send',16)} Send feedback`; }
  };
}

const QA_ITEMS=[
  {ic:'🎯',label:'Quizzes',href:'#/quiz',c:'#7cb518'},
  {ic:'📝',label:'Mock Papers',href:'#/papers',c:'#4c86f9'},
  {ic:'🌐',label:'Communities',href:'#/communities',c:'#f4845f'},
  {ic:'📋',label:'Survey',survey:1,c:'#9d4edd'},
  {ic:'🏆',label:'Leaderboard',href:'#/leaderboard',c:'#e8a13a'},
];
function suggFeaturesHTML(){
  return `<section class="feed-sugg"><div class="fs-head"><span class="fs-title">✨ Explore LinkPoll</span></div>
    <div class="qa-scroll">${QA_ITEMS.map((it)=>{
      const inner=`<span class="qa-ic" style="background:${it.c}22">${it.ic}</span><span class="qa-lbl">${it.label}</span>`;
      return it.survey ? `<button type="button" class="qa-tile" data-qa-survey>${inner}</button>` : `<a class="qa-tile" href="${it.href}">${inner}</a>`;
    }).join('')}</div></section>`;
}
let TREND_CACHE=null;
async function loadTrendStrip(){
  const el=$('#trend-strip'); if(!el) return;
  if(feedTab!=="foryou"){ el.style.display="none"; el.innerHTML=""; return; }
  el.style.display="";
  try{
    if(!TREND_CACHE){ TREND_CACHE=((await api("/trending/tags")).tags)||[]; }
    const tags=TREND_CACHE.slice(0,12);
    if(!tags.length){ el.style.display="none"; return; }
    el.innerHTML=`<div class="ts-lead">🔥 Trending topics</div>
      <div class="ts-scroll">${tags.map((t)=>`<button class="ts-chip" data-search="${esc(t.tag)}">#${esc(t.tag)}${t.count>1?`<span class="ts-n">${fmt(t.count)}</span>`:""}</button>`).join("")}</div>`;
  }catch(e){ el.style.display="none"; }
}
function initBackToTop(){
  let btn=document.getElementById("to-top");
  if(!btn){ btn=document.createElement("button"); btn.id="to-top"; btn.className="to-top"; btn.type="button";
    btn.setAttribute("aria-label","Back to top");
    btn.innerHTML=`<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>`;
    document.body.appendChild(btn);
    btn.onclick=()=>{ try{ window.scrollTo({top:0,behavior:"smooth"}); }catch(e){ window.scrollTo(0,0); } };
  }
  let ticking=false;
  window.addEventListener("scroll",()=>{ if(ticking) return; ticking=true;
    requestAnimationFrame(()=>{ const y=window.scrollY||document.documentElement.scrollTop||0;
      btn.classList.toggle("show", y>640); ticking=false; }); }, {passive:true});
}

function renderDiscover(){
  chrome();
  const cards=[
    {ic:'🔎', t:'Explore', d:'Search and discover trending polls, people and topics.', href:'#/explore', c:'#4c86f9'},
    {ic:'🎯', t:'Quizzes', d:'Test your knowledge and earn points on the leaderboard.', href:'#/quiz', c:'#7cb518'},
    {ic:'📝', t:'Mock Papers', d:'Take timed MCQ tests, or create one and share the link.', href:'#/papers', c:'#f4845f'},
    {ic:'🌐', t:'Communities', d:'Join groups built around your interests and vote together.', href:'#/communities', c:'#00b4d8'},
    {ic:'🏆', t:'Leaderboard', d:'See the top members and where you rank this week.', href:'#/leaderboard', c:'#e8a13a'},
    {ic:'📊', t:'Create a Survey', d:'Ask several questions in a row and see combined results.', survey:1, c:'#9d4edd'},
    {ic:'👥', t:'Find People', d:'Discover creators and friends worth following.', href:'#/network', c:'#ef476f'},
    {ic:'🔖', t:'Saved', d:'Everything you’ve bookmarked, in one place.', href:'#/saved', c:'#118ab2'},
  ];
  const chev='<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>';
  setMain(`<div class="disc-head"><h1>✨ Discover LinkPoll</h1><p>Everything the platform offers, all in one place.</p></div>
    <div class="disc-grid">${cards.map((c)=>{
      const inner=`<span class="disc-ic" style="background:${c.c}22">${c.ic}</span>
        <span class="disc-txt"><b>${c.t}</b><small>${esc(c.d)}</small></span><span class="disc-arrow">${chev}</span>`;
      return c.survey ? `<button type="button" class="disc-card" data-disc-survey>${inner}</button>` : `<a class="disc-card" href="${c.href}">${inner}</a>`;
    }).join('')}</div>`);
  const sv=$('[data-disc-survey]'); if(sv) sv.onclick=()=>{ if(!ME){ openAuth(); return; } openComposer('survey'); };
}
async function renderHome(){
  chrome();
  const tabs=[['foryou','For You'],['following','Following'],['trending','Trending'],['new','New'],['live','Live']];
  setMain(`<div class="tabs">${tabs.map(([k,l])=>`<button data-tab="${k}" class="${feedTab===k?'active':''}">${l}</button>`).join('')}</div>
    <div id="profile-nudge"></div>
    <div id="story-tray" class="story-tray"></div>
    <div id="trend-strip" class="trend-strip"></div>
    <div id="feed">${skCards(3)}</div>`);
  renderProfileNudge();
  loadStoryTray();
  loadTrendStrip();
  $$('[data-tab]').forEach((b)=>b.onclick=()=>{ if(feedTab===b.dataset.tab) return;
    switchFeedTab(b.dataset.tab); });
  loadStats();
  await loadFeed($('#feed'), feedTab);
}

/* =====================================================================
   VIEW CACHE (bfcache-style)
   Keeps the REAL DOM nodes of a page alive when you navigate away, so
   returning restores it instantly — every card, click handler, observer
   and scroll position intact, with no refetching.
   ===================================================================== */
const VIEW_CACHE=new Map();
const VIEW_TTL=10*60*1000, VIEW_MAX=8;
/* pages that are safe to freeze (skip live/stateful ones: chat, poll detail, settings, quiz session) */
/* Switch home feed tabs like a native app: the tab you leave is frozen (DOM +
   scroll position), and returning to it restores exactly what you were looking at. */
const FEED_TAB_ORDER=['foryou','following','trending','new','live'];
/* Interactive feed-tab swipe: the feed tracks your finger as you drag, then
   completes the switch or springs back when you release (like a native pager). */
function initFeedSwipe(){
  let sx=0, sy=0, active=false, decided=false, horiz=false, dx=0, feedEl=null, W=0, animating=false;
  const blocked=(el)=> !el||!el.closest ? false : !!el.closest('.tabs,.story-tray,.stories,.chat-wrap,.opts.grid,.gopt,input,textarea,select,[contenteditable],.no-swipe,.sheet,.modal,.comm-scroll,.lb-list,.ic-list,[data-noswipe]');
  const canStart=(e)=>{
    if(animating) return false;
    if(e.touches.length!==1 || window.innerWidth>820) return false;
    if(route!=='home') return false;
    if(document.body.classList.contains('locked')) return false;
    if($('#side') && $('#side').classList.contains('open')) return false;
    if($('#overlay') && $('#overlay').innerHTML.trim()) return false;
    if(blocked(e.target)) return false;
    return true;
  };
  const targetFor=(dir)=>{ const i=FEED_TAB_ORDER.indexOf(feedTab);
    if(dir<0) return i<FEED_TAB_ORDER.length-1 ? {tab:FEED_TAB_ORDER[i+1]} : {handoff:'explore'};
    return i>0 ? {tab:FEED_TAB_ORDER[i-1]} : {edge:true}; };
  const onStart=(e)=>{ active=false; decided=false; horiz=false; dx=0;
    if(!canStart(e)) return;
    feedEl=$('#feed'); if(!feedEl) return;
    sx=e.touches[0].clientX; sy=e.touches[0].clientY; W=window.innerWidth; active=true;
    feedEl.style.transition='none';
  };
  const onMove=(e)=>{
    if(!active||!feedEl) return;
    dx=e.touches[0].clientX-sx; const dy=e.touches[0].clientY-sy;
    if(!decided){ if(Math.abs(dx)>8||Math.abs(dy)>8){ decided=true; horiz=Math.abs(dx)>Math.abs(dy)*1.2;
      if(!horiz){ active=false; feedEl.style.transform=''; feedEl.style.transition=''; return; } } else return; }
    if(!horiz) return;
    if(e.cancelable) e.preventDefault();                 // own the gesture; block vertical scroll
    const tgt=targetFor(dx);
    let t=dx;
    if(tgt.edge) t=dx*0.28;                              // hard edge (before first tab) → strong resistance
    else if(tgt.handoff) t=dx*0.55;                      // section boundary → mild resistance
    feedEl.style.transform='translateX('+t+'px)';
  };
  const onEnd=()=>{
    if(!active||!feedEl){ active=false; return; } active=false;
    const el=feedEl;
    if(!horiz){ el.style.transform=''; el.style.transition=''; return; }
    const commit=Math.abs(dx) > Math.min(90, W*0.2);
    const tgt=targetFor(dx);
    if(commit && tgt.tab){
      const dir = dx<0 ? -1 : 1;                          // -1: content exits left, +1: exits right
      animating=true;
      el.style.transition='transform .16s ease-out';
      el.style.transform='translateX('+(dir<0?-W:W)+'px)';
      setTimeout(()=>{
        switchFeedTab(tgt.tab);                           // swaps #feed content (transforms cleared inside)
        const nf=$('#feed');
        if(nf){ nf.style.transition='none'; nf.style.transform='translateX('+(dir<0?W:-W)+'px)'; void nf.offsetWidth;
          nf.style.transition='transform .2s ease-out'; nf.style.transform='translateX(0)';
          setTimeout(()=>{ if(nf){ nf.style.transition=''; nf.style.transform=''; } animating=false; }, 210); }
        else animating=false;
      }, 155);
    } else if(commit && tgt.handoff){
      el.style.transition='transform .2s ease'; el.style.transform='';
      try{ if(navigator.vibrate) navigator.vibrate(8); }catch(_){}
      if(typeof animateMain==='function') animateMain(1);
      go('#/'+tgt.handoff);
      setTimeout(()=>{ if(el){ el.style.transition=''; el.style.transform=''; } }, 220);
    } else {
      el.style.transition='transform .24s cubic-bezier(.2,.9,.3,1)'; el.style.transform='translateX(0)';
      setTimeout(()=>{ if(el){ el.style.transition=''; el.style.transform=''; } }, 260);
    }
  };
  document.addEventListener('touchstart',onStart,{passive:true});
  document.addEventListener('touchmove',onMove,{passive:false});
  document.addEventListener('touchend',onEnd,{passive:true});
  document.addEventListener('touchcancel',()=>{ if(active&&feedEl){ active=false;
    feedEl.style.transition='transform .2s ease'; feedEl.style.transform=''; } },{passive:true});
}
function animFeedSlide(dir){ const f=$('#feed'); if(!f) return;
  f.classList.remove('feed-in-l','feed-in-r'); void f.offsetWidth;
  f.classList.add(dir>0?'feed-in-r':'feed-in-l');
  setTimeout(()=>{ f&&f.classList.remove('feed-in-l','feed-in-r'); }, 320); }
function bounceFeed(dir){ const f=$('#feed'); if(!f) return;
  f.classList.remove('feed-bounce-l','feed-bounce-r'); void f.offsetWidth;
  f.classList.add(dir>0?'feed-bounce-r':'feed-bounce-l');
  setTimeout(()=>{ f&&f.classList.remove('feed-bounce-l','feed-bounce-r'); }, 300); }

function switchFeedTab(next, dir){
  if(feedTab===next) return;
  const cur=$('#feed'); if(cur){ cur.style.transform=''; cur.style.transition=''; }  // drop any drag transform before freezing
  cacheView(viewKey());          // freeze the tab we're leaving under its own key
  feedTab=next;
  const key=viewKey(); VIEW_LAST_KEY=key;
  const hit=VIEW_CACHE.get(key);
  if(hit && (Date.now()-hit.ts)<VIEW_TTL){
    hit.key=key; VIEW_CACHE.delete(key);
    restoreView(hit);            // instant: same cards, same place on the page
    const nf=$('#feed'); if(nf){ nf.style.transform=''; nf.style.transition=''; }
    if(dir) animFeedSlide(dir);
    return;
  }
  jumpTo(0);                     // a tab we haven't opened yet starts at the top
  renderHome();
  const nf=$('#feed'); if(nf){ nf.style.transform=''; nf.style.transition=''; }
  if(dir) animFeedSlide(dir);
}
// Cache EVERY page/tab by default so it reopens where you left it.
// Only these stay fresh: live chat, admin moderation, and settings forms
// (they must reflect real-time or current state, not a snapshot).
const NO_CACHE_ROUTES=['messages','admin','settings','quiz'];
function viewKey(){ const p=location.pathname||'/';
  // the home feed has sub-tabs (For you / Following / …) — each keeps its own snapshot
  return (p==='/'||p==='') ? '/::'+feedTab : p; }
function dropView(key){ VIEW_CACHE.delete(key||viewKey()); }
function clearViews(){ VIEW_CACHE.clear(); }
/* invalidate AND skip the next snapshot, so the stale page isn't re-frozen on the way out */
function invalidateViews(all){ if(all) VIEW_CACHE.clear(); else VIEW_CACHE.delete(viewKey()); VIEW_LAST_KEY=null; }
function cacheView(explicitKey){
  if(NO_CACHE_ROUTES.includes(route)) return;
  const m=$('#main'); if(!m||!m.firstChild) return;
  const key=explicitKey||VIEW_LAST_KEY; if(!key) return;
  // Move (don't clone) the nodes into a fragment — cloning would drop event handlers.
  const frag=document.createDocumentFragment();
  while(m.firstChild) frag.appendChild(m.firstChild);
  VIEW_CACHE.delete(key);
  const lockedNow=document.body.classList.contains('locked');
  const sy=lockedNow?LOCK_Y:(window.scrollY||document.documentElement.scrollTop||document.body.scrollTop||0);
  VIEW_CACHE.set(key,{frag,route,scrollY:sy,ts:Date.now(),tab:feedTab});
  while(VIEW_CACHE.size>VIEW_MAX) VIEW_CACHE.delete(VIEW_CACHE.keys().next().value);
}
function restoreView(hit){
  route=hit.route; chrome();
  const m=$('#main'); m.innerHTML=''; m.appendChild(hit.frag);
  VIEW_CACHE.delete(hit.key||'');
  // refresh any cards whose data changed while away (e.g. you voted on the detail page)
  try{ $$('#main .card[id^="card-"]').forEach((el)=>{ const code=el.id.replace('card-',''); const p=POLLS[code];
    if(p && el.dataset.v!==String(p.totalVotes)+':'+(p.hasVoted?1:0)) replaceCard(p); }); }catch(e){}
  // keep infinite scroll alive
  const more=$('#main .feed-more'); if(more&&more._lpIO){ try{ more._lpIO.observe(more); FEED_IO=more._lpIO; }catch(e){} }
  const sy=hit.scrollY||0;
  const rk=viewKey();
  const set=()=>{ if(viewKey()===rk) jumpTo(sy); };   // only if still on this view
  jumpTo(sy);
  requestAnimationFrame(()=>{ set(); requestAnimationFrame(set); });
  setTimeout(set, 60); setTimeout(set, 160);
}
let VIEW_LAST_KEY=null;
async function loadStats(force){
  if(STATS && !force){ paintStats(); return STATS; }
  try{ STATS=await api('/stats'); }catch(e){ return null; }
  paintStats(); return STATS;
}
function paintStats(){ if(!STATS) return; const a=$('#st1'), b=$('#st2');
  if(a) a.textContent=fmt(STATS.votesToday); if(b) b.textContent=fmt(STATS.activePolls); }
async function loadFeed(feed, tab){
  if(tab==='following' && !ME){ feed.innerHTML=empt('👥','Sign in and follow people to build your feed.',true); return; }
  const serverTab = (tab==='foryou'||tab==='new')?'new':tab;
  feed.innerHTML='';
  FEED_SUGG_DONE=new Set();
  FAQ_QUEUE=[]; FAQ_CURSOR=null; FAQ_DONE=false; FAQ_SHOWN=new Set(); FAQ_NEXTAT=3;
  FEED_STATE={tab, serverTab, cursor:null, done:false};
  const more=document.createElement('div'); more.className='feed-more'; more.style.textAlign='center';
  feed.appendChild(more); wireFeed(feed);
  const ctl=wireInfinite(feed, serverTab, null, false);
  await ctl.load();
}
/* attach (or re-attach) the infinite-scroll loader to an existing feed container */
function wireInfinite(feed, serverTab, cursor, done){
  let loading=false;
  let more=feed.querySelector('.feed-more');
  if(!more){ more=document.createElement('div'); more.className='feed-more'; more.style.textAlign='center'; feed.appendChild(more); }
  let io=null;
  async function load(){
    if(loading||done)return; loading=true; more.innerHTML=skCards(1);
    try{ const d=await api('/feed',{query:{tab:serverTab,cursor}}); cachePolls(d.polls);
      d.polls.forEach((p)=>feed.insertBefore(htmlToNode(cardHTML(p)),more)); paintFills(feed);
      injectFeedSuggestions(feed);
      injectFeedFaqs(feed);
      cursor=d.nextCursor; FEED_STATE.cursor=cursor;
      if(!cursor){ done=true; FEED_STATE.done=true; more.innerHTML=`<div class="feed-end-card">
          <div class="fe-emoji">🎉</div>
          <div class="fe-title">You’re all caught up!</div>
          <div class="fe-sub">You’ve seen the latest polls. Ask the world something new, or explore more.</div>
          <div class="fe-acts">${ME?`<button class="btn btn-lime btn-sm" data-create>${ic('plus',14)} Create Poll</button>`:''}<a class="btn btn-ghost btn-sm" href="#/explore">Explore</a></div>
        </div>`; io&&io.disconnect(); }
      else { more.innerHTML=''; }
      if(!feed.querySelector('.card')) feed.innerHTML=empt('📊','No polls yet. Be the first to ask the world something!');
    }catch(e){ more.innerHTML=`<button class="btn btn-ghost btn-sm">Retry</button>`; more.querySelector('button').onclick=()=>{ more.innerHTML=''; load(); }; }
    loading=false;
  }
  if('IntersectionObserver' in window){
    io=new IntersectionObserver((ents)=>{ if(ents.some((e)=>e.isIntersecting)) load(); }, {rootMargin:'600px 0px'});
    io.observe(more);
  } else { more.onclick=load; }
  more._lpIO=io; more._lpLoad=load; FEED_IO=io;
  return {load};
}
let FEED_STATE={tab:null,serverTab:null,cursor:null,done:false};
let FEED_IO=null;
/* ---- in-feed suggestions (who to follow / communities to join) ---- */
let FEED_SUGG_DONE=new Set();
const SUGG_CACHE={people:null, comms:null};
const SUGG_SLOTS=[];  /* no cards injected into the home feed — pure poll stream */
function injectFeedSuggestions(feed){
  if(!ME || route!=='home' || !feed) return;
  const cards=[...feed.children].filter((n)=>n.classList&&n.classList.contains('card'));
  SUGG_SLOTS.forEach(([pos,kind])=>{
    if(FEED_SUGG_DONE.has(pos) || cards.length<pos) return;
    const anchor=cards[pos-1]; if(!anchor) return;
    FEED_SUGG_DONE.add(pos);
    const ph=htmlToNode(`<div class="feed-sugg-ph"></div>`); anchor.after(ph);
    fillSuggestion(ph, kind);
  });
}
async function fillSuggestion(ph, kind){
  try{
    let node=null;
    if(kind==='features'){
      node=htmlToNode(suggFeaturesHTML()); ph.replaceWith(node);
      const sv=node.querySelector('[data-qa-survey]'); if(sv) sv.onclick=()=>{ if(!ME){ openAuth(); return; } openComposer('survey'); };
      return;
    }
    if(kind==='people'){
      if(!SUGG_CACHE.people){ SUGG_CACHE.people=((await api('/suggested',{query:{limit:10}})).users)||[]; }
      const users=SUGG_CACHE.people; if(!users.length){ ph.remove(); return; }
      node=htmlToNode(suggPeopleHTML(users.slice(0,10)));
    } else {
      if(!SUGG_CACHE.comms){ SUGG_CACHE.comms=(((await api('/communities')).discover)||[]).filter((c)=>!c.myStatus); }
      const comms=SUGG_CACHE.comms; if(!comms.length){ ph.remove(); return; }
      node=htmlToNode(suggCommHTML(comms.slice(0,10)));
    }
    if(!node){ ph.remove(); return; }
    ph.replaceWith(node); wireSuggButtons(node);
  }catch(e){ ph.remove(); }
}
function suggPeopleHTML(users){
  return `<section class="feed-sugg"><div class="fs-head"><span class="fs-title">👥 Who to follow</span><a href="#/network" class="fs-all">See all</a></div>
    <div class="fs-scroll">${users.map((u)=>`<div class="fs-card">
      <a href="#/u/${esc(u.username)}" class="fs-av">${avatar(u,'s56')}</a>
      <a href="#/u/${esc(u.username)}" class="fs-name">${esc(u.name)}${u.verified?vbadge(1):''}</a>
      <span class="fs-handle">@${esc(u.username)}</span>
      <button class="btn btn-dark btn-sm fs-btn" data-sfollow="${esc(u.username)}">Follow</button></div>`).join('')}</div></section>`;
}
function suggCommHTML(comms){
  return `<section class="feed-sugg"><div class="fs-head"><span class="fs-title">🌐 Communities to join</span><a href="#/communities" class="fs-all">See all</a></div>
    <div class="fs-scroll">${comms.map((c)=>`<div class="fs-card">
      <a href="#/c/${esc(c.slug)}" class="fs-cav" style="background:${esc(c.color||'#15140f')}">${c.cover?`<img src="${esc(c.cover)}" alt="">`:esc((c.name||'?').trim().slice(0,2).toUpperCase())}</a>
      <a href="#/c/${esc(c.slug)}" class="fs-name">${esc(c.name)}</a>
      <span class="fs-handle">${fmt(c.members)} member${c.members===1?'':'s'}</span>
      <button class="btn btn-dark btn-sm fs-btn" data-cjoin="${esc(c.slug)}" data-priv="${c.privacy==='private'?1:0}">${c.privacy==='private'?'Request':'Join'}</button></div>`).join('')}</div></section>`;
}
function wireSuggButtons(node){
  node.querySelectorAll('[data-qa-survey]').forEach((b)=>b.onclick=()=>{ if(!ME){ openAuth(); return; } openComposer('survey'); });
  node.querySelectorAll('[data-sfollow]').forEach((b)=>b.onclick=async()=>{ const un=b.dataset.sfollow; b.disabled=true;
    try{ const r=await api('/follow',{method:'POST',body:{username:un}}); afterFollow(un,r.following);
      b.textContent=r.following?'Following':'Follow'; b.className='btn btn-sm fs-btn '+(r.following?'btn-ghost':'btn-dark'); }
    catch(e){ toast(e.message); } b.disabled=false; });
  node.querySelectorAll('[data-cjoin]').forEach((b)=>b.onclick=async()=>{ const slug=b.dataset.cjoin; b.disabled=true;
    try{ const r=await api('/community/join',{method:'POST',body:{slug}});
      b.textContent=r.status==='pending'?'Requested':'Joined'; b.className='btn btn-sm fs-btn btn-ghost';
      toast(r.status==='pending'?'Request sent — waiting for approval.':'Joined! 🎉'); }
    catch(e){ toast(e.message); b.disabled=false; } });
}
function htmlToNode(h){ const t=document.createElement('div'); t.innerHTML=h; return t.firstElementChild; }

async function renderFeedList(tab){
  chrome(); setMain(`<div class="page-title">Following</div><div id="feed">${skCards(3)}</div>`);
  await loadFeed($('#feed'), tab);
}
async function renderMyPolls(){
  chrome();
  if(!ME){ setMain(`<div class="page-title">My Polls</div>${empt('📊','Sign in to create and track your polls.',true)}`); return; }
  setMain(`<div class="page-title">My Polls</div>
    <button class="btn btn-lime" data-create style="margin:0 4px 16px">${ic('plus')} Create Poll</button>
    <div id="feed"><div class="skeleton"></div></div>`);
  try{ const{polls}=await api('/user',{query:{username:ME.username}}); cachePolls(polls);
    const feed=$('#feed'); if(!polls.length){ feed.innerHTML=empt('📊','You haven\'t posted any polls yet.'); return; }
    feed.innerHTML=polls.map(cardHTML).join(''); wireFeed(feed); paintFills(feed);
  }catch(e){ $('#feed').innerHTML=empt('⚠️',e.message); }
}

let EX_TAB='trending';
async function renderExplore(){
  chrome();
  const mag=ic('search',18).replace('class="ico"','class="ico mag"');
  setMain(`<div class="ex-top"><div class="search">${mag}
      <input id="main-search" placeholder="Search polls, people, #tags…" value="${esc(query)}"></div></div>
    <div id="ex" class="ex-body"></div>
    <div class="ex-searchbar" id="ex-bar"><div class="search">${mag}
        <input id="ex-search-m" placeholder="Search polls, people, #tags…" value="${esc(query)}" enterkeyhint="search"></div>
      <button class="ex-clear" id="ex-clear" aria-label="Clear search"${query?'':' style="display:none"'}>✕</button></div>`);
  const top=$('#main-search'), mob=$('#ex-search-m'), clr=$('#ex-clear');
  const sync=(from)=>{ [top,mob].forEach((x)=>{ if(x&&x!==from) x.value=query; }); if(clr) clr.style.display=query?'grid':'none'; };
  const wire=(el)=>{ if(!el) return; el.addEventListener('input',()=>{ query=el.value; sync(el); if(el===mob && query.trim()) document.body.classList.add('ex-searching'); exResults(); }); };
  wire(top); wire(mob);
  // mobile: raise the search bar to the top while it's active
  let blurT=null;
  if(mob){
    mob.addEventListener('focus',()=>{ clearTimeout(blurT); document.body.classList.add('ex-searching'); });
    mob.addEventListener('blur',()=>{ blurT=setTimeout(()=>{ if(!query.trim()) document.body.classList.remove('ex-searching'); },150); });
  }
  if(clr) clr.onclick=()=>{ query=''; [top,mob].forEach((x)=>x&&(x.value='')); sync(null); document.body.classList.remove('ex-searching'); exResults(); };
  if(query.trim()) document.body.classList.add('ex-searching');
  sync(null);
  exResults();
}
let RECENT_SEARCHES=[];
function pushRecentSearch(q){ q=(q||'').trim(); if(q.length<2) return;
  RECENT_SEARCHES=[q].concat(RECENT_SEARCHES.filter((x)=>x.toLowerCase()!==q.toLowerCase())).slice(0,8); }
function recentSearchHTML(){ if(!RECENT_SEARCHES.length) return '';
  return `<div class="ex-sec"><div class="ex-sec-h ex-sec-h-row"><span>🕘 Recent searches</span>
      <button class="rs-clear" id="rs-clear">Clear</button></div>
    <div class="rs-list">${RECENT_SEARCHES.map((q)=>`<button class="rs-item" data-rs="${esc(q)}">${ic('search',14)}<span>${esc(q)}</span></button>`).join('')}</div></div>`; }
function exPersonCard(u){
  return `<div class="ex-person" data-author="${esc(u.username)}">
    ${avatar(u,'s56')}<b>${esc(u.name)}${vbadge(u.verified)}</b><span>@${esc(u.username)}</span>
    ${(ME&&!u.isMe)?`<button class="btn btn-sm btn-dark ex-follow" data-xfollow="${esc(u.username)}">Follow</button>`:''}</div>`;
}
let exTimer=null;
async function exResults(){
  const box=$('#ex'); if(!box)return; const raw=query.trim(); const q=raw.replace(/^#/,'');
  clearTimeout(exTimer);
  if(!q){
    box.innerHTML=`<div class="ex-hero"><h1 class="ex-h1">Explore</h1><p class="ex-sub">Find polls, people and trending topics.</p></div>
      ${recentSearchHTML()}
      <div class="ex-sec"><div class="ex-sec-h">🔥 Trending hashtags</div><div id="trend-tags" class="tagcloud">${'<span class="pill sk" style="width:74px;height:30px"></span>'.repeat(6)}</div></div>
      <div class="ex-sec" id="ex-people-sec" style="display:none"><div class="ex-sec-h">✨ People to discover</div><div id="ex-people" class="ex-people"></div></div>
      <div class="ex-sec"><div class="ex-sec-h ex-sec-h-row"><span>📊 Polls</span>
          <div class="ex-seg"><button data-extab="trending" class="${EX_TAB==='trending'?'on':''}">Trending</button><button data-extab="new" class="${EX_TAB==='new'?'on':''}">Latest</button></div></div>
        <div id="feed">${skCards(3)}</div></div>`;
    api('/trending/tags').then(async (t)=>{ if(!t||!t.tags||!t.tags.length){ try{ const s=STATS||await loadStats(); t={tags:((s&&s.trending)||[]).map(x=>({tag:x.tag,count:x.count}))}; }catch(e){ t={tags:[]}; } } const el=$('#trend-tags'); if(!el)return;
      el.innerHTML = t.tags.length ? t.tags.map((x)=>`<a class="tag big" data-tag="${esc(x.tag)}">#${esc(x.tag)} <span class="tcount">${x.count}</span></a>`).join('') : '<span style="color:var(--soft);font-size:13px">No hashtags yet — add #tags to your poll questions.</span>'; }).catch(()=>{});
    api('/suggested').then((d)=>{ const sec=$('#ex-people-sec'), el=$('#ex-people'); if(!sec||!el||!(d.users&&d.users.length)) return;
      sec.style.display=''; el.innerHTML=d.users.slice(0,10).map(exPersonCard).join('');
      el.querySelectorAll('[data-xfollow]').forEach((b)=>b.onclick=async(e)=>{ e.stopPropagation(); const un=b.dataset.xfollow;
        b.disabled=true; try{ const r=await api('/follow',{method:'POST',body:{username:un}}); afterFollow(un, r.following); }catch(err){ toast(err.message); } b.disabled=false; }); }).catch(()=>{});
    $$('[data-rs]').forEach((b)=>b.onclick=()=>{ query=b.dataset.rs;
      const t=$('#main-search'), m=$('#ex-search-m'); if(t)t.value=query; if(m)m.value=query;
      const c=$('#ex-clear'); if(c) c.style.display='grid'; document.body.classList.add('ex-searching'); exResults(); });
    const rc=$('#rs-clear'); if(rc) rc.onclick=()=>{ RECENT_SEARCHES=[]; exResults(); };
    $$('[data-extab]').forEach((b)=>b.onclick=()=>{ if(EX_TAB===b.dataset.extab) return; EX_TAB=b.dataset.extab;
      $$('[data-extab]').forEach((x)=>x.classList.toggle('on',x===b)); const f=$('#feed'); if(f){ f.innerHTML=skCards(3); loadFeed(f,EX_TAB); } });
    await loadFeed($('#feed'), EX_TAB); return;
  }
  box.innerHTML=skCards(2);
  exTimer=setTimeout(async()=>{
    try{ const d=await api('/search',{query:{q:raw}}); cachePolls(d.polls); pushRecentSearch(raw);
      let html='';
      if(d.tags&&d.tags.length){ html+='<div class="page-title" style="padding:0 4px 10px">Hashtags</div>'+
        '<div class="tagcloud" style="margin-bottom:8px">'+d.tags.map((t)=>`<a class="tag big" data-tag="${esc(t.tag)}">#${esc(t.tag)} <span class="tcount">${t.count}</span></a>`).join('')+'</div>'; }
      if(d.users.length){ html+='<div class="page-title" style="padding:8px 4px 10px">People</div>'+
        d.users.map((u)=>`<div class="card" style="display:flex;align-items:center;gap:11px;padding:14px 18px;cursor:pointer" data-author="${esc(u.username)}">
          ${avatar(u,'s40')}<div><b style="font-weight:700">${esc(u.name)}${vbadge(u.verified)}</b><div style="color:var(--muted);font-size:13px">@${esc(u.username)}${u.bio?' · '+esc(u.bio):''}</div></div></div>`).join(''); }
      html+='<div class="page-title" style="padding:14px 4px 10px">Polls</div><div id="feed"></div>';
      box.innerHTML=html; const feed=$('#feed',box);
      if(!d.polls.length) feed.innerHTML=empt('🔍','No polls match “'+esc(q)+'”.');
      else { feed.innerHTML=d.polls.map(cardHTML).join(''); wireFeed(feed); paintFills(feed); }
      wireFeed(box);
    }catch(e){ box.innerHTML=empt('⚠️',e.message); }
  },220);
}

async function renderDetail(code){
  chrome();
  setMain(`<button class="back-btn" data-back>← Back</button><div class="poll-detail">${skCard()}</div>
    <div class="detail-comments" id="dc">${skList(3)}</div>`);
  $('[data-back]').onclick=()=>{ if(history.length>1)history.back(); else go('#/'); };
  let poll;
  try{ poll=(await api('/poll',{query:{code}})).poll; }catch(e){ return notFound({emoji:'🗳️', title:'Poll not found', sub:'This poll may have been deleted or closed, or the link may be incorrect.', actions:[{label:'Browse polls',href:'#/explore',primary:true},{label:'Go home',href:'#/'}]}); }
  POLLS[poll.code]=poll;
  const wrap=$('.poll-detail'); if(!wrap || route!=='poll') return; wrap.innerHTML=cardHTML(poll); wireFeed(wrap); paintFills(wrap);
  if(poll.author && poll.author.isMe){
    const ins=document.createElement('button'); ins.className='btn btn-ghost btn-sm insights-btn';
    ins.innerHTML='📊 Audience insights'; ins.onclick=()=>openInsights(poll); wrap.appendChild(ins);
  }
  await loadComments(code, poll.id);
}
/* Results breakdown — anyone can open this to read the numbers clearly. */
/* Profile cover carousel: swipeable slides + dots + gentle auto-advance. */
function initCoverCarousel(p){
  const banner=$('.profile-banner'); if(!banner) return;
  const covers=(p.covers&&p.covers.length)?p.covers:(p.cover?[p.cover]:[]);
  if(covers.length<2) return;
  banner.classList.add('has-carousel');
  banner.style.backgroundImage='none';
  banner.innerHTML=`<div class="cc-track">${covers.map((c)=>`<div class="cc-slide" style="background-image:url('${esc(c)}')"></div>`).join('')}</div>
    <div class="cc-dots">${covers.map((_,i)=>`<button class="cc-dot${i===0?' on':''}" data-cc="${i}" aria-label="Cover ${i+1}"></button>`).join('')}</div>`;
  const track=banner.querySelector('.cc-track'); let idx=0, timer=null;
  const go=(i)=>{ idx=(i+covers.length)%covers.length; track.style.transform=`translateX(${-idx*100}%)`;
    banner.querySelectorAll('.cc-dot').forEach((d,j)=>d.classList.toggle('on',j===idx)); };
  banner.querySelectorAll('.cc-dot').forEach((d)=>d.onclick=(e)=>{ e.stopPropagation(); go(+d.dataset.cc); restart(); });
  // swipe
  let x0=null, dx=0;
  banner.addEventListener('touchstart',(e)=>{ if(e.touches.length!==1)return; x0=e.touches[0].clientX; dx=0; stop(); },{passive:true});
  banner.addEventListener('touchmove',(e)=>{ if(x0===null)return; dx=e.touches[0].clientX-x0; },{passive:true});
  banner.addEventListener('touchend',()=>{ if(x0===null)return;
    if(Math.abs(dx)>44){ go(idx+(dx<0?1:-1)); } x0=null; restart(); },{passive:true});
  const stop=()=>{ if(timer){ clearInterval(timer); timer=null; } };
  const restart=()=>{ stop(); timer=setInterval(()=>{ if(!document.body.contains(banner)||route!=='profile'){ stop(); return; } go(idx+1); },5000);
    window.__coverRot=timer; };
  restart();
}
async function openResults(poll){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim data-keepmenu><div class="sheet" style="max-height:84vh;overflow-y:auto">
    <div class="sheet-grab"></div>
    <div class="sheet-head"><h3>📊 Results</h3><button class="close" data-x>✕</button></div>
    <div id="res-body">${skList(2)}</div></div></div>`;
  $('[data-x]',ov).onclick=closeOverlay; $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  let p=poll;
  try{ p=(await api('/poll',{query:{code:poll.code}})).poll; POLLS[p.code]=p; }catch(e){}
  const body=$('#res-body',ov); if(!body) return;
  const total=p.totalVotes||0;
  const opts=(p.options||[]).slice().sort((a,b)=>(b.votes||0)-(a.votes||0));
  const top=opts.length?(opts[0].votes||0):0;
  if(!total){ body.innerHTML=empt('🗳️','No votes yet.','Be the first to vote on this poll.'); return; }
  body.innerHTML=`<div class="res-q">${esc(p.question)}</div>
    <div class="res-top">${fmt(total)} vote${total===1?'':'s'}${p.voters?` · ${fmt(p.voters)} voter${p.voters===1?'':'s'}`:''}${p.closed?' · closed':''}</div>
    <div class="res-list">${opts.map((o)=>{ const v=o.votes||0, pct=total?Math.round(v/total*100):0;
      return `<div class="res-row${v===top&&v>0?' lead':''}">
        <div class="res-head"><span class="res-lab">${esc(o.label)}${o._correct?' ✅':''}</span><span class="res-pct">${pct}%</span></div>
        <div class="res-track"><span class="res-fill" style="width:${pct}%"></span></div>
        <div class="res-n">${fmt(v)} vote${v===1?'':'s'}</div></div>`; }).join('')}</div>
    <div class="res-acts">
      <button class="btn btn-ghost btn-sm" id="res-copy">📋 Copy summary</button>
      ${p.author&&p.author.isMe?`<button class="btn btn-ghost btn-sm" id="res-csv">⬇️ Export CSV</button>
      <button class="btn btn-ghost btn-sm" id="res-who">👥 Who voted</button>`:''}
    </div>`;
  const w=$('#res-who',ov); if(w) w.onclick=()=>openVoters(p);
  const summary=()=>`${p.question}\n`+opts.map((o)=>{ const v=o.votes||0;
    return `• ${o.label}: ${v} vote${v===1?'':'s'} (${total?Math.round(v/total*100):0}%)`; }).join('\n')
    +`\nTotal: ${total} vote${total===1?'':'s'}\n${p.shareUrl||''}`;
  const cp=$('#res-copy',ov);
  if(cp) cp.onclick=async()=>{ try{ await navigator.clipboard.writeText(summary()); toast('Results copied!'); }
    catch(e){ prompt('Copy results:', summary()); } };
  const cs=$('#res-csv',ov);
  if(cs) cs.onclick=()=>{
    const esc2=(v)=>`"${String(v).replace(/"/g,'""')}"`;
    const rows=[['Option','Votes','Percent']].concat(opts.map((o)=>[o.label,o.votes||0,(total?Math.round((o.votes||0)/total*100):0)+'%']));
    const csv=rows.map((r)=>r.map(esc2).join(',')).join('\r\n');
    const blob=new Blob(['\ufeff'+csv],{type:'text/csv;charset=utf-8'});
    const a=document.createElement('a'); a.href=URL.createObjectURL(blob);
    a.download='poll-'+(p.code||'results')+'.csv'; document.body.appendChild(a); a.click();
    setTimeout(()=>{ URL.revokeObjectURL(a.href); a.remove(); },400); toast('CSV downloaded'); };
}
/* Voter list — poll owner only (server enforces this too). */
async function openVoters(poll){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim data-keepmenu><div class="sheet" style="max-height:84vh;overflow-y:auto">
    <div class="sheet-grab"></div>
    <div class="sheet-head"><h3>👥 Who voted</h3><button class="close" data-x>✕</button></div>
    <div id="vt-body">${skList(3)}</div></div></div>`;
  $('[data-x]',ov).onclick=closeOverlay; $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  let d; try{ d=await api('/poll/voters',{query:{pollId:poll.id}}); }
  catch(e){ const b=$('#vt-body',ov); if(b) b.innerHTML=empt('🔒',e.message); return; }
  const body=$('#vt-body',ov); if(!body) return;
  if(d.anonymous){ body.innerHTML=empt('🕶️','Anonymous poll',d.note); return; }
  if(!d.voters.length){ body.innerHTML=empt('🗳️','No signed-in voters yet.',d.anonCount?`${fmt(d.anonCount)} anonymous vote${d.anonCount===1?'':'s'} so far.`:''); return; }
  // group voters by the option they chose
  const groups={}; d.voters.forEach((v)=>{ (groups[v.option]=groups[v.option]||[]).push(v); });
  body.innerHTML=Object.entries(groups).map(([opt,list])=>`<div class="vt-group">
      <div class="vt-opt">${esc(opt)} <span>${fmt(list.length)}</span></div>
      ${list.map((v)=>`<a class="vt-row" href="#/u/${esc(v.username)}">${avatar(v,'s36',true)}
        <span class="vt-who"><b>${esc(v.name)}${vbadge(v.verified)}</b><span>@${esc(v.username)} · ${timeAgo(v.at)}</span></span></a>`).join('')}
    </div>`).join('')
    + (d.anonCount?`<p class="ins-note">Plus ${fmt(d.anonCount)} anonymous vote${d.anonCount===1?'':'s'} — those voters are never recorded.</p>`:'')
    + `<p class="ins-note">Only you can see this list.</p>`;
}
async function openInsights(poll){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim" data-scrim><div class="modal" data-keepmenu style="max-width:480px">
    <div class="mhead"><h3>📊 Audience insights</h3><button class="close" data-x>✕</button></div>
    <div id="insights-body">${skList(3)}</div></div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  let d; try{ d=(await api('/poll/demographics',{query:{pollId:poll.id}})).demographics; }
  catch(e){ $('#insights-body').innerHTML=empt('🔒',e.message); return; }
  const body=$('#insights-body');
  if(d.registered===0){ body.innerHTML=`<div class="ins-empty">
    <p>No insights yet${d.anon?` — your ${d.anon} vote${d.anon===1?'':'s'} so far came from people without a profile`:''}.</p>
    <p style="color:var(--soft);font-size:13px">Demographic breakdowns appear once signed-in members vote.</p></div>`; return; }
  const bars=(rows,key,labelKey)=>{ const max=Math.max(1,...rows.map((r)=>r[key]));
    return `<div class="ins-bars">${rows.map((r)=>`<div class="ins-bar"><span class="ins-lab">${esc(String(r[labelKey]))}</span>
      <span class="ins-track"><span class="ins-fill" style="width:${Math.round(r[key]/max*100)}%"></span></span>
      <span class="ins-val">${fmt(r[key])}</span></div>`).join('')}</div>`; };
  const genderBlock=d.gender.length?`<div class="ins-sec"><h4>By gender</h4>${bars(d.gender,'count','gender')}</div>`:'';
  const ageBlock=d.age.length?`<div class="ins-sec"><h4>By age</h4>${bars(d.age,'count','bucket')}</div>`:'';
  const locBlock=d.topLocations.length?`<div class="ins-sec"><h4>Top locations</h4>${bars(d.topLocations,'count','location')}</div>`:'';
  // per-option winner-by-gender summary
  const optBlock=`<div class="ins-sec"><h4>Who picked what</h4>${d.options.map((o)=>{
    const gtop=Object.entries(o.gender||{}).sort((a,b)=>b[1]-a[1])[0];
    const sub=o.votes?(gtop?`mostly ${esc(gtop[0])} (${gtop[1]}/${o.votes})`:''):'no member votes';
    return `<div class="ins-opt"><b>${esc(o.label)}</b><span>${fmt(o.votes)} member vote${o.votes===1?'':'s'}${sub?` · ${sub}`:''}</span></div>`;
  }).join('')}</div>`;
  body.innerHTML=`<div class="ins-top">${fmt(d.registered)} member vote${d.registered===1?'':'s'}${d.anon?` · ${fmt(d.anon)} anonymous`:''}</div>
    ${genderBlock}${ageBlock}${locBlock}${optBlock}
    <p class="ins-note">Only votes from signed-in members count toward demographics. Anonymous votes stay private.</p>`;
}
let CMAP={};
function countComments(list){ let n=0; for(const c of list){ n++; if(c.replies)n+=c.replies.length; } return n; }
function cpollHTML(c){
  const p=c.cpoll; if(!p) return '';
  const voted=p.myVote>=0, total=p.total;
  const opts=p.options.map((o,i)=>{
    if(voted){ const pct=total?Math.round(o.votes/total*100):0, mine=p.myVote===i;
      return `<div class="cp-opt voted ${mine?'mine':''}"><span class="cp-fill" style="width:${pct}%"></span><span class="cp-lbl">${mine?'✓ ':''}${esc(o.label)}</span><span class="cp-pct">${pct}%</span></div>`; }
    return `<button class="cp-opt" data-cpvote="${c.id}|${i}">${esc(o.label)}</button>`;
  }).join('');
  return `<div class="cpoll"><div class="cpoll-q">${ic('bars',14)} ${esc(p.q)}</div><div class="cpoll-opts">${opts}</div><div class="cpoll-total">${fmt(total)} vote${total===1?'':'s'} · poll</div></div>`;
}
function commentHTML(c,isReply){
  const cu={name:c.name,username:c.username,color:c.color,avatar:c.avatar};
  CMAP[c.id]=c;
  const acts=`<div class="cactions">
      <button class="cact ${c.liked?'on':''}" data-clike="${c.id}">${ic('heart',14)} <span class="cl">${c.likes?fmt(c.likes):''}</span></button>
      ${ME?`<button class="cact" data-creply="${c.id}">Reply</button>`:''}
      ${c.mine?`<button class="cact" data-cedit="${c.id}">Edit</button>`:''}
      ${(c.mine||(ME&&ME.admin))?`<button class="cact danger" data-cdel="${c.id}">Delete${!c.mine&&ME&&ME.admin?' (admin)':''}</button>`:''}
      ${(ME&&!c.mine)?`<button class="cact" data-creport="${c.id}">Report</button>`:''}
    </div><div class="reply-slot" id="rs-${c.id}"></div>`;
  const replies=(c.replies&&c.replies.length)?`<div class="creplies">${c.replies.map((r)=>commentHTML(r,true)).join('')}</div>`:'';
  return `<div class="citem ${isReply?'reply':''}" id="c-${c.id}">${avatar(cu,'s30',true)}
    <div class="cb"><b data-author="${esc(c.username)}" style="cursor:pointer">${esc(c.name)}${vbadge(c.verified)}</b>
      <span class="ct">@${esc(c.username)} · ${timeAgo(c.created_at*1000)}${c.edited?' · edited':''}</span>
      ${c.body?`<p class="cbody">${linkifyMentions(esc(c.body))}</p>`:''}${c.cpoll?cpollHTML(c):''}${acts}${replies}</div></div>`;
}
/* LinkedIn-style comments: a bottom sheet over the feed — no page navigation,
   so you never lose your place in the feed. */
/* Comments: on a phone a bottom sheet slides up; on desktop the card expands
   inline so you keep the poll in view. */
function openComments(code, anchorEl){
  if(window.innerWidth>820) return toggleInlineComments(code, anchorEl);
  return openCommentsSheet(code);
}
/* Desktop: expand a comments panel directly under the poll card. */
async function toggleInlineComments(code, anchorEl){
  const card=(anchorEl&&anchorEl.closest('.card')) || $('#card-'+code);
  if(!card) return openCommentsSheet(code);
  const existing=card.querySelector('.ic-panel');
  if(existing){ existing.remove(); card.classList.remove('ic-open'); return; }   // toggle closed
  let poll=POLLS[code];
  card.classList.add('ic-open');
  const panel=document.createElement('div');
  panel.className='ic-panel';
  panel.innerHTML=`<div class="ic-head"><b class="ic-title">Comments</b>
      <button class="ic-close" aria-label="Hide comments">\u2715</button></div>
    <div class="ic-list">${skList(2)}</div>
    ${ME?`<div class="ic-bar"><span class="cs-av">${avatar(ME,'s30')}</span>
        <textarea class="field ic-input" placeholder="Add a comment\u2026" rows="1"></textarea>
        <button class="cs-poll-btn ic-pollbtn" title="Add a poll" aria-label="Add a poll">${ic('bars',18)}</button>
        <button class="cs-send ic-send" aria-label="Post comment">${ic('send',18)}</button></div>`
      :`<div class="ic-bar signin"><button class="btn btn-dark btn-block" data-auth>${ic('signin',15)} Sign in to comment</button></div>`}`;
  card.appendChild(panel);
  panel.querySelector('.ic-close').onclick=()=>{ panel.remove(); card.classList.remove('ic-open'); };
  if(!poll){ try{ poll=(await api('/poll',{query:{code}})).poll; POLLS[code]=poll; }catch(e){} }
  if(!poll){ panel.querySelector('.ic-list').innerHTML=empt('\ud83e\udd14','Couldn\u2019t load comments.'); return; }
  await loadInlineComments(code, poll.id);
  const pbI=panel.querySelector('.ic-pollbtn'); if(pbI) pbI.onclick=()=>openCommentPollComposer(poll.id, code);
  const ta=panel.querySelector('.ic-input');
  if(ta){ attachMentions(ta);
    const grow=()=>{ ta.style.height='auto'; ta.style.height=Math.min(ta.scrollHeight,120)+'px'; };
    ta.addEventListener('input',grow);
    ta.addEventListener('keydown',(e)=>{ if(e.key==='Enter'&&(e.metaKey||e.ctrlKey)){ e.preventDefault(); panel.querySelector('.ic-send').click(); } });
    panel.querySelector('.ic-send').onclick=async()=>{
      const body=ta.value.trim(); if(!body){ toast('Write something first'); return; }
      const btn=panel.querySelector('.ic-send'); btn.disabled=true;
      try{ await api('/comments',{method:'POST',body:{pollId:poll.id,body}});
        ta.value=''; ta.style.height='auto'; await loadInlineComments(code, poll.id); }
      catch(e){ toast(e.message); }
      btn.disabled=false;
    };
  }
}
async function loadInlineComments(code, pollId){
  const card=$('#card-'+code) || document.querySelector('.card.ic-open');
  const panel=card?card.querySelector('.ic-panel'):null;
  const list=panel?panel.querySelector('.ic-list'):null; if(!list) return; CMAP={};
  let comments=[]; try{ comments=(await api('/comments',{query:{pollId}})).comments; }catch(e){}
  const total=countComments(comments);
  const t=panel.querySelector('.ic-title');
  if(t) t.textContent=total?`${fmt(total)} comment${total===1?'':'s'}`:'Comments';
  list.innerHTML=comments.length?comments.map((c)=>commentHTML(c,false)).join('')
    : `<div class="cs-empty">\ud83d\udcac<p>No comments yet</p><span>Start the conversation.</span></div>`;
  wireComments(list, code, pollId);
  const p=POLLS[code]; if(p){ p.comments=total;
    const n=$('#card-'+code+' .act.cmt .n'); if(n) n.textContent=fmt(total); }
}
async function openCommentsSheet(code){
  let poll=POLLS[code];
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim data-keepmenu>
    <div class="sheet comments-sheet" data-keepmenu>
      <div class="sheet-grab"></div>
      <div class="sheet-head"><h3 id="cs-title">Comments</h3><button class="close" data-x>\u2715</button></div>
      ${poll?`<button class="cs-poll" id="cs-poll">${ic('bars',15)} <span>${esc(poll.question)}</span></button>`:''}
      <div class="cs-list" id="cs-list">${skList(3)}</div>
      ${ME?`<div class="cs-bar"><span class="cs-av">${avatar(ME,'s30')}</span>
          <textarea class="field" id="cs-input" placeholder="Add a comment\u2026" rows="1"></textarea>
          <button class="cs-poll-btn" id="cs-pollbtn" title="Add a poll" aria-label="Add a poll">${ic('bars',18)}</button>
          <button class="cs-send" id="cs-send" aria-label="Post comment">${ic('send',18)}</button></div>`
        :`<div class="cs-bar signin"><button class="btn btn-dark btn-block" data-auth>${ic('signin',15)} Sign in to comment</button></div>`}
    </div></div>`;
  $('[data-x]',ov).onclick=closeOverlay;
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget) closeOverlay(); };
  const jump=$('#cs-poll',ov); if(jump) jump.onclick=()=>{ closeOverlay(); go('#/poll/'+code); };
  // make sure we have the poll (e.g. opened straight from a notification)
  if(!poll){ try{ poll=(await api('/poll',{query:{code}})).poll; POLLS[code]=poll;
      const t=$('#cs-title',ov); if(t&&poll) t.textContent='Comments'; }catch(e){} }
  if(!poll){ const l=$('#cs-list',ov); if(l) l.innerHTML=empt('\ud83e\udd14','Couldn\u2019t load this poll.'); return; }
  await loadSheetComments(code, poll.id);
  const pbS=$('#cs-pollbtn',ov); if(pbS) pbS.onclick=()=>openCommentPollComposer(poll.id, code);
  const ta=$('#cs-input',ov);
  if(ta){ attachMentions(ta);
    const grow=()=>{ ta.style.height='auto'; ta.style.height=Math.min(ta.scrollHeight,120)+'px'; };
    ta.addEventListener('input',grow);
    ta.addEventListener('keydown',(e)=>{ if(e.key==='Enter'&&(e.metaKey||e.ctrlKey)){ e.preventDefault(); $('#cs-send').click(); } });
    $('#cs-send',ov).onclick=async()=>{
      const body=ta.value.trim(); if(!body){ toast('Write something first'); return; }
      const btn=$('#cs-send'); btn.disabled=true;
      try{ await api('/comments',{method:'POST',body:{pollId:poll.id,body}});
        ta.value=''; ta.style.height='auto';
        await loadSheetComments(code, poll.id);
        const l=$('#cs-list'); if(l) l.scrollTop=l.scrollHeight;
      }catch(e){ toast(e.message); }
      btn.disabled=false;
    };
  }
}
async function loadSheetComments(code, pollId){
  const list=$('#cs-list'); if(!list) return; CMAP={};
  let comments=[]; try{ comments=(await api('/comments',{query:{pollId}})).comments; }catch(e){}
  const total=countComments(comments);
  const t=$('#cs-title'); if(t) t.textContent=total?`${fmt(total)} comment${total===1?'':'s'}`:'Comments';
  list.innerHTML=comments.length?comments.map((c)=>commentHTML(c,false)).join('')
    : `<div class="cs-empty">\ud83d\udcac<p>No comments yet</p><span>Start the conversation.</span></div>`;
  wireComments(list, code, pollId);
  // keep the card's comment counter in sync
  const p=POLLS[code]; if(p){ p.comments=total;
    const n=$('#card-'+code+' .act.cmt .n'); if(n) n.textContent=fmt(total); }
}
/* Comments live in two places: the poll page (#dc) and the bottom sheet (#cs-list).
   Refresh whichever one is currently on screen. */
async function refreshComments(code, pollId){
  const icard=$('#card-'+code); if(icard && icard.querySelector('.ic-panel')) return loadInlineComments(code, pollId);
  if($('#cs-list')) return loadSheetComments(code, pollId);
  return loadComments(code, pollId);
}
async function loadComments(code, pollId){
  const dc=$('#dc'); if(!dc)return; CMAP={};
  let comments=[];
  try{ comments=(await api('/comments',{query:{pollId}})).comments; }catch(e){}
  const total=countComments(comments);
  dc.innerHTML=`<h3>${total} comment${total===1?'':'s'}</h3>
    ${ME?`<div class="cbox">${avatar(ME,'s30')}<textarea class="field" id="cbody" placeholder="Add a comment…" style="min-height:44px"></textarea>
      <button class="btn btn-dark btn-sm" id="csend">Post</button></div>`
      :`<p class="note" style="margin-bottom:12px"><button class="btn btn-ghost btn-sm" data-auth>${ic('signin',14)} Sign in to comment</button></p>`}
    <div id="clist"></div>`;
  const list=$('#clist');
  list.innerHTML=comments.length?comments.map((c)=>commentHTML(c,false)).join('')
    : '<p class="empty" style="padding:24px">No comments yet — start the conversation.</p>';
  wireComments(list, code, pollId);
  if(ME){ const ta=$('#cbody'); attachMentions(ta);
    $('#csend').onclick=async()=>{ const body=ta.value.trim(); if(!body){toast('Write something first');return;}
    try{ await api('/comments',{method:'POST',body:{pollId,body}}); ta.value=''; await loadComments(code,pollId); }catch(e){ toast(e.message); } }; }
}
function wireComments(list, code, pollId){
  list.addEventListener('click', async (e)=>{
    const au=e.target.closest('[data-author]'); if(au){ go('#/u/'+au.dataset.author); return; }
    const lk=e.target.closest('[data-clike]'); if(lk){ if(!ME){ openAuth(); return; }
      const id=+lk.dataset.clike;
      try{ const r=await api('/comments/like',{method:'POST',body:{commentId:id}});
        lk.classList.toggle('on',r.liked); lk.querySelector('.cl').textContent=r.likes?fmt(r.likes):''; }catch(err){ toast(err.message); } return; }
    const rp=e.target.closest('[data-creply]'); if(rp){ openReplyBox(+rp.dataset.creply, code, pollId); return; }
    const ed=e.target.closest('[data-cedit]'); if(ed){ openEditBox(+ed.dataset.cedit, code, pollId); return; }
    const rep=e.target.closest('[data-creport]'); if(rep){ openReport('comment', +rep.dataset.creport); return; }
    const cpv=e.target.closest('[data-cpvote]'); if(cpv){ if(!ME){ openAuth(); return; }
      const [cid,opt]=cpv.dataset.cpvote.split('|');
      try{ await api('/comments/pollvote',{method:'POST',body:{commentId:+cid,opt:+opt}}); await refreshComments(code,pollId); }catch(err){ toast(err.message); } return; }
    const dl=e.target.closest('[data-cdel]'); if(dl){ const id=+dl.dataset.cdel;
      if(!confirm('Delete this comment? Replies to it will be removed too.'))return;
      try{ await api('/comments/delete',{method:'POST',body:{commentId:id}}); await refreshComments(code,pollId); toast('Comment deleted'); }catch(err){ toast(err.message); } return; }
  });
}
function openCommentPollComposer(pollId, code){
  if(!ME){ openAuth(); return; }
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim><div class="sheet cpoll-composer">
    <div class="sheet-head"><h3>${ic('bars',18)} Add a poll</h3><button class="close" data-x>✕</button></div>
    <p class="sheet-sub">Post a quick poll as a comment — everyone in this thread can vote.</p>
    <input class="cpc-in" id="cpc-q" placeholder="Ask a question…" maxlength="200">
    <div id="cpc-opts">
      <input class="cpc-in cpc-opt" placeholder="Option 1" maxlength="80">
      <input class="cpc-in cpc-opt" placeholder="Option 2" maxlength="80">
    </div>
    <button class="cpc-add" id="cpc-add">+ Add option</button>
    <button class="btn btn-lime btn-block" id="cpc-post" style="margin-top:12px">Post poll</button>
  </div></div>`;
  $('[data-x]',ov).onclick=()=>{ closeOverlay(); restoreComments(); };
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget){ closeOverlay(); restoreComments(); } };
  const sheetMode = window.innerWidth<=820;
  function restoreComments(){ if(sheetMode) openCommentsSheet(code); else { const card=$('#card-'+code); if(card && card.querySelector('.ic-panel')) loadInlineComments(code, pollId); } }
  $('#cpc-q',ov).focus();
  $('#cpc-add',ov).onclick=()=>{ const box=$('#cpc-opts',ov); if(box.querySelectorAll('.cpc-opt').length>=4){ toast('Up to 4 options.'); return; }
    const i=document.createElement('input'); i.className='cpc-in cpc-opt'; i.placeholder='Option '+(box.querySelectorAll('.cpc-opt').length+1); i.maxLength=80; box.appendChild(i); i.focus(); };
  $('#cpc-post',ov).onclick=async()=>{
    const q=$('#cpc-q',ov).value.trim();
    const options=[...ov.querySelectorAll('.cpc-opt')].map((i)=>i.value.trim()).filter(Boolean);
    if(q.length<3){ toast('Write a question.'); return; }
    if(options.length<2){ toast('Add at least 2 options.'); return; }
    try{ await api('/comments',{method:'POST',body:{pollId, body:'', cpoll:{question:q, options}}}); closeOverlay(); restoreComments(); toast('Poll posted!'); }
    catch(e){ toast(e.message); }
  };
}
function openReplyBox(id, code, pollId){
  const slot=$('#rs-'+id); if(!slot)return;
  if(slot.firstChild){ slot.innerHTML=''; return; }   // toggle
  slot.innerHTML=`<div class="cbox reply-box">${avatar(ME,'s30')}<textarea class="field" placeholder="Write a reply…" style="min-height:40px"></textarea>
    <button class="btn btn-dark btn-sm" data-go>Reply</button></div>`;
  const ta=slot.querySelector('textarea'); ta.focus(); attachMentions(ta);
  slot.querySelector('[data-go]').onclick=async()=>{ const body=ta.value.trim(); if(!body){toast('Write a reply first');return;}
    try{ await api('/comments',{method:'POST',body:{pollId,body,parentId:id}}); await refreshComments(code,pollId); }catch(e){ toast(e.message); } };
}
function openEditBox(id, code, pollId){
  const item=$('#c-'+id); if(!item)return; const p=item.querySelector('.cbody'); if(!p||item._editing)return; item._editing=true;
  const cur=(CMAP[id]&&CMAP[id].body)||p.textContent;
  const box=document.createElement('div'); box.className='cedit-box';
  box.innerHTML=`<textarea class="field" style="min-height:44px"></textarea>
    <div class="cedit-row"><button class="btn btn-dark btn-sm" data-save>Save</button><button class="btn btn-ghost btn-sm" data-cancel>Cancel</button></div>`;
  const ta=box.querySelector('textarea'); ta.value=cur; p.style.display='none'; p.after(box); ta.focus(); attachMentions(ta);
  box.querySelector('[data-cancel]').onclick=()=>{ box.remove(); p.style.display=''; item._editing=false; };
  box.querySelector('[data-save]').onclick=async()=>{ const body=ta.value.trim(); if(!body){toast('Comment can\'t be empty');return;}
    try{ await api('/comments/edit',{method:'POST',body:{commentId:id,body}}); await refreshComments(code,pollId); toast('Comment updated'); }catch(e){ toast(e.message); } };
}

const BRANDS={
  instagram:["M7.0301.084c-1.2768.0602-2.1487.264-2.911.5634-.7888.3075-1.4575.72-2.1228 1.3877-.6652.6677-1.075 1.3368-1.3802 2.127-.2954.7638-.4956 1.6365-.552 2.914-.0564 1.2775-.0689 1.6882-.0626 4.947.0062 3.2586.0206 3.6671.0825 4.9473.061 1.2765.264 2.1482.5635 2.9107.308.7889.72 1.4573 1.388 2.1228.6679.6655 1.3365 1.0743 2.1285 1.38.7632.295 1.6361.4961 2.9134.552 1.2773.056 1.6884.069 4.9462.0627 3.2578-.0062 3.668-.0207 4.9478-.0814 1.28-.0607 2.147-.2652 2.9098-.5633.7889-.3086 1.4578-.72 2.1228-1.3881.665-.6682 1.0745-1.3378 1.3795-2.1284.2957-.7632.4966-1.636.552-2.9124.056-1.2809.0692-1.6898.063-4.948-.0063-3.2583-.021-3.6668-.0817-4.9465-.0607-1.2797-.264-2.1487-.5633-2.9117-.3084-.7889-.72-1.4568-1.3876-2.1228C21.2982 1.33 20.628.9208 19.8378.6165 19.074.321 18.2017.1197 16.9244.0645 15.6471.0093 15.236-.005 11.977.0014 8.718.0076 8.31.0215 7.0301.0839m.1402 21.6932c-1.17-.0509-1.8053-.2453-2.2287-.408-.5606-.216-.96-.4771-1.3819-.895-.422-.4178-.6811-.8186-.9-1.378-.1644-.4234-.3624-1.058-.4171-2.228-.0595-1.2645-.072-1.6442-.079-4.848-.007-3.2037.0053-3.583.0607-4.848.05-1.169.2456-1.805.408-2.2282.216-.5613.4762-.96.895-1.3816.4188-.4217.8184-.6814 1.3783-.9003.423-.1651 1.0575-.3614 2.227-.4171 1.2655-.06 1.6447-.072 4.848-.079 3.2033-.007 3.5835.005 4.8495.0608 1.169.0508 1.8053.2445 2.228.408.5608.216.96.4754 1.3816.895.4217.4194.6816.8176.9005 1.3787.1653.4217.3617 1.056.4169 2.2263.0602 1.2655.0739 1.645.0796 4.848.0058 3.203-.0055 3.5834-.061 4.848-.051 1.17-.245 1.8055-.408 2.2294-.216.5604-.4763.96-.8954 1.3814-.419.4215-.8181.6811-1.3783.9-.4224.1649-1.0577.3617-2.2262.4174-1.2656.0595-1.6448.072-4.8493.079-3.2045.007-3.5825-.006-4.848-.0608M16.953 5.5864A1.44 1.44 0 1 0 18.39 4.144a1.44 1.44 0 0 0-1.437 1.4424M5.8385 12.012c.0067 3.4032 2.7706 6.1557 6.173 6.1493 3.4026-.0065 6.157-2.7701 6.1506-6.1733-.0065-3.4032-2.771-6.1565-6.174-6.1498-3.403.0067-6.156 2.771-6.1496 6.1738M8 12.0077a4 4 0 1 1 4.008 3.9921A3.9996 3.9996 0 0 1 8 12.0077","#FF0069","instagram.com"],
  youtube:["M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z","#FF0000","youtube.com,youtu.be"],
  facebook:["M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z","#0866FF","facebook.com,fb.com,fb.me"],
  x:["M14.234 10.162 22.977 0h-2.072l-7.591 8.824L7.251 0H.258l9.168 13.343L.258 24H2.33l8.016-9.318L16.749 24h6.993zm-2.837 3.299-.929-1.329L3.076 1.56h3.182l5.965 8.532.929 1.329 7.754 11.09h-3.182z","#000000","x.com,twitter.com"],
  tiktok:["M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z","#000000","tiktok.com"],
  github:["M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12","#181717","github.com"],
  whatsapp:["M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z","#25D366","whatsapp.com,wa.me"],
  telegram:["M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z","#26A5E4","t.me,telegram.me,telegram.org"],
  spotify:["M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z","#1ED760","spotify.com"],
  pinterest:["M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z","#BD081C","pinterest.com,pin.it"],
  snapchat:["M12.206.793c.99 0 4.347.276 5.93 3.821.529 1.193.403 3.219.299 4.847l-.003.06c-.012.18-.022.345-.03.51.075.045.203.09.401.09.3-.016.659-.12 1.033-.301.165-.088.344-.104.464-.104.182 0 .359.029.509.09.45.149.734.479.734.838.015.449-.39.839-1.213 1.168-.089.029-.209.075-.344.119-.45.135-1.139.36-1.333.81-.09.224-.061.524.12.868l.015.015c.06.136 1.526 3.475 4.791 4.014.255.044.435.27.42.509 0 .075-.015.149-.045.225-.24.569-1.273.988-3.146 1.271-.059.091-.12.375-.164.57-.029.179-.074.36-.134.553-.076.271-.27.405-.555.405h-.03c-.135 0-.313-.031-.538-.074-.36-.075-.765-.135-1.273-.135-.3 0-.599.015-.913.074-.6.104-1.123.464-1.723.884-.853.599-1.826 1.288-3.294 1.288-.06 0-.119-.015-.18-.015h-.149c-1.468 0-2.427-.675-3.279-1.288-.599-.42-1.107-.779-1.707-.884-.314-.045-.629-.074-.928-.074-.54 0-.958.089-1.272.149-.211.043-.391.074-.54.074-.374 0-.523-.224-.583-.42-.061-.192-.09-.389-.135-.567-.046-.181-.105-.494-.166-.57-1.918-.222-2.95-.642-3.189-1.226-.031-.063-.052-.15-.055-.225-.015-.243.165-.465.42-.509 3.264-.54 4.73-3.879 4.791-4.02l.016-.029c.18-.345.224-.645.119-.869-.195-.434-.884-.658-1.332-.809-.121-.029-.24-.074-.346-.119-1.107-.435-1.257-.93-1.197-1.273.09-.479.674-.793 1.168-.793.146 0 .27.029.383.074.42.194.789.3 1.104.3.234 0 .384-.06.465-.105l-.046-.569c-.098-1.626-.225-3.651.307-4.837C7.392 1.077 10.739.807 11.727.807l.419-.015h.06z","#FFFC00","snapchat.com"],
  twitch:["M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714Z","#9146FF","twitch.tv"],
  discord:["M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189Z","#5865F2","discord.gg,discord.com"],
  reddit:["M12 0C5.373 0 0 5.373 0 12c0 3.314 1.343 6.314 3.515 8.485l-2.286 2.286C.775 23.225 1.097 24 1.738 24H12c6.627 0 12-5.373 12-12S18.627 0 12 0Zm4.388 3.199c1.104 0 1.999.895 1.999 1.999 0 1.105-.895 2-1.999 2-.946 0-1.739-.657-1.947-1.539v.002c-1.147.162-2.032 1.15-2.032 2.341v.007c1.776.067 3.4.567 4.686 1.363.473-.363 1.064-.58 1.707-.58 1.547 0 2.802 1.254 2.802 2.802 0 1.117-.655 2.081-1.601 2.531-.088 3.256-3.637 5.876-7.997 5.876-4.361 0-7.905-2.617-7.998-5.87-.954-.447-1.614-1.415-1.614-2.538 0-1.548 1.255-2.802 2.803-2.802.645 0 1.239.218 1.712.585 1.275-.79 2.881-1.291 4.64-1.365v-.01c0-1.663 1.263-3.034 2.88-3.207.188-.911.993-1.595 1.959-1.595Zm-8.085 8.376c-.784 0-1.459.78-1.506 1.797-.047 1.016.64 1.429 1.426 1.429.786 0 1.371-.369 1.418-1.385.047-1.017-.553-1.841-1.338-1.841Zm7.406 0c-.786 0-1.385.824-1.338 1.841.047 1.017.634 1.385 1.418 1.385.785 0 1.473-.413 1.426-1.429-.046-1.017-.721-1.797-1.506-1.797Zm-3.703 4.013c-.974 0-1.907.048-2.77.135-.147.015-.241.168-.183.305.483 1.154 1.622 1.964 2.953 1.964 1.33 0 2.47-.81 2.953-1.964.057-.137-.037-.29-.184-.305-.863-.087-1.795-.135-2.769-.135Z","#FF4500","reddit.com"],
  medium:["M4.21 0A4.201 4.201 0 0 0 0 4.21v15.58A4.201 4.201 0 0 0 4.21 24h15.58A4.201 4.201 0 0 0 24 19.79v-1.093c-.137.013-.278.02-.422.02-2.577 0-4.027-2.146-4.09-4.832a7.592 7.592 0 0 1 .022-.708c.093-1.186.475-2.241 1.105-3.022a3.885 3.885 0 0 1 1.395-1.1c.468-.237 1.127-.367 1.664-.367h.023c.101 0 .202.004.303.01V4.211A4.201 4.201 0 0 0 19.79 0Zm.198 5.583h4.165l3.588 8.435 3.59-8.435h3.864v.146l-.019.004c-.705.16-1.063.397-1.063 1.254h-.003l.003 10.274c.06.676.424.885 1.063 1.03l.02.004v.145h-4.923v-.145l.019-.005c.639-.144.994-.353 1.054-1.03V7.267l-4.745 11.15h-.261L6.15 7.569v9.445c0 .857.358 1.094 1.063 1.253l.02.004v.147H4.405v-.147l.019-.004c.705-.16 1.065-.397 1.065-1.253V6.987c0-.857-.358-1.094-1.064-1.254l-.018-.004zm19.25 3.668c-1.086.023-1.733 1.323-1.813 3.124H24V9.298a1.378 1.378 0 0 0-.342-.047Zm-1.862 3.632c-.1 1.756.86 3.239 2.204 3.634v-3.634z","#000000","medium.com"],
  substack:["M22.539 8.242H1.46V5.406h21.08v2.836zM1.46 10.812V24L12 18.11 22.54 24V10.812H1.46zM22.54 0H1.46v2.836h21.08V0z","#FF6719","substack.com"],
  patreon:["M22.957 7.21c-.004-3.064-2.391-5.576-5.191-6.482-3.478-1.125-8.064-.962-11.384.604C2.357 3.231 1.093 7.391 1.046 11.54c-.039 3.411.302 12.396 5.369 12.46 3.765.047 4.326-4.804 6.068-7.141 1.24-1.662 2.836-2.132 4.801-2.618 3.376-.836 5.678-3.501 5.673-7.031Z","#000000","patreon.com"],
  threads:["M18.263 11.097c-.03-3.486-1.92-5.586-5.111-5.586-2.13 0-3.922.963-4.863 2.499l2.062 1.438c.535-.843 1.272-1.543 2.628-1.543 1.528 0 2.318.85 2.544 2.431a15 15 0 0 0-2.236-.173c-4.125 0-6.068 1.867-6.068 4.336s1.943 3.99 4.804 3.99c3.139 0 5.013-2.115 5.781-4.735.798.361 1.348 1.204 1.348 2.47 0 3.387-3.907 5.232-7.22 5.232-4.885 0-8.077-3.207-8.077-8.424 0-6.392 4.223-10.487 9.9-10.487 3.808 0 5.69 1.671 6.97 3.914l2.108-1.475C21.44 2.078 18.331 0 13.663 0 6.227 0 1.168 5.277 1.168 12.934c0 7 4.953 11.066 10.856 11.066 4.878 0 9.809-2.846 9.809-7.716 0-2.545-1.46-4.231-3.569-5.187m-6.33 4.855c-1.077 0-2.026-.512-2.026-1.453 0-1.483 1.822-1.934 3.606-1.934.678 0 1.34.045 1.927.173-.422 1.927-1.671 3.215-3.508 3.214Z","#000000","threads.net"],
  linkedin:["M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.225 0z","#0A66C2","linkedin.com,lnkd.in"]
};
function brandFor(url){
  const u=String(url||'').toLowerCase();
  if(u.startsWith('mailto:')) return {svg:'<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 4l-8 5-8-5V6l8 5 8-5z"/></svg>', c:'#ea4335', key:'email'};
  if(u.startsWith('tel:')) return {svg:'<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M6.6 10.8a15.5 15.5 0 006.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 013 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.2.2 2.4.6 3.6.1.4 0 .8-.3 1z"/></svg>', c:'#16a34a', key:'phone'};
  let host=''; try{ host=new URL(u.match(/^https?:\/\//?u:'https://'+u)).hostname.replace(/^www\./,''); }catch(e){ return null; }
  for(const k in BRANDS){ const hs=BRANDS[k][2]; if(!hs) continue;
    for(const dom of hs.split(',')){ if(dom && (host===dom || host.endsWith('.'+dom))) {
      return {svg:'<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="'+BRANDS[k][0]+'"/></svg>', c:BRANDS[k][1], key:k}; } } }
  return null;
}

const CARD_THEMES={
  aurora:{bg:'linear-gradient(160deg,#6c4cff,#a17bff 55%,#ff7a5c 130%)',fg:'#fff',btn:'rgba(255,255,255,.16)',btnfg:'#fff',bd:'rgba(255,255,255,.34)',soft:'rgba(255,255,255,.82)'},
  lime:{bg:'linear-gradient(165deg,#0f0f12,#1b2913 135%)',fg:'#ecffd6',btn:'#c6f24e',btnfg:'#15140f',bd:'rgba(198,242,78,.45)',soft:'#b3caa0'},
  ocean:{bg:'linear-gradient(165deg,#0b2b52,#1466a8 55%,#3fc7e0 135%)',fg:'#eaf6ff',btn:'rgba(255,255,255,.15)',btnfg:'#fff',bd:'rgba(255,255,255,.3)',soft:'rgba(234,246,255,.78)'},
  rose:{bg:'linear-gradient(165deg,#ff8fb1,#ff5c8a 55%,#c44bd6 135%)',fg:'#fff',btn:'rgba(255,255,255,.2)',btnfg:'#fff',bd:'rgba(255,255,255,.42)',soft:'rgba(255,255,255,.86)'},
  gold:{bg:'linear-gradient(165deg,#1a1710,#3a2f16 135%)',fg:'#ffe9b0',btn:'#e8b64a',btnfg:'#241c08',bd:'rgba(232,182,74,.4)',soft:'#c9b083'},
  grape:{bg:'linear-gradient(165deg,#2a1258,#5b2bb0 55%,#9b6bff 135%)',fg:'#f2e9ff',btn:'rgba(255,255,255,.16)',btnfg:'#fff',bd:'rgba(255,255,255,.32)',soft:'rgba(242,233,255,.8)'},
  sunset:{bg:'linear-gradient(165deg,#ff7a5c,#ff5c8a 55%,#7c4cff 135%)',fg:'#fff',btn:'rgba(255,255,255,.18)',btnfg:'#fff',bd:'rgba(255,255,255,.4)',soft:'rgba(255,255,255,.85)'},
  mint:{bg:'linear-gradient(165deg,#0fb8a6,#14d3b0 60%,#9af5c0 130%)',fg:'#04231f',btn:'rgba(255,255,255,.85)',btnfg:'#04231f',bd:'rgba(4,35,31,.14)',soft:'rgba(4,35,31,.66)'},
  midnight:{bg:'#0f1020',fg:'#f4f4ff',btn:'#1c1d33',btnfg:'#f4f4ff',bd:'#2b2c48',soft:'#9a9ac0'},
  graphite:{bg:'#17181c',fg:'#eef0f4',btn:'#26282f',btnfg:'#eef0f4',bd:'#33353d',soft:'#9aa0ab'},
  paper:{bg:'#f4f2ec',fg:'#15140f',btn:'#fff',btnfg:'#15140f',bd:'#e4e0d5',soft:'#6b6659'},
};
const CARD_THEME_ORDER=['aurora','lime','ocean','rose','gold','grape','sunset','mint','midnight','graphite','paper'];
const CARD_THEME_NAME={aurora:'Aurora',lime:'Lime',ocean:'Ocean',rose:'Rose',gold:'Gold',grape:'Grape',sunset:'Sunset',mint:'Mint',midnight:'Midnight',graphite:'Graphite',paper:'Paper'};
const CARD_FONTS={
  sans:{name:'Clean',css:"'Plus Jakarta Sans',system-ui,sans-serif"},
  serif:{name:'Editorial',css:"'Fraunces','Georgia',serif"},
  rounded:{name:'Rounded',css:"'Baloo 2',system-ui,sans-serif"},
  mono:{name:'Mono',css:"'Space Mono',ui-monospace,monospace"},
};
const CARD_FONT_ORDER=['sans','serif','rounded','mono'];
function cardBtnRadius(b){ return b==='sharp'?'8px':(b==='pill'?'999px':'16px'); }
let CARD_FONT_LOADED={};
function linkHost(url){ try{ const u=String(url||''); return new URL(/^https?:\/\//.test(u)?u:'https://'+u).hostname.replace(/^www\./,''); }catch(e){ return ''; } }
function ensureCardFont(key){
  const map={sans:'Plus+Jakarta+Sans:wght@500;700;800',serif:'Fraunces:opsz,wght@9..144,600;9..144,800',rounded:'Baloo+2:wght@600;800',mono:'Space+Mono:wght@700'};
  const spec=map[key||'sans']||map.sans; if(CARD_FONT_LOADED[spec]) return; CARD_FONT_LOADED[spec]=1;
  const l=document.createElement('link'); l.rel='stylesheet'; l.href='https://fonts.googleapis.com/css2?family='+spec+'&display=swap'; document.head.appendChild(l);
}
function docIcon(ext){ ext=(ext||'').toLowerCase();
  const map={pdf:'📄',doc:'📝',docx:'📝',xls:'📊',xlsx:'📊',ppt:'📽️',pptx:'📽️',txt:'📃',csv:'📊',zip:'🗜️',png:'🖼️',jpg:'🖼️',jpeg:'🖼️',webp:'🖼️',gif:'🖼️'};
  return map[ext]||'📎'; }
let QR_LOADING=null;
function loadQR(){ if(window.qrcode) return Promise.resolve();
  if(QR_LOADING) return QR_LOADING;
  QR_LOADING=new Promise((res,rej)=>{ const s=document.createElement('script'); s.src='/qrcode.js?v=1'; s.onload=()=>res(); s.onerror=()=>rej(new Error('QR failed to load')); document.head.appendChild(s); });
  return QR_LOADING;
}
async function openQRModal(url, name){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim><div class="sheet qr-sheet">
    <div class="sheet-head"><h3>${ic('link',18)} QR code</h3><button class="close" data-x>✕</button></div>
    <p class="sheet-sub">Scan to open this link-in-bio. Great for posters, packaging, cards and stories.</p>
    <div class="qr-box" id="qr-box"><div class="qr-load">Generating…</div></div>
    <div class="qr-url">${esc(url)}</div>
    <div class="qr-acts">
      <button class="btn btn-lime" id="qr-dl">${ic('link',15)} Download</button>
      <button class="btn btn-ghost" id="qr-share">${ic('share',15)} Share</button>
    </div>
  </div></div>`;
  $('[data-x]',ov).onclick=closeOverlay; $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget) closeOverlay(); };
  try{ await loadQR(); }catch(e){ $('#qr-box',ov).innerHTML='<div class="qr-load">Couldn’t load the QR generator.</div>'; return; }
  const qr=window.qrcode(0,'M'); qr.addData(url); qr.make();
  const n=qr.getModuleCount(); const cell=8, margin=4, size=(n+margin*2)*cell;
  let svg=`<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}"><rect width="100%" height="100%" fill="#fff"/>`;
  for(let r=0;r<n;r++) for(let c=0;c<n;c++){ if(qr.isDark(r,c)) svg+=`<rect x="${(c+margin)*cell}" y="${(r+margin)*cell}" width="${cell}" height="${cell}" fill="#0f0f12"/>`; }
  svg+='</svg>';
  const box=$('#qr-box',ov); if(box) box.innerHTML=svg;
  const fname=(name||'link-in-bio')+'-qr.png';
  const toPng=()=>new Promise((resolve)=>{ const img=new Image();
    const b=new Blob([svg],{type:'image/svg+xml'}); const u=URL.createObjectURL(b);
    img.onload=()=>{ const cv=document.createElement('canvas'); const S=760; cv.width=S; cv.height=S; const cx=cv.getContext('2d');
      cx.fillStyle='#fff'; cx.fillRect(0,0,S,S); cx.drawImage(img,0,0,S,S); URL.revokeObjectURL(u);
      cv.toBlob((bl)=>resolve(bl),'image/png'); };
    img.onerror=()=>{ URL.revokeObjectURL(u); resolve(null); }; img.src=u; });
  $('#qr-dl',ov).onclick=async()=>{ const b=await toPng(); if(!b){ toast('Could not create image.'); return; }
    const a=document.createElement('a'); a.href=URL.createObjectURL(b); a.download=fname; a.click(); setTimeout(()=>URL.revokeObjectURL(a.href),1200); toast('QR downloaded ✓'); };
  $('#qr-share',ov).onclick=async()=>{ const b=await toPng();
    try{
      if(b && navigator.canShare){ const file=new File([b],fname,{type:'image/png'});
        if(navigator.canShare({files:[file]})){ await navigator.share({files:[file],title:'LinkPoll QR code',text:url}); return; } }
      if(navigator.share){ await navigator.share({title:'LinkPoll',text:'Scan or open my links:',url}); return; }
      await navigator.clipboard.writeText(url); toast('Link copied — paste it anywhere!');
    }catch(e){}
  };
}

function starRow(rating, cls){ rating=Math.round((+rating||0)*2)/2; let h='';
  for(let i=1;i<=5;i++){ const on=rating>=i, half=!on&&rating>=i-0.5; h+=`<span class="star ${on?'on':half?'half':''}">★</span>`; }
  return `<span class="stars ${cls||''}">${h}</span>`;
}
function rvDate(ts){ try{ return new Date((+ts||0)*1000).toLocaleDateString(undefined,{year:'numeric',month:'short',day:'numeric'}); }catch(e){ return ''; } }
async function openReviewsHub(username, name){
  lockScroll(); const ov=$('#overlay');
  const origin=location.origin;
  const badgeUrl=origin+'/api.php?path=/reviews/badge&username='+encodeURIComponent(username);
  const cardUrl=origin+'/b/'+username;
  const embed=`<a href="${cardUrl}" target="_blank" rel="noopener">\n  <img src="${badgeUrl}" alt="Reviews on LinkPoll" width="252" height="70">\n</a>`;
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim><div class="sheet rvhub-sheet">
    <div class="sheet-head"><h3>${ic('trophy',18)} Reviews &amp; badge</h3><button class="close" data-x>✕</button></div>
    <p class="sheet-sub">Collect star reviews and show them off — on your card and on any website.</p>
    <div class="rvhub-badge"><img src="${badgeUrl}" alt="Reviews badge" width="252" height="70" onerror="this.style.opacity=.3"></div>
    <label class="rvhub-lbl">Embed this badge on your website</label>
    <textarea class="rvhub-code" id="rvhub-code" readonly rows="4" spellcheck="false">${esc(embed)}</textarea>
    <div class="rvhub-acts">
      <button class="btn btn-lime btn-sm" id="rvhub-copy">${ic('link',14)} Copy embed code</button>
      <button class="btn btn-ghost btn-sm" id="rvhub-manage">Manage reviews</button>
    </div>
    <p class="rvhub-hint">Paste this into your website’s HTML (footer, homepage, anywhere). The badge updates itself as reviews come in, and tapping it opens your review page. Works on any site or platform.</p>
    <button class="btn btn-ghost btn-sm rvhub-link" id="rvhub-cardcopy">Copy your “leave a review” link</button>
  </div></div>`;
  $('[data-x]',ov).onclick=closeOverlay; $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget) closeOverlay(); };
  $('#rvhub-copy',ov).onclick=()=>{ navigator.clipboard.writeText(embed).then(()=>toast('Embed code copied!')); };
  $('#rvhub-cardcopy',ov).onclick=()=>{ navigator.clipboard.writeText(cardUrl).then(()=>toast('Review link copied!')); };
  $('#rvhub-manage',ov).onclick=()=>{ openReviews(username, name); };
}
async function openReviews(username, name){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim><div class="sheet rv-sheet">
    <div class="sheet-head"><h3>${ic('trophy',18)} Reviews${name?` · ${esc(name)}`:''}</h3><button class="close" data-x>✕</button></div>
    <div id="rv-body"><div class="rv-load">Loading…</div></div>
  </div></div>`;
  $('[data-x]',ov).onclick=closeOverlay; $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget) closeOverlay(); };
  let d; try{ d=await api('/reviews',{query:{username}}); }catch(e){ $('#rv-body',ov).innerHTML=`<div class="rv-load">${esc(e.message)}</div>`; return; }
  renderReviewsBody(ov, username, name, d);
}
function renderReviewsBody(ov, username, name, d){
  const s=d.summary||{avg:0,count:0,dist:[0,0,0,0,0]}, list=d.reviews||[];
  const dmax=Math.max(1,...s.dist);
  const dist=[5,4,3,2,1].map((n)=>`<div class="rv-drow"><span>${n}</span><span class="rv-dbar"><span style="width:${Math.round((s.dist[n-1]/dmax)*100)}%"></span></span><b>${s.dist[n-1]}</b></div>`).join('');
  const items = list.length ? list.map((r)=>`<div class="rv-item ${r.hidden?'is-hidden':''}">
      <div class="rv-ihead">${avatar({name:r.name,username:r.name},'s34')}<div class="rv-iwho"><b>${esc(r.name)}</b>${starRow(r.rating,'sm')}</div><span class="rv-idate">${rvDate(r.at)}</span></div>
      ${r.title?`<div class="rv-ititle">${esc(r.title)}</div>`:''}
      ${r.body?`<p class="rv-ibody">${esc(r.body)}</p>`:''}
      ${d.isOwner?`<div class="rv-imod"><button class="rv-modbtn" data-mod="${r.hidden?'show':'hide'}" data-id="${r.id}">${r.hidden?'Show':'Hide'}</button><button class="rv-modbtn danger" data-mod="delete" data-id="${r.id}">Delete</button></div>`:''}
    </div>`).join('') : `<div class="rv-empty">No reviews yet.${d.isOwner?' Share your card and ask for feedback!':' Be the first to leave one!'}</div>`;
  $('#rv-body',ov).innerHTML=`
    <div class="rv-summary">
      <div class="rv-big"><span class="rv-avg">${s.count?s.avg:'—'}</span>${starRow(s.avg,'lg')}<span class="rv-cnt">${s.count} review${s.count===1?'':'s'}</span></div>
      <div class="rv-dist">${dist}</div>
    </div>
    ${!d.isOwner?`<button class="btn btn-lime rv-write" data-write>${ic('sparkle',15)} Write a review</button>`:''}
    <div class="rv-list">${items}</div>`;
  const w=$('[data-write]',ov); if(w) w.onclick=()=>openReviewForm(username,name,()=>openReviews(username,name));
  $$('[data-mod]',ov).forEach((b)=>b.onclick=async()=>{ const id=+b.dataset.id, action=b.dataset.mod;
    if(action==='delete' && !confirm('Delete this review permanently?')) return;
    try{ await api('/reviews/moderate',{method:'POST',body:{id,action}}); const nd=await api('/reviews',{query:{username}}); renderReviewsBody(ov,username,name,nd); }catch(e){ toast(e.message); } });
}
async function renderReviewsPage(username){
  username=(username||'').toLowerCase();
  route='reviews'; chrome();
  setMain(`<div class="rvpage"><div class="rvp-load">Loading reviews…</div></div>`);
  let d; try{ d=await api('/reviews',{query:{username}}); }
  catch(e){ setMain(`<div class="rvpage"><div class="rvp-nf"><div class="rvp-nf-em">🔍</div><h2>Page not found</h2><p>This reviews page doesn’t exist.</p><a class="btn btn-lime" href="#/">Go to LinkPoll</a></div></div>`); return; }
  drawReviewsPage(username, d);
}
function drawReviewsPage(username, d){
  const u=d.user||{username}, s=d.summary||{avg:0,count:0,dist:[0,0,0,0,0]}, list=d.reviews||[];
  const dmax=Math.max(1,...s.dist);
  const dist=[5,4,3,2,1].map((n)=>`<div class="rv-drow"><span>${n}</span><span class="rv-dbar"><span style="width:${Math.round((s.dist[n-1]/dmax)*100)}%"></span></span><b>${s.dist[n-1]}</b></div>`).join('');
  const items = list.length ? list.map((r)=>`<div class="rv-item ${r.hidden?'is-hidden':''}">
      <div class="rv-ihead">${avatar({name:r.name,username:r.name},'s34')}<div class="rv-iwho"><b>${esc(r.name)}</b>${starRow(r.rating,'sm')}</div><span class="rv-idate">${rvDate(r.at)}</span></div>
      ${r.title?`<div class="rv-ititle">${esc(r.title)}</div>`:''}${r.body?`<p class="rv-ibody">${esc(r.body)}</p>`:''}
      ${d.isOwner?`<div class="rv-imod"><button class="rv-modbtn" data-mod="${r.hidden?'show':'hide'}" data-id="${r.id}">${r.hidden?'Show':'Hide'}</button><button class="rv-modbtn danger" data-mod="delete" data-id="${r.id}">Delete</button></div>`:''}
    </div>`).join('') : `<div class="rv-empty">No reviews yet. ${d.isOwner?'Share your link below to collect feedback.':'Be the first to leave feedback!'}</div>`;
  const leftCard = d.isOwner
    ? `<div class="rvp-card">
        <h3>${ic('share',18)} Collect reviews</h3>
        <p class="rvp-sub">Share this link with your customers or followers to gather feedback.</p>
        <div class="rvp-linkbar"><input readonly id="rvp-link" value="${esc(location.host)}/r/${esc(u.username)}"><button class="btn btn-sm btn-lime" data-copyrv>Copy</button></div>
        <div class="rvp-owneracts"><button class="btn btn-sm btn-ghost" data-sharerv>${ic('share',14)} Share</button><a class="btn btn-sm btn-ghost" href="#/links">Manage & embed →</a></div>
        <p class="rvp-tip">Reviews appear here and as a ★ badge on your card. You can hide or delete any review below.</p>
      </div>`
    : `<div class="rvp-card">
        <h3>Leave your feedback</h3>
        <p class="rvp-sub">Tell others about your experience with ${esc(u.name||('@'+u.username))}.</p>
        <div class="rvf">
          <div class="rvf-stars" id="rvp-stars">${[1,2,3,4,5].map((i)=>`<button class="rvf-star" data-star="${i}">★</button>`).join('')}</div>
          <input id="rvp-name" placeholder="Your name" maxlength="80" value="${esc((ME&&ME.name)||'')}">
          <input id="rvp-title" placeholder="Headline (optional)" maxlength="120">
          <textarea id="rvp-body" placeholder="Share your experience…" maxlength="1500" rows="4"></textarea>
          <button class="btn btn-lime" id="rvp-submit">Post review</button>
          <p class="rvf-note">Your review will be public on this page and their card.</p>
        </div>
      </div>`;
  setMain(`<div class="rvpage">
    <div class="rvp-head">
      <div class="rvp-ava" style="background:${esc(u.color||'#15140f')}"><span class="rvp-ava-i">${esc(initials(u.name||u.username))}</span>${u.avatar?`<img class="rvp-ava-img" src="${esc(u.avatar)}" onload="this.classList.add('on')" onerror="this.remove()">`:''}</div>
      <h1 class="rvp-name">${esc(u.name||('@'+u.username))}${vbadge(u.verified)}</h1>
      <div class="rvp-handle"><a href="#/b/${esc(u.username)}">${esc(location.host)}/b/${esc(u.username)}</a></div>
      <div class="rvp-rate">${starRow(s.avg,'lg')}<span class="rvp-avg">${s.count?s.avg:'—'}</span><span class="rvp-cnt">· ${s.count} review${s.count===1?'':'s'}</span></div>
    </div>
    <div class="rvp-grid">
      ${leftCard}
      <div class="rvp-reviews">
        <div class="rvp-distcard"><div class="rv-big"><span class="rv-avg">${s.count?s.avg:'—'}</span>${starRow(s.avg,'lg')}<span class="rv-cnt">${s.count} review${s.count===1?'':'s'}</span></div><div class="rv-dist">${dist}</div></div>
        <div class="rv-list">${items}</div>
      </div>
    </div>
    <div class="rvp-foot"><a href="#/">✦ Powered by LinkPoll</a></div>
  </div>`);
  if(d.isOwner){
    const cp=$('[data-copyrv]'); if(cp) cp.onclick=()=>navigator.clipboard.writeText(location.origin+'/r/'+u.username).then(()=>toast('Review link copied!'));
    const sh=$('[data-sharerv]'); if(sh) sh.onclick=async()=>{ const url=location.origin+'/r/'+u.username;
      if(navigator.share){ try{ await navigator.share({title:'Leave a review',text:'I’d love your feedback:',url}); }catch(e){} } else { navigator.clipboard.writeText(url).then(()=>toast('Link copied!')); } };
  } else {
    let rating=0;
    $$('[data-star]').forEach((b)=>{ b.onclick=()=>{ rating=+b.dataset.star; $$('[data-star]').forEach((x)=>x.classList.toggle('on',+x.dataset.star<=rating)); };
      b.onmouseenter=()=>$$('[data-star]').forEach((x)=>x.classList.toggle('hov',+x.dataset.star<=+b.dataset.star));
      b.onmouseleave=()=>$$('[data-star]').forEach((x)=>x.classList.remove('hov')); });
    const sub=$('#rvp-submit'); if(sub) sub.onclick=async()=>{
      if(!rating){ toast('Please pick a star rating.'); return; }
      const payload={username, rating, name:$('#rvp-name').value.trim(), title:$('#rvp-title').value.trim(), body:$('#rvp-body').value.trim()};
      if(!payload.body && !payload.title){ toast('Please write a short review.'); return; }
      try{ await api('/reviews',{method:'POST',body:payload}); toast('Thanks for your review! ★'); const nd=await api('/reviews',{query:{username}}); drawReviewsPage(username, nd); window.scrollTo(0,0); }
      catch(e){ toast(e.message); } };
  }
  $$('[data-mod]').forEach((b)=>b.onclick=async()=>{ const id=+b.dataset.id, action=b.dataset.mod;
    if(action==='delete' && !confirm('Delete this review permanently?')) return;
    try{ await api('/reviews/moderate',{method:'POST',body:{id,action}}); const nd=await api('/reviews',{query:{username}}); drawReviewsPage(username, nd); }catch(e){ toast(e.message); } });
}
let RVSEC='reviews', RVDATA=null;
function renderReviewsSide(){
  const u=ME?esc(ME.username||''):'';
  $('#side').innerHTML = `
    <div class="brand"><img class="logo" src="logo-icon.png" alt="LinkPoll" /><b>LinkPoll</b></div>
    <a class="lm-snav lm-snav-back" href="#/">‹<span>Back to LinkPoll</span></a>
    <div class="lm-snav-title">${ic('trophy',15)} Feedback</div>
    <nav class="nav lm-sidenav">
      <button class="lm-snav" data-rvside="reviews">${ic('paper')}<span>All reviews</span></button>
      <button class="lm-snav" data-rvside="collect">${ic('share')}<span>Collect reviews</span></button>
      <button class="lm-snav" data-rvside="embed">${ic('sparkle')}<span>Website badge</span></button>
      <a class="lm-snav" href="#/r/${u}">${ic('compass')}<span>Public page</span></a>
    </nav>
    ${ME?`<div class="userchip" data-usermenu data-keepmenu>${avatar(ME,'s34')}
      <div class="who"><b>${esc(ME.name)}</b><span>@${esc(ME.username)}</span></div><span class="dots">⋯</span></div>`:''}`;
  $$('[data-rvside]').forEach((b)=>b.onclick=()=>drawReviewsSection(b.dataset.rvside));
  const act=$(`[data-rvside="${RVSEC}"]`); if(act) act.classList.add('active');
}
async function renderReviewsManagePage(){
  if(!ME){ openAuth(); return; }
  const username=ME.username||'';
  RVSEC='reviews'; route='myreviews'; chrome();
  setMain(`<div class="lm2 rvm2">
    <div class="lm2-left">
      <div class="lm2-top"><button class="lm-back" data-back title="Back">‹</button><h1 class="lm-title" id="rvm-title">${ic('trophy',20)} Feedback &amp; Reviews</h1></div>
      <p class="lm-intro" id="rvm-intro">Read and moderate what people say about you.</p>
      <div id="rvm-root"><div class="le-empty">Loading…</div></div>
    </div>
    <div class="lm2-right">
      <div class="rvm-pvwrap">
        <div class="rvm-pv-label">Your website badge</div>
        <div class="rvm-badge-frame"><iframe id="rvm-badge" title="Reviews badge" src="${location.origin}/embed.php?type=reviews&u=${esc(username)}"></iframe></div>
        <a class="btn btn-ghost btn-sm" href="#/r/${esc(username)}">Open public review page ↗</a>
      </div>
    </div>
  </div>`);
  const back=$('[data-back]'); if(back) back.onclick=()=>{ if(history.length>1) history.back(); else go('#/'); };
  RVDATA=null;
  try{ RVDATA=await api('/reviews',{query:{username}}); }catch(e){ $('#rvm-root').innerHTML=`<div class="le-empty">${esc(e.message)}</div>`; return; }
  drawReviewsSection('reviews');
}
function drawReviewsSection(sec){
  RVSEC=sec;
  $$('[data-rvside]').forEach((b)=>b.classList.toggle('active', b.dataset.rvside===sec));
  const root=$('#rvm-root'); if(!root || !RVDATA) return;
  const username=ME.username||'', s=RVDATA.summary||{avg:0,count:0,dist:[0,0,0,0,0]}, list=RVDATA.reviews||[];
  const intro=$('#rvm-intro'), title=$('#rvm-title');
  const src=location.origin+'/embed.php?type=reviews&u='+encodeURIComponent(username);
  if(sec==='reviews'){
    if(title) title.innerHTML=`${ic('trophy',20)} All reviews`;
    if(intro) intro.textContent='Read and moderate what people say about you.';
    const dmax=Math.max(1,...s.dist);
    const dist=[5,4,3,2,1].map((n)=>`<div class="rv-drow"><span>${n}</span><span class="rv-dbar"><span style="width:${Math.round((s.dist[n-1]/dmax)*100)}%"></span></span><b>${s.dist[n-1]}</b></div>`).join('');
    const items = list.length ? list.map((r)=>`<div class="rv-item ${r.hidden?'is-hidden':''}">
        <div class="rv-ihead">${avatar({name:r.name,username:r.name},'s34')}<div class="rv-iwho"><b>${esc(r.name)}</b>${starRow(r.rating,'sm')}</div><span class="rv-idate">${rvDate(r.at)}</span></div>
        ${r.title?`<div class="rv-ititle">${esc(r.title)}</div>`:''}${r.body?`<p class="rv-ibody">${esc(r.body)}</p>`:''}
        <div class="rv-imod"><button class="rv-modbtn" data-mod="${r.hidden?'show':'hide'}" data-id="${r.id}">${r.hidden?'Show':'Hide'}</button><button class="rv-modbtn danger" data-mod="delete" data-id="${r.id}">Delete</button></div>
      </div>`).join('') : `<div class="le-empty">No reviews yet. Use “Collect reviews” to share your feedback link.</div>`;
    root.innerHTML=`<div class="rvp-distcard"><div class="rv-big"><span class="rv-avg">${s.count?s.avg:'—'}</span>${starRow(s.avg,'lg')}<span class="rv-cnt">${s.count} review${s.count===1?'':'s'}</span></div><div class="rv-dist">${dist}</div></div>
      <div class="rv-list">${items}</div>`;
    $$('[data-mod]',root).forEach((b)=>b.onclick=async()=>{ const id=+b.dataset.id, action=b.dataset.mod;
      if(action==='delete' && !confirm('Delete this review permanently?')) return;
      try{ await api('/reviews/moderate',{method:'POST',body:{id,action}}); RVDATA=await api('/reviews',{query:{username}}); drawReviewsSection('reviews'); refreshRvBadge(); }catch(e){ toast(e.message); } });
  } else if(sec==='collect'){
    if(title) title.innerHTML=`${ic('share',20)} Collect reviews`;
    if(intro) intro.textContent='Share this link with customers or followers to gather feedback.';
    root.innerHTML=`<div class="rvp-card">
      <h3>Your review link</h3>
      <p class="rvp-sub">Anyone with this link can leave you a star rating and feedback — no account needed.</p>
      <div class="rvp-linkbar"><input readonly id="rvm-link" value="${esc(location.host)}/r/${esc(username)}"><button class="btn btn-sm btn-lime" data-rvcopy>Copy</button></div>
      <div class="rvp-owneracts"><button class="btn btn-sm btn-ghost" data-rvshare>${ic('share',14)} Share link</button><button class="btn btn-sm btn-ghost" data-rvqr>${ic('link',14)} QR code</button><a class="btn btn-sm btn-ghost" href="#/r/${esc(username)}">Preview ↗</a></div>
      <p class="rvp-tip">Tip: put this link in your emails, receipts, or after a sale to steadily collect reviews. New reviews show on your card and in the website badge automatically.</p>
    </div>`;
    const cp=$('[data-rvcopy]',root); if(cp) cp.onclick=()=>navigator.clipboard.writeText(location.origin+'/r/'+username).then(()=>toast('Review link copied!'));
    const sh=$('[data-rvshare]',root); if(sh) sh.onclick=async()=>{ const url=location.origin+'/r/'+username;
      if(navigator.share){ try{ await navigator.share({title:'Leave a review',text:'I’d love your feedback:',url}); }catch(e){} } else { navigator.clipboard.writeText(url).then(()=>toast('Link copied!')); } };
    const qr=$('[data-rvqr]',root); if(qr) qr.onclick=()=>qrBio(username, ME.name);
  } else if(sec==='embed'){
    if(title) title.innerHTML=`${ic('sparkle',20)} Website badge`;
    if(intro) intro.textContent='Show your live LinkPoll rating on any external website — like a Clutch or Google badge.';
    const embed=`<iframe src="${src}" width="330" height="128" style="border:0;overflow:hidden" frameborder="0" scrolling="no" title="LinkPoll reviews"></iframe>`;
    root.innerHTML=`<div class="rvp-card">
      <h3>Embed your rating</h3>
      <p class="rvp-sub">Paste this HTML anywhere on your site. It updates automatically as new reviews come in.</p>
      <textarea class="rv-embed" id="rvm-embed" readonly rows="3">${esc(embed)}</textarea>
      <div class="rv-embed-acts"><button class="btn btn-sm btn-lime" data-embcopy>Copy embed code</button><a class="btn btn-sm btn-ghost" href="${src}" target="_blank">Open badge ↗</a></div>
      <p class="rvp-tip">The badge links back to your public review page, so visitors can read reviews and add their own.</p>
    </div>`;
    const ec=$('[data-embcopy]',root); if(ec) ec.onclick=()=>navigator.clipboard.writeText($('#rvm-embed',root).value).then(()=>toast('Embed code copied!'));
  }
}
function refreshRvBadge(){ const f=$('#rvm-badge'); if(f) f.src=f.src.split('#')[0]+'#'+Date.now(); }
async function renderManagerReviews(root, username){
  const box=$('#le-reviews',root); if(!box) return;
  let d; try{ d=await api('/reviews',{query:{username}}); }catch(e){ box.innerHTML=`<div class="le-empty">${esc(e.message)}</div>`; return; }
  const s=d.summary||{avg:0,count:0}; const src=location.origin+'/embed.php?type=reviews&u='+encodeURIComponent(username);
  const embed=`<iframe src="${src}" width="330" height="128" style="border:0;overflow:hidden" frameborder="0" scrolling="no" title="LinkPoll reviews"></iframe>`;
  const items = (d.reviews||[]).length ? d.reviews.map((r)=>`<div class="rv-item ${r.hidden?'is-hidden':''}">
      <div class="rv-ihead">${avatar({name:r.name,username:r.name},'s34')}<div class="rv-iwho"><b>${esc(r.name)}</b>${starRow(r.rating,'sm')}</div><span class="rv-idate">${rvDate(r.at)}</span></div>
      ${r.title?`<div class="rv-ititle">${esc(r.title)}</div>`:''}${r.body?`<p class="rv-ibody">${esc(r.body)}</p>`:''}
      <div class="rv-imod"><button class="rv-modbtn" data-mod="${r.hidden?'show':'hide'}" data-id="${r.id}">${r.hidden?'Show':'Hide'}</button><button class="rv-modbtn danger" data-mod="delete" data-id="${r.id}">Delete</button></div>
    </div>`).join('') : `<div class="le-empty">No reviews yet. Share your card link and ask customers for feedback.</div>`;
  box.innerHTML=`
    <div class="rv-summary sm"><div class="rv-big"><span class="rv-avg">${s.count?s.avg:'—'}</span>${starRow(s.avg,'lg')}<span class="rv-cnt">${s.count} review${s.count===1?'':'s'}</span></div></div>
    <p class="le-lbl">Your review link</p>
    <p class="sheet-sub">Share this so customers can leave feedback about you.</p>
    <div class="rvp-linkbar"><input readonly id="rv-rlink" value="${esc(location.host)}/r/${esc(username)}"><button class="btn btn-sm btn-lime" id="rv-rlink-copy">Copy</button><a class="btn btn-sm btn-ghost" href="#/r/${esc(username)}">Open ↗</a></div>
    <p class="le-lbl">Show your rating on any website</p>
    <p class="sheet-sub">Paste this on your site to display a LinkPoll rating badge — like a Clutch or Google reviews widget.</p>
    <textarea class="rv-embed" id="rv-embed" readonly rows="3">${esc(embed)}</textarea>
    <div class="rv-embed-acts"><button class="btn btn-sm btn-lime" id="rv-embed-copy">Copy embed code</button><a class="btn btn-sm btn-ghost" href="${src}" target="_blank">Preview badge ↗</a></div>
    <p class="le-lbl">All reviews (${(d.reviews||[]).length})</p>
    <div class="rv-list">${items}</div>`;
  const rc=$('#rv-rlink-copy',root); if(rc) rc.onclick=()=>{ navigator.clipboard.writeText(location.origin+'/r/'+username).then(()=>toast('Review link copied!')); };
  const ec=$('#rv-embed-copy',root); if(ec) ec.onclick=()=>{ navigator.clipboard.writeText($('#rv-embed',root).value).then(()=>toast('Embed code copied!')); };
  $$('[data-mod]',box).forEach((b)=>b.onclick=async()=>{ const id=+b.dataset.id, action=b.dataset.mod;
    if(action==='delete' && !confirm('Delete this review permanently?')) return;
    try{ await api('/reviews/moderate',{method:'POST',body:{id,action}}); renderManagerReviews(root, username); }catch(e){ toast(e.message); } });
}
function openReviewForm(username, name, onDone){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim><div class="sheet rv-sheet">
    <div class="sheet-head"><h3>Write a review${name?` · ${esc(name)}`:''}</h3><button class="close" data-x>✕</button></div>
    <div class="rvf">
      <div class="rvf-stars" id="rvf-stars">${[1,2,3,4,5].map((i)=>`<button class="rvf-star" data-star="${i}">★</button>`).join('')}</div>
      <input id="rvf-name" placeholder="Your name" maxlength="80" value="${esc((ME&&ME.name)||'')}">
      <input id="rvf-title" placeholder="Headline (optional)" maxlength="120">
      <textarea id="rvf-body" placeholder="Share your experience…" maxlength="1500" rows="4"></textarea>
      <button class="btn btn-lime" id="rvf-submit">Post review</button>
      <p class="rvf-note">Your review will be public on this card.</p>
    </div>
  </div></div>`;
  $('[data-x]',ov).onclick=closeOverlay; $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget) closeOverlay(); };
  let rating=0;
  $$('[data-star]',ov).forEach((b)=>{ b.onclick=()=>{ rating=+b.dataset.star; $$('[data-star]',ov).forEach((x)=>x.classList.toggle('on',+x.dataset.star<=rating)); };
    b.onmouseenter=()=>$$('[data-star]',ov).forEach((x)=>x.classList.toggle('hov',+x.dataset.star<=+b.dataset.star));
    b.onmouseleave=()=>$$('[data-star]',ov).forEach((x)=>x.classList.remove('hov')); });
  $('#rvf-submit',ov).onclick=async()=>{
    if(!rating){ toast('Please pick a star rating.'); return; }
    const payload={username, rating, name:$('#rvf-name',ov).value.trim(), title:$('#rvf-title',ov).value.trim(), body:$('#rvf-body',ov).value.trim()};
    if(!payload.body && !payload.title){ toast('Please write a short review.'); return; }
    try{ await api('/reviews',{method:'POST',body:payload}); toast('Thanks for your review! ★'); if(onDone) onDone(); else closeOverlay(); }
    catch(e){ toast(e.message); }
  };
}

async function renderBioCard(code){
  route='biocard'; chrome();
  setMain(`<div class="biocard-screen bc-plain"><div class="bc-load">Loading…</div></div>`);
  let d;
  try{ d=await api('/bio',{query:{code}}); }
  catch(e){
    setMain(`<div class="biocard-screen bc-plain"><div class="biocard"><div class="bc-notfound">
      <div class="bc-nf-emoji">🔗</div><h2>Card not found</h2>
      <p>This link-in-bio doesn’t exist or was removed.</p>
      <a class="btn btn-lime" href="#/">Go to LinkPoll</a>
      <a class="btn btn-ghost" href="${ME?'#/settings':'#/'}" data-mkcta>Create your own</a>
    </div></div></div>`);
    const c=$('[data-mkcta]'); if(c&&!ME) c.onclick=(e)=>{ e.preventDefault(); openAuth(); };
    return;
  }
  const u=d.user, links=d.links||[], isMe=d.isMe;
  const t=CARD_THEMES[u.theme]||CARD_THEMES.aurora; const rad=cardBtnRadius(u.btn);
  const font=CARD_FONTS[u.font]||CARD_FONTS.sans; ensureCardFont(u.font);
  try{ document.body.style.background=t.bg; }catch(e){}
  const vars=`--cfg:${t.fg};--csoft:${t.soft};--cbtn:${t.btn};--cbtnfg:${t.btnfg};--cbd:${t.bd};--crad:${rad};--cfont:${font.css}`;
  const itemHTML=(l)=>{
    if(l.type==='image'){
      return `<figure class="bc-photo" data-photo="${esc(l.url)}" data-cap="${esc(l.title)}"><img src="${esc(l.url)}" alt="${esc(l.title)}" loading="lazy">${l.title&&l.title!=='Photo'?`<figcaption>${esc(l.title)}</figcaption>`:''}</figure>`;
    }
    if(l.type==='file'){
      return `<a class="bc-file" href="${esc(l.url)}" target="_blank" rel="noopener" data-lclick="${l.id}" download><span class="bc-file-ic">${docIcon(l.subtitle)}</span><span class="bc-file-body"><b>${esc(l.title)}</b><small>${esc(l.subtitle||'FILE')} · tap to open</small></span><span class="bc-go">↓</span></a>`;
    }
    const b=!l.icon?brandFor(l.url):null;
    const iconHTML = l.icon ? `<span class="bc-ic">${esc(l.icon)}</span>` : (b?`<span class="bc-ic">${b.svg}</span>`:`<span class="bc-ic">${ic('link',18)}</span>`);
    const host=linkHost(l.url); const showSub = host && host.toLowerCase()!==esc(l.title).toLowerCase();
    return `<a class="bc-link" href="${esc(l.url)}" target="_blank" rel="noopener nofollow" data-lclick="${l.id}">${iconHTML}<span class="bc-link-body"><span class="bc-t">${esc(l.title)}</span>${showSub?`<span class="bc-sub">${esc(host)}</span>`:''}</span><span class="bc-go">↗</span></a>`;
  };
  setMain(`<div class="biocard-screen" style="background:${t.bg};background-attachment:fixed;${vars}">
    <a class="bc-home" href="#/">${ic('link',15)} LinkPoll</a>
    <div class="bc-top-acts">
      <button class="bc-top-btn" data-bio-qr title="Get QR code"><svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm10-2h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm2 2h2v2h-2v-2zm2-2h2v2h-2v-2z"/></svg></button>
      <button class="bc-top-btn" data-bio-share="${esc(u.username)}" title="Share this card">${ic('share',16)}</button>
    </div>
    <div class="biocard">
      <div class="bc-ava" style="background:${esc(u.color||'#15140f')}"><span class="bc-ava-i">${esc(initials(u.name||u.username))}</span>${u.avatar?`<img class="bc-ava-img" src="${esc(u.avatar)}" alt="" onload="this.classList.add('on')" onerror="this.remove()">`:''}</div>
      <h1 class="bc-name">${esc(u.name)}${vbadge(u.verified)}</h1>
      <div class="bc-handle"><a href="#/u/${esc(u.username)}">@${esc(u.username)}</a></div>
      ${(d.reviews&&d.reviews.count>0)?`<button class="bc-rvbadge" data-reviews>${starRow(d.reviews.avg)}<b>${d.reviews.avg}</b><span>· ${d.reviews.count} review${d.reviews.count===1?'':'s'}</span></button>`:''}
      ${u.bio?`<p class="bc-bio">${esc(u.bio)}</p>`:''}
      <div class="bc-links">${links.map(itemHTML).join('')||`<p class="bc-empty">No links added yet.</p>`}</div>
      <div class="bc-rvcta">${!isMe?`<button class="bc-rvleave" data-leave-review>★ Leave a review</button>`:(d.reviews&&d.reviews.count>0?`<button class="bc-rvleave ghost" data-reviews>See all reviews →</button>`:'')}</div>
      ${isMe?`<div class="bc-owner">
        <button class="btn btn-sm bc-owner-btn" data-bio-qr>${ic('link',14)} QR</button>
        <button class="btn btn-sm bc-owner-btn" data-bio-copy="${esc(u.username)}">Copy</button>
        <button class="btn btn-sm bc-owner-btn" data-bio-share="${esc(u.username)}">${ic('share',13)} Share</button>
        <a class="btn btn-sm bc-owner-btn" href="#/u/${esc(u.username)}">Edit</a>
      </div>`:''}
    </div>
    ${!isMe?`<div class="bc-cta">
      <div class="bc-cta-inner">
        <div class="bc-cta-txt"><b>Get your own link in bio — free</b><span>One short link for all your socials, docs and more.</span></div>
        <button class="btn btn-lime bc-cta-btn" data-bio-make>Create yours →</button>
      </div>
    </div>`:''}
    <a class="bc-made" href="#/"><span class="bc-made-mark">✦</span><span>Make your own <b>LinkPoll</b> bio</span></a>
  </div>`);
  $$('[data-lclick]').forEach((a)=>a.addEventListener('click',()=>{ const id=a.dataset.lclick;
    try{ const body=JSON.stringify({id:+id}); if(navigator.sendBeacon){ navigator.sendBeacon('/api.php?path=/links/click', new Blob([body],{type:'application/json'})); } else { api('/links/click',{method:'POST',body:{id:+id}}); } }catch(e){} }));
  $$('[data-photo]').forEach((f)=>f.onclick=()=>{ try{ openPhotoViewer(f.dataset.photo, f.dataset.cap||''); }catch(e){} });
  const cp=$('[data-bio-copy]'); if(cp) cp.onclick=()=>{ navigator.clipboard.writeText(location.origin+'/b/'+cp.dataset.bioCopy).then(()=>toast('Link copied!')); };
  $$('[data-bio-qr]').forEach((qb)=>qb.onclick=()=>qrBio(u.username, u.name));
  $$('[data-bio-share]').forEach((sb)=>sb.onclick=()=>shareBio(u.username, u.name));
  const mk=$('[data-bio-make]'); if(mk) mk.onclick=()=>{ if(ME){ go('#/u/'+encodeURIComponent(ME.username||'')); } else { openAuth(); } };
  $$('[data-reviews]').forEach((b)=>b.onclick=()=>go('#/r/'+encodeURIComponent(u.username)));
  $$('[data-leave-review]').forEach((b)=>b.onclick=()=>go('#/r/'+encodeURIComponent(u.username)));
}

const LINK_MANAGER_HTML=`
    <div class="le-tabs"><button class="le-tab active" data-letab="content">Content</button><button class="le-tab" data-letab="design">Design</button><button class="le-tab" data-letab="insights">Insights</button></div>
    <div id="le-pane-content" class="le-pane">
      <p class="sheet-sub">Add links, photos or documents. Drag to reorder, toggle to show/hide.</p>
      <div class="le-add">
        <input id="le-title" placeholder="Title (e.g. My YouTube)" maxlength="120">
        <input id="le-url" placeholder="https://…">
        <button class="btn btn-lime btn-sm" id="le-addbtn">${ic('plus',15)} Link</button>
      </div>
      <div class="le-media">
        <button class="le-mbtn" id="le-photo">📷 Add photo</button>
        <button class="le-mbtn" id="le-file">📎 Add document</button>
      </div>
      <div id="le-list" class="le-list"></div>
    </div>
    <div id="le-pane-design" class="le-pane" style="display:none">
      <p class="sheet-sub">Make the card yours — theme, buttons and font. Changes save automatically.</p>
      <p class="le-lbl" style="margin-top:2px">Theme</p>
      <div class="le-themes" id="le-themes"></div>
      <p class="le-lbl">Button shape</p>
      <div class="le-btns" id="le-btns"></div>
      <p class="le-lbl">Font</p>
      <div class="le-fonts" id="le-fonts"></div>
      <a class="btn btn-ghost btn-sm le-viewcard" id="le-view" target="_blank">Open my card ↗</a>
    </div>
    <div id="le-pane-insights" class="le-pane" style="display:none">
      <div id="le-insights"><div class="le-empty">Loading insights…</div></div>
    </div>
`;

async function openLinksEditor(username, mountEl, opts){
  opts=opts||{};
  const page=!!mountEl; let root;
  if(page){ root=mountEl; root.innerHTML=LINK_MANAGER_HTML; }
  else {
    lockScroll(); const ov=$('#overlay');
    ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim><div class="sheet links-sheet">
      <div class="sheet-head"><h3>${ic('link',18)} My link-in-bio</h3><button class="close" data-x>✕</button></div>
      ${LINK_MANAGER_HTML}
    </div></div>`;
    root=$('.links-sheet',ov);
    const done=()=>{ closeOverlay(); renderProfile(username); };
    $('[data-x]',ov).onclick=done;
    $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget) done(); };
  }
  let LE=[], THEME='aurora', BTN='rounded', FONT='sans', CODE='';
  const fireChange=()=>{ try{ opts.onChange&&opts.onChange({links:LE,theme:THEME,btn:BTN,font:FONT}); }catch(e){} };
  let insLoaded=false, rvLoaded=false;
  $$('[data-letab]',root).forEach((b)=>b.onclick=()=>{ const k=b.dataset.letab;
    $$('[data-letab]',root).forEach((x)=>x.classList.toggle('active',x===b));
    $('#le-pane-content',root).style.display=k==='content'?'':'none';
    $('#le-pane-design',root).style.display=k==='design'?'':'none';
    $('#le-pane-insights',root).style.display=k==='insights'?'':'none';
    if(k==='insights' && !insLoaded){ insLoaded=true; renderInsights(); }
  });
  async function renderInsights(){
    const box=$('#le-insights',root); if(!box) return;
    let s; try{ s=await api('/links/stats'); }catch(e){ box.innerHTML=`<div class="le-empty">${esc(e.message)}</div>`; return; }
    const maxV=Math.max(1,...s.daily.map((d)=>d.views), ...s.daily.map((d)=>d.clicks));
    const maxC=Math.max(1,...s.links.map((l)=>l.clicks));
    const bars=s.daily.map((d)=>`<div class="ins-bar" title="${d.label} ${d.day}: ${d.views} views, ${d.clicks} clicks">
      <div class="ins-bcol"><span class="ins-bv" style="height:${Math.round((d.views/maxV)*100)}%"></span><span class="ins-bc" style="height:${Math.round((d.clicks/maxV)*100)}%"></span></div>
      <span class="ins-blbl">${d.day}</span></div>`).join('');
    const linkRows = s.links.length ? s.links.map((l)=>`<div class="ins-lrow"><span class="ins-lt">${l.type==='image'?'🖼️':(l.type==='file'?'📄':'🔗')} ${esc(l.title)}</span>
      <span class="ins-lbar"><span style="width:${Math.round((l.clicks/maxC)*100)}%"></span></span><b class="ins-lc">${fmt(l.clicks)}</b></div>`).join('')
      : `<div class="le-empty">No clicks yet — share your link to start seeing data.</div>`;
    box.innerHTML=`
      <div class="ins-cards">
        <div class="ins-card"><span class="ins-n">${fmt(s.views)}</span><span class="ins-l">Card views</span></div>
        <div class="ins-card"><span class="ins-n">${fmt(s.clicks)}</span><span class="ins-l">Link clicks</span></div>
        <div class="ins-card"><span class="ins-n">${s.ctr}%</span><span class="ins-l">Click rate</span></div>
      </div>
      <div class="ins-sec-h">Last 14 days <span class="ins-legend"><i class="lg-v"></i>views <i class="lg-c"></i>clicks</span></div>
      <div class="ins-chart">${bars}</div>
      <div class="ins-sec-h">Top links</div>
      <div class="ins-links">${linkRows}</div>
      <p class="ins-note">Views count visits to your card from other people. Your own visits aren’t counted.</p>`;
  }
  async function load(){
    try{ const r=await api('/links'); LE=r.links||[]; THEME=r.theme||'aurora'; BTN=r.btn||'rounded'; FONT=r.font||'sans'; }catch(e){ toast(e.message); }
    try{ CODE=(ME&&ME.bioCode)||''; }catch(e){}
    if(!CODE){ try{ CODE=(await api('/user',{query:{username}})).profile.bioCode||''; }catch(e){} }
    renderList(); renderDesign(); fireChange();
  }
  function saveDesign(){ api('/card/theme',{method:'POST',body:{theme:THEME,btn:BTN,font:FONT}}); }
  function typeBadge(l){ return l.type==='image'?'🖼️':(l.type==='file'?'📄':'🔗'); }
  function renderList(){
    const box=$('#le-list',root); if(!box) return;
    if(!LE.length){ box.innerHTML=`<div class="le-empty">Nothing yet. Add a link, photo or document above ↑</div>`; return; }
    box.innerHTML=LE.map((l)=>{
      const media=(l.type==='image'||l.type==='file');
      const body = media
        ? `<div class="le-body le-media-body"><input class="le-t" value="${esc(l.title)}" data-f="title" maxlength="120"><span class="le-sub">${l.type==='image'?'Photo':('Document · '+esc(l.subtitle||'FILE'))}</span></div>`
        : `<div class="le-body"><input class="le-t" value="${esc(l.title)}" data-f="title" maxlength="120"><input class="le-u" value="${esc(l.url)}" data-f="url"></div>`;
      return `<div class="le-item" draggable="true" data-id="${l.id}">
        <span class="le-handle">⠿</span><span class="le-type">${typeBadge(l)}</span>
        ${body}
        <span class="le-clicks">${fmt(l.clicks)}</span>
        <button class="switch ${l.active?'on':''}" data-toggle title="Show on card"></button>
        <button class="le-del" data-del title="Delete">🗑</button>
      </div>`; }).join('');
    box.querySelectorAll('.le-item').forEach((el)=>{
      const id=+el.dataset.id;
      el.querySelectorAll('input').forEach((inp)=>inp.addEventListener('change',()=>{
        const l=LE.find((x)=>x.id===id); const f=inp.dataset.f; l[f]=inp.value;
        api('/links/update',{method:'POST',body:{id,[f]:inp.value}}).catch((e)=>toast(e.message)); fireChange(); }));
      el.querySelector('[data-toggle]').onclick=(e)=>{ const l=LE.find((x)=>x.id===id); l.active=l.active?0:1;
        e.currentTarget.classList.toggle('on',!!l.active); api('/links/update',{method:'POST',body:{id,active:l.active}}); fireChange(); };
      el.querySelector('[data-del]').onclick=()=>{ if(!confirm('Delete this item?')) return;
        LE=LE.filter((x)=>x.id!==id); api('/links/delete',{method:'POST',body:{id}}); renderList(); fireChange(); };
      let dragEl=null;
      el.addEventListener('dragstart',()=>{ dragEl=el; el.classList.add('dragging'); });
      el.addEventListener('dragend',()=>{ el.classList.remove('dragging');
        const ids=[...box.querySelectorAll('.le-item')].map((e)=>+e.dataset.id);
        LE.sort((a,b)=>ids.indexOf(a.id)-ids.indexOf(b.id)); api('/links/reorder',{method:'POST',body:{order:ids}}); fireChange(); });
      el.addEventListener('dragover',(e)=>{ e.preventDefault(); const dd=box.querySelector('.dragging'); if(!dd||dd===el) return;
        const b=el.getBoundingClientRect(); const after=(e.clientY-b.top)/b.height>0.5;
        box.insertBefore(dd, after?el.nextSibling:el); });
    });
  }
  function renderDesign(){
    const tw=$('#le-themes',root); if(tw) tw.innerHTML=CARD_THEME_ORDER.map((k)=>`<button class="le-sw ${THEME===k?'sel':''}" data-theme="${k}" style="background:${CARD_THEMES[k].bg}"><span>${CARD_THEME_NAME[k]}</span></button>`).join('');
    const bw=$('#le-btns',root); if(bw) bw.innerHTML=['rounded','pill','sharp'].map((b)=>`<button class="le-bsw ${BTN===b?'sel':''}" data-btn="${b}"><span class="le-bdemo le-b-${b}"></span>${b[0].toUpperCase()+b.slice(1)}</button>`).join('');
    const fw=$('#le-fonts',root); if(fw){ CARD_FONT_ORDER.forEach((k)=>ensureCardFont(k)); fw.innerHTML=CARD_FONT_ORDER.map((k)=>`<button class="le-fsw ${FONT===k?'sel':''}" data-font="${k}" style="font-family:${CARD_FONTS[k].css}">Aa<small>${CARD_FONTS[k].name}</small></button>`).join(''); }
    const v=$('#le-view',root); if(v&&CODE) v.href='#/b/'+CODE;
    $$('[data-theme]',root).forEach((b)=>b.onclick=()=>{ THEME=b.dataset.theme; renderDesign(); saveDesign(); fireChange(); });
    $$('[data-btn]',root).forEach((b)=>b.onclick=()=>{ BTN=b.dataset.btn; renderDesign(); saveDesign(); fireChange(); });
    $$('[data-font]',root).forEach((b)=>b.onclick=()=>{ FONT=b.dataset.font; renderDesign(); saveDesign(); fireChange(); });
  }
  $('#le-addbtn',root).onclick=async()=>{
    const title=$('#le-title',root).value.trim(), url=$('#le-url',root).value.trim();
    if(!title){ toast('Give the link a title.'); return; }
    if(!url){ toast('Add a URL.'); return; }
    try{ const d=await api('/links',{method:'POST',body:{title,url}});
      LE.push({id:d.id,title,url:/^https?:\/\//.test(url)?url:'https://'+url,icon:'',type:'link',active:1,clicks:0});
      $('#le-title',root).value=''; $('#le-url',root).value=''; renderList(); fireChange(); toast('Link added'); }
    catch(e){ toast(e.message); }
  };
  $('#le-url',root).addEventListener('keydown',(e)=>{ if(e.key==='Enter') $('#le-addbtn',root).click(); });
  $('#le-photo',root).onclick=()=>pickMedia('image');
  $('#le-file',root).onclick=()=>pickMedia('file');
  function uploadMedia(type,title,data,onProgress){
    return new Promise((resolve,reject)=>{
      const xhr=new XMLHttpRequest();
      xhr.open('POST','/api.php?path=/links/media');
      xhr.setRequestHeader('Content-Type','application/json');
      xhr.setRequestHeader('X-Requested-With','linkpoll');
      xhr.upload.onprogress=(e)=>{ if(e.lengthComputable&&onProgress) onProgress(e.loaded/e.total); };
      xhr.onload=()=>{ let d={}; try{ d=JSON.parse(xhr.responseText); }catch(e){}
        if(xhr.status>=200&&xhr.status<300) resolve(d); else reject(new Error(d.error||'Upload failed.')); };
      xhr.onerror=()=>reject(new Error('Upload failed — check your connection.'));
      xhr.send(JSON.stringify({type,title,data}));
    });
  }
  function pickMedia(type){
    const inp=document.createElement('input'); inp.type='file';
    inp.accept=type==='image'?'image/*':'.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,application/pdf';
    inp.onchange=()=>{ const f=inp.files&&inp.files[0]; if(!f) return;
      if(f.size>10*1024*1024){ toast('Max file size is 10 MB.'); return; }
      const title=(f.name||'').replace(/\.[^.]+$/,'').slice(0,120)||(type==='image'?'Photo':'Document');
      const prog=showProgress(type==='image'?'Adding photo':'Adding document');
      const rd=new FileReader();
      rd.onerror=()=>{ prog.done(); toast('Could not read that file.'); };
      rd.onload=async()=>{
        const send=async(data)=>{ try{
            prog.set(0.05);
            const d=await uploadMedia(type,title,data,(p)=>prog.set(0.05+p*0.9));
            prog.set(1); setTimeout(()=>prog.done(),250);
            LE.push({id:d.id,title,url:d.url,type,subtitle:d.subtitle||'',icon:'',active:1,clicks:0}); renderList(); fireChange();
            toast(type==='image'?'Photo added ✓':'Document added ✓'); }
          catch(e){ prog.done(); toast(e.message); } };
        if(type==='image'){ downscaleImage(rd.result, 1080, (out)=>send(out)); }
        else { send(rd.result); }
      };
      rd.readAsDataURL(f); };
    inp.click();
  }
  function showProgress(label){
    const pane=$('#le-pane-content',root); if(!pane) return {set(){},done(){}};
    const el=htmlToNode(`<div class="le-prog"><div class="le-prog-top"><span class="le-prog-lbl">${esc(label)}…</span><span class="le-prog-pct">0%</span></div><div class="le-prog-bar"><div class="le-prog-fill"></div></div></div>`);
    const media=$('.le-media',pane); if(media) media.after(el); else pane.prepend(el);
    return {
      set(p){ p=Math.max(0,Math.min(1,p)); const f=el.querySelector('.le-prog-fill'); const t=el.querySelector('.le-prog-pct');
        if(f) f.style.width=(p*100).toFixed(0)+'%'; if(t) t.textContent=(p*100).toFixed(0)+'%'; },
      done(){ el.remove(); }
    };
  }
  load();
}


function bioPreviewHTML(u, links, theme, btn, font){
  const t=CARD_THEMES[theme]||CARD_THEMES.aurora; const rad=cardBtnRadius(btn); const f=CARD_FONTS[font]||CARD_FONTS.sans;
  const vars=`--cfg:${t.fg};--csoft:${t.soft};--cbtn:${t.btn};--cbtnfg:${t.btnfg};--cbd:${t.bd};--crad:${rad};--cfont:${f.css}`;
  const item=(l)=>{
    if(l.type==='image'){ return `<figure class="bc-photo"><img src="${esc(l.url)}" alt=""></figure>`; }
    if(l.type==='file'){ return `<div class="bc-file"><span class="bc-file-ic">${docIcon(l.subtitle)}</span><span class="bc-file-body"><b>${esc(l.title)}</b><small>${esc(l.subtitle||'FILE')}</small></span></div>`; }
    const b=!l.icon?brandFor(l.url):null; const ico=l.icon?`<span class="bc-ic">${esc(l.icon)}</span>`:(b?`<span class="bc-ic">${b.svg}</span>`:`<span class="bc-ic">${ic('link',16)}</span>`);
    const host=linkHost(l.url); const sub=host&&host.toLowerCase()!==(l.title||'').toLowerCase();
    return `<div class="bc-link">${ico}<span class="bc-link-body"><span class="bc-t">${esc(l.title)}</span>${sub?`<span class="bc-sub">${esc(host)}</span>`:''}</span></div>`;
  };
  const active=(links||[]).filter((l)=>l.active!==0);
  return `<div class="bc-preview" style="background:${t.bg};${vars};font-family:${f.css}">
    <div class="bc-ava" style="background:${esc(u.color||'#15140f')}"><span class="bc-ava-i">${esc(initials(u.name||u.username))}</span>${u.avatar?`<img class="bc-ava-img" src="${esc(u.avatar)}" onload="this.classList.add('on')" onerror="this.remove()">`:''}</div>
    <div class="bc-name">${esc(u.name||('@'+u.username))}${vbadge(u.verified)}</div>
    <div class="bc-handle">@${esc(u.username)}</div>
    ${u.bio?`<p class="bc-bio">${esc(u.bio)}</p>`:''}
    <div class="bc-links">${active.map(item).join('')||`<p class="bc-empty">Add your first link →</p>`}</div>
    <div class="bc-made"><span class="bc-made-mark">✦</span>LinkPoll</div>
  </div>`;
}

async function renderLinksPage(){
  if(!ME){ openAuth(); return; }
  const username=ME.username||'';
  route='links'; chrome();
  let U={username, name:(ME.name||username), avatar:(ME.avatar||''), bio:'', color:'', verified:0};
  try{ const pr=await api('/user',{query:{username}}); const p=pr.profile; U={username, name:p.name, avatar:p.avatar||'', bio:p.bio||'', color:p.color||'', verified:p.verified||0}; }catch(e){}
  const qricon='<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm10-2h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm2 2h2v2h-2v-2zm2-2h2v2h-2v-2z"/></svg>';
  setMain(`<div class="lm2">
    <div class="lm2-left">
      <div class="lm2-top"><button class="lm-back" data-back title="Back">‹</button><h1 class="lm-title">${ic('link',20)} My link-in-bio</h1></div>
      <p class="lm-intro">Everything visitors see on your card at <b>/b/${esc(username)}</b>.</p>
      <div class="lm-linkbar">
        <a class="bl-chip" href="#/b/${esc(username)}" title="Open card">${ic('link',13)}<b>${esc(location.host)}/b/${esc(username)}</b></a>
        <button class="bl-ib" data-bio-copy title="Copy link">${ic('link',15)}</button>
        <button class="bl-ib" data-bio-qr title="QR code">${qricon}</button>
        <button class="bl-ib bl-ib-share" data-bio-share title="Share">${ic('share',14)}</button>
      </div>
      <div id="lm-root" class="lm-root"></div>
    </div>
    <div class="lm2-right">
      <div class="lm2-pvwrap">
        <div class="phone"><div class="phone-cam"></div><div class="phone-screen" id="lm-preview"></div></div>
        <a class="btn btn-ghost btn-sm lm2-openbtn" href="#/b/${esc(username)}" target="_blank">Open live card ↗</a>
      </div>
    </div>
  </div>`);
  const back=$('[data-back]'); if(back) back.onclick=()=>{ if(history.length>1) history.back(); else go('#/u/'+username); };
  const cp=$('[data-bio-copy]'); if(cp) cp.onclick=()=>navigator.clipboard.writeText(location.origin+'/b/'+username).then(()=>toast('Link copied!'));
  const qr=$('[data-bio-qr]'); if(qr) qr.onclick=()=>qrBio(username, ME.name);
  const sh=$('[data-bio-share]'); if(sh) sh.onclick=()=>shareBio(username, ME.name);
  const updatePreview=(st)=>{ const el=$('#lm-preview'); if(el) el.innerHTML=bioPreviewHTML(U, st.links, st.theme, st.btn, st.font); };
  openLinksEditor(username, $('#lm-root'), {onChange:updatePreview});
}
async function renderProfile(username){
  chrome();
  setMain(`<button class="back-btn" data-back>← Back</button>${skProfile()}`);
  $('[data-back]').onclick=()=>{ if(history.length>1)history.back(); else go('#/'); };
  let data;
  try{ data=await api('/user',{query:{username}}); }catch(e){ return notFound({emoji:'👤', title:'Profile not found', sub:'This person may have changed their username, or is no longer on LinkPoll.', actions:[{label:'Find people',href:'#/network',primary:true},{label:'Go home',href:'#/'}]}); }
  const p=data.profile; cachePolls(data.polls);
  const web=p.website?`<span>${ic('link',14)} <a href="${/^https?:/.test(p.website)?esc(p.website):'https://'+esc(p.website)}" target="_blank" rel="noopener">${esc(p.website)}</a></span>`:'';
  const loc=p.location?`<span>${ic('loc',14)} ${esc(p.location)}</span>`:'';
  const prof=p.profession?`<span>${ic('user',14)} ${esc(p.profession)}</span>`:'';
  const ageBits=[]; if(p.age) ageBits.push('🎂 '+p.age); if(p.gender&&p.gender!=='Prefer not to say') ageBits.push(esc(p.gender));
  const ag=ageBits.length?`<span>${ageBits.join(' · ')}</span>`:'';
  const joined=p.joined?`<span>${ic('clock',14)} Joined ${new Date(p.joined*1000).toLocaleDateString(undefined,{month:'short',year:'numeric'})}</span>`:'';
  if(typeof PROFILE_TAB==='undefined') PROFILE_TAB='polls';
  if(!p.isMe && (PROFILE_TAB==='activity'||PROFILE_TAB==='drafts')) PROFILE_TAB='polls';
  setMain(`<button class="back-btn" data-back>← Back</button>
    <div class="profile-card">
      <div class="profile-banner${(p.cover||p.isMe)?' tappable':''}" ${(p.cover||p.isMe)?'data-cover="1" role="button" tabindex="0" title="Cover options"':''} style="${p.cover?`background-image:url('${esc(p.cover)}');background-size:cover;background-position:center`:`background:linear-gradient(120deg, ${esc(p.color||'#15140f')}, ${esc(p.color||'#15140f')}cc)`}">${p.isMe?`<span class="cover-edit">${ic('camera',15)} ${p.cover?'Edit cover':'Add cover'}</span>`:''}</div>
      <div class="profile-body">
        <div class="profile-avwrap${p.isMe?' editable':''}" ${(!p.hasStory&&(p.avatar||p.isMe))?'data-photo="1" role="button" tabindex="0" title="Photo options"':''}>${avatar(p,'s64')}${p.isMe?`<span class="photo-edit-badge" aria-hidden="true">${ic('camera',13)}</span>`:''}</div>
        <div class="profile-actions" data-act></div>
        <h1 class="profile-name">${esc(p.name)}${vbadge(p.verified)}</h1>
        <div class="handle">@${esc(p.username)}</div>
        ${p.bio?`<div class="profile-bio">${esc(p.bio)}</div>`:''}
        <div class="profile-meta">${prof}${loc}${web}</div>
        <div class="profile-stats">
          <span><b>${fmt(p.pollCount)}</b> polls</span>
          <span class="pstat" data-rel="followers"><b>${fmt(p.followers)}</b> followers</span>
          <span class="pstat" data-rel="following"><b>${fmt(p.following)}</b> following</span>
          <a class="pstat pstat-pts" href="#/${p.isMe?'points':'leaderboard'}" title="${esc(p.levelName||'')}"><b>${p.levelIcon||'🌱'} ${fmt(p.points||0)}</b> pts</a></div>
      </div>
    </div>
    ${(p.bioCode && ((p.links&&p.links.length)||p.isMe))?`<div class="bio-links" id="bio-links">
      <div class="bl-share">
        <a class="bl-chip" href="#/b/${esc(username)}" title="Open link-in-bio card">${ic('link',13)}<b>/b/${esc(username)}</b></a>
        <button class="bl-ib" data-bio-copy="${esc(username)}" title="Copy link">${ic('link',15)}</button>
        <button class="bl-ib" data-bio-qr="${esc(username)}" title="QR code"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm10-2h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm2 2h2v2h-2v-2zm2-2h2v2h-2v-2z"/></svg></button>
        <button class="bl-ib bl-ib-share" data-bio-share="${esc(username)}" title="Share">${ic('share',14)}</button>
        ${p.isMe?`<button class="bl-ib bl-ib-edit" id="edit-links" title="Edit link-in-bio"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 000-1.41l-2.34-2.34a1 1 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg></button>`:''}
      </div>
    </div>`:''}
    <div class="tabs prof-tabs"><button data-ptab="polls" class="${PROFILE_TAB==='polls'?'active':''}">Polls</button>
      <button data-ptab="communities" class="${PROFILE_TAB==='communities'?'active':''}">Communities</button>
      ${p.isMe?`<button data-ptab="drafts" class="${PROFILE_TAB==='drafts'?'active':''}">Drafts</button>`:''}
      ${p.isMe?`<button data-ptab="activity" class="${PROFILE_TAB==='activity'?'active':''}">Activity</button>`:''}</div>
    <div id="prof-content"></div>`);
  $('[data-back]').onclick=()=>{ if(history.length>1)history.back(); else go('#/'); };
  $$('[data-rel]').forEach((el)=>el.onclick=()=>openUserList(username, el.dataset.rel));
  const ph=$('[data-photo]');
  if(ph) ph.onclick=()=>{ actionSheet([
      p.avatar && {label:'View photo', icon:'🔍', onClick:()=>openPhotoViewer(p.avatar, p.name)},
      p.isMe && {label:p.avatar?'Change photo':'Add photo', icon:'📷', onClick:()=>uploadUserImage('avatar', ()=>renderProfile(username))},
      (p.isMe&&p.avatar) && {label:'Remove photo', icon:'🗑️', danger:true, onClick:()=>removeUserImage('avatar', ()=>renderProfile(username))},
    ].filter(Boolean), ph, 'Profile photo'); };
  const cv=$('[data-cover]');
  if(cv) cv.onclick=()=>{ actionSheet([
      p.cover && {label:'View cover', icon:'🔍', onClick:()=>openPhotoViewer(p.cover, 'Cover photo')},
      p.isMe && {label:p.cover?'Change cover':'Add cover', icon:'📷', onClick:()=>uploadUserImage('cover', ()=>renderProfile(username))},
      (p.isMe&&p.cover) && {label:'Remove cover', icon:'🗑️', danger:true, onClick:()=>removeUserImage('cover', ()=>renderProfile(username))},
    ].filter(Boolean), cv, 'Cover photo'); };
  $$('[data-ptab]').forEach((b)=>b.onclick=()=>{ PROFILE_TAB=b.dataset.ptab;
    // remember where the tab strip sits so switching tabs never yanks the page around
    const strip=$('.prof-tabs'); const before=strip?strip.getBoundingClientRect().top:null;
    $$('[data-ptab]').forEach((x)=>x.classList.toggle('active', x===b));
    loadProfileTab(username, data);
    if(before!==null) requestAnimationFrame(()=>{ const st=$('.prof-tabs'); if(!st) return;
      const after=st.getBoundingClientRect().top;
      if(Math.abs(after-before)>1) jumpTo((window.scrollY||0)+(after-before)); }); });
  const act=$('[data-act]');
  if(p.isMe){ act.innerHTML=`<a class="btn btn-sm btn-dark" href="#/settings/profile">Edit profile</a>`;
    const cb=document.createElement('button'); cb.className='btn btn-sm btn-lime'; cb.innerHTML='🎉 Celebrate';
    cb.onclick=()=>openCelebratePicker(); act.appendChild(cb);
    const sb=document.createElement('button'); sb.className='btn btn-sm btn-ghost'; sb.innerHTML=ic('share',15)+' Share';
    sb.onclick=()=>shareProfile(username, (p&&p.name)||username); act.appendChild(sb);
    const gear=document.createElement('a'); gear.className='btn btn-sm btn-ghost iconbtn'; gear.setAttribute('aria-label','Settings'); gear.href='#/settings'; gear.innerHTML=ic('gear',18);
    act.appendChild(gear); }
  else if(ME){
    if(p.isBlocked){ const ub=document.createElement('button'); ub.className='btn btn-sm btn-ghost'; ub.textContent='Unblock';
      ub.onclick=async()=>{ try{ await api('/block',{method:'POST',body:{username}}); toast('Unblocked'); renderProfile(username); }catch(e){ toast(e.message); } }; act.appendChild(ub); }
    else {
      const b=document.createElement('button'); b.className='btn btn-sm '+(p.isFollowing?'btn-ghost':'btn-dark'); b.textContent=p.isFollowing?'Following':'Follow';
      b.onclick=async()=>{ try{ const r=await api('/follow',{method:'POST',body:{username}}); b.textContent=r.following?'Following':'Follow'; b.className='btn btn-sm '+(r.following?'btn-ghost':'btn-dark'); afterFollow(username, r.following); }catch(e){ toast(e.message); } }; act.appendChild(b);
      const mb=document.createElement('button'); mb.className='btn btn-sm btn-ghost'; mb.innerHTML=`${ic('mail',15)} Message`; mb.onclick=()=>go('#/messages/'+username); act.appendChild(mb);
      const ob=document.createElement('button'); ob.className='btn btn-sm btn-ghost iconbtn'; ob.setAttribute('aria-label','More'); ob.innerHTML=ic('more',18);
      ob.onclick=(e)=>{ openProfileMenu(username, e.currentTarget); }; act.appendChild(ob);
    }
    const sb=document.createElement('button'); sb.className='btn btn-sm btn-ghost'; sb.innerHTML=ic('share',15)+' Share';
    sb.onclick=()=>shareProfile(username, (data&&data.user&&data.user.name)||username); act.appendChild(sb);
  }
  else { act.innerHTML=`<button class="btn btn-sm btn-dark" data-auth>Follow</button>`;
    const sb=document.createElement('button'); sb.className='btn btn-sm btn-ghost'; sb.innerHTML=ic('share',15)+' Share';
    sb.onclick=()=>shareProfile(username, username); act.appendChild(sb); }
  clearInterval(window.__coverRot);
  initCoverCarousel(p);
  $$('[data-lclick]').forEach((a)=>a.addEventListener('click',()=>{ const id=a.dataset.lclick;
    try{ const body=JSON.stringify({id:+id}); if(navigator.sendBeacon){ navigator.sendBeacon('/api.php?path=/links/click', new Blob([body],{type:'application/json'})); } else { api('/links/click',{method:'POST',body:{id:+id}}); } }catch(e){} }));
  const el=$('#edit-links')||$('#add-first-link'); if(el) el.onclick=()=>go('#/links');
  const bcopy=$('[data-bio-copy]'); if(bcopy) bcopy.onclick=()=>{ navigator.clipboard.writeText(location.origin+'/b/'+bcopy.dataset.bioCopy).then(()=>toast('Link-in-bio copied!')); };
  const bqr=$('[data-bio-qr]'); if(bqr) bqr.onclick=()=>qrBio(bqr.dataset.bioQr, (p&&p.name)||username);
  const bshare=$('[data-bio-share]'); if(bshare) bshare.onclick=()=>shareBio(bshare.dataset.bioShare, (p&&p.name)||username);
  loadProfileTab(username, data);
}
/* ============================ QUIZ MODE ============================
   Serves quiz polls one at a time. Answer → reveal correct/wrong +
   explanation + running score → "Next question" loads the next quiz
   into the same card. */
let QUIZ_SEEN=[], QUIZ_SESSION={done:0,right:0};
/* =====================================================================
   MOCK PAPERS (teacher MCQ tests) — dashboard, builder, take, results
   ===================================================================== */
async function renderPapers(){
  chrome();
  if(!ME){ setMain(empt('📝','Sign in to create mock papers for your students.',true)); return; }
  setMain(`<div class="page-head"><h1 class="page-title">📝 Mock Papers</h1>
      <button class="btn btn-lime btn-sm" id="new-paper">${ic('plus',15)} Create paper</button></div>
    <p class="page-sub">Create MCQ tests, share the link with students, and see their results automatically.</p>
    <div id="papers-list">${skCards(2)}</div>`);
  $('#new-paper').onclick=()=>openPaperBuilder();
  let d; try{ d=await api('/papers/mine'); }catch(e){ $('#papers-list').innerHTML=empt('🙈',e.message); return; }
  const list=$('#papers-list'); if(!list) return;
  if(!d.papers.length){ list.innerHTML=empt('📝','No papers yet. Tap “Create paper” to build your first one.'); return; }
  list.innerHTML=d.papers.map((p)=>`<div class="paper-card" data-pc="${esc(p.code)}">
      <div class="paper-card-main">
        <div class="paper-title">${esc(p.title)}${p.closed?'<span class="paper-closed">Closed</span>':''}</div>
        <div class="paper-meta">${p.questions} question${p.questions===1?'':'s'} · <b>${fmt(p.submissions)}</b> submission${p.submissions===1?'':'s'}${p.durationMin?` · ⏱ ${p.durationMin} min`:''}</div>
      </div>
      <div class="paper-card-acts">
        <button class="btn btn-sm btn-dark" data-presults="${esc(p.code)}">${ic('bars',14)} Results</button>
        <button class="btn btn-sm btn-ghost" data-pshare="${esc(p.code)}">${ic('share',14)} Share</button>
        <button class="iconbtn" data-pmenu2="${esc(p.code)}" aria-label="More" data-keepmenu>${ic('lines2',18)}</button>
      </div></div>`).join('');
  list.onclick=(e)=>{
    const r=e.target.closest('[data-presults]'); if(r){ go('#/paper/'+r.dataset.presults+'/results'); return; }
    const sh=e.target.closest('[data-pshare]'); if(sh){ const p=d.papers.find((x)=>x.code===sh.dataset.pshare);
      openShareSheet({url:p.shareUrl,title:p.title,subtitle:'Mock paper · '+p.questions+' questions'}); return; }
    const mn=e.target.closest('[data-pmenu2]'); if(mn){ const code=mn.dataset.pmenu2; const p=d.papers.find((x)=>x.code===code);
      actionSheet([
        {label:'Open results', icon:'📊', onClick:()=>go('#/paper/'+code+'/results')},
        {label:'Preview as student', icon:'👁️', onClick:()=>go('#/paper/'+code)},
        {label:'Copy link', icon:ic('link',18), onClick:async()=>{ try{ await navigator.clipboard.writeText(p.shareUrl); toast('Link copied!'); }catch(_e){ prompt('Copy:',p.shareUrl); } }},
        {label:p.closed?'Reopen paper':'Close paper', icon:ic('clock',18), onClick:async()=>{ try{ const r=await api('/paper/close',{method:'POST',body:{code}}); toast(r.closed?'Paper closed':'Paper reopened'); renderPapers(); }catch(err){ toast(err.message); } }},
        {label:'Delete paper', icon:ic('out',18), danger:true, onClick:async()=>{ if(!confirm('Delete this paper and all its submissions?'))return; try{ await api('/paper/delete',{method:'POST',body:{code}}); toast('Paper deleted'); renderPapers(); }catch(err){ toast(err.message); } }},
      ], mn, p.title); return; }
  };
}

let PAPER_DRAFT=null;
function openPaperBuilder(){
  if(!ME){ openAuth(); return; }
  PAPER_DRAFT=PAPER_DRAFT||{title:'',description:'',durationMin:0,questions:[blankQ(),blankQ()]};
  chrome();
  setMain(`<button class="back-btn" data-pb-back>← Mock Papers</button>
    <div class="pb-wrap">
      <h1 class="page-title">Create mock paper</h1>
      <label class="pb-label">Title</label>
      <input class="field pb-input" id="pb-title" maxlength="200" placeholder="e.g. Physics Mock Test 1" value="${esc(PAPER_DRAFT.title)}">
      <label class="pb-label">Description <span class="pb-opt">(optional)</span></label>
      <textarea class="field pb-input" id="pb-desc" rows="2" maxlength="2000" placeholder="Instructions or topics covered…">${esc(PAPER_DRAFT.description)}</textarea>
      <label class="pb-label">Time limit <span class="pb-opt">(optional, minutes)</span></label>
      <input class="field pb-input pb-dur" id="pb-dur" type="number" min="0" max="600" placeholder="0 = no limit" value="${PAPER_DRAFT.durationMin||''}">
      <div class="pb-qhead"><h3>Questions</h3><span id="pb-count"></span></div>
      <div id="pb-questions"></div>
      <button class="pb-addq" id="pb-addq">${ic('plus',16)} Add question</button>
      <div class="pb-foot">
        <button class="btn btn-lime btn-block" id="pb-save">Publish paper</button>
        <p class="pb-hint" id="pb-hint"></p>
      </div>
    </div>`);
  $('[data-pb-back]').onclick=()=>{ PAPER_DRAFT=null; go('#/papers'); };
  const sync=()=>{ PAPER_DRAFT.title=$('#pb-title').value; PAPER_DRAFT.description=$('#pb-desc').value; PAPER_DRAFT.durationMin=+$('#pb-dur').value||0; };
  $('#pb-title').oninput=sync; $('#pb-desc').oninput=sync; $('#pb-dur').oninput=sync;
  $('#pb-addq').onclick=()=>{ sync(); PAPER_DRAFT.questions.push(blankQ()); renderPB(); };
  $('#pb-save').onclick=savePaper;
  renderPB();
}
function blankQ(){ return {question:'',options:['',''],correct:0,explain:''}; }
function renderPB(){
  const box=$('#pb-questions'); if(!box) return;
  box.innerHTML=PAPER_DRAFT.questions.map((q,qi)=>`<div class="pb-q" data-qi="${qi}">
      <div class="pb-q-head"><span class="pb-q-n">Q${qi+1}</span>
        ${PAPER_DRAFT.questions.length>1?`<button class="pb-q-del" data-qdel="${qi}" aria-label="Remove question">${ic('trash',15)}</button>`:''}</div>
      <textarea class="field pb-q-text" data-qtext="${qi}" rows="1" maxlength="500" placeholder="Type the question…">${esc(q.question)}</textarea>
      <div class="pb-opts">${q.options.map((o,oi)=>`<div class="pb-opt-row ${q.correct===oi?'correct':''}">
          <button class="pb-correct" data-correct="${qi}:${oi}" title="Mark as correct answer" aria-label="Correct answer">${q.correct===oi?'✓':''}</button>
          <input class="field pb-opt-in" data-opt="${qi}:${oi}" maxlength="300" placeholder="Option ${oi+1}" value="${esc(o)}">
          ${q.options.length>2?`<button class="pb-opt-x" data-optdel="${qi}:${oi}" aria-label="Remove option">✕</button>`:''}
        </div>`).join('')}
        ${q.options.length<6?`<button class="pb-addopt" data-addopt="${qi}">${ic('plus',13)} Add option</button>`:''}</div>
      <input class="field pb-explain" data-explain="${qi}" maxlength="500" placeholder="Explanation (optional, shown after submit)" value="${esc(q.explain)}">
    </div>`).join('');
  const c=$('#pb-count'); if(c) c.textContent=PAPER_DRAFT.questions.length+' question'+(PAPER_DRAFT.questions.length===1?'':'s');
  box.querySelectorAll('.pb-q-text').forEach((t)=>{ const qi=+t.dataset.qtext;
    const grow=()=>{ t.style.height='auto'; t.style.height=t.scrollHeight+'px'; }; grow();
    t.oninput=()=>{ PAPER_DRAFT.questions[qi].question=t.value; grow(); }; });
  box.querySelectorAll('.pb-opt-in').forEach((i)=>{ const [qi,oi]=i.dataset.opt.split(':').map(Number);
    i.oninput=()=>{ PAPER_DRAFT.questions[qi].options[oi]=i.value; }; });
  box.querySelectorAll('.pb-explain').forEach((i)=>{ const qi=+i.dataset.explain; i.oninput=()=>{ PAPER_DRAFT.questions[qi].explain=i.value; }; });
  box.querySelectorAll('[data-correct]').forEach((b)=>b.onclick=()=>{ const [qi,oi]=b.dataset.correct.split(':').map(Number); PAPER_DRAFT.questions[qi].correct=oi; renderPB(); });
  box.querySelectorAll('[data-addopt]').forEach((b)=>b.onclick=()=>{ const qi=+b.dataset.addopt; if(PAPER_DRAFT.questions[qi].options.length<6){ PAPER_DRAFT.questions[qi].options.push(''); renderPB(); } });
  box.querySelectorAll('[data-optdel]').forEach((b)=>b.onclick=()=>{ const [qi,oi]=b.dataset.optdel.split(':').map(Number); const q=PAPER_DRAFT.questions[qi]; q.options.splice(oi,1); if(q.correct>=q.options.length) q.correct=0; renderPB(); });
  box.querySelectorAll('[data-qdel]').forEach((b)=>b.onclick=()=>{ PAPER_DRAFT.questions.splice(+b.dataset.qdel,1); renderPB(); });
}
async function savePaper(){
  const t=$('#pb-title').value.trim();
  if(!t){ $('#pb-hint').textContent='Give your paper a title.'; $('#pb-title').focus(); return; }
  const questions=PAPER_DRAFT.questions.map((q)=>({question:q.question.trim(),
    options:q.options.map((o)=>o.trim()).filter(Boolean), correctIndex:q.correct, explain:(q.explain||'').trim()}))
    .filter((q)=>q.question && q.options.length>=2);
  if(!questions.length){ $('#pb-hint').textContent='Add at least one question with 2+ options.'; return; }
  const btn=$('#pb-save'); btn.disabled=true; btn.textContent='Publishing…';
  try{
    const r=await api('/papers',{method:'POST',body:{title:t,description:$('#pb-desc').value.trim(),durationMin:+$('#pb-dur').value||0,questions}});
    PAPER_DRAFT=null; toast('Paper published! 🎉');
    openShareSheet({url:r.paper.shareUrl,title:r.paper.title,subtitle:'Mock paper · share with students'});
    go('#/paper/'+r.paper.code+'/results');
  }catch(e){ $('#pb-hint').textContent=e.message; btn.disabled=false; btn.textContent='Publish paper'; }
}

let PAPER_STATE=null;
async function renderPaperTake(code){
  chrome();
  setMain(`<div id="paper-take">${skCards(2)}</div>`);
  let d; try{ d=(await api('/paper',{query:{code}})).paper; }catch(e){ return notFound({emoji:'📝', title:'Paper not found', sub:'This mock paper may have been removed, or the link may be wrong. Check with whoever shared it.', actions:[{label:'Go home',href:'#/',primary:true}]}); }
  PAPER_STATE={code, paper:d, answers:new Array(d.questions.length).fill(-1), name:(ME?ME.name:'')};
  paintTake();
}
function paintTake(){
  const d=PAPER_STATE.paper; const box=$('#paper-take'); if(!box) return;
  if(d.closed){ box.innerHTML=`<div class="paper-hero"><h1>${esc(d.title)}</h1>${d.teacher?`<p class="paper-by">by ${esc(d.teacher.name)}</p>`:''}</div>${empt('🔒','This paper is closed and no longer accepting submissions.')}`; return; }
  const answered=PAPER_STATE.answers.filter((a)=>a>=0).length;
  box.innerHTML=`<div class="paper-hero">
      <span class="paper-badge">📝 Mock Paper</span>
      <h1>${esc(d.title)}</h1>
      ${d.teacher?`<p class="paper-by">by ${esc(d.teacher.name)} · @${esc(d.teacher.username)}</p>`:''}
      ${d.description?`<p class="paper-desc">${esc(d.description)}</p>`:''}
      <div class="paper-info">${d.questionCount} questions${d.durationMin?` · ⏱ ${d.durationMin} min`:''}</div>
    </div>
    ${!ME?`<div class="paper-name"><label>Your name</label>
      <input class="field" id="pt-name" maxlength="120" placeholder="Enter your name" value="${esc(PAPER_STATE.name)}"></div>`:''}
    <div class="pt-questions">${d.questions.map((q,qi)=>`<div class="pt-q">
        <div class="pt-q-title"><span class="pt-q-n">${qi+1}</span> ${esc(q.question)}</div>
        <div class="pt-opts">${q.options.map((o,oi)=>`<button class="pt-opt ${PAPER_STATE.answers[qi]===oi?'sel':''}" data-pick="${qi}:${oi}">
            <span class="pt-radio"></span><span>${esc(o)}</span></button>`).join('')}</div>
      </div>`).join('')}</div>
    <div class="pt-foot">
      <div class="pt-progress">${answered} of ${d.questionCount} answered</div>
      <button class="btn btn-lime btn-block" id="pt-submit"${answered<d.questionCount?' disabled':''}>Submit answers</button>
    </div>`;
  const nm=$('#pt-name'); if(nm) nm.oninput=()=>{ PAPER_STATE.name=nm.value; };
  box.querySelectorAll('[data-pick]').forEach((b)=>b.onclick=()=>{ const [qi,oi]=b.dataset.pick.split(':').map(Number); PAPER_STATE.answers[qi]=oi; paintTake(); });
  const sb=$('#pt-submit'); if(sb) sb.onclick=submitPaper;
}
async function submitPaper(){
  const d=PAPER_STATE.paper;
  const name=(ME?ME.name:(PAPER_STATE.name||'').trim());
  if(!ME && !name){ toast('Please enter your name'); const n=$('#pt-name'); if(n) n.focus(); return; }
  if(PAPER_STATE.answers.some((a)=>a<0)){ toast('Please answer all questions'); return; }
  const btn=$('#pt-submit'); btn.disabled=true; btn.textContent='Submitting…';
  try{
    const r=await api('/paper/submit',{method:'POST',body:{code:PAPER_STATE.code,studentName:name,answers:PAPER_STATE.answers}});
    paintResult(r);
  }catch(e){ toast(e.message); btn.disabled=false; btn.textContent='Submit answers'; }
}
function paintResult(r){
  const box=$('#paper-take'); if(!box) return;
  const pct=r.percent; const tone=pct>=70?'good':pct>=40?'ok':'low';
  box.innerHTML=`<div class="pt-result ${tone}">
      <div class="pt-score-ring"><b>${r.score}</b><span>/ ${r.total}</span></div>
      <h2>${pct>=70?'Great work! 🎉':pct>=40?'Good effort 👍':'Keep practicing 💪'}</h2>
      <p class="pt-pct">${pct}%</p>
    </div>
    <div class="pt-review"><h3>Review</h3>
      ${r.review.map((q,i)=>{ const right=q.yourAnswer===q.correctIndex; return `<div class="pt-rev-q ${right?'right':'wrong'}">
        <div class="pt-rev-title"><span class="pt-rev-ic">${right?'✓':'✕'}</span> ${i+1}. ${esc(q.question)}</div>
        <div class="pt-rev-opts">${q.options.map((o,oi)=>`<div class="pt-rev-opt ${oi===q.correctIndex?'ans':''} ${oi===q.yourAnswer&&oi!==q.correctIndex?'yours':''}">
            ${oi===q.correctIndex?'✓ ':(oi===q.yourAnswer?'✗ ':'')}${esc(o)}</div>`).join('')}</div>
        ${q.explain?`<div class="pt-rev-explain">💡 ${esc(q.explain)}</div>`:''}</div>`; }).join('')}
    </div>
    <div class="pt-foot"><a class="btn btn-dark btn-block" href="#/">Done</a></div>`;
  window.scrollTo({top:0,behavior:'smooth'});
}

async function renderPaperResults(code){
  chrome();
  setMain(`<div id="paper-results">${skCards(2)}</div>`);
  let d; try{ d=await api('/paper/results',{query:{code}}); }catch(e){ return notFound({emoji:'📊', title:'Results unavailable', sub:e.message||'This paper may have been removed, or you may not have access to its results.', actions:[{label:'My papers',href:'#/papers',primary:true},{label:'Go home',href:'#/'}]}); }
  const p=d.paper;
  const box=$('#paper-results'); if(!box) return;
  box.innerHTML=`<button class="back-btn" data-back>← Mock Papers</button>
    <div class="pr-head">
      <h1 class="page-title">${esc(p.title)}${p.closed?'<span class="paper-closed">Closed</span>':''}</h1>
      <div class="pr-actions">
        <button class="btn btn-sm btn-ghost" id="pr-share">${ic('share',14)} Share link</button>
        <button class="btn btn-sm btn-ghost" id="pr-csv"${d.count?'':' disabled'}>${ic('download',14)} CSV</button>
      </div>
    </div>
    <div class="pr-stats">
      <div class="pr-stat"><b>${fmt(d.count)}</b><span>Submissions</span></div>
      <div class="pr-stat"><b>${d.avgPercent}%</b><span>Avg score</span></div>
      <div class="pr-stat"><b>${p.questionCount}</b><span>Questions</span></div>
    </div>
    ${!d.count?empt('👥','No submissions yet. Share the link with your students!'):`
    <h3 class="pr-sec">Students (ranked)</h3>
    <div class="pr-table">
      <div class="pr-row pr-th"><span>#</span><span>Student</span><span>Score</span><span>%</span></div>
      ${d.submissions.map((s,i)=>`<div class="pr-row">
        <span class="pr-rank">${i+1}</span>
        <span class="pr-name">${esc(s.name)}${s.registered?' <span class="pr-verified" title="Registered user">•</span>':''}<span class="pr-time">${timeAgo(s.at)}</span></span>
        <span class="pr-score">${s.score}/${s.total}</span>
        <span class="pr-pct ${s.percent>=70?'good':s.percent>=40?'ok':'low'}">${s.percent}%</span>
      </div>`).join('')}
    </div>
    <h3 class="pr-sec">Per-question performance</h3>
    <div class="pr-qstats">${d.questionStats.map((q,i)=>`<div class="pr-qstat">
      <div class="pr-qs-top"><span class="pr-qs-q">${i+1}. ${esc(q.question)}</span><span class="pr-qs-pct">${q.pct}%</span></div>
      <div class="pr-qs-bar"><span style="width:${q.pct}%"></span></div>
      <div class="pr-qs-sub">${q.correct} of ${d.count} correct</div>
    </div>`).join('')}`}`;
  $('[data-back]').onclick=()=>go('#/papers');
  $('#pr-share').onclick=()=>openShareSheet({url:p.shareUrl,title:p.title,subtitle:'Mock paper · '+p.questionCount+' questions'});
  const csv=$('#pr-csv'); if(csv&&d.count) csv.onclick=()=>{
    const rows=[['Rank','Student','Score','Total','Percent','Submitted']];
    d.submissions.forEach((s,i)=>rows.push([i+1,s.name,s.score,s.total,s.percent+'%',new Date(s.at).toLocaleString()]));
    const blob=new Blob([rows.map((r)=>r.map((c)=>`"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n')],{type:'text/csv'});
    const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='paper-'+p.code+'-results.csv'; a.click(); URL.revokeObjectURL(a.href);
  };
}

async function renderQuiz(){
  chrome();
  setMain(`<div class="quiz-wrap">
    <div class="quiz-top"><h1 class="quiz-title">${ic('target',22)} Quiz Mode</h1>
      <div class="quiz-score" id="quiz-score"></div></div>
    <div id="quiz-stage">${skCard()}</div></div>`);
  QUIZ_SEEN=[]; QUIZ_SESSION={done:0,right:0};
  updateQuizScore();
  loadNextQuiz();
}
function updateQuizScore(){ const s=$('#quiz-score'); if(!s) return;
  s.innerHTML = QUIZ_SESSION.done
    ? `<b>${QUIZ_SESSION.right}/${QUIZ_SESSION.done}</b> this session`
    : `<span class="qs-hint">Answer to build your streak 🔥</span>`;
}
async function loadNextQuiz(){
  const stage=$('#quiz-stage'); if(!stage) return; stage.innerHTML=skCard();
  let data; try{ data=await api('/quiz/next',{query:{exclude:QUIZ_SEEN.slice(-40).join(',')}}); }
  catch(e){ stage.innerHTML=empt('⚠️',e.message); return; }
  if(route!=='quiz') return;
  if(!data.poll){
    stage.innerHTML=`<div class="quiz-done">
      <div class="qd-emoji">🎉</div><h2>You're all caught up!</h2>
      <p>You've answered every available quiz${ME?'':' — sign in to track your score'}.</p>
      ${QUIZ_SESSION.done?`<div class="qd-stats"><b>${QUIZ_SESSION.right}/${QUIZ_SESSION.done}</b> correct this session</div>`:''}
      <div class="qd-actions"><a class="btn btn-dark" href="#/explore">Explore polls</a>
        ${ME?`<button class="btn btn-ghost" data-create>${ic('plus',15)} Create a quiz</button>`:''}</div></div>`;
    return;
  }
  const poll=data.poll; POLLS[poll.code]=poll; QUIZ_SEEN.push(poll.code);
  paintQuizCard(stage, poll, data.remaining);
}
function paintQuizCard(stage, poll, remaining){
  stage.innerHTML=`<div class="quiz-card" id="qcard">
    <div class="qc-head"><a class="qc-author" href="#/u/${esc(poll.author.username)}">${avatar(poll.author,'s28')} <span>@${esc(poll.author.username)}</span></a>
      ${typeof remaining==='number'?`<span class="qc-remain">${fmt(remaining)} left</span>`:''}</div>
    <div class="qc-q">${poll.image?`<div class="qc-img"><img src="${esc(poll.image)}" alt=""></div>`:''}
      <h2>${linkifyHashtags(esc(poll.question))}</h2></div>
    <div class="qc-opts" id="qc-opts">${poll.options.map((o)=>`<button class="qc-opt" data-oid="${o.id}"><span class="qc-mark"></span><span class="qc-lbl">${esc(o.label)}</span></button>`).join('')}</div>
    <div class="qc-foot" id="qc-foot"></div></div>`;
  const optsBox=$('#qc-opts');
  optsBox.querySelectorAll('.qc-opt').forEach((b)=>b.onclick=()=>submitQuizAnswer(poll, +b.dataset.oid));
}
async function submitQuizAnswer(poll, oid){
  if(!ME){ openAuth(); return; }
  const optsBox=$('#qc-opts'); if(!optsBox || optsBox.classList.contains('locked')) return;
  optsBox.classList.add('locked');
  optsBox.querySelectorAll('.qc-opt').forEach((b)=>{ b.classList.toggle('picked', +b.dataset.oid===oid); b.disabled=true; });
  let res; try{ res=await api('/vote',{method:'POST',body:{pollId:poll.id,optionIds:[oid]}}); }
  catch(e){ toast(e.message); optsBox.classList.remove('locked'); optsBox.querySelectorAll('.qc-opt').forEach((b)=>b.disabled=false); return; }
  const fresh=res.poll; POLLS[poll.code]=fresh;
  const correctId=fresh.correctOptionId; const gotIt=fresh.quizResult?fresh.quizResult.correct:(oid===correctId);
  // reveal
  optsBox.querySelectorAll('.qc-opt').forEach((b)=>{ const id=+b.dataset.oid;
    if(id===correctId) b.classList.add('correct');
    else if(id===oid) b.classList.add('wrong'); });
  QUIZ_SESSION.done++; if(gotIt) QUIZ_SESSION.right++; updateQuizScore();
  try{ if(navigator.vibrate) navigator.vibrate(gotIt?12:[0,30,40,30]); }catch(_){}
  const foot=$('#qc-foot');
  foot.innerHTML=`<div class="qc-verdict ${gotIt?'ok':'no'}">${gotIt?'🎯 Correct!':'❌ Not quite'}</div>
    ${fresh.quizExplain?`<div class="qc-explain">${esc(fresh.quizExplain)}</div>`:''}
    <button class="btn btn-dark btn-block qc-next" id="qc-next">Next question →</button>`;
  $('#qc-next').onclick=loadNextQuiz;
  foot.querySelector('.qc-verdict').animate?.([{opacity:0,transform:'translateY(6px)'},{opacity:1,transform:'none'}],{duration:220});
}

let PROFILE_TAB='polls';
function loadProfileTab(username, data){
  const c=$('#prof-content'); if(!c) return;
  if(PROFILE_TAB==='polls'){
    if(!data.polls.length){ c.innerHTML=empt('📊', (data.profile.isMe?'You haven\'t':'No polls')+' posted any polls yet.'); return; }
    c.innerHTML=`<div id="feed"></div>`; const feed=$('#feed'); feed.innerHTML=data.polls.map(cardHTML).join(''); wireFeed(feed); paintFills(feed);
  } else if(PROFILE_TAB==='communities'){ renderProfileCommunities(username, c);
  } else if(PROFILE_TAB==='drafts'){ renderProfileDrafts(c);
  } else { renderProfileActivity(username, c); }
}
async function renderProfileCommunities(username, c){
  c.innerHTML=skList(3);
  let d; try{ d=await api('/user/communities',{query:{username}}); }catch(e){ c.innerHTML=empt('⚠️',e.message); return; }
  if(!d.communities.length){ c.innerHTML=empt('🌐','Not in any communities yet.'); return; }
  c.innerHTML=`<div class="comm-grid">${d.communities.map(commCard).join('')}</div>`;
}
async function renderProfileDrafts(c){
  c.innerHTML=skList(3);
  let drafts; try{ drafts=(await api('/drafts')).drafts; }catch(e){ c.innerHTML=empt('\u26a0\ufe0f',e.message); return; }
  if(!drafts||!drafts.length){ c.innerHTML=empt('\ud83d\udcdd','No drafts yet. Start a poll and save it to keep it here.'); return; }
  c.innerHTML='<div class="draft-list">'+drafts.map((dr)=>`<div class="draft-item" data-did="${dr.id}"><div class="draft-info"><div class="draft-q">${esc(dr.question||'(untitled)')}</div><div class="draft-meta">${dr.optionCount} option${dr.optionCount===1?'':'s'} \u00b7 ${timeAgo(dr.updatedAt*1000)}</div></div><button class="draft-del" data-ddel="${dr.id}" aria-label="Delete">\ud83d\uddd1</button></div>`).join('')+'</div>';
  c.querySelectorAll('[data-ddel]').forEach((b)=>b.onclick=async(e)=>{ e.stopPropagation();
    if(!confirm('Delete this draft?')) return;
    try{ await api('/drafts/delete',{method:'POST',body:{id:+b.dataset.ddel}}); b.closest('.draft-item').remove(); if(!c.querySelector('.draft-item')) renderProfileDrafts(c); }catch(err){ toast(err.message); } });
  c.querySelectorAll('.draft-item').forEach((it)=>it.onclick=()=>{
    const dr=drafts.find((x)=>x.id===+it.dataset.did); if(!dr) return;
    openComposer((dr.data&&dr.data.type)||'simple',{draft:dr}); });
}
async function renderProfileActivity(username, c){
  c.innerHTML=skList(4);
  let act; try{ act=(await api('/user/activity',{query:{username}})).activity; }catch(e){ c.innerHTML=empt('⚠️',e.message); return; }
  if(!act.length){ c.innerHTML=empt('📭','No activity yet.'); return; }
  const meta={
    poll:{ic:'📊',cls:'ac-poll',verb:'created a poll'},
    comment:{ic:'💬',cls:'ac-comment',verb:'commented on'},
    reaction:{ic:'❤️',cls:'ac-reaction',verb:'reacted to'},
    vote:{ic:'🗳️',cls:'ac-vote',verb:'voted on'},
  };
  c.innerHTML=`<div class="activity-feed">${act.map((a)=>{
    const m=meta[a.type]||meta.vote;
    const badge=a.type==='reaction'&&a.reaction?(REMOJI[a.reaction]||'❤️'):m.ic;
    const when=a.at?`<span class="ac-time">${timeAgo(a.at)}</span>`:'';
    const snip=a.snippet?`<div class="ac-snip">“${esc(a.snippet)}”</div>`:'';
    return `<a class="ac-row" href="#/poll/${esc(a.code)}">
      <span class="ac-badge ${m.cls}">${badge}</span>
      <div class="ac-body"><div class="ac-line"><span class="ac-verb">${m.verb}</span> <b>${esc(a.question)}</b></div>
        ${snip}<div class="ac-foot">${when}</div></div>
      <span class="ac-chev">›</span></a>`;
  }).join('')}</div>`;
}

function openProfileMenu(username, anchor){
  actionSheet([
    {label:'Block @'+username, icon:ic('shield',16), danger:true, onClick:async()=>{
      if(!confirm('Block @'+username+'? They won\'t be able to message you, and you\'ll unfollow each other.')) return;
      try{ await api('/block',{method:'POST',body:{username}}); toast('Blocked'); renderProfile(username); }catch(e){ toast(e.message); } }},
  ], anchor, '@'+username);
}

/* ---------- saved ---------- */
/* ============================ COMMUNITIES ============================ */
function commCard(c){
  const priv=c.privacy==='private';
  const badge=priv?`<span class="cbadge private">${ic('lockc',11)} Private</span>`:`<span class="cbadge">${ic('globe',11)} Public</span>`;
  const initials=(c.name||'?').trim().slice(0,2).toUpperCase();
  const joined=c.myStatus==='approved'||c.isCreator;
  return `<a class="comm-card" href="#/c/${esc(c.slug)}">
    <span class="comm-cover" style="${c.cover?`background-image:url('${esc(c.cover)}')`:`background:linear-gradient(135deg,${esc(c.color)},${esc(c.color)}aa)`}">
      ${joined?`<span class="comm-joined">✓ Joined</span>`:''}
    </span>
    <span class="comm-ava" style="background:${esc(c.color||'#15140f')}">${c.cover?`<img src="${esc(c.cover)}" alt="">`:esc(initials)}</span>
    <span class="comm-body">
      <span class="comm-title"><b>${esc(c.name)}</b>${badge}</span>
      ${c.description?`<span class="comm-desc">${esc(c.description)}</span>`:''}
      <span class="comm-meta">
        <span class="cm-stat"><b>${fmt(c.members)}</b> member${c.members===1?'':'s'}</span>
        <span class="cm-dot">·</span>
        <span class="cm-stat"><b>${fmt(c.pollCount)}</b> poll${c.pollCount===1?'':'s'}</span>
      </span>
    </span></a>`;
}
async function renderCommunities(){
  chrome();
  setMain(`<div class="page-head"><div class="page-title" style="margin:0">${ic('users',20)} Communities</div>
      ${ME?`<button class="btn btn-dark btn-sm" id="comm-new">${ic('plus',15)} Create</button>`:''}</div>
    <div id="comm-page">${skList(3)}</div>`);
  const nb=$('#comm-new'); if(nb) nb.onclick=()=>openCreateCommunity();
  let d; try{ d=await api('/communities'); }catch(e){ $('#comm-page').innerHTML=empt('⚠️',e.message); return; }
  if(route!=='communities') return;
  const mine=d.mine.length?`<h2 class="sec-h">Your communities</h2><div class="comm-grid">${d.mine.map(commCard).join('')}</div>`:'';
  const disc=d.discover.length?`<h2 class="sec-h">Discover</h2><div class="comm-grid">${d.discover.map(commCard).join('')}</div>`
    :(!d.mine.length?empt('🌐','No communities yet. Create the first one!'):'');
  $('#comm-page').innerHTML=mine+disc;
}
function openCreateCommunity(){
  if(!ME){ openAuth(); return; }
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim" data-scrim data-keepmenu><div class="modal">
    <div class="modal-head"><h2>Create community</h2><button class="close" data-x>✕</button></div>
    <div class="modal-body">
      <div class="f"><label>Name</label><input class="field" id="cc-name" maxlength="80" placeholder="e.g. Movie Buffs"></div>
      <div class="f"><label>Description</label><textarea class="field" id="cc-desc" maxlength="300" style="min-height:80px" placeholder="What's this community about?"></textarea></div>
      <div class="f"><label>Privacy</label>
        <div class="seg" id="cc-priv">
          <button type="button" class="seg-b on" data-priv="public">${ic('globe',15)} Public</button>
          <button type="button" class="seg-b" data-priv="private">${ic('lockc',15)} Private</button>
        </div>
        <div class="desc" id="cc-privhint">Anyone can see and join. Polls appear in the public feed.</div></div>
      <div class="f"><label>Accent color</label><div class="swatches" id="cc-colors">${['#15140f','#c2f23a','#e0562d','#2d9cdb','#8b5cf6','#e11d74','#0f9d58'].map((c,i)=>`<button type="button" class="swatch ${i===0?'sel':''}" data-color="${c}" style="background:${c}"></button>`).join('')}</div></div>
      <button class="btn btn-dark" id="cc-create" style="width:100%;padding:13px">Create community</button>
    </div></div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  let privacy='public', color='#15140f';
  $$('#cc-priv .seg-b').forEach((b)=>b.onclick=()=>{ $$('#cc-priv .seg-b').forEach((x)=>x.classList.remove('on')); b.classList.add('on'); privacy=b.dataset.priv;
    $('#cc-privhint').textContent=privacy==='private'?'Only approved members can see & post. Requests need your approval.':'Anyone can see and join. Polls appear in the public feed.'; });
  $$('#cc-colors .swatch').forEach((s)=>s.onclick=()=>{ $$('#cc-colors .swatch').forEach((x)=>x.classList.remove('sel')); s.classList.add('sel'); color=s.dataset.color; });
  $('#cc-create').onclick=async()=>{
    const name=$('#cc-name').value.trim(); if(name.length<3){ toast('Name must be at least 3 characters.'); return; }
    const btn=$('#cc-create'); btn.disabled=true; btn.textContent='Creating…';
    try{ const r=await api('/communities',{method:'POST',body:{name,description:$('#cc-desc').value.trim(),privacy,color}});
      closeOverlay(); toast('Community created! 🎉'); go('#/c/'+r.community.slug);
    }catch(e){ toast(e.message); btn.disabled=false; btn.textContent='Create community'; }
  };
}
let COMM_TAB='polls';
async function renderCommunity(slug){
  chrome(); COMM_TAB='polls';
  setMain(skList(3));
  let d; try{ d=await api('/community',{query:{slug}}); }catch(e){ return notFound({emoji:'🌐', title:'Community not found', sub:'This community may have been deleted or made private, or the link may be incorrect.', actions:[{label:'Browse communities',href:'#/communities',primary:true},{label:'Go home',href:'#/'}]}); }
  if(route!=='community') return;
  const c=d.community; window.__COMM=d;
  const priv=c.privacy==='private';
  const joinBtn=()=>{
    if(c.isCreator) return `<span class="btn btn-ghost btn-sm">${ic('shield',14)} Admin</span>`;
    if(c.myStatus==='approved') return `<button class="btn btn-ghost btn-sm" data-leave>Leave</button>`;
    if(c.myStatus==='pending') return `<button class="btn btn-ghost btn-sm" disabled>Requested</button>`;
    return `<button class="btn btn-dark btn-sm" data-join>${priv?'Request to join':'Join'}</button>`;
  };
  setMain(`<button class="back-btn" data-back>← Back</button>
    <div class="comm-hero">
      <div class="comm-hero-cover" style="${c.cover?`background-image:url('${esc(c.cover)}')`:`background:linear-gradient(120deg,${esc(c.color)},${esc(c.color)}bb)`}"></div>
      <div class="comm-hero-body">
        <div class="comm-hero-top"><h1>${esc(c.name)}</h1><div class="comm-actions" data-cact>${ME?joinBtn():`<button class="btn btn-dark btn-sm" data-auth>Join</button>`}<button class="btn btn-ghost btn-sm" data-cshare>${ic('share',14)} Share</button></div></div>
        <div class="comm-hero-meta">${priv?`${ic('lockc',13)} Private`:`${ic('globe',13)} Public`} · ${fmt(c.members)} member${c.members===1?'':'s'} · ${fmt(c.pollCount)} poll${c.pollCount===1?'':'s'}</div>
        ${c.description?`<p class="comm-hero-desc">${esc(c.description)}</p>`:''}
      </div>
    </div>
    <div class="tabs prof-tabs"><button data-ctab="polls" class="active">Polls</button><button data-ctab="about">About</button><button data-ctab="members">Members</button></div>
    <div id="comm-content"></div>`);
  $('[data-back]').onclick=()=>{ if(history.length>1)history.back(); else go('#/communities'); };
  wireCommActions(slug);
  $$('[data-ctab]').forEach((b)=>b.onclick=()=>{ COMM_TAB=b.dataset.ctab; $$('[data-ctab]').forEach((x)=>x.classList.toggle('active',x===b)); loadCommTab(slug); });
  loadCommTab(slug);
}
function wireCommActions(slug){
  const j=$('[data-join]'); if(j) j.onclick=async()=>{ try{ const r=await api('/community/join',{method:'POST',body:{slug}}); toast(r.status==='pending'?'Request sent — waiting for approval.':'Joined! 🎉'); renderCommunity(slug); }catch(e){ toast(e.message); } };
  const l=$('[data-leave]'); if(l) l.onclick=async()=>{ if(!confirm('Leave this community?'))return; try{ await api('/community/leave',{method:'POST',body:{slug}}); toast('Left community.'); renderCommunity(slug); }catch(e){ toast(e.message); } };
  const a=$('[data-auth]'); if(a) a.onclick=()=>openAuth();
  const sh=$('[data-cshare]'); if(sh) sh.onclick=()=>shareCommunity(slug, c.name);
}
function openCommunityShare(slug){
  const c=(window.__COMM&&window.__COMM.community)||{name:slug,slug};
  const url=location.origin+location.pathname+'#/c/'+slug;
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim><div class="sheet share-sheet" data-keepmenu>
    <div class="sheet-grab"></div><div class="sheet-head"><h3>Share community</h3><button class="close" data-x>✕</button></div>
    <div class="sheet-poll">${ic('users',15)} <span>${esc(c.name)}</span></div>
    ${ME?`<div class="share-people-wrap"><div id="cshare-people" class="share-people">${skRow()}</div></div>`:''}
    <div class="share-apps" id="cshare-apps"></div></div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  const text=encodeURIComponent('Join "'+c.name+'" on LinkPoll'); const u=encodeURIComponent(url);
  const apps=[{k:'copy',label:'Copy link',cls:'a-copy',html:ic('link',24)},
    {k:'wa',label:'WhatsApp',cls:'a-wa',html:'<span class="aemoji">🟢</span>'},
    {k:'x',label:'X',cls:'a-x',html:'<span class="aemoji">𝕏</span>'},
    {k:'fb',label:'Facebook',cls:'a-fb',html:'<span class="aemoji">📘</span>'}];
  if(ME) apps.unshift({k:'story',label:'Add to story',cls:'a-story',html:'<span class="aemoji">⊕</span>'});
  if(navigator.share) apps.unshift({k:'native',label:'Share to…',cls:'a-native',html:ic('share',24)});
  $('#cshare-apps').innerHTML=apps.map((a)=>`<button class="share-app" data-a="${a.k}"><span class="sa-ic ${a.cls}">${a.html}</span><span class="sa-lab">${a.label}</span></button>`).join('');
  const act={ copy:async()=>{ try{ await navigator.clipboard.writeText(url); toast('Invite link copied!'); }catch(e){ prompt('Copy this link:',url); } closeOverlay(); },
    story:async()=>{ try{ await api('/stories',{method:'POST',body:{communitySlug:slug}}); toast('Community added to your story! 🎉'); closeOverlay(); }catch(e){ toast(e.message); } },
    wa:()=>{ window.open('https://wa.me/?text='+text+'%20'+u,'_blank'); closeOverlay(); },
    x:()=>{ window.open('https://twitter.com/intent/tweet?text='+text+'&url='+u,'_blank'); closeOverlay(); },
    fb:()=>{ window.open('https://www.facebook.com/sharer/sharer.php?u='+u,'_blank'); closeOverlay(); },
    native:async()=>{ try{ await navigator.share({title:c.name,text:'Join '+c.name+' on LinkPoll',url}); }catch(e){} closeOverlay(); } };
  $$('#cshare-apps [data-a]').forEach((b)=>b.onclick=()=>act[b.dataset.a]&&act[b.dataset.a]());
  // invite/send to people you follow via DM
  if(ME){ (async()=>{ let targets=[]; try{ targets=(await api('/share/targets')).targets||[]; }catch(e){}
    const box=$('#cshare-people'); if(!box) return; const sent={};
    box.innerHTML = targets.length ? targets.slice(0,12).map((p)=>`<button class="sp-person" data-u="${esc(p.username)}"><span class="sp-pav">${avatar(p,'s56',false)}</span><span class="sp-pname">${esc(p.username)}</span></button>`).join('')
      : `<span class="sp-person more"><span class="sp-pav search">${ic('users',20)}</span><span class="sp-pname">Follow people to invite</span></span>`;
    box.querySelectorAll('[data-u]').forEach((b)=>b.onclick=async()=>{ const un=b.dataset.u; if(sent[un])return;
      try{ await api('/messages',{method:'POST',body:{to:un,body:'Join my community "'+c.name+'" on LinkPoll: '+url}}); sent[un]=1; b.classList.add('sent'); b.querySelector('.sp-pname').textContent='Sent ✓'; }catch(e){ toast(e.message); } });
  })(); }
}
function loadCommTab(slug){
  const d=window.__COMM; const box=$('#comm-content'); if(!box||!d) return;
  if(COMM_TAB==='polls'){
    if(!d.canSeePolls){ box.innerHTML=empt('🔒','This community is private. Join to see its polls.'); return; }
    if(!d.polls.length){ box.innerHTML=empt('📊','No polls in this community yet.'+(d.community.myStatus==='approved'||d.community.isCreator?' Be the first — create one and pick this community!':'')); return; }
    box.innerHTML=`<div id="feed"></div>`; const feed=$('#feed'); feed.innerHTML=d.polls.map(cardHTML).join(''); wireFeed(feed); paintFills(feed);
  } else if(COMM_TAB==='about'){
    const c=d.community; const priv=c.privacy==='private';
    box.innerHTML=`<div class="about-card">
      <h2 class="sec-h">About</h2>
      <p class="about-desc">${c.description?esc(c.description):'<em style="color:var(--soft)">No description yet.</em>'}</p>
      <div class="about-rows">
        <div class="about-row"><span>${priv?ic('lockc',15):ic('globe',15)}</span><b>${priv?'Private community':'Public community'}</b><em>${priv?'Members must be approved; polls are members-only.':'Anyone can join and see polls.'}</em></div>
        <div class="about-row"><span>${ic('users',15)}</span><b>${fmt(c.members)} member${c.members===1?'':'s'}</b></div>
        <div class="about-row"><span>${ic('bars',15)}</span><b>${fmt(c.pollCount)} poll${c.pollCount===1?'':'s'}</b></div>
        <div class="about-row"><span>${ic('clock',15)}</span><b>Created ${new Date(c.createdAt).toLocaleDateString()}</b></div>
      </div>
      <div class="about-actions"><button class="btn btn-ghost btn-sm" data-cshare2>${ic('share',14)} Share / invite</button></div>
    </div>`;
    const s2=$('[data-cshare2]'); if(s2) s2.onclick=()=>openCommunityShare(slug);
  } else {
    box.innerHTML=skList(3);
    api('/community/members',{query:{slug}}).then((m)=>{
      let html='';
      if(m.isAdmin && m.pending.length){ html+=`<h2 class="sec-h">Requests</h2>`+m.pending.map((u)=>`<div class="viewer-row">${avatar(u,'s40',true)}
        <div class="vr-info"><b>${esc(u.name)}${vbadge(u.verified)}</b><span>@${esc(u.username)}</span></div>
        <span class="crow-act"><button class="btn btn-dark btn-xs" data-appr="${esc(u.username)}">Approve</button><button class="btn btn-ghost btn-xs" data-rej="${esc(u.username)}">✕</button></span></div>`).join(''); }
      html+=`<h2 class="sec-h">Members</h2>`+m.members.map((u)=>`<div class="viewer-row">${avatar(u,'s40',true)}
        <div class="vr-info"><b data-author="${esc(u.username)}">${esc(u.name)}${vbadge(u.verified)}</b><span>@${esc(u.username)}</span></div>
        ${u.role==='admin'?`<span class="crow-role">${ic('shield',13)} Admin</span>`:''}</div>`).join('');
      box.innerHTML=html;
      box.querySelectorAll('[data-appr]').forEach((b)=>b.onclick=async()=>{ try{ await api('/community/approve',{method:'POST',body:{slug,username:b.dataset.appr}}); toast('Approved'); loadCommTab(slug); }catch(e){ toast(e.message); } });
      box.querySelectorAll('[data-rej]').forEach((b)=>b.onclick=async()=>{ try{ await api('/community/approve',{method:'POST',body:{slug,username:b.dataset.rej,action:'reject'}}); toast('Removed'); loadCommTab(slug); }catch(e){ toast(e.message); } });
    }).catch((e)=>{ box.innerHTML=empt('⚠️',e.message); });
  }
}

async function renderPoints(){
  chrome(); if(!ME){ openAuth(); return; }
  setMain(`<button class="back-btn" data-back>← Back</button>
    <div class="page-title">🏆 Your points</div><div id="pts-page">${skList(3)}</div>`);
  $('[data-back]').onclick=()=>{ if(history.length>1)history.back(); else go('#/'); };
  let d; try{ d=await api('/me/points'); }catch(e){ $('#pts-page').innerHTML=empt('⚠️',e.message); return; }
  if(route!=='points') return;
  const lv=d.level; const floor=lv.floor; const nextAt=lv.next?lv.next.at:null;
  const prog=nextAt?Math.min(100,Math.round(((d.points-floor)/(nextAt-floor))*100)):100;
  $('#pts-page').innerHTML=`
    <div class="pts-hero">
      <div class="pts-badge">${lv.icon}</div>
      <div class="pts-big">${fmt(d.points)} <span>pts</span></div>
      <div class="pts-lvl">Level ${lv.level} · ${esc(lv.name)} · Rank #${fmt(d.rank)}</div>
      ${lv.next?`<div class="pts-bar"><span style="width:${prog}%"></span></div>
        <div class="pts-next">${fmt(nextAt-d.points)} pts to <b>${esc(lv.next.name)}</b></div>`:`<div class="pts-next">Top tier reached! 👑</div>`}
      <a class="btn btn-ghost btn-sm" href="#/leaderboard" style="margin-top:12px">View leaderboard →</a>
      <button class="btn btn-lime btn-sm" id="celeb-btn" style="margin-top:12px;margin-left:8px">🎉 Celebrate</button>
    </div>
    <div class="streak-row" id="streak-row"></div>
    <h2 class="sec-h">Achievements</h2>
    <div id="pts-badges" class="badges-grid">${skList(2)}</div>
    <h2 class="sec-h">How you earned points</h2>
    ${d.breakdown.length?`<div class="pts-break">${d.breakdown.map((b)=>`<div class="pts-brow"><span class="pts-blab">${esc(b.label)}</span>
      <span class="pts-bcount">×${fmt(b.count)}</span><span class="pts-btot">+${fmt(b.total)}</span></div>`).join('')}</div>`
      :empt('✨','Start creating polls, voting and commenting to earn points!')}
    <h2 class="sec-h">Perks & unlocks</h2>
    <div id="pts-perks" class="pts-perks">${skList(2)}</div>
    <h2 class="sec-h">Ways to earn</h2>
    <div class="pts-ways">
      ${[['📊','Create a poll','+10'],['🗳️','Vote on a poll','+2'],['💬','Comment','+3'],['➕','Follow someone','+1'],
         ['📷','Post a story','+5'],['👥','Create a community','+15'],['❤️','Someone reacts to your poll','+1'],
         ['👤','Someone follows you','+3'],['🎯','Someone votes on your poll','+1']].map(([i,l,p])=>
        `<div class="pts-way"><span>${i}</span><b>${l}</b><em>${p}</em></div>`).join('')}
    </div>`;
  api('/perks').then((pk)=>{ const box=$('#pts-perks'); if(!box) return;
    box.innerHTML=pk.perks.filter((p)=>p.enabled).map((p)=>`<div class="perk-row ${p.unlocked?'on':''}">
      <span class="perk-ic">${p.icon}</span>
      <div class="perk-info"><b>${esc(p.label)}</b><span>${esc(p.desc)}</span></div>
      <span class="perk-state">${p.unlocked?'✓ Unlocked':`🔒 ${fmt(p.threshold)} pts`}</span></div>`).join('')
      ||`<div class="ins-empty"><p>No perks available.</p></div>`;
  }).catch(()=>{ const box=$('#pts-perks'); if(box) box.innerHTML=''; });
  api('/badges').then((bd)=>{
    const sr=$('#streak-row'); if(sr){ const acc=bd.quizTotal?Math.round(bd.quizCorrect/bd.quizTotal*100):0;
      sr.innerHTML=`<div class="streak-card"><span class="streak-ic">🔥</span><b>${fmt(bd.streak)}</b><em>day streak</em></div>
        <div class="streak-card"><span class="streak-ic">🏅</span><b>${bd.earnedCount}/${bd.total}</b><em>badges</em></div>
        ${bd.quizTotal?`<div class="streak-card"><span class="streak-ic">🎯</span><b>${acc}%</b><em>quiz accuracy</em></div>`:''}`; }
    const bb=$('#pts-badges'); if(bb){ bb.innerHTML=bd.badges.map((b)=>`<div class="badge ${b.earned?'earned':'locked'}" ${b.earned?`data-celeb-badge="${esc(b.label)}" role="button" tabindex="0"`:''} title="${b.earned?'Tap to celebrate':esc(b.desc)}">
      <span class="badge-ic">${b.icon}</span><b>${esc(b.label)}</b><span class="badge-desc">${esc(b.desc)}</span>
      ${b.earned?'<span class="badge-tick">✓</span>':'<span class="badge-lock">🔒</span>'}</div>`).join('');
      bb.querySelectorAll('[data-celeb-badge]').forEach((el)=>el.onclick=()=>{ const b=bd.badges.find((x)=>x.label===el.dataset.celebBadge);
        if(b) openCelebrate({icon:b.icon||'🏅', eyebrow:'Badge earned', title:b.label, stat:b.desc||''}); }); }
  }).catch(()=>{ const bb=$('#pts-badges'); if(bb) bb.innerHTML=''; });
  const cbtn=$('#celeb-btn'); if(cbtn) cbtn.onclick=()=>openCelebratePicker();
}
let NET_VIEW='list';
async function renderNetwork(tab){
  chrome(); if(!ME){ openAuth(); return; }
  const t=(tab==='following'||tab==='followers')?tab:'suggested';
  setMain(`<div class="net-head"><div class="page-title" style="margin:0">${ic('users',20)} Your network</div>
      <div class="view-toggle"><button class="vt ${NET_VIEW==='list'?'on':''}" data-view="list" aria-label="List">${ic('bars',15)}</button><button class="vt ${NET_VIEW==='grid'?'on':''}" data-view="grid" aria-label="Grid">▦</button></div></div>
    <div class="tabs net-tabs">
      <button data-nt="suggested" class="${t==='suggested'?'active':''}">Suggested</button>
      <button data-nt="following" class="${t==='following'?'active':''}">Following</button>
      <button data-nt="followers" class="${t==='followers'?'active':''}">Followers</button></div>
    <div id="net-body">${skList(4)}</div>`);
  $$('[data-nt]').forEach((b)=>b.onclick=()=>go('#/network/'+b.dataset.nt));
  $$('[data-view]').forEach((b)=>b.onclick=()=>{ NET_VIEW=b.dataset.view; renderNetwork(t); });
  const box=$('#net-body');
  const rowList=(u)=>`<div class="net-row">${avatar(u,'s48',true)}
    <div class="net-who" data-author="${esc(u.username)}"><b>${esc(u.name)}${vbadge(u.verified)}${u.flair?' <span class="flair">✨</span>':''}</b>
      <span>@${esc(u.username)}</span>${u.bio?`<em>${esc(u.bio)}</em>`:(u.followers!=null?`<em>${fmt(u.followers)} follower${u.followers===1?'':'s'}</em>`:'')}</div>
    ${(!u.isMe)?`<button class="btn btn-sm ${u.isFollowing?'btn-ghost':'btn-dark'}" data-follow="${esc(u.username)}">${u.isFollowing?'Following':'+ Follow'}</button>`:'<span class="net-you">You</span>'}</div>`;
  const rowGrid=(u)=>`<div class="net-tile"><div class="net-tile-av" data-author="${esc(u.username)}">${avatar(u,'s64',true)}</div>
    <b data-author="${esc(u.username)}">${esc(u.name)}${vbadge(u.verified)}${u.flair?' <span class="flair">✨</span>':''}</b>
    <span>@${esc(u.username)}</span>
    ${u.followers!=null?`<em>${fmt(u.followers)} follower${u.followers===1?'':'s'}</em>`:''}
    ${(!u.isMe)?`<button class="btn btn-sm ${u.isFollowing?'btn-ghost':'btn-dark'}" data-follow="${esc(u.username)}" style="margin-top:8px;width:100%">${u.isFollowing?'Following':'+ Follow'}</button>`:'<span class="net-you">You</span>'}</div>`;
  try{
    let users;
    if(t==='suggested') users=(await api('/suggested',{query:{limit:30}})).users;
    else users=(await api('/'+t,{query:{username:ME.username}})).users;
    if(route!=='network') return;
    if(!users.length){
      const msg=t==='suggested'?['🧭','No suggestions right now','Follow a few people and check back — we\'ll surface accounts you might like.']
        :t==='following'?['👀','You\'re not following anyone yet','Head to Suggested or Explore to find people to follow.']
        :['👋','No followers yet','Share your polls to grow your audience — great polls get noticed!'];
      box.innerHTML=`<div class="net-empty"><div class="ne-ic">${msg[0]}</div><h3>${msg[1]}</h3><p>${msg[2]}</p>${t!=='suggested'?'<a class="btn btn-dark btn-sm" href="#/network/suggested">Find people</a>':''}</div>`;
      return;
    }
    box.innerHTML = NET_VIEW==='grid'
      ? `<div class="net-grid">${users.map(rowGrid).join('')}</div>`
      : `<div class="net-list">${users.map(rowList).join('')}</div>`;
  }catch(e){ if(box) box.innerHTML=empt('⚠️',e.message); }
}
async function renderLeaderboard(){
  chrome();
  setMain(`<button class="back-btn" data-back>← Back</button>
    <div class="page-title">🏆 Leaderboard</div><div id="lb-page">${skList(6)}</div>`);
  $('[data-back]').onclick=()=>{ if(history.length>1)history.back(); else go('#/'); };
  let d; try{ d=await api('/leaderboard'); }catch(e){ $('#lb-page').innerHTML=empt('⚠️',e.message); return; }
  if(route!=='leaderboard') return;
  const medal=(r)=>r===1?'🥇':r===2?'🥈':r===3?'🥉':`<span class="lb-num">${r}</span>`;
  const rows=d.leaderboard.map((u)=>`<a class="lb-row ${u.isMe?'me':''}" href="#/u/${esc(u.username)}">
    <span class="lb-rank">${medal(u.rank)}</span>${avatar(u,'s40',true)}
    <div class="lb-info"><b>${esc(u.name)}${vbadge(u.verified)}</b><span>${u.levelIcon} ${esc(u.levelName)}</span></div>
    <span class="lb-pts">${fmt(u.points)}</span></a>`).join('');
  const meLine=d.me?`<div class="lb-mine">You're <b>#${fmt(d.me.rank)}</b> with <b>${fmt(d.me.points)}</b> pts · ${d.me.icon} ${esc(d.me.name)}</div>`:'';
  $('#lb-page').innerHTML=meLine+(d.leaderboard.length?`<div class="lb-list">${rows}</div>`:empt('🏆','No rankings yet.'));
}
async function renderInsights(){
  chrome();
  if(!ME){ openAuth(); return; }
  setMain(`<button class="back-btn" data-back>← Back</button>
    <div class="page-title">📊 Your reach & insights</div>
    <div id="ins-page">${skList(3)}</div>`);
  $('[data-back]').onclick=()=>{ if(history.length>1)history.back(); else go('#/'); };
  let d; try{ d=(await api('/me/insights')).insights; }catch(e){ $('#ins-page').innerHTML=empt('⚠️',e.message); return; }
  if(route!=='insights') return;
  const card=(icon,label,val,sub)=>`<div class="instat"><span class="instat-ic">${icon}</span>
    <div class="instat-main"><b>${fmt(val)}</b><span>${label}</span>${sub?`<small>${sub}</small>`:''}</div></div>`;
  $('#ins-page').innerHTML=`
    <div class="ins-hero">📣 Across your <b>${fmt(d.polls)}</b> poll${d.polls===1?'':'s'}, you've reached <b>${fmt(d.reach)}</b> ${d.reach===1?'person':'people'} who voted, with <b>${fmt(d.votesReceived)}</b> total vote${d.votesReceived===1?'':'s'}.</div>
    <div class="instat-grid">
      ${card('🗳️','Votes received',d.votesReceived)}
      ${card('🎯','People reached',d.reach,'unique voters')}
      ${card('❤️','Reactions',d.reactionsReceived)}
      ${card('💬','Comments',d.commentsReceived)}
      ${card('👥','Followers',d.followers)}
      ${card('➡️','Following',d.following)}
      ${card('🔗','Shares',d.shares)}
      ${card('👁','Story views',d.storyViews)}
    </div>
    ${d.topPoll?`<div class="panel" style="margin-top:16px"><h2 style="font-size:16px">🏆 Your top poll</h2>
      <a class="ins-top-poll" href="#/poll/${esc(d.topPoll.code)}"><b>${esc(d.topPoll.question)}</b><span>${fmt(d.topPoll.votes)} vote${d.topPoll.votes===1?'':'s'}</span></a></div>`:''}
    <p class="ins-note" style="margin-top:14px">Open any individual poll → <b>Audience insights</b> to see its full breakdown by gender, age and location.</p>`;
}
async function renderSaved(){
  chrome();
  if(!ME){ setMain(`<div class="page-title">Saved</div>${empt('🔖','Sign in to bookmark polls and read them later.',true)}`); return; }
  setMain(`<div class="page-title">Saved</div><div id="feed">${skCards(3)}</div>`);
  try{ const{polls}=await api('/saved'); cachePolls(polls);
    const feed=$('#feed'); if(!polls.length){ feed.innerHTML=empt('🔖','No saved polls yet. Tap the bookmark on any poll to save it.'); return; }
    feed.innerHTML=polls.map(cardHTML).join(''); wireFeed(feed); paintFills(feed);
  }catch(e){ $('#feed').innerHTML=empt('⚠️',e.message); }
}

/* ---------- notifications ---------- */
const BADGE_LABELS={first_poll:'📊 First Poll',polls_10:'🗳️ Poll Maker',polls_50:'🏭 Poll Machine',first_vote:'✅ First Vote',votes_100:'🗳️ Decider',social_10:'🤝 Connector',popular_50:'🌟 Rising Star',streak_7:'🔥 On Fire',streak_30:'🌋 Unstoppable',quiz_10:'🎯 Quiz Whiz',level_up:'⭐ Level Up',community:'🏘️ Community Builder'};
function notifText(t){ if(t&&t.indexOf('badge:')===0){ const k=t.slice(6); return 'earned the '+(BADGE_LABELS[k]||'new')+' badge!'; }
  return {vote:'voted on your poll',like:'liked your poll',comment:'commented on your poll',reply:'replied to your comment',follow:'started following you',mention:'mentioned you in a comment'}[t]||'interacted with you'; }
async function renderNotifications(){
  chrome();
  if(!ME){ setMain(`<div class="page-title">Notifications</div>${empt('🔔','Sign in to see your notifications.',true)}`); return; }
  setMain(`<div class="page-title">Notifications</div><div id="nlist">${skList(5)}</div>`);
  let items=[];
  try{ items=(await api('/notifications')).notifications; }catch(e){}
  const list=$('#nlist');
  if(!items.length){ list.innerHTML=empt('🔔','You\'re all caught up.'); }
  else {
    list.innerHTML=items.map((n)=>{
      const u={name:n.name,username:n.username,color:n.color,avatar:n.avatar};
      const link=n.poll_code?`#/poll/${n.poll_code}`:`#/u/${n.username}`;
      return `<a href="${link}" class="notif ${n.read_flag?'':'unread'}">${avatar(u,'s40')}
        <div class="nbody"><div><b>${esc(n.name)}</b> ${notifText(n.type)}${n.poll_question?` <span class="nq">“${esc(n.poll_question.slice(0,60))}${n.poll_question.length>60?'…':''}”</span>`:''}</div>
        <div class="nt">${timeAgo(n.created_at*1000)}</div></div></a>`;
    }).join('');
  }
  // mark read
  try{ await api('/notifications/read',{method:'POST'}); }catch(e){}
  UNREAD=0; renderSide();
}

/* ---------- theme ---------- */
function hideSplash(){ const s=document.getElementById('splash'); if(s){ s.classList.add('hide'); setTimeout(()=>{ try{ s.remove(); }catch(e){} }, 550); } }

/* =====================================================================
   ONBOARDING — quick welcome tour for first-time visitors
   ===================================================================== */
const ONBOARD_SLIDES=[
  {icon:'\u{1F44B}', title:'Welcome to LinkPoll', text:'The social network built entirely around polls. Ask the world anything \u2014 and see what people really think.'},
  {icon:'\u270D\uFE0F', title:'Create in seconds', text:'Tap Create Poll to ask a question. Pick from simple, yes/no, quiz, image, rating, this-or-that, or a full survey.'},
  {icon:'\u{1F5F3}\uFE0F', title:'Vote & watch it live', text:'Swipe through your feed, vote on polls, and see the results update in real time as others join in.'},
  {icon:'\u{1F3C6}', title:'Quizzes, streaks & badges', text:'Test your knowledge, keep a daily streak, earn points and unlock badges \u2014 then celebrate and share them.'},
  {icon:'\u{1F310}', title:'Communities & chat', text:'Join communities around your interests and message other members. Your opinion matters here.'},
];
let ONB_IDX=0;
function showOnboarding(force){
  try{ if(!force && localStorage.getItem('lp_onboarded')==='1') return; }catch(e){}
  ONB_IDX=0; lockScroll(); renderOnboard();
}
function renderOnboard(){
  const ov=$('#overlay'); if(!ov) return;
  const s=ONB_IDX, total=ONBOARD_SLIDES.length, slide=ONBOARD_SLIDES[s];
  const first=s===0, last=s===total-1;
  ov.innerHTML=`<div class="onboard" role="dialog" aria-label="Welcome tour">
    <div class="onb-top"><button class="onb-skip" data-skip>${last?'':'Skip'}</button></div>
    <div class="onb-body">
      <div class="onb-icon">${slide.icon}</div>
      <h2 class="onb-title">${esc(slide.title)}</h2>
      <p class="onb-text">${esc(slide.text)}</p>
    </div>
    <div class="onb-foot">
      <div class="onb-dots">${ONBOARD_SLIDES.map((_,i)=>`<span class="onb-dot ${i===s?'on':''}" data-dot="${i}"></span>`).join('')}</div>
      <div class="onb-nav">
        <button class="btn btn-ghost onb-back ${first?'onb-hide':''}" data-back>\u2190 Back</button>
        <button class="btn btn-lime onb-next" data-next>${last?'Get Started \u{1F389}':'Next \u2192'}</button>
      </div>
    </div>
  </div>`;
  const finish=()=>{ try{ localStorage.setItem('lp_onboarded','1'); }catch(e){} closeOverlay(); };
  const go1=(i)=>{ ONB_IDX=Math.max(0,Math.min(total-1,i)); renderOnboard(); };
  const sk=$('[data-skip]',ov); if(sk) sk.onclick=finish;
  const bk=$('[data-back]',ov); if(bk) bk.onclick=()=>go1(s-1);
  $('[data-next]',ov).onclick=()=>{ if(last) finish(); else go1(s+1); };
  ov.querySelectorAll('[data-dot]').forEach((d)=>d.onclick=()=>go1(+d.dataset.dot));
  const card=$('.onboard',ov); let sx=null;
  card.addEventListener('touchstart',(e)=>{ sx=e.touches[0].clientX; },{passive:true});
  card.addEventListener('touchend',(e)=>{ if(sx==null) return; const dx=e.changedTouches[0].clientX-sx; sx=null;
    if(dx<-45 && !last) go1(s+1); else if(dx>45 && !first) go1(s-1); },{passive:true});
}

function applyThemeColor(){
  const dark=document.documentElement.dataset.theme==='dark';
  const col=dark?'#0f0f12':'#f3f1ea';
  let m=document.querySelector('meta[name="theme-color"]');
  if(!m){ m=document.createElement('meta'); m.setAttribute('name','theme-color'); document.head.appendChild(m); }
  m.setAttribute('content',col);
  let a=document.querySelector('meta[name="apple-mobile-web-app-status-bar-style"]');
  if(!a){ a=document.createElement('meta'); a.setAttribute('name','apple-mobile-web-app-status-bar-style'); document.head.appendChild(a); }
  a.setAttribute('content', dark?'black-translucent':'default');
}
function initTheme(){ try{
  let t=localStorage.getItem('lp_theme');
  if(t==null && window.matchMedia){ t=matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'; }  // follow device on first run
  if(t==='dark') document.documentElement.dataset.theme='dark'; else delete document.documentElement.dataset.theme;
  setThemeCookie(t==='dark'?'dark':'light');   // keep the manifest (install splash) in sync with the theme
  applyThemeColor();
  // keep following the device theme until the user explicitly picks one
  if(window.matchMedia){ try{ matchMedia('(prefers-color-scheme: dark)').addEventListener('change',(e)=>{
    if(localStorage.getItem('lp_theme')==null){ if(e.matches) document.documentElement.dataset.theme='dark'; else delete document.documentElement.dataset.theme; applyThemeColor(); }
  }); }catch(_){} }
}catch(e){} }
function setThemeCookie(t){ try{ document.cookie='lp_theme='+t+';path=/;max-age=31536000;samesite=Lax'; }catch(e){} }
function setTheme(dark){ try{ localStorage.setItem('lp_theme',dark?'dark':'light'); }catch(e){} setThemeCookie(dark?'dark':'light'); if(dark) document.documentElement.dataset.theme='dark'; else delete document.documentElement.dataset.theme; applyThemeColor(); }
function isDark(){ return document.documentElement.dataset.theme==='dark'; }

/* ---------- settings ---------- */
let settingsView=null;
function renderSettings(){
  chrome();
  if(!ME){ setMain(`<div class="page-title">Settings</div>${empt('⚙️','Sign in to manage your account.',true)}`); return; }
  // ---- sub-page (a specific settings panel) ----
  if(settingsView){
    settingsTab=settingsView;
    const titles={profile:'Edit profile',password:'Password & security',appearance:'Appearance',account:'Account & privacy'};
    setMain(`<div class="ig-set"><div class="ig-subhead"><button class="ig-back" data-sback aria-label="Back">‹</button><h1>${titles[settingsView]||'Settings'}</h1></div><div id="spanel"></div></div>`);
    $('[data-sback]').onclick=()=>{ settingsView=null; renderSettings(); };
    settingsPanel($('#spanel'));
    return;
  }
  // ---- Instagram-style settings list ----
  const u=ME, chev=`<span class="ig-chev">›</span>`;
  const R=(icon,label,attrs,sub)=>`<button class="ig-row" ${attrs||''}><span class="ig-ic">${icon}</span><span class="ig-lab">${label}${sub?`<small>${sub}</small>`:''}</span>${chev}</button>`;
  const L=(icon,label,href,sub)=>`<a class="ig-row" href="${href}"><span class="ig-ic">${icon}</span><span class="ig-lab">${label}${sub?`<small>${sub}</small>`:''}</span>${chev}</a>`;
  const admin = '';
  setMain(`<div class="ig-set">
    <div class="ig-top"><h1>Settings</h1></div>
    <button class="ig-profile" data-open="profile">${avatar(u,'s48')}<span class="ig-pw"><b>${esc(u.name)}</b><span>@${esc(u.username)} · View profile</span></span>${chev}</button>

    <div class="ig-sec">Account</div>
    <div class="ig-group">
      ${R(ic('user',19),'Edit profile','data-open="profile"')}
      ${R(ic('lock',19),'Password &amp; security','data-open="password"')}
      ${R(ic('shield',19),'Account &amp; privacy','data-open="account"')}
    </div>

    <div class="ig-sec">Preferences</div>
    <div class="ig-group">
      ${R('🎨','Appearance','data-open="appearance"')}
      ${R(ic('bell',19),'Notifications &amp; emails','data-open="account"')}
    </div>
    ${admin}
    <div class="ig-sec">About &amp; support</div>
    <div class="ig-group">
      ${L('💡','Help Center','#/help')}
      ${L('🌐','About LinkPoll','#/about')}
      ${L('🚀','What\u2019s new','#/updates')}
      ${L('💬','Send feedback','#/feedback')}
      ${L('🔒','Privacy Policy','#/privacy')}
      ${L('📜','Terms of Service','#/terms')}
      ${L('🤝','Community Guidelines','#/guidelines')}
    </div>
    <div class="ig-sec">Login</div>
    <div class="ig-group">
      <button class="ig-row danger" data-signout><span class="ig-ic">🚪</span><span class="ig-lab">Log out</span></button>
    </div>
    <div class="ig-ver">LinkPoll · link-poll.com</div>
  </div>`);
  $$('[data-open]').forEach((b)=>b.onclick=()=>{ settingsView=b.dataset.open; renderSettings(); });
}
function settingsPanel(p){
  const u=ME;
  if(settingsTab==='profile'){
    const isPro=((ME&&ME.perks)||[]).includes('multi_cover'); const maxCov=isPro?3:1;
    p.innerHTML=`<div class="panel"><h2>Edit Profile</h2><div class="desc">This is what other people see.</div>
      <div class="avrow"><button type="button" class="av-tap" id="av-preview" title="Photo options" aria-label="Profile photo options">${avatar(u,'s64')}</button><div class="avctl">
        <input type="file" id="av-file" accept="image/*" hidden>
        <button class="btn btn-dark btn-sm" id="av-up">${ic('user',15)} Upload photo</button>
        <button class="btn btn-ghost btn-sm" id="av-grav">Use Gravatar</button>
        <button class="btn btn-sm" style="background:#fde8e8;color:var(--red)" id="rm">Remove</button>
        <div class="meta">Tap your photo for more options, or upload one from your device. You can also use your Gravatar, or just keep your colour + initials.</div></div></div>
      <div class="frow"><div class="f"><label>Full name</label><input class="field" id="s-name" value="${esc(u.name)}"></div>
        <div class="f"><label>Username</label><input class="field" id="s-user" value="${esc(u.username)}"></div></div>
      <div class="f"><label>Bio</label><textarea class="field" id="s-bio" style="min-height:74px">${esc(u.bio||'')}</textarea></div>
      <div class="frow"><div class="f"><label>Location</label><div class="loc-row"><input class="field" id="s-loc" value="${esc(u.location||'')}"><button type="button" class="btn btn-ghost btn-sm loc-btn" id="s-loc-detect">📍 Detect</button></div></div>
        <div class="f"><label>Website</label><input class="field" id="s-web" value="${esc(u.website||'')}"></div></div>
      <div class="frow"><div class="f"><label>Profession</label><input class="field" id="s-prof" placeholder="e.g. Designer" value="${esc(u.profession||'')}"></div>
        <div class="f"><label>Age</label><input class="field" id="s-age" type="number" min="13" max="120" inputmode="numeric" value="${u.age||''}"></div>
        <div class="f"><label>Gender</label><select class="field" id="s-gender">
          ${['','Female','Male','Non-binary','Prefer not to say'].map((g)=>`<option value="${g}" ${(u.gender||'')===g?'selected':''}>${g||'Select…'}</option>`).join('')}</select></div></div></div>
      <div class="panel"><h2 style="font-size:18px">Profile Color</h2>
        <div class="swatches" style="margin-top:14px">${COLORS.map((c)=>`<div class="sw ${u.color===c?'sel':''}" data-color="${c}" style="background:${c}"></div>`).join('')}</div></div>
      <div class="panel"><h2 style="font-size:18px">Verified badge</h2><div class="desc">${u.verified?'Your account is verified.':'Verification is granted by LinkPoll admins.'}</div>
        <div class="set-row"><span class="t">${vbadge(1)} Verified status</span><span style="color:${u.verified?'#5c7a13':'var(--soft)'};font-weight:700">${u.verified?'Verified':'Not verified'}</span></div></div>
      <div class="panel"><h2 style="font-size:18px">Cover banner${isPro?'s':''}</h2>
        <div class="desc">Shown as the banner on your profile. Wide images (16:9) look best.${isPro?' Add up to 3 — they rotate automatically. 🖼️':''}</div>
        <div class="cover-mgr" id="cover-mgr"></div>
        <input type="file" id="cov-file" accept="image/*" hidden>
        ${!isPro?`<div class="perk-lock" style="margin-top:12px">🖼️ <b>Multiple rotating banners</b> unlock with more points. <a href="#/points">See perks →</a></div>`:''}</div>
      <div class="panel"><h2 style="font-size:18px">Email preferences</h2>
        <div class="desc">Choose which emails you'd like to receive from LinkPoll.</div>
        <label class="set-row" style="cursor:pointer"><span class="t">📣 Promotional & news</span>
          <input type="checkbox" id="s-em-promo" ${(u.emailPromo??1)?'checked':''}></label>
        <label class="set-row" style="cursor:pointer"><span class="t">🔔 Activity & updates</span>
          <input type="checkbox" id="s-em-act" ${(u.emailActivity??1)?'checked':''}></label></div>
      <button class="btn btn-dark" id="save" style="padding:13px 22px">✓ Save Changes</button>`;
    let pendingColor=u.color, pendingAvatar=u.avatar||''; let COVER_REPLACE=-1;
    let pendingCovers=(u.covers&&u.covers.length)?u.covers.slice(0,maxCov):(u.cover?[u.cover]:[]);
    const renderCoverMgr=()=>{ const box=$('#cover-mgr',p); if(!box) return;
      let html=pendingCovers.map((c,i)=>`<div class="cov-tile" style="background-image:url('${esc(c)}')"><button type="button" class="cov-x" data-ci="${i}" aria-label="Remove">✕</button></div>`).join('');
      if(pendingCovers.length<maxCov) html+=`<button type="button" class="cov-add" id="cov-add">${ic('plus',16)}<span>Add banner</span></button>`;
      box.innerHTML=html || `<button type="button" class="cov-add" id="cov-add">${ic('plus',16)}<span>Add banner</span></button>`;
      box.querySelectorAll('.cov-x').forEach((b)=>b.onclick=(e)=>{ e.stopPropagation(); pendingCovers.splice(+b.dataset.ci,1); renderCoverMgr(); });
      box.querySelectorAll('.cov-tile').forEach((t,i)=>t.onclick=()=>{
        actionSheet([
          {label:'View photo', icon:'🔍', onClick:()=>openPhotoViewer(pendingCovers[i],'Cover banner')},
          {label:'Replace photo', icon:'🖼️', onClick:()=>{ COVER_REPLACE=i; $('#cov-file',p).click(); }},
          {label:'Remove photo', icon:'🗑️', danger:true, onClick:()=>{ pendingCovers.splice(i,1); renderCoverMgr(); }},
        ], t, 'Cover banner'); });
      const ab=$('#cov-add',p); if(ab) ab.onclick=()=>$('#cov-file',p).click(); };
    const setPrev=()=>{ const el=$('#av-preview',p); if(el) el.innerHTML=avatar({name:u.name,username:u.username,avatar:pendingAvatar,color:pendingColor},'s64'); };
    $$('[data-color]',p).forEach((s)=>s.onclick=()=>{ $$('[data-color]',p).forEach((x)=>x.classList.remove('sel')); s.classList.add('sel'); pendingColor=s.dataset.color; setPrev(); });
    renderCoverMgr();
    $('#cov-file',p).onchange=(e)=>{ const f=e.target.files&&e.target.files[0]; if(!f)return;
      if(!/^image\//.test(f.type)){ toast('Please choose an image file'); return; }
      if(f.size>12*1024*1024){ toast('Image too large (max 12MB)'); return; }
      const rd=new FileReader(); rd.onload=()=>downscaleImage(rd.result,1280,(out)=>{
        if(COVER_REPLACE>=0 && pendingCovers[COVER_REPLACE]!==undefined){ pendingCovers[COVER_REPLACE]=out; COVER_REPLACE=-1; }
        else if(pendingCovers.length<maxCov){ pendingCovers.push(out); }
        else { toast('Up to '+maxCov+' banner'+(maxCov===1?'':'s')); return; }
        renderCoverMgr(); toast('Banner ready — Save to apply'); }); rd.readAsDataURL(f); e.target.value=''; };
    const sld=$('#s-loc-detect',p); if(sld) sld.onclick=()=>detectLocation($('#s-loc',p), sld);
    $('#av-up',p).onclick=()=>$('#av-file',p).click();
    const avPreview=$('#av-preview',p);
    if(avPreview) avPreview.onclick=(e)=>{ e.preventDefault();
      const cur=pendingAvatar||u.avatar||'';
      actionSheet([
        cur && {label:'View photo', icon:'🔍', onClick:()=>openPhotoViewer(cur,'Profile photo')},
        {label:cur?'Change photo':'Upload photo', icon:'🖼️', onClick:()=>$('#av-file',p).click()},
        {label:'Use Gravatar', icon:'🌐', onClick:()=>$('#av-grav',p).click()},
        cur && {label:'Remove photo', icon:'🗑️', danger:true, onClick:()=>{ pendingAvatar=''; setPrev(); toast('Photo will be removed on Save'); }},
      ].filter(Boolean), avPreview, 'Profile photo'); };
    $('#av-file',p).onchange=(e)=>{ const f=e.target.files&&e.target.files[0]; if(!f)return;
      if(!/^image\//.test(f.type)){ toast('Please choose an image file'); return; }
      if(f.size>8*1024*1024){ toast('Image too large (max 8MB)'); return; }
      const rd=new FileReader(); rd.onload=()=>downscaleImage(rd.result,256,(out)=>{ pendingAvatar=out; setPrev(); toast('Photo ready — Save to apply'); }); rd.readAsDataURL(f); };
    $('#av-grav',p).onclick=async()=>{ const email=(u.email||'').trim().toLowerCase();
      if(!email){ toast('No email on file for Gravatar'); return; }
      try{ const h=await sha256hex(email); pendingAvatar='https://www.gravatar.com/avatar/'+h+'?s=256&d=identicon'; setPrev(); toast('Gravatar ready — Save to apply'); }
      catch(err){ toast('Could not load Gravatar here'); } };
    $('#rm',p).onclick=()=>{ pendingAvatar=''; setPrev(); toast('Avatar will be removed on Save'); };
    $('#save',p).onclick=async()=>{
      try{ const body={name:$('#s-name').value,username:$('#s-user').value,
        bio:$('#s-bio').value,location:$('#s-loc').value,website:$('#s-web').value,
        profession:$('#s-prof').value,age:$('#s-age').value,gender:$('#s-gender').value,
        color:pendingColor,avatar:pendingAvatar,cover:pendingCovers[0]||'',covers:pendingCovers,
        emailPromo:$('#s-em-promo').checked?1:0, emailActivity:$('#s-em-act').checked?1:0};
        if(GEO_LL){ body.lat=GEO_LL.lat; body.lng=GEO_LL.lng; }
        const{user}=await api('/user/update',{method:'POST',body});
        ME=user; chrome(); toast('Profile saved!'); }catch(e){ toast(e.message); }
    };
  } else if(settingsTab==='password'){
    p.innerHTML=`<div class="panel"><h2>Change Password</h2><div class="desc">Pick something you don't use elsewhere.</div>
      <div class="f"><label>Current password</label><input class="field" id="pw-cur" type="password"></div>
      <div class="f"><label>New password</label><input class="field" id="pw-new" type="password" placeholder="at least 6 characters"></div>
      <div class="f"><label>Confirm new password</label><input class="field" id="pw-c2" type="password"></div>
      <button class="btn btn-dark" id="pw-save" style="padding:12px 22px">Update password</button></div>`;
    $('#pw-save',p).onclick=async()=>{
      const cur=$('#pw-cur').value, nw=$('#pw-new').value, c2=$('#pw-c2').value;
      if(nw.length<6){ toast('New password must be 6+ characters'); return; }
      if(nw!==c2){ toast('New passwords don\'t match'); return; }
      try{ await api('/auth/password',{method:'POST',body:{current:cur,new:nw}}); $('#pw-cur').value=$('#pw-new').value=$('#pw-c2').value=''; toast('Password updated ✓'); }catch(e){ toast(e.message); }
    };
  } else if(settingsTab==='appearance'){
    p.innerHTML=`<div class="panel"><h2>Appearance</h2><div class="desc">Switch between light and dark, and set your accent / avatar colour.</div>
      <div class="set-row" style="border-bottom:1px solid var(--line-2)"><span class="ico">🌙</span><span class="t">Dark mode</span>
        <button class="toggle ${isDark()?'on':''}" id="dark-tg"></button></div>
      <div class="set-row" style="border-bottom:1px solid var(--line-2)"><span class="ico">🧭</span><span class="t">Welcome tour</span>
        <button class="btn btn-ghost btn-sm" id="replay-onb">Replay</button></div>
      <div style="margin-top:16px"><label style="font-size:11.5px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--muted)">Profile color</label>
      <div class="swatches" style="margin-top:10px">${COLORS.map((c)=>`<div class="sw ${ME.color===c?'sel':''}" data-color="${c}" style="background:${c}"></div>`).join('')}</div></div></div>
      <div class="panel"><h2 style="font-size:18px">Notifications</h2><div class="desc">Get a notification on your device when someone follows you, votes, likes or comments — even when LinkPoll is open in another tab.</div>
        <div class="set-row"><span class="ico">🔔</span><span class="t">Device notifications</span>
          <button class="toggle ${(notifySupported()&&Notification.permission==='granted')?'on':''}" id="notif-tg"></button></div>
        ${(notifySupported()&&Notification.permission==='denied')?'<div class="desc" style="color:var(--red);margin-top:4px">Notifications are blocked. Enable them for this site in your browser settings.</div>':''}
        ${!notifySupported()?'<div class="desc" style="margin-top:4px">This browser doesn\'t support notifications.</div>':''}</div>`;
    const nt=$('#notif-tg',p); if(nt) nt.onclick=async()=>{ const ok=await ensureNotifyPermission(true); nt.classList.toggle('on',ok); if(ok) toast('Notifications enabled'); };
    $('#dark-tg',p).onclick=(e)=>{ const on=!isDark(); setTheme(on); e.currentTarget.classList.toggle('on',on); };
    const rob=$('#replay-onb',p); if(rob) rob.onclick=()=>showOnboarding(true);
    $$('[data-color]',p).forEach((s)=>s.onclick=async()=>{ try{ const{user}=await api('/user/update',{method:'POST',body:{name:ME.name,username:ME.username,bio:ME.bio,location:ME.location,website:ME.website,color:s.dataset.color,avatar:ME.avatar||'',verified:ME.verified}}); ME=user; renderSettings(); chrome(); }catch(e){ toast(e.message); } });
  } else {
    p.innerHTML=`<div class="panel"><h2>Account</h2><div class="desc">Manage your account.</div>
      <div class="set-row" style="border-bottom:1px solid var(--line-2)"><span class="t">Username</span><span style="color:var(--muted)">@${esc(u.username)}</span></div>
      <div class="set-row"><span class="t">Email</span><span style="color:var(--muted)">${esc(u.email||'—')}</span></div></div>
      <div class="panel" style="border-color:#f3c9c9">
        <h2 style="color:var(--red)">Danger zone</h2><div class="desc">Deleting your account removes your profile, polls, votes and comments permanently. This can't be undone.</div>
        <button class="btn" style="background:#fde8e8;color:var(--red)" id="del-acc" style="padding:12px 22px">Delete my account</button></div>`;
    $('#del-acc',p).onclick=async()=>{
      if(!confirm('Permanently delete your account and all your polls?')) return;
      const pass=prompt('Confirm your password to delete your account:'); if(pass===null) return;
      try{ await api('/auth/delete',{method:'POST',body:{password:pass}}); ME=null; UNREAD=0; toast('Account deleted'); go('#/'); chrome(); }catch(e){ toast(e.message); }
    };
  }
}

/* =====================================================================
   CREATE POLL MODAL
   ===================================================================== */
const POLL_TYPES=[
  {id:'simple',icon:'📊',name:'Simple Poll',desc:'A question with your own options'},
  {id:'yesno',icon:'✅',name:'Yes / No',desc:'A quick two-choice question'},
  {id:'quiz',icon:'🎯',name:'Quiz',desc:'Mark the correct answer'},
  {id:'image',icon:'🖼️',name:'Image Poll',desc:'Let people vote with pictures'},
  {id:'rating',icon:'⭐',name:'Rating',desc:'Collect a 1–5 style rating'},
  {id:'thisorthat',icon:'⚔️',name:'This or That',desc:'Two options, head to head'},
  {id:'survey',icon:'📋',name:'Survey',desc:'Several questions in a row'},
];
let CREATE_DRAFT_ID=0, CREATE_DIRTY=false;
function openCreate(){ if(!ME){ openAuth(); return; } closeDrawer();
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim><div class="sheet create-choose">
    <div class="sheet-head"><h3>Create</h3><button class="close" data-x>✕</button></div>
    <button class="cc-opt" data-cc="poll"><span class="cc-ic">📊</span><span class="cc-tx"><b>Poll</b><small>Ask with options and see how people vote</small></span><span class="cc-ar">›</span></button>
    <button class="cc-opt" data-cc="faq"><span class="cc-ic">💬</span><span class="cc-tx"><b>Question</b><small>Ask anything — everyone can answer in the feed</small></span><span class="cc-ar">›</span></button>
  </div></div>`;
  $('[data-x]',ov).onclick=closeOverlay; $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget) closeOverlay(); };
  $('[data-cc="poll"]',ov).onclick=()=>{ closeOverlay(); openComposer('simple'); };
  $('[data-cc="faq"]',ov).onclick=()=>{ closeOverlay(); openFaqComposer(); };
}
function openTypePicker(){
  if(!ME){ openAuth(); return; }
  closeDrawer(); lockScroll(); CREATE_DRAFT_ID=0;
  const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim" data-scrim><div class="modal type-picker" data-keepmenu>
    <div class="mhead"><h3>Create a Poll</h3><button class="close" data-x>✕</button></div>
    <p class="tp-sub">Pick a type to start — you can change anything after.</p>
    <div class="type-grid">${POLL_TYPES.map((t)=>`<button class="type-card" data-type="${t.id}"><span class="tc-ico">${t.icon}</span><span class="tc-txt"><b>${t.name}</b><small>${t.desc}</small></span></button>`).join('')}</div>
    <button class="btn btn-ghost btn-sm" id="tp-drafts" style="display:none;width:100%;margin-top:10px"></button>
  </div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  $$('.type-card',ov).forEach((b)=>b.onclick=()=>openComposer(b.dataset.type));
  (async()=>{ try{ const d=await api('/drafts'); const b=$('#tp-drafts');
    if(b&&d.drafts&&d.drafts.length){ b.style.display=''; b.textContent='📝 Continue a draft ('+d.drafts.length+')'; b.onclick=()=>openComposer('simple',{openDrafts:true}); } }catch(e){} })();
}
function openComposer(CTYPE, copts){
  CTYPE=CTYPE||'simple'; copts=copts||{};
  if(!ME){ openAuth(); return; }
  lockScroll(); if(!copts.openDrafts) CREATE_DRAFT_ID=0; CREATE_DIRTY=false;
  const TYPE=POLL_TYPES.find((t)=>t.id===CTYPE)||POLL_TYPES[0];
  const perks=(ME&&ME.perks)||[]; const maxOpts=perks.includes('poll_options_8')?8:6; const canBg=perks.includes('poll_bg');
  const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim" data-scrim><div class="modal" data-keepmenu>
    <div class="mhead"><button class="comp-type" id="comp-back" title="Change poll type">${TYPE.icon} ${esc(TYPE.name)} <span class="ct-caret">▾</span></button><div class="mhead-actions"><button class="btn btn-ghost btn-sm" id="drafts-btn" style="display:none">📝 Drafts</button><button class="close" data-x>✕</button></div></div>
    ${TYPE.tip?`<div class="tc-tip">${TYPE.tip}</div>`:''}
    <div style="display:flex;justify-content:space-between;align-items:baseline"><span class="f-lab">Your Question</span><span class="cqcount" id="cqcount" style="font-size:12px;color:var(--soft)">0 / 280</span></div>
    <textarea class="field" id="cq" maxlength="280" placeholder="What do you want to ask? e.g. Tea or coffee?"></textarea>
    <span class="f-lab">Answer Options (2–${maxOpts})</span><div id="copts"></div>
    <button class="add-opt" id="cadd">+ Add option</button>

    <div class="more-wrap">
      <button type="button" class="more-toggle" id="more-toggle">⚙️ More options <span class="more-caret">▾</span></button>
      <div class="more-body" id="more-body" style="display:none">
        <span class="f-lab">Cover image (optional)</span>
        <div class="img-row"><div class="img-prev" id="img-prev"><span>No image</span></div>
          <div class="img-ctl"><input type="file" id="img-file" accept="image/*" hidden>
            <button class="btn btn-ghost btn-sm" id="img-up">${ic('plus',14)} Add image</button>
            <button class="btn btn-sm" id="img-rm" style="background:#fde8e8;color:var(--red);display:none">Remove</button></div></div>
        <span class="f-lab">Layout</span>
        <div class="layout-pick" id="clayout">
          <button type="button" class="lay-opt sel" data-lay="list">${ic('bars',15)} List</button>
          <button type="button" class="lay-opt" data-lay="grid">▦ Grid</button>
        </div>
        <div class="lay-hint" id="lay-hint">Options stack vertically. Great for text choices.</div>
        ${canBg?`<span class="f-lab">🎨 Poll background <span class="perk-tag">Pro</span></span>
          <div class="bg-picker" id="cbg"><button type="button" class="bg-sw sel" data-bg="" style="background:var(--card,#fff);border:1px solid var(--line)" title="None"></button>${['#fff4e6','#e6f4ff','#eafbe7','#fdeef4','#f0e9ff','#fff0f0','#151714'].map((c)=>`<button type="button" class="bg-sw" data-bg="${c}" style="background:${c}"></button>`).join('')}</div>`
          :`<div class="perk-lock">🎨 <b>Custom poll background</b> unlocks with more points. <a href="#/points" data-x>See perks →</a></div>`}
        <span class="f-lab">Duration</span>
        <select class="field" id="cdur">${DURATIONS.map(([l],i)=>`<option value="${i}" ${l==='24 hours'?'selected':''}>${l}</option>`).join('')}</select>
        <span class="f-lab">Post to community (optional)</span>
        <select class="field" id="ccomm"><option value="">— No community (public poll) —</option></select>
        <span class="f-lab">Settings</span>
        <label class="quiz-toggle"><input type="checkbox" id="cquiz"> <span>🎯 <b>Quiz mode</b> — mark one option as the correct answer</span></label>
        <div class="quiz-hint" id="quiz-hint" style="display:none">Tap the ◯ next to an option to set it as correct. Voters find out if they got it right.
          <textarea class="field" id="cquizexp" maxlength="500" rows="2" placeholder="Optional: explain the answer (shown after voting)" style="margin-top:8px"></textarea></div>
        ${setRow('multiple','Multiple selection',false)}
        ${setRow('anonymous','Anonymous voting',false)}
        <div class="adv-wrap">
          <button type="button" class="adv-toggle" id="adv-toggle">📋 Turn into a survey (add more questions) <span class="adv-caret">▾</span></button>
          <div class="adv-body" id="adv-body" style="display:none">
            <p class="adv-hint">After someone answers this question, the next one appears on the same card.</p>
            <div id="series-list"></div>
            <button type="button" class="add-opt" id="add-question">+ Add a question</button>
          </div>
        </div>
      </div>
    </div>

    <div class="comp-hint" id="comp-hint"></div>
    <div class="mfoot"><button class="btn btn-ghost btn-sm" id="save-draft">Save draft</button>
      <button class="btn btn-dark" id="post" disabled>${ic('rocket',16).replace('class="ico"','class="ico i-lime"')}<span style="color:var(--lime)">Post Poll</span></button></div>
  </div></div>`;
  const cq=$('#cq'); const autoGrow=()=>{ cq.style.height='auto'; cq.style.height=Math.min(cq.scrollHeight,220)+'px'; };
  cq.addEventListener('input',()=>{ $('#cqcount').textContent=cq.value.length+' / 280'; autoGrow(); validateComposer(); }); setTimeout(autoGrow,30);
  function validateComposer(){
    const qOk=((cq&&cq.value)||'').trim().length>0;
    const nOpts=$$('#copts .opt-edit input.field').filter((i)=>i.value.trim()).length;
    const quizOn=$('#cquiz')&&$('#cquiz').checked;
    const correctOk=!quizOn || $$('#copts .opt-edit').some((r)=>r.classList.contains('is-correct'));
    const ok=qOk && nOpts>=2 && correctOk;
    const post=$('#post'); if(post){ post.disabled=!ok; post.classList.toggle('is-disabled',!ok); }
    const hint=$('#comp-hint'); if(hint){ hint.textContent = ok ? '' :
      (!qOk ? 'Write your question to continue' : nOpts<2 ? 'Add at least 2 answer options' :
       !correctOk ? 'Tap the ◯ to mark the correct answer' : ''); }
    return ok;
  }
  const box=$('#copts'); const add=(v='',img='')=>{ if(box.children.length>=maxOpts){toast('Up to '+maxOpts+' options');return;}
    const r=document.createElement('div'); r.className='opt-edit'; r._img=img||'';
    r.innerHTML=`<button type="button" class="opt-correct" title="Mark as correct answer" aria-label="Mark correct">◯</button><span class="grip">⋮⋮</span><input class="field" maxlength="120" placeholder="Option ${box.children.length+1}" value="${esc(v)}">
      <button class="opt-img-btn" title="Add image">${ic('plus',13)}</button>
      <input type="file" accept="image/*" hidden><button class="rm">✕</button>`;
    const corr=r.querySelector('.opt-correct');
    corr.onclick=()=>{ box.querySelectorAll('.opt-edit').forEach((x)=>{ x.classList.remove('is-correct'); const c=x.querySelector('.opt-correct'); if(c)c.textContent='◯'; }); r.classList.add('is-correct'); corr.textContent='✓'; validateComposer(); };
    const fileIn=r.querySelector('input[type="file"]'); const imgBtn=r.querySelector('.opt-img-btn');
    const paint=()=>{ imgBtn.classList.toggle('has',!!r._img); imgBtn.innerHTML = r._img?`<img src="${esc(r._img)}" alt="">`:ic('plus',13); };
    imgBtn.onclick=()=>{ if(r._img){ r._img=''; paint(); toast('Option image removed'); } else fileIn.click(); };
    fileIn.onchange=(e)=>{ const f=e.target.files&&e.target.files[0]; if(!f)return; if(!/^image\//.test(f.type)){toast('Please choose an image');return;}
      if(f.size>8*1024*1024){toast('Image too large (max 8MB)');return;}
      const rd=new FileReader(); rd.onload=()=>downscaleImage(rd.result,600,(out)=>{ r._img=out; paint(); toast('Option image added'); }); rd.readAsDataURL(f); };
    r.querySelector('.rm').onclick=()=>{ if(box.children.length<=2){toast('At least 2 options');return;} r.remove(); }; paint(); box.appendChild(r); };
  add(); add();
  $('#cadd').onclick=()=>{ add(); validateComposer(); };
  $('#copts').addEventListener('input', validateComposer);
  $('#copts').addEventListener('click',(e)=>{ if(e.target.closest('.rm')) setTimeout(validateComposer,0); });
  let pendingImage='';
  const ovEl=ov; ovEl._getImg=()=>pendingImage;
  const setImg=()=>{ const el=$('#img-prev'); if(el){ el.classList.toggle('has',!!pendingImage); el.innerHTML=pendingImage?`<img src="${esc(pendingImage)}" alt="">`:'<span>No image</span>'; } const rm=$('#img-rm'); if(rm) rm.style.display=pendingImage?'':'none'; };
  $('#img-up').onclick=()=>$('#img-file').click();
  $('#img-file').onchange=(e)=>{ const f=e.target.files&&e.target.files[0]; if(!f)return; if(!/^image\//.test(f.type)){toast('Please choose an image');return;}
    if(f.size>10*1024*1024){toast('Image too large (max 10MB)');return;}
    const rd=new FileReader(); rd.onload=()=>downscaleImage(rd.result,1000,(out)=>{ pendingImage=out; setImg(); toast('Image added'); }); rd.readAsDataURL(f); };
  $('#img-rm').onclick=()=>{ pendingImage=''; setImg(); };
  // "More options" progressive disclosure
  const moreToggle=$('#more-toggle'), moreBody=$('#more-body');
  if(moreToggle){ moreToggle.onclick=()=>{ const open=moreBody.style.display==='none'; moreBody.style.display=open?'block':'none'; moreToggle.classList.toggle('open',open); }; }
  validateComposer();
  // ----- drafts -----
  function collectComposerData(){
    const opts=$$('#copts .opt-edit').map((r)=>({label:r.querySelector('input.field').value,image:r._img||''}));
    const series=[]; const sl=$('#series-list');
    if(sl){ [...sl.children].forEach((b)=>{ const rows=[...b.querySelectorAll('.sq-optrow')]; const chk=b.querySelector('.sq-quiz-chk');
      let ci=-1,fi=-1; rows.forEach((r)=>{ if(r.querySelector('.sq-opt').value.trim()){ fi++; if(r.classList.contains('is-correct')) ci=fi; } });
      series.push({question:b.querySelector('.sq-q').value,options:rows.map((r)=>r.querySelector('.sq-opt').value),isQuiz:!!(chk&&chk.checked),correctIndex:ci}); }); }
    return { type:CTYPE, question:$('#cq').value, options:opts, image:pendingImage||'', layout:pendingLayout, bgColor:pendingBg||'',
      durIdx:+($('#cdur')?$('#cdur').value:0), community:$('#ccomm')?$('#ccomm').value:'',
      isQuiz:!!($('#cquiz')&&$('#cquiz').checked), quizExplain:$('#cquizexp')?$('#cquizexp').value:'',
      correctIndex:$$('#copts .opt-edit').findIndex((r)=>r.classList.contains('is-correct')),
      multiple:$('[data-set="multiple"]')?.classList.contains('on')||false,
      anonymous:$('[data-set="anonymous"]')?.classList.contains('on')||false, series };
  }
  function composerHasContent(){ const d=collectComposerData(); return (d.question||'').trim().length>0 || d.options.some((o)=>(o.label||'').trim()); }
  async function saveDraftNow(silent){
    if(!composerHasContent()){ if(!silent) toast('Nothing to save yet'); return false; }
    try{ const r=await api('/drafts/save',{method:'POST',body:{id:CREATE_DRAFT_ID||undefined,data:collectComposerData()}});
      CREATE_DRAFT_ID=r.id; refreshDraftsBtn(); if(!silent) toast('Saved to drafts 📝'); return true; }
    catch(e){ if(!silent) toast(e.message); return false; }
  }
  function fillComposer(d){
    if(!d) return;
    $('#cq').value=d.question||''; $('#cqcount').textContent=($('#cq').value.length)+' / 280';
    box.innerHTML=''; (d.options&&d.options.length?d.options:[{},{}]).forEach((o)=>add(o.label||'',o.image||''));
    while(box.children.length<2) add();
    pendingImage=d.image||''; setImg();
    pendingLayout=d.layout||'list'; const lw=$('#clayout'); if(lw){ lw.querySelectorAll('.lay-opt').forEach((x)=>x.classList.toggle('sel',x.dataset.lay===pendingLayout)); }
    pendingBg=d.bgColor||''; const bw=$('#cbg'); if(bw){ bw.querySelectorAll('.bg-sw').forEach((x)=>x.classList.toggle('sel',(x.dataset.bg||'')===pendingBg)); }
    if($('#cdur')&&typeof d.durIdx==='number') $('#cdur').value=d.durIdx;
    if(d.multiple){ const t=$('[data-set="multiple"]'); if(t) t.classList.add('on'); }
    if(d.anonymous){ const t=$('[data-set="anonymous"]'); if(t) t.classList.add('on'); }
    const hasAdvanced = d.isQuiz || (d.series&&d.series.length) || d.bgColor || d.image || d.multiple || d.anonymous;
    if(hasAdvanced){ moreBody.style.display='block'; moreToggle.classList.add('open'); }
    if(d.isQuiz){ const cq2=$('#cquiz'); if(cq2){ cq2.checked=true; if(cq2.onchange) cq2.onchange(); }
      if(d.correctIndex>=0){ const rr=$$('#copts .opt-edit')[d.correctIndex]; if(rr){ rr.classList.add('is-correct'); const c=rr.querySelector('.opt-correct'); if(c)c.textContent='✓'; } }
      if($('#cquizexp')) $('#cquizexp').value=d.quizExplain||''; }
    if(d.series&&d.series.length){ advBody.style.display='block'; advToggle.classList.add('open');
      d.series.forEach((sq)=>{ addQuestionBlock(); const blk=seriesList.lastElementChild;
        blk.querySelector('.sq-q').value=sq.question||'';
        const rows=blk.querySelector('.sq-opts'); rows.innerHTML='';
        (sq.options&&sq.options.length?sq.options:['','']).forEach((ov,i)=>{ const wrap=document.createElement('div'); wrap.className='sq-optrow';
          wrap.innerHTML=`<button type="button" class="opt-correct sq-correct">◯</button><input class="field sq-opt" maxlength="120" placeholder="Option ${i+1}" value="${esc(ov||'')}">`;
          rows.appendChild(wrap); });
        blk.querySelectorAll('.sq-correct').forEach((bb)=>bb.onclick=()=>{ blk.querySelectorAll('.sq-correct').forEach((c)=>c.textContent='◯'); blk.querySelectorAll('.sq-optrow').forEach((rr)=>rr.classList.remove('is-correct')); bb.textContent='✓'; bb.closest('.sq-optrow').classList.add('is-correct'); blk.querySelector('.sq-quiz-chk').checked=true; blk.classList.add('sq-quizon'); });
        if(sq.isQuiz){ blk.querySelector('.sq-quiz-chk').checked=true; blk.classList.add('sq-quizon');
          const corrs=[...blk.querySelectorAll('.sq-optrow')]; if(sq.correctIndex>=0&&corrs[sq.correctIndex]){ corrs[sq.correctIndex].classList.add('is-correct'); corrs[sq.correctIndex].querySelector('.sq-correct').textContent='✓'; } }
      }); }
    if(typeof validateComposer==='function') validateComposer(); autoGrow();
  }
  async function refreshDraftsBtn(){ try{ const d=await api('/drafts'); const b=$('#drafts-btn');
    if(b){ if(d.drafts&&d.drafts.length){ b.style.display=''; b.textContent='📝 Drafts ('+d.drafts.length+')'; } else b.style.display='none'; } }catch(e){} }
  async function openDraftsList(){
    let d; try{ d=await api('/drafts'); }catch(e){ toast(e.message); return; }
    if(!d.drafts||!d.drafts.length){ toast('No saved drafts yet'); return; }
    const ov2=$('#overlay');
    ov2.insertAdjacentHTML('beforeend',`<div class="scrim sheet-scrim" id="drafts-sheet" data-keepmenu><div class="sheet"><div class="sheet-grab"></div>
      <div class="sheet-head"><h3>📝 Your drafts</h3><button class="close" id="dx">✕</button></div>
      <div class="draft-list">${d.drafts.map((dr)=>`<div class="draft-item" data-did="${dr.id}"><div class="draft-info"><div class="draft-q">${esc(dr.question||'(untitled)')}</div><div class="draft-meta">${dr.optionCount} option${dr.optionCount===1?'':'s'} · ${timeAgo(dr.updatedAt*1000)}</div></div><button class="draft-del" data-ddel="${dr.id}" aria-label="Delete">🗑</button></div>`).join('')}</div></div></div>`);
    const sheet=$('#drafts-sheet'); const closeSheet=()=>sheet.remove();
    $('#dx').onclick=closeSheet; sheet.onclick=(e)=>{ if(e.target===sheet) closeSheet(); };
    sheet.querySelectorAll('[data-ddel]').forEach((b)=>b.onclick=async(e)=>{ e.stopPropagation();
      try{ await api('/drafts/delete',{method:'POST',body:{id:+b.dataset.ddel}}); b.closest('.draft-item').remove(); refreshDraftsBtn(); if(!sheet.querySelector('.draft-item')) closeSheet(); }catch(err){ toast(err.message); } });
    sheet.querySelectorAll('.draft-item').forEach((it)=>it.onclick=()=>{
      const dr=d.drafts.find((x)=>x.id===+it.dataset.did); if(dr){ CREATE_DRAFT_ID=dr.id; fillComposer(dr.data); toast('Draft loaded'); } closeSheet(); });
  }
  const draftsBtn=$('#drafts-btn'); if(draftsBtn) draftsBtn.onclick=openDraftsList;
  const saveBtn=$('#save-draft'); if(saveBtn) saveBtn.onclick=()=>saveDraftNow(false);
  refreshDraftsBtn();
  const closeWithSave=()=>{ if(composerHasContent()){ saveDraftNow(true).then((ok)=>{ if(ok) toast('Saved to drafts 📝'); }); } closeOverlay(); };
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeWithSave);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget) closeWithSave(); };
  $$('.toggle',ov).forEach((t)=>t.onclick=()=>t.classList.toggle('on'));
  $('#post').onclick=postPoll;
  let pendingLayout='list'; ovEl._getLayout=()=>pendingLayout;
  const quizChk=$('#cquiz');
  if(quizChk){ quizChk.onchange=()=>{ const on=quizChk.checked; ovEl.classList.toggle('quiz-on',on);
    $('#quiz-hint').style.display=on?'block':'none'; validateComposer(); }; }
  // Advanced: multi-question survey
  const advToggle=$('#adv-toggle'), advBody=$('#adv-body'), seriesList=$('#series-list');
  if(advToggle){ advToggle.onclick=()=>{ const open=advBody.style.display==='none'; advBody.style.display=open?'block':'none'; advToggle.classList.toggle('open',open); }; }
  function addQuestionBlock(){
    if(seriesList.children.length>=9){ toast('Up to 10 questions total'); return; }
    const idx=seriesList.children.length+2;
    const block=document.createElement('div'); block.className='sq-block';
    const optRow=(n)=>`<div class="sq-optrow"><button type="button" class="opt-correct sq-correct" title="Mark correct">◯</button><input class="field sq-opt" maxlength="120" placeholder="Option ${n}"></div>`;
    block.innerHTML=`<div class="sq-head"><b>Question ${idx}</b><button type="button" class="sq-del" aria-label="Remove">✕</button></div>
      <input class="field sq-q" maxlength="280" placeholder="Question ${idx}…">
      <div class="sq-opts">${optRow(1)}${optRow(2)}</div>
      <button type="button" class="sq-addopt">+ Add option</button>
      <label class="sq-quiz"><input type="checkbox" class="sq-quiz-chk"> 🎯 Make this a quiz (tap ◯ to mark the correct answer)</label>`;
    const mark=(btn)=>{ block.querySelectorAll('.sq-correct').forEach((c)=>c.textContent='◯'); block.querySelectorAll('.sq-optrow').forEach((r)=>r.classList.remove('is-correct')); btn.textContent='✓'; btn.closest('.sq-optrow').classList.add('is-correct'); const chk=block.querySelector('.sq-quiz-chk'); chk.checked=true; block.classList.add('sq-quizon'); };
    block.querySelectorAll('.sq-correct').forEach((b)=>b.onclick=()=>mark(b));
    block.querySelector('.sq-quiz-chk').onchange=(e)=>{ block.classList.toggle('sq-quizon',e.target.checked); if(!e.target.checked){ block.querySelectorAll('.sq-correct').forEach((c)=>c.textContent='◯'); block.querySelectorAll('.sq-optrow').forEach((r)=>r.classList.remove('is-correct')); } };
    block.querySelector('.sq-del').onclick=()=>{ block.remove(); renumberBlocks(); };
    block.querySelector('.sq-addopt').onclick=()=>{ const opts=block.querySelector('.sq-opts');
      if(opts.children.length>=6){ toast('Up to 6 options'); return; }
      const wrap=document.createElement('div'); wrap.className='sq-optrow';
      wrap.innerHTML=`<button type="button" class="opt-correct sq-correct" title="Mark correct">◯</button><input class="field sq-opt" maxlength="120" placeholder="Option ${opts.children.length+1}">`;
      wrap.querySelector('.sq-correct').onclick=()=>mark(wrap.querySelector('.sq-correct')); opts.appendChild(wrap); };
    seriesList.appendChild(block);
  }
  function renumberBlocks(){ [...seriesList.children].forEach((b,i)=>{ const t=b.querySelector('.sq-head b'); if(t) t.textContent='Question '+(i+2); }); }
  const addQ=$('#add-question'); if(addQ) addQ.onclick=addQuestionBlock;
  const layWrap=$('#clayout'); if(layWrap){ layWrap.querySelectorAll('.lay-opt').forEach((b)=>b.onclick=()=>{
    layWrap.querySelectorAll('.lay-opt').forEach((x)=>x.classList.remove('sel')); b.classList.add('sel'); pendingLayout=b.dataset.lay;
    const h=$('#lay-hint'); if(h) h.textContent=pendingLayout==='grid'?'Options show as tiles in a 2-column grid. Perfect for photos or short answers.':'Options stack vertically. Great for text choices.'; }); }
  let pendingBg=''; ovEl._getBg=()=>pendingBg;
  const bgWrap=$('#cbg'); if(bgWrap){ bgWrap.querySelectorAll('.bg-sw').forEach((b)=>b.onclick=()=>{
    bgWrap.querySelectorAll('.bg-sw').forEach((x)=>x.classList.remove('sel')); b.classList.add('sel'); pendingBg=b.dataset.bg; }); }
  // ---- apply the chosen poll type ----
  const setLayout=(lay)=>{ pendingLayout=lay; const lw=$('#clayout'); if(lw){ lw.querySelectorAll('.lay-opt').forEach((x)=>x.classList.toggle('sel',x.dataset.lay===lay)); } const h=$('#lay-hint'); if(h) h.textContent=lay==='grid'?'Options show as tiles in a 2-column grid. Perfect for photos or short answers.':'Options stack vertically. Great for text choices.'; };
  const setOpts=(vals)=>{ box.innerHTML=''; vals.forEach((v)=>add(v)); while(box.children.length<2) add(); };
  const cqEl=$('#cq');
  if(CTYPE==='yesno'){ setOpts(['Yes','No']); if(cqEl)cqEl.placeholder='Ask a yes-or-no question…'; }
  else if(CTYPE==='rating'){ setOpts(['😞 Poor','😐 Okay','🙂 Good','😊 Great','😍 Amazing']); setLayout('grid'); if(cqEl)cqEl.placeholder='What should people rate?'; }
  else if(CTYPE==='thisorthat'){ setOpts(['','']); setLayout('grid'); const ins=$$('#copts .opt-edit input.field'); if(ins[0])ins[0].placeholder='This'; if(ins[1])ins[1].placeholder='That'; if(cqEl)cqEl.placeholder='This or that? Ask away…'; }
  else if(CTYPE==='image'){ setLayout('grid'); if(cqEl)cqEl.placeholder='Ask people to pick a picture…'; moreBody.style.display='block'; moreToggle.classList.add('open'); setTimeout(()=>toast('Tip: tap ➕ on an option to add a picture'),300); }
  else if(CTYPE==='quiz'){ const cq=$('#cquiz'); if(cq){ cq.checked=true; if(cq.onchange) cq.onchange(); } moreBody.style.display='block'; moreToggle.classList.add('open'); if(cqEl)cqEl.placeholder='Ask a question with one correct answer…'; }
  else if(CTYPE==='survey'){ moreBody.style.display='block'; moreToggle.classList.add('open'); advBody.style.display='block'; advToggle.classList.add('open'); if(seriesList && !seriesList.children.length) addQuestionBlock(); if(cqEl)cqEl.placeholder='Your first question…'; }
  const backBtn=$('#comp-back'); if(backBtn) backBtn.onclick=()=>{ if(composerHasContent()) saveDraftNow(true); openTypePicker(); };
  setTimeout(()=>{ const c=$('#cq'); if(c&&!copts.openDrafts&&!copts.draft) c.focus(); },60);
  if(copts.openDrafts) openDraftsList();
  if(copts.draft && copts.draft.data){ CREATE_DRAFT_ID=copts.draft.id; fillComposer(copts.draft.data); }
  // populate communities the user can post to
  (async()=>{ try{ const d=await api('/communities'); const sel=$('#ccomm'); if(!sel) return;
    const mine=(d.mine||[]).filter((c)=>c.myStatus==='approved'||c.isCreator);
    if(mine.length){ sel.innerHTML='<option value="">— No community (public poll) —</option>'+mine.map((c)=>`<option value="${esc(c.slug)}">${esc(c.name)}${c.privacy==='private'?' 🔒':''}</option>`).join('');
      if(openCreate._prefill){ sel.value=openCreate._prefill; openCreate._prefill=null; } }
  }catch(e){} })();
}
function setRow(key,label,on){ return `<div class="set-row"><span class="t">${label}</span><button class="toggle ${on?'on':''}" data-set="${key}"></button></div>`; }
async function postPoll(){
  const q=$('#cq').value.trim();
  const rows=$$('#copts .opt-edit');
  const opts=rows.map((r)=>({label:r.querySelector('input.field').value.trim(), image:r._img||''})).filter((o)=>o.label);
  if(q.length<3){toast('Write a question first');return;} if(opts.length<2){toast('Add at least 2 options');return;}
  const get=(k)=>$(`[data-set="${k}"]`).classList.contains('on');
  const durIdx=+$('#cdur').value; const durationH=DURATIONS[durIdx][1];
  const btn=$('#post'); btn.disabled=true;
  const image=($('#overlay')&&$('#overlay')._getImg)?$('#overlay')._getImg():'';
  const communitySlug=$('#ccomm')?$('#ccomm').value:'';
  const bgColor=($('#overlay')&&$('#overlay')._getBg)?$('#overlay')._getBg():'';
  const layout=($('#overlay')&&$('#overlay')._getLayout)?$('#overlay')._getLayout():'list';
  const isQuiz=!!($('#cquiz')&&$('#cquiz').checked);
  let correctIndex=-1;
  if(isQuiz){ const kept=rows.filter((r)=>r.querySelector('input.field').value.trim());
    correctIndex=kept.findIndex((r)=>r.classList.contains('is-correct'));
    if(correctIndex<0){ toast('Tap ◯ to mark the correct answer'); btn.disabled=false; return; } }
  // multi-question survey (advanced)
  let series=[];
  const seriesList=$('#series-list');
  if(seriesList){ [...seriesList.children].forEach((b)=>{
    const qv=b.querySelector('.sq-q').value.trim();
    const rows=[...b.querySelectorAll('.sq-optrow')];
    const ov=rows.map((r)=>r.querySelector('.sq-opt').value.trim());
    const filled=ov.filter(Boolean);
    if(qv.length>=3 && filled.length>=2){
      const q={question:qv,options:filled};
      const chk=b.querySelector('.sq-quiz-chk');
      if(chk && chk.checked){
        // correct index among the FILLED options
        let ci=-1, fi=-1;
        rows.forEach((r)=>{ const val=r.querySelector('.sq-opt').value.trim(); if(val){ fi++; if(r.classList.contains('is-correct')) ci=fi; } });
        if(ci>=0){ q.isQuiz=true; q.correctIndex=ci; }
      }
      series.push(q);
    }
  }); }
  try{ const{poll}=await api('/polls',{method:'POST',body:{question:q,options:opts,multiple:isQuiz?false:get('multiple'),anonymous:get('anonymous'),durationH,image,communitySlug,bgColor,layout,isQuiz,correctIndex,quizExplain:isQuiz&&$('#cquizexp')?$('#cquizexp').value.trim():'',series,draftId:CREATE_DRAFT_ID||0}});
    CREATE_DRAFT_ID=0; CREATE_DIRTY=false; invalidateViews(true); closeOverlay(); toast('Poll posted! 🚀'); go('#/poll/'+poll.code); }
  catch(e){ toast(e.message); btn.disabled=false; }
}

/* ---------- edit poll (owner: before first vote, or within a short window) ---------- */
function editLeftText(poll){ const ms=poll.editUntil-Date.now(); if(ms<=0)return'';
  const m=Math.floor(ms/60000), s=Math.floor(ms%60000/1000); return m>0?`${m}m left`:`${s}s left`; }
function openEditPoll(code){
  const poll=POLLS[code]; if(!poll){ return; }
  if(!ME || !poll.author.isMe){ toast('You can only edit your own polls.'); return; }
  if(!poll.canEdit){ toast('This poll has votes and the edit window has closed.'); return; }
  closeMenus(); closeDrawer(); lockScroll();
  const locked=poll.optionsLocked;            // options change only before the first vote
  const noVotes=poll.noVotes;                 // editable freely until the first vote
  const note = noVotes
    ? `${ic('eye',13)} No votes yet — you can freely edit everything until the first vote comes in.`
    : `${ic('clock',13)} This poll already has votes — you can still fix the question, image & settings for <b id="elt">${esc(editLeftText(poll))}</b>.`;
  const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim" data-scrim><div class="modal" data-keepmenu>
    <div class="mhead"><h3>Edit Poll</h3><button class="close" data-x>✕</button></div>
    <div class="edit-note ${noVotes?'':'warn'}">${note}</div>
    <div style="display:flex;justify-content:space-between;align-items:baseline"><span class="f-lab">Your Question</span><span class="cqcount" id="cqcount" style="font-size:12px;color:var(--soft)">${poll.question.length} / 280</span></div>
    <textarea class="field" id="cq" maxlength="280">${esc(poll.question)}</textarea>
    <span class="f-lab">Cover image (optional)</span>
    <div class="img-row"><div class="img-prev ${poll.image?'has':''}" id="img-prev">${poll.image?`<img src="${esc(poll.image)}" alt="">`:'<span>No image</span>'}</div>
      <div class="img-ctl"><input type="file" id="img-file" accept="image/*" hidden>
        <button class="btn btn-ghost btn-sm" id="img-up">${ic('plus',14)} ${poll.image?'Change':'Add'} image</button>
        ${poll.image?'<button class="btn btn-sm" id="img-rm" style="background:#fde8e8;color:var(--red)">Remove</button>':''}</div></div>
    <span class="f-lab">Answer Options (2–6)</span>
    ${locked?`<div class="edit-note warn">${ic('lock',13)} Options are locked because this poll already has votes.</div>`:''}
    <div id="copts"></div>
    ${locked?'':'<button class="add-opt" id="cadd">+ Add option</button>'}
    <span class="f-lab">Settings</span>
    ${setRow('multiple','Multiple selection',poll.multiple)}
    ${setRow('anonymous','Anonymous voting',poll.anonymous)}
    <div class="mfoot"><button class="btn btn-ghost" data-x>Cancel</button>
      <button class="btn btn-dark" id="psave"><span style="color:var(--lime)">✓ Save changes</span></button></div>
  </div></div>`;
  const cq=$('#cq'); cq.addEventListener('input',()=>{ $('#cqcount').textContent=cq.value.length+' / 280'; });
  let pendingImage=poll.image||'';
  const setImg=()=>{ const el=$('#img-prev'); if(el){ el.classList.toggle('has',!!pendingImage); el.innerHTML=pendingImage?`<img src="${esc(pendingImage)}" alt="">`:'<span>No image</span>'; } };
  $('#img-up').onclick=()=>$('#img-file').click();
  $('#img-file').onchange=(e)=>{ const f=e.target.files&&e.target.files[0]; if(!f)return; if(!/^image\//.test(f.type)){toast('Please choose an image');return;}
    if(f.size>10*1024*1024){toast('Image too large (max 10MB)');return;}
    const rd=new FileReader(); rd.onload=()=>downscaleImage(rd.result,1000,(out)=>{ pendingImage=out; setImg(); toast('Image ready — Save to apply'); }); rd.readAsDataURL(f); };
  if($('#img-rm')) $('#img-rm').onclick=()=>{ pendingImage=''; setImg(); };
  const box=$('#copts'); const add=(v='',img='')=>{ if(box.children.length>=6){toast('Up to 6 options');return;}
    const r=document.createElement('div'); r.className='opt-edit'; r._img=img||'';
    r.innerHTML=`<span class="grip">⋮⋮</span><input class="field" maxlength="120" placeholder="Option ${box.children.length+1}" value="${esc(v)}" ${locked?'disabled':''}>
      ${locked?'':`<button class="opt-img-btn ${img?'has':''}" title="Add image">${img?`<img src="${esc(img)}" alt="">`:ic('plus',13)}</button><input type="file" accept="image/*" hidden><button class="rm">✕</button>`}`;
    if(!locked){
      const fileIn=r.querySelector('input[type="file"]'); const imgBtn=r.querySelector('.opt-img-btn');
      const paint=()=>{ imgBtn.classList.toggle('has',!!r._img); imgBtn.innerHTML = r._img?`<img src="${esc(r._img)}" alt="">`:ic('plus',13); };
      imgBtn.onclick=()=>{ if(r._img){ r._img=''; paint(); toast('Option image removed'); } else fileIn.click(); };
      fileIn.onchange=(e)=>{ const f=e.target.files&&e.target.files[0]; if(!f)return; if(!/^image\//.test(f.type)){toast('Please choose an image');return;}
        if(f.size>8*1024*1024){toast('Image too large (max 8MB)');return;}
        const rd=new FileReader(); rd.onload=()=>downscaleImage(rd.result,600,(out)=>{ r._img=out; paint(); toast('Option image added'); }); rd.readAsDataURL(f); };
      r.querySelector('.rm').onclick=()=>{ if(box.children.length<=2){toast('At least 2 options');return;} r.remove(); };
    }
    box.appendChild(r); };
  poll.options.forEach((o)=>add(o.label, o.image||'')); if(!poll.options.length){ add(); add(); }
  if(!locked && $('#cadd')) $('#cadd').onclick=()=>add();
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  $$('.toggle',ov).forEach((t)=>t.onclick=()=>t.classList.toggle('on'));
  // countdown only matters when a vote already exists (windowed edit)
  let tick=null;
  if(!noVotes){ tick=setInterval(()=>{ const el=$('#elt'); if(!el){ clearInterval(tick); return; }
    const t=editLeftText(poll); if(!t){ clearInterval(tick); closeOverlay(); toast('Edit window closed'); } else el.textContent=t; },1000); }
  $('#psave').onclick=async()=>{
    const q=$('#cq').value.trim(); if(q.length<3){toast('Write a question first');return;}
    const get=(k)=>$(`[data-set="${k}"]`).classList.contains('on');
    const payload={pollId:poll.id,question:q,multiple:get('multiple'),anonymous:get('anonymous'),image:pendingImage};
    if(!locked) payload.options=$$('#copts .opt-edit').map((r)=>({label:r.querySelector('input.field').value.trim(), image:r._img||''})).filter((o)=>o.label);
    const btn=$('#psave'); btn.disabled=true;
    try{ const{poll:fresh}=await api('/poll/edit',{method:'POST',body:payload}); if(tick)clearInterval(tick);
      POLLS[code]&&Object.assign(POLLS[code],fresh); closeOverlay();
      if(route==='poll') renderDetail(code); else replaceCard(POLLS[code]);
      toast('Poll updated'); }
    catch(e){ toast(e.message); btn.disabled=false; }
  };
}
/* ---------- report content ---------- */
/* ---------- hashtag feed ---------- */
/* ---------- direct messages: inbox + chat ---------- */

let CHAT_TIMER=null, CHAT_USER=null, CHATPOLL=null;
function stopChatPoll(){ if(CHAT_TIMER){ clearInterval(CHAT_TIMER); CHAT_TIMER=null; } if(CHATPOLL){ clearInterval(CHATPOLL); CHATPOLL=null; } }

let INBOX_TAB='chats';
async function renderInbox(){
  stopChatPoll(); CHAT_USER=null; chrome();
  if(!ME){ setMain(`<div class="page-title">Messages</div>${empt('💬','Sign in to message people.',true)}`); return; }
  setMain(`<div class="inbox-head"><button class="iconbtn msg-back" data-homeback aria-label="Back">←</button><div class="page-title" style="margin:0">Messages</div>
      <button class="btn btn-dark btn-sm" id="newgroup">${ic('users',15)} New group</button></div>
    <div class="tabs msg-tabs"><button data-mt="chats" class="${INBOX_TAB==='chats'?'active':''}">Chats</button>
      <button data-mt="groups" class="${INBOX_TAB==='groups'?'active':''}">Groups</button>
      <button data-mt="requests" class="${INBOX_TAB==='requests'?'active':''}">Requests</button></div>
    <div id="inbox">${skList(4)}</div>`);
  $('#newgroup').onclick=openCreateGroup;
  const hb=$('[data-homeback]'); if(hb) hb.onclick=()=>go('#/');
  $$('[data-mt]').forEach((b)=>b.onclick=()=>{ INBOX_TAB=b.dataset.mt; renderInbox(); });
  let data,groups=[]; try{ data=await api('/messages/threads'); }catch(e){ const b=$('#inbox'); if(b)b.innerHTML=empt('⚠️',e.message); return; }
  try{ groups=(await api('/groups')).groups; }catch(e){}
  if(route!=='messages' || pathToParts(location.pathname)[1]) return;
  MSG_UNREAD=data.unreadMessages; renderSide(); renderTabbar(); renderTopbar();
  const box=$('#inbox'); if(!box) return;
  const reqs=data.threads.filter((t)=>t.isRequest);
  const chats=data.threads.filter((t)=>!t.isRequest);
  // update Requests tab count
  const rtab=$('[data-mt="requests"]'); if(rtab && reqs.length) rtab.innerHTML=`Requests <span class="mt-badge">${reqs.length}</span>`;
  if(INBOX_TAB==='requests'){
    box.innerHTML = reqs.length ? reqs.map((t)=>{ const u=t.user;
      return `<div class="thread req-thread">${avatar(u,'s48',false)}
        <a class="th-main" href="#/messages/${esc(u.username)}"><div class="th-top"><b>${esc(u.name)}${vbadge(u.verified)}</b><span class="th-time">${timeAgo(t.last.createdAt)}</span></div>
          <div class="th-last">${esc(t.last.body.slice(0,90))}</div></a>
        <div class="req-actions"><button class="btn btn-sm btn-dark" data-acc="${esc(u.username)}">Accept</button>
          <button class="btn btn-sm btn-ghost" data-del="${esc(u.username)}">Delete</button></div></div>`;
    }).join('') : empt('📨','No message requests.');
    box.querySelectorAll('[data-acc]').forEach((b)=>b.onclick=async()=>{ try{ await api('/messages/accept',{method:'POST',body:{username:b.dataset.acc}}); go('#/messages/'+b.dataset.acc); }catch(e){ toast(e.message); } });
    box.querySelectorAll('[data-del]').forEach((b)=>b.onclick=async()=>{ if(!confirm('Delete this request?'))return; try{ await api('/messages/delete-thread',{method:'POST',body:{username:b.dataset.del}}); renderInbox(); }catch(e){ toast(e.message); } });
    return;
  }
  if(INBOX_TAB==='groups'){
    box.innerHTML = groups.length ? groups.map((g)=>`<a class="thread ${g.unread?'unread':''}" href="#/messages/g/${g.id}">
        <span class="av s48 grp-av">${g.avatar?`<img src="${esc(g.avatar)}" alt="">`:ic('users',20)}</span>
        <div class="th-main"><div class="th-top"><b>${esc(g.name)}</b><span class="th-grouptag">${fmt(g.members)} members</span><span class="th-time">${g.last?timeAgo(g.last.createdAt):''}</span></div>
          <div class="th-last">${g.last?esc(g.last.body.slice(0,90)):'No messages yet'}</div></div>
        ${g.unread?`<span class="th-dot">${g.unread}</span>`:''}</a>`).join('')
      : `<div class="net-empty"><div class="ne-ic">👥</div><h3>No groups yet</h3><p>Create a group to chat with several people at once.</p><button class="btn btn-dark btn-sm" id="ng2">New group</button></div>`;
    const ng2=$('#ng2'); if(ng2) ng2.onclick=openCreateGroup;
    return;
  }
  // chats: accepted DMs only (groups live in their own tab)
  const items=chats.map((t)=>({kind:'dm',t,sort:t.lastId})).sort((a,b)=>b.sort-a.sort);
  if(!items.length){ box.innerHTML=empt('💬','No conversations yet. Open someone’s profile and tap Message, or start a group.'); return; }
  box.innerHTML=items.map((it)=>{
    const t=it.t, u=t.user;
    return `<a class="thread ${t.unread?'unread':''}" href="#/messages/${esc(u.username)}">
      ${avatar(u,'s48',false)}
      <div class="th-main"><div class="th-top"><b>${esc(u.name)}${vbadge(u.verified)}</b><span class="th-time">${timeAgo(t.last.createdAt)}</span></div>
        <div class="th-last">${t.last.fromMe?'<span class="th-you">You: </span>':''}${esc(t.last.body.slice(0,90))}</div></div>
      ${t.unread?`<span class="th-dot">${t.unread}</span>`:''}</a>`;
  }).join('');
}
async function openCreateGroup(){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim" data-scrim><div class="modal" data-keepmenu style="max-width:460px">
    <div class="mhead"><h3>New group</h3><button class="close" data-x>✕</button></div>
    <div class="g-iconrow">
      <label class="g-iconpick" id="g-iconpick"><span id="g-iconprev" class="g-iconprev">${ic('users',22)}</span><span class="g-iconedit">＋</span>
        <input type="file" id="g-icon" accept="image/*" hidden></label>
      <div style="flex:1"><span class="f-lab" style="margin-top:0">Group name</span>
        <input class="field" id="g-name" maxlength="80" placeholder="e.g. Weekend Trip"></div>
    </div>
    <span class="f-lab">Description <span style="color:var(--soft);font-weight:400">(optional)</span></span>
    <textarea class="field" id="g-desc" rows="2" maxlength="300" placeholder="What's this group about?"></textarea>
    <span class="f-lab">Add people you follow</span>
    <div id="g-people" class="g-people">${skList(2)}</div>
    <div class="mfoot"><button class="btn btn-ghost" data-x>Cancel</button>
      <button class="btn btn-dark" id="g-create">Create group</button></div></div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  let iconData='';
  $('#g-icon').onchange=(e)=>{ const f=e.target.files[0]; if(!f)return; const rd=new FileReader(); rd.onload=()=>downscaleImage(rd.result,400,(url)=>{ iconData=url; $('#g-iconprev').innerHTML=`<img src="${url}" alt="">`; }); rd.readAsDataURL(f); };
  const picked=new Set();
  try{ const {users}=await api('/following',{query:{username:ME.username}}); const box=$('#g-people');
    box.innerHTML = users.length ? users.map((u)=>`<button class="g-pick" data-u="${esc(u.username)}">${avatar(u,'s34',false)}<span>${esc(u.name)}</span><span class="g-check">✓</span></button>`).join('')
      : `<p class="empty" style="padding:14px">Follow people first to add them to a group.</p>`;
    box.querySelectorAll('[data-u]').forEach((b)=>b.onclick=()=>{ const un=b.dataset.u; if(picked.has(un)){picked.delete(un);b.classList.remove('on');}else{picked.add(un);b.classList.add('on');} });
  }catch(e){ $('#g-people').innerHTML=empt('⚠️',e.message); }
  $('#g-create').onclick=async()=>{
    const name=$('#g-name').value.trim(); if(name.length<2){ toast('Give the group a name'); return; }
    if(!picked.size){ toast('Add at least one person'); return; }
    const btn=$('#g-create'); btn.disabled=true;
    try{ const{group}=await api('/groups',{method:'POST',body:{name,description:$('#g-desc').value.trim(),avatar:iconData,members:[...picked]}}); closeOverlay(); go('#/messages/g/'+group.id); }
    catch(e){ toast(e.message); btn.disabled=false; }
  };
}

let CHAT_ATTACH=null; // {id,code,question} pending poll attachment
async function renderChat(username){
  stopChatPoll(); CHAT_USER=username; GROUP_ID=null; CHAT_ATTACH=null; chrome();
  if(!ME){ openAuth(); return; }
  setMain(`<div class="chat-wrap">
    <div class="chat-head"><button class="iconbtn cback" data-back aria-label="Back">←</button><div id="chat-peer" class="chat-peer">…</div>
      <button class="iconbtn" id="chat-more" aria-label="More" data-keepmenu>⋯</button></div>
    <div class="chat-body" id="chat-body">${skList(3)}</div>
    <div id="chat-banner"></div>
    <div id="chat-attach"></div>
    <div class="chat-input">
      <button class="chat-attach-btn" id="chat-poll" aria-label="Send a poll" title="Send a poll">${ic('bars',18)}</button>
      <textarea id="chat-text" rows="1" placeholder="Message…" maxlength="2000"></textarea>
      <button class="chat-send" id="chat-send" aria-label="Send">${ic('send',18)}</button></div></div>`);
  $('[data-back]').onclick=()=>{ stopChatPoll(); go('#/messages'); };
  let data; try{ data=await api('/messages/with',{query:{username}}); }
  catch(e){ setMain(`<button class="back-btn" data-back>← Back</button>${empt('🙈',e.message)}`); $('[data-back]').onclick=()=>go('#/messages'); return; }
  if(route!=='messages' || CHAT_USER!==username) return;
  const peer=data.user;
  $('#chat-peer').innerHTML=`<span data-author="${esc(peer.username)}" style="display:flex;align-items:center;gap:9px;cursor:pointer">${avatar(peer,'s34',false)}<span class="cp-name"><b>${esc(peer.name)}${vbadge(peer.verified)}</b><span class="cp-handle">@${esc(peer.username)}</span></span></span>`;
  $('#chat-more').onclick=(e)=>openChatMenu(username, e.currentTarget);
  if(data.isRequest){ $('#chat-banner').innerHTML=`<div class="chat-req">💬 <b>@${esc(username)}</b> wants to message you. Replying accepts the conversation.
    <button class="btn btn-sm btn-ghost" id="req-block">Block</button></div>`;
    $('#req-block').onclick=()=>blockFromChat(username); }
  paintChat(data.messages);
  MSG_UNREAD=data.unreadMessages; renderSide(); renderTabbar(); renderTopbar();
  data.messages.forEach((m)=>{ if(!m.fromMe && m.id>MSG_SEEN) MSG_SEEN=m.id; });
  const ta=$('#chat-text');
  const send=async()=>{ const body=ta.value.trim(); const pollId=CHAT_ATTACH?CHAT_ATTACH.id:0;
    if(!body && !pollId)return; ta.value=''; ta.style.height='auto';
    try{ const r=await api('/messages',{method:'POST',body:{to:username,body,pollId}}); CHAT_ATTACH=null; renderAttach(); appendChatMessage(r.message); }
    catch(e){ toast(e.message); ta.value=body; } };
  $('#chat-send').onclick=send;
  $('#chat-poll').onclick=()=>openPollPicker();
  ta.addEventListener('keydown',(e)=>{ if(e.key==='Enter' && !e.shiftKey){ e.preventDefault(); send(); } });
  ta.addEventListener('input',()=>{ ta.style.height='auto'; ta.style.height=Math.min(130,ta.scrollHeight)+'px'; });
  // delegated: vote inside chat + unsend own message
  $('#chat-body').addEventListener('click',(e)=>{
    const o=e.target.closest('[data-cvote]'); if(o){ chatVote(o.dataset.cmid, +o.dataset.cvote, o); return; }
    const own=e.target.closest('.msg.me'); if(own && !e.target.closest('.cp-card,a,button')){
      const mid=own.dataset.mid; if(mid && confirm('Unsend this message?')) api('/messages/delete',{method:'POST',body:{id:+mid}}).then(()=>own.remove()).catch((err)=>toast(err.message));
    }
  });
  ta.focus();
  CHAT_TIMER=setInterval(async()=>{
    if(route!=='messages' || CHAT_USER!==username){ stopChatPoll(); return; }
    try{ const d=await api('/messages/with',{query:{username}});
      syncChat(d.messages, msgBubble);
      d.messages.forEach((m)=>{ if(!m.fromMe && m.id>MSG_SEEN) MSG_SEEN=m.id; });
    }catch(e){}
  }, 4000);
}
let GROUP_ID=null;
async function renderGroupChat(gid){
  stopChatPoll(); CHAT_USER=null; GROUP_ID=gid; CHAT_ATTACH=null; chrome();
  if(!ME){ openAuth(); return; }
  setMain(`<div class="chat-wrap">
    <div class="chat-head"><button class="iconbtn cback" data-back aria-label="Back">←</button><div id="chat-peer" class="chat-peer">…</div>
      <button class="iconbtn" id="chat-more" aria-label="More" data-keepmenu>⋯</button></div>
    <div class="chat-body" id="chat-body">${skList(3)}</div>
    <div id="chat-attach"></div>
    <div class="chat-input">
      <button class="chat-attach-btn" id="chat-poll" aria-label="Send a poll">${ic('bars',18)}</button>
      <textarea id="chat-text" rows="1" placeholder="Message the group…" maxlength="2000"></textarea>
      <button class="chat-send" id="chat-send" aria-label="Send">${ic('send',18)}</button></div></div>`);
  $('[data-back]').onclick=()=>{ stopChatPoll(); go('#/messages'); };
  let data; try{ data=await api('/group',{query:{id:gid}}); }
  catch(e){ setMain(`<button class="back-btn" data-back>← Back</button>${empt('🙈',e.message)}`); $('[data-back]').onclick=()=>go('#/messages'); return; }
  if(route!=='messages' || GROUP_ID!==gid) return;
  const g=data.group;
  $('#chat-peer').innerHTML=`<span id="gchat-peer" style="display:flex;align-items:center;gap:9px;cursor:pointer"><span class="av s34 grp-av">${g.avatar?`<img src="${esc(g.avatar)}">`:ic('users',16)}</span><span class="cp-name"><b>${esc(g.name)}</b><span class="cp-handle">${fmt(g.members)} members</span></span></span>`;
  $('#gchat-peer').onclick=()=>openGroupDetails(gid);
  $('#chat-more').onclick=(e)=>openGroupMenu(gid, data, e.currentTarget);
  paintGroupChat(data.messages);
  const ta=$('#chat-text'); const send=async()=>{ const body=ta.value.trim(); if(!body && !CHAT_ATTACH)return;
    ta.value=''; ta.style.height='auto';
    try{ await api('/group/send',{method:'POST',body:{groupId:+gid,body,pollId:CHAT_ATTACH?CHAT_ATTACH.id:0}}); CHAT_ATTACH=null; renderAttach();
      const d=await api('/group',{query:{id:gid}}); syncChat(d.messages, groupBubble);
    }catch(e){ toast(e.message); } };
  $('#chat-send').onclick=send;
  ta.addEventListener('keydown',(e)=>{ if(e.key==='Enter'&&!e.shiftKey){ e.preventDefault(); send(); } });
  ta.addEventListener('input',()=>{ ta.style.height='auto'; ta.style.height=Math.min(ta.scrollHeight,120)+'px'; });
  $('#chat-poll').onclick=openPollPicker;
  CHATPOLL=setInterval(async()=>{
    if(route!=='messages' || GROUP_ID!==gid){ stopChatPoll(); return; }
    try{ const dd=await api('/group',{query:{id:gid}});
    syncChat(dd.messages, groupBubble); }catch(e){} },4000);
}
function groupBubble(m){
  const showName = !m.fromMe && m.sender;
  let text='';
  if(m.body){ const sp=splitLpMessage(m.body);
    const inner=sp.text?linkifyUrls(linkifyHashtags(linkifyMentions(esc(sp.text)))):'';
    text=(inner?`<div class="bub">${inner}</div>`:'')+(sp.card||'');
  }
  const poll=m.poll?chatPollCard(m.poll,m.id):'';
  const name=showName?`<div class="gm-name">${esc(m.sender.name)}</div>`:'';
  return `<div class="msg ${m.fromMe?'me':'them'}" data-mid="${m.id}">${name}${text}${poll}<span class="msg-time">${timeAgo(m.createdAt)}</span></div>`;
}
function paintGroupChat(msgs){ const body=$('#chat-body'); if(!body) return;
  if(!msgs.length){ body.innerHTML=`<div class="chat-empty">No messages yet — say hi 👋</div>`; return; }
  body.innerHTML=msgs.map(groupBubble).join('');
  body.scrollTop=body.scrollHeight; enrichChatCards();
  if(!body.dataset.wired){ body.dataset.wired='1';
    body.addEventListener('click',(e)=>{ const b=e.target.closest('[data-cvote]'); if(b) chatVote(b.dataset.cmid, +b.dataset.cvote, b); }); }
}
function openGroupMenu(gid, data, anchor){
  const admin=data.isAdmin;
  actionSheet([
    {label:'Group details', icon:ic('info',16), onClick:()=>openGroupDetails(gid)},
    {label:'Members ('+data.group.members+')', icon:ic('users',16), onClick:()=>openGroupMembers(data)},
    {label:'Add people', icon:ic('plus',16), onClick:()=>openGroupAdd(gid)},
    {label:'Leave group', icon:ic('out',16), danger:true, onClick:async()=>{ if(!confirm('Leave this group?'))return;
      try{ await api('/group/leave',{method:'POST',body:{groupId:+gid}}); stopChatPoll(); go('#/messages'); toast('Left group'); }catch(e){ toast(e.message); } }},
    admin && {label:'Delete group', icon:'🗑', danger:true, onClick:async()=>{ if(!confirm('Delete this group for everyone? This cannot be undone.'))return;
      try{ await api('/group/delete',{method:'POST',body:{groupId:+gid}}); stopChatPoll(); go('#/messages'); toast('Group deleted'); }catch(e){ toast(e.message); } }},
  ], anchor, data.group.name);
}
async function openGroupDetails(gid){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim data-keepmenu><div class="sheet" style="max-height:82vh">
    <div class="sheet-grab"></div><div class="sheet-head"><h3>Group details</h3><button class="close" data-x>✕</button></div>
    <div id="gd-body">${skList(3)}</div></div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  let data; try{ data=await api('/group',{query:{id:gid}}); }catch(e){ $('#gd-body').innerHTML=empt('⚠️',e.message); return; }
  const g=data.group, admin=data.isAdmin;
  const view=()=>{
    $('#gd-body').innerHTML=`<div class="gd-head">
        <span class="gd-icon">${g.avatar?`<img src="${esc(g.avatar)}" alt="">`:ic('users',34)}</span>
        <h2 class="gd-name">${esc(g.name)}</h2>
        <span class="gd-count">${fmt(g.members)} member${g.members===1?'':'s'}</span>
        ${g.description?`<p class="gd-desc">${esc(g.description)}</p>`:'<p class="gd-desc gd-muted">No description yet.</p>'}
        ${admin?`<button class="btn btn-ghost btn-sm" id="gd-edit">${ic('edit',14)} Edit details</button>`:''}
      </div>
      <div class="gd-mtitle">Members</div>
      <div class="viewers-list">${data.membersList.map((u)=>`<div class="viewer-row">${avatar(u,'s40',true)}
        <div class="vr-info"><b data-author="${esc(u.username)}">${esc(u.name)}${vbadge(u.verified)}</b><span>@${esc(u.username)}</span></div>
        ${u.role==='admin'?'<span class="gd-admin">Admin</span>':''}</div>`).join('')}</div>
      ${admin?`<button class="btn btn-ghost btn-sm gd-addbtn" id="gd-add">${ic('plus',14)} Add people</button>`:''}`;
    const eb=$('#gd-edit'); if(eb) eb.onclick=edit;
    const ab=$('#gd-add'); if(ab) ab.onclick=()=>openGroupAdd(gid);
  };
  const edit=()=>{
    let iconData=null;
    $('#gd-body').innerHTML=`<div class="gd-editform">
      <label class="g-iconpick" id="gd-iconpick"><span id="gd-iconprev" class="g-iconprev">${g.avatar?`<img src="${esc(g.avatar)}" alt="">`:ic('users',22)}</span><span class="g-iconedit">＋</span>
        <input type="file" id="gd-icon" accept="image/*" hidden></label>
      <span class="f-lab">Group name</span><input class="field" id="gd-name" maxlength="80" value="${esc(g.name)}">
      <span class="f-lab">Description</span><textarea class="field" id="gd-desc" rows="3" maxlength="300" placeholder="What's this group about?">${esc(g.description||'')}</textarea>
      <div class="mfoot"><button class="btn btn-ghost" id="gd-cancel">Cancel</button><button class="btn btn-dark" id="gd-save">Save</button></div></div>`;
    $('#gd-icon').onchange=(e)=>{ const f=e.target.files[0]; if(!f)return; const rd=new FileReader(); rd.onload=()=>downscaleImage(rd.result,400,(url)=>{ iconData=url; $('#gd-iconprev').innerHTML=`<img src="${url}" alt="">`; }); rd.readAsDataURL(f); };
    $('#gd-cancel').onclick=view;
    $('#gd-save').onclick=async()=>{
      const name=$('#gd-name').value.trim(); if(name.length<2){ toast('Give the group a name'); return; }
      const btn=$('#gd-save'); btn.disabled=true;
      const payload={groupId:+gid,name,description:$('#gd-desc').value.trim()}; if(iconData!==null) payload.avatar=iconData;
      try{ const r=await api('/group/update',{method:'POST',body:payload}); g.name=r.group.name; g.description=r.group.description; g.avatar=r.group.avatar; toast('Group updated'); view();
        if(GROUP_ID===gid){ const nm=$('#chat-peer .cp-name b'); if(nm) nm.textContent=g.name; const ga=$('#chat-peer .grp-av'); if(ga) ga.innerHTML=g.avatar?`<img src="${esc(g.avatar)}">`:ic('users',16); }
      }catch(e){ toast(e.message); btn.disabled=false; }
    };
  };
  view();
}
function openGroupMembers(data){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim sheet-scrim" data-scrim data-keepmenu><div class="sheet" style="max-height:70vh">
    <div class="sheet-grab"></div><div class="sheet-head"><h3>${esc(data.group.name)} · members</h3><button class="close" data-x>✕</button></div>
    <div class="viewers-list">${data.membersList.map((u)=>`<div class="viewer-row">${avatar(u,'s40',true)}
      <div class="vr-info"><b data-author="${esc(u.username)}">${esc(u.name)}${vbadge(u.verified)}</b><span>@${esc(u.username)}</span></div>
      ${u.role==='admin'?'<span class="vr-emoji" style="font-size:12px;color:var(--soft);font-weight:700">Admin</span>':''}</div>`).join('')}</div></div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
}
async function openGroupAdd(gid){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim" data-scrim><div class="modal" data-keepmenu style="max-width:420px">
    <div class="mhead"><h3>Add people</h3><button class="close" data-x>✕</button></div>
    <div id="ga-people" class="g-people">${skList(2)}</div></div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  try{ const {users}=await api('/following',{query:{username:ME.username}}); const box=$('#ga-people');
    box.innerHTML=users.length?users.map((u)=>`<button class="g-pick" data-u="${esc(u.username)}">${avatar(u,'s34',false)}<span>${esc(u.name)}</span><span class="g-check">＋</span></button>`).join(''):`<p class="empty" style="padding:14px">No one to add.</p>`;
    box.querySelectorAll('[data-u]').forEach((b)=>b.onclick=async()=>{ try{ await api('/group/add',{method:'POST',body:{groupId:+gid,username:b.dataset.u}}); b.classList.add('on'); b.querySelector('.g-check').textContent='✓'; toast('Added'); }catch(e){ toast(e.message); } });
  }catch(e){ $('#ga-people').innerHTML=empt('⚠️',e.message); }
}
function renderAttach(){ const el=$('#chat-attach'); if(!el) return;
  if(!CHAT_ATTACH){ el.innerHTML=''; return; }
  el.innerHTML=`<div class="attach-chip">${ic('bars',14)} <span>${esc(CHAT_ATTACH.question.slice(0,60))}${CHAT_ATTACH.question.length>60?'…':''}</span><button id="attach-x" aria-label="Remove">✕</button></div>`;
  $('#attach-x').onclick=()=>{ CHAT_ATTACH=null; renderAttach(); };
}
async function openPollPicker(){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim" data-scrim><div class="modal" data-keepmenu style="max-width:460px">
    <div class="mhead"><h3>Send a poll</h3><button class="close" data-x>✕</button></div>
    <div class="desc">Pick one of your polls to send. They'll be able to vote right inside the chat.</div>
    <div id="pp-list" style="max-height:50vh;overflow:auto;margin-top:6px">${skList(3)}</div></div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  try{ const{polls}=await api('/mine'); const list=$('#pp-list');
    if(!polls.length){ list.innerHTML=empt('📊','You have no polls yet. Create one first!'); return; }
    list.innerHTML=polls.map((p)=>`<button class="pp-item" data-pp='${esc(JSON.stringify(p))}'>${ic('bars',15)} <span>${esc(p.question)}</span></button>`).join('');
    list.querySelectorAll('[data-pp]').forEach((b)=>b.onclick=()=>{ CHAT_ATTACH=JSON.parse(b.dataset.pp); renderAttach(); closeOverlay(); });
  }catch(e){ $('#pp-list').innerHTML=empt('⚠️',e.message); }
}
function openChatMenu(username, anchor){
  closeMenus(); const r=anchor.getBoundingClientRect();
  const m=document.createElement('div'); m.className='menu'; m.dataset.keepmenu='1';
  m.style.right=Math.max(10,window.innerWidth-r.right)+'px'; m.style.top=(r.bottom+6)+'px';
  m.innerHTML=`<button data-mi="view">${ic('user',16)} View profile</button>
    <button data-mi="delete">${ic('out',16)} Delete chat</button>
    <button class="danger" data-mi="block">${ic('shield',16)} Block @${esc(username)}</button>`;
  document.body.appendChild(m);
  m.querySelector('[data-mi="view"]').onclick=()=>{ closeMenus(); go('#/u/'+username); };
  m.querySelector('[data-mi="delete"]').onclick=async()=>{ closeMenus(); if(!confirm('Delete this conversation?'))return;
    try{ await api('/messages/delete-thread',{method:'POST',body:{username}}); stopChatPoll(); toast('Chat deleted'); go('#/messages'); }catch(e){ toast(e.message); } };
  m.querySelector('[data-mi="block"]').onclick=()=>{ closeMenus(); blockFromChat(username); };
}
async function blockFromChat(username){
  if(!confirm('Block @'+username+'? They won\'t be able to message you.')) return;
  try{ await api('/block',{method:'POST',body:{username}}); stopChatPoll(); toast('Blocked'); go('#/messages'); }catch(e){ toast(e.message); }
}
async function chatVote(mid, oid, btnEl){
  try{ const{poll}=await api('/vote',{method:'POST',body:{pollId:+btnEl.closest('[data-pollcard]').dataset.pollcard, optionIds:[oid]}});
    const card=btnEl.closest('[data-mid]'); const holder=card.querySelector('[data-pollcard]');
    if(holder) holder.outerHTML=chatPollCard(poll, mid);
  }catch(e){ toast(e.message); }
}
/* Turn a LinkPoll deep link (paper/poll/community/profile) into a rich, tappable
   card in chat instead of a plain-text URL. */
function parseLpLink(u){
  let path=null;
  try{ path=new URL(u).pathname; }
  catch(_){ const h=String(u).match(/#(\/[^\s]+)/); if(h) path=h[1]; else return null; }
  const m=path && path.match(/^\/(paper|poll|p|c|community|u)\/([A-Za-z0-9_-]+)/);
  if(!m) return null;
  const kind=m[1].toLowerCase(), id=m[2];
  const MAP={
    paper:{type:'Mock Paper', icon:'paper', route:'#/paper/'+id},
    poll:{type:'Poll', icon:'bars', route:'#/poll/'+id},
    p:{type:'Poll', icon:'bars', route:'#/poll/'+id},
    c:{type:'Community', icon:'users', route:'#/c/'+id},
    community:{type:'Community', icon:'users', route:'#/c/'+id},
    u:{type:'Profile', icon:'user', route:'#/u/'+id},
  };
  const base=MAP[kind]; if(!base) return null;
  return {...base, kind, id, url:u};
}
const LP_CARD_CACHE={};
function chatLinkCard(link){
  const key=link.kind+':'+link.id; const cached=LP_CARD_CACHE[key];
  const title = cached!==undefined ? cached : link.id;
  return `<a class="cl-card" href="${esc(link.route)}" data-lpcard="${esc(key)}" data-lproute="${esc(link.route)}">
    <span class="cl-ic">${ic(link.icon,20)}</span>
    <span class="cl-body"><span class="cl-type">${esc(link.type)}</span><span class="cl-title">${esc(title)}</span></span>
    <span class="cl-open">${ic('arrow',16)}</span></a>`;
}
// fetch real titles for any link cards currently on screen (once per id)
async function enrichChatCards(){
  const cards=$$('.cl-card[data-lpcard]'); if(!cards.length) return;
  const seen={};
  for(const c of cards){
    const key=c.dataset.lpcard; if(seen[key]) continue; seen[key]=1;
    const [kind,id]=key.split(':');
    if(LP_CARD_CACHE[key]!==undefined){ setCardTitle(key, LP_CARD_CACHE[key]); continue; }
    let title=null;
    try{
      if(kind==='paper'){ title=(await api('/paper',{query:{code:id}})).paper.title; }
      else if(kind==='poll'||kind==='p'){ title=(await api('/poll',{query:{code:id}})).poll.question; }
      else if(kind==='u'){ title='@'+id; try{ title=(await api('/user',{query:{username:id}})).user.name||('@'+id); }catch(_){} }
      else if(kind==='c'||kind==='community'){ title=id; try{ const cs=(await api('/communities')).communities||[]; const f=cs.find((x)=>x.slug===id); if(f) title=f.name; }catch(_){} }
    }catch(_){ title=null; }
    if(title){ LP_CARD_CACHE[key]=title; setCardTitle(key, title); }
  }
}
function setCardTitle(key, title){ $$('.cl-card[data-lpcard="'+CSS.escape(key)+'"] .cl-title').forEach((el)=>{ el.textContent=title; }); }
/* body text may be "note\nhttps://…/paper/CODE" — split the LP link out and return {text, card} */
function splitLpMessage(body){
  if(!body) return {text:'', card:''};
  const urlRe=/(https?:\/\/[^\s]+|#\/(?:paper|poll|p|c|community|u)\/[A-Za-z0-9_-]+)/g;
  let link=null, matched=null;
  const urls=body.match(urlRe)||[];
  for(const u of urls){ const p=parseLpLink(u); if(p){ link=p; matched=u; break; } }
  if(!link) return {text:body, card:'', hasUrl:urls.length>0};
  const text=body.replace(matched,'').trim();
  return {text, card:chatLinkCard(link)};
}
/* make plain URLs in a message clickable (used when there's no LP card) */
function linkifyUrls(escaped){
  return escaped.replace(/(https?:\/\/[^\s<]+)/g, (m)=>`<a href="${m}" target="_blank" rel="noopener noreferrer" class="msg-link">${m}</a>`);
}

function chatPollCard(poll, mid){
  if(!poll) return '';
  const voted=poll.hasVoted || poll.closed;
  const total=poll.totalVotes||0;
  let leadId=null,lead=-1; if(voted) poll.options.forEach((o)=>{ if(o.votes>lead){ lead=o.votes; leadId=o.id; } });
  const opts=poll.options.map((o)=>{
    if(voted){ const pct=total?Math.round(o.votes/total*100):0; const mine=(poll.myVotes||[]).includes(o.id);
      return `<div class="cp-opt ${mine?'mine':''} ${o.id===leadId&&total?'lead':''}"><span class="cp-fill" style="width:${pct}%"></span><span class="cp-lab">${mine?'<span class="tick">✓</span>':''}${esc(o.label)}<span class="cp-pct">${pct}%</span></span></div>`; }
    return `<button class="cp-opt votable" data-cvote="${o.id}" data-cmid="${mid}"><span class="cp-lab">${esc(o.label)}</span></button>`;
  }).join('');
  return `<div class="cp-card" data-pollcard="${poll.id}"><div class="cp-q">${ic('bars',14)} ${esc(poll.question)}</div>
    ${poll.community?`<a class="cp-comm" href="#/c/${esc(poll.community.slug)}">${poll.community.privacy==='private'?ic('lockc',11):ic('users',11)} ${esc(poll.community.name)}</a>`:''}
    <div class="cp-opts">${opts}</div>
    <div class="cp-foot">${fmt(total)} vote${total===1?'':'s'} · <a href="#/poll/${poll.code}">Open poll</a></div></div>`;
}
/* Incrementally reconcile the chat body with the latest messages WITHOUT a full
   innerHTML rebuild — appends only new bubbles and removes unsent ones. This
   prevents the periodic "blink"/image-reload that a full repaint caused. */
function syncChat(msgs, bubbleFn){
  const body=$('#chat-body'); if(!body) return;
  if(!msgs.length){ if(!body.querySelector('.chat-empty')) body.innerHTML=`<div class="chat-empty">No messages yet — say hi 👋</div>`; return; }
  const emptyEl=body.querySelector('.chat-empty'); if(emptyEl) emptyEl.remove();
  const want=new Set(msgs.map((m)=>String(m.id)));
  body.querySelectorAll('.msg').forEach((n)=>{ if(!want.has(n.dataset.mid)) n.remove(); });
  const have=new Set([...body.querySelectorAll('.msg')].map((n)=>n.dataset.mid));
  const nearBottom = body.scrollHeight - body.scrollTop - body.clientHeight < 140;
  let added=false, mine=false;
  msgs.forEach((m)=>{ if(!have.has(String(m.id))){ body.insertAdjacentHTML('beforeend', bubbleFn(m)); added=true; if(m.fromMe) mine=true; } });
  if(added && (nearBottom||mine)) body.scrollTop=body.scrollHeight;
  if(added) enrichChatCards();
}
function paintChat(msgs){ const body=$('#chat-body'); if(!body) return;
  if(!msgs.length){ body.innerHTML=`<div class="chat-empty">No messages yet — say hi 👋</div>`; return; }
  body.innerHTML=msgs.map(msgBubble).join(''); body.scrollTop=body.scrollHeight; enrichChatCards();
}
function msgBubble(m){
  const poll=m.poll?chatPollCard(m.poll, m.id):'';
  let text='';
  if(m.body){ const sp=splitLpMessage(m.body);
    const inner=sp.text?linkifyUrls(linkifyHashtags(linkifyMentions(esc(sp.text)))):'';
    text=(inner?`<div class="bub">${inner}</div>`:'')+(sp.card||'');
  }
  return `<div class="msg ${m.fromMe?'me':'them'}" data-mid="${m.id}">${text}${poll}<span class="msg-time">${timeAgo(m.createdAt)}</span></div>`;
}
function appendChatMessage(m){ const body=$('#chat-body'); if(!body) return;
  const empty=body.querySelector('.chat-empty'); if(empty) body.innerHTML='';
  if(body.querySelector(`[data-mid="${m.id}"]`)) return;
  body.insertAdjacentHTML('beforeend', msgBubble(m)); body.scrollTop=body.scrollHeight; enrichChatCards(); }

async function renderTag(tag){
  tag=(tag||'').toLowerCase(); chrome();
  setMain(`<button class="back-btn" data-back>← Back</button>
    <div class="page-title" style="display:flex;align-items:center;gap:8px">${ic('search',20)} #${esc(tag)}</div>
    <div id="tag-feed">${skList(3)}</div>`);
  $('[data-back]').onclick=()=>{ if(history.length>1)history.back(); else go('#/explore'); };
  let data;
  try{ data=await api('/tag',{query:{tag}}); }catch(e){ $('#tag-feed').innerHTML=empt('🤔',e.message); return; }
  if(route!=='tag') return;
  const wrap=$('#tag-feed'); if(!wrap) return;
  if(!data.polls.length){ wrap.innerHTML=empt('🔍',`No polls tagged #${esc(tag)} yet.`); return; }
  wrap.innerHTML=`<div class="muted-line" style="margin:-4px 2px 12px;color:var(--muted);font-size:13px">${data.count} poll${data.count===1?'':'s'} tagged <b>#${esc(tag)}</b></div>`+
    data.polls.map(cardHTML).join('');
  const c=wrap; wireFeed(c); paintFills(c);
}

function openReport(type, id){
  if(!ME){ openAuth(); return; }
  closeMenus(); lockScroll();
  const reasons=['Spam or scam','Harassment or hate','Misinformation','Sexual or explicit','Off-topic / irrelevant','Other'];
  const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim" data-scrim><div class="modal" data-keepmenu style="max-width:420px">
    <div class="mhead"><h3>Report ${type==='comment'?'comment':type==='faq'?'discussion':'poll'}</h3><button class="close" data-x>✕</button></div>
    <div class="desc">Tell us what's wrong. Our moderators will review it.</div>
    <div class="rsn">${reasons.map((r,i)=>`<label class="rsn-row"><input type="radio" name="rsn" value="${esc(r)}" ${i===0?'checked':''}><span>${r}</span></label>`).join('')}</div>
    <textarea class="field" id="rdetail" maxlength="300" placeholder="Add details (optional)" style="min-height:60px;margin-top:8px"></textarea>
    <div class="mfoot"><button class="btn btn-ghost" data-x>Cancel</button>
      <button class="btn btn-dark" id="rsend"><span style="color:var(--lime)">${ic('flag',15)} Submit report</span></button></div>
  </div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  $('#rsend').onclick=async()=>{
    const sel=$('input[name="rsn"]:checked'); const detail=$('#rdetail').value.trim();
    const reason=[sel?sel.value:'', detail].filter(Boolean).join(' — ');
    const btn=$('#rsend'); btn.disabled=true;
    try{ const r=await api('/report',{method:'POST',body:{type,id,reason}}); closeOverlay();
      toast(r.already?'You already reported this — thanks.':'Report submitted. Thank you.'); }
    catch(e){ toast(e.message); btn.disabled=false; }
  };
}

/* ---------- admin moderation queue ---------- */
let adminTab='overview';
async function renderAdmin(){
  chrome();
  if(!ME){ setMain(`<div class="page-title">Admin panel</div>${empt('\u{1F6E1}\uFE0F','Please sign in to access the admin panel.',true)}`); return; }
  if(!ME.admin){ setMain(`<div class="page-title">Admin panel</div>
    <div class="empty"><div class="emoji">\u{1F6E1}\uFE0F</div>
      <p>You're signed in as <b>@${esc(ME.username)}</b>, but this account doesn't have admin access.</p>
      <p style="color:var(--soft);font-size:13px;margin-top:6px">An existing admin can grant you access from the Users tab, or set it up via phpMyAdmin / config.php.</p>
      <a class="btn btn-dark btn-sm" href="#/" style="margin-top:14px">\u2190 Back to home</a></div>`); return; }
  const tabs=[['overview','Overview'],['reports','Reports'],['users','Users'],['polls','Polls'],['comments','Comments'],['feedback','Feedback'],['email','Email'],['perks','Perks']];
  setMain(`<div class="page-title" style="display:flex;align-items:center;gap:8px">${ic('shield',20)} Admin panel</div>
    <div class="atabs">${tabs.map(([k,l])=>`<button class="atab ${adminTab===k?'on':''}" data-atab="${k}">${l}</button>`).join('')}</div>
    <div id="admin-body">${skList(3)}</div>`);
  $$('[data-atab]').forEach((b)=>b.onclick=()=>{ adminTab=b.dataset.atab; renderAdmin(); });
  if(adminTab==='overview') return adminOverview();
  if(adminTab==='users') return adminUsers();
  if(adminTab==='polls') return adminPolls();
  if(adminTab==='comments') return adminComments();
  if(adminTab==='email') return adminEmail();
  if(adminTab==='feedback') return adminFeedback();
  if(adminTab==='perks') return adminPerks();
  return adminReports();
}
function adminPerks(){
  const body=$('#admin-body'); if(!body) return; body.innerHTML=skList(3);
  api('/perks').then((d)=>{
    body.innerHTML=`<div class="panel"><h2 style="font-size:18px">Points-gated perks</h2>
      <div class="desc">Set how many points unlock each feature, or turn a perk off entirely. Applies to everyone instantly.</div>
      <div id="perk-edit">${d.perks.map((p)=>`<div class="perk-edit-row" data-key="${p.key}">
        <span class="perk-ic">${p.icon}</span>
        <div class="perk-info"><b>${esc(p.label)}</b><span>${esc(p.desc)}</span></div>
        <label class="perk-en"><input type="checkbox" class="pk-en" ${p.enabled?'checked':''}> On</label>
        <div class="perk-thr"><input type="number" min="0" class="field pk-thr" value="${p.threshold}"><span>pts</span></div>
      </div>`).join('')}</div>
      <button class="btn btn-dark" id="perk-save" style="margin-top:14px">Save perks</button>
      <div id="perk-result" style="margin-top:10px;font-size:13.5px"></div></div>`;
    $('#perk-save').onclick=async()=>{
      const perks={}; $$('#perk-edit .perk-edit-row').forEach((r)=>{ perks[r.dataset.key]={
        threshold:+r.querySelector('.pk-thr').value||0, enabled:r.querySelector('.pk-en').checked }; });
      const btn=$('#perk-save'); btn.disabled=true; btn.textContent='Saving…';
      try{ await api('/admin/perks',{method:'POST',body:{perks}}); $('#perk-result').innerHTML='<span style="color:#5c7a13">✓ Saved.</span>';
        if(ME){ try{ ME=(await api('/auth/me')).user; }catch(e){} } }
      catch(e){ $('#perk-result').innerHTML=`<span style="color:var(--red)">${esc(e.message)}</span>`; }
      btn.disabled=false; btn.textContent='Save perks';
    };
  }).catch((e)=>{ body.innerHTML=empt('⚠️',e.message); });
}
const EMAIL_TEMPLATES=[
  {id:'',      name:'— Start from scratch —', subject:'', body:''},
  {id:'welcome', name:'👋 Welcome', subject:'Welcome to LinkPoll!', body:"Hi there,\n\nThanks for joining LinkPoll — the social network built around polls.\n\nCreate your first poll, follow a few people, and see what the community thinks.\n\nSee you inside,\nThe LinkPoll team"},
  {id:'announce', name:'📣 Announcement', subject:'A quick announcement from LinkPoll', body:"Hi {name},\n\nWe wanted to share some news with you...\n\n[Write your announcement here]\n\nThanks for being part of LinkPoll!"},
  {id:'feature', name:'✨ New feature', subject:'New on LinkPoll: [feature name]', body:"Hi {name},\n\nWe just shipped something we think you'll love: [feature].\n\n[Explain what it does and why it's great]\n\nGive it a try and let us know what you think!\n\n— The LinkPoll team"},
  {id:'reengage', name:'💜 We miss you', subject:'Come see what you\'ve missed on LinkPoll', body:"Hi {name},\n\nIt's been a while! There are new polls waiting for your vote and fresh conversations happening right now.\n\nJump back in — your opinion matters.\n\n— The LinkPoll team"},
  {id:'maintenance', name:'🛠️ Maintenance notice', subject:'Scheduled maintenance on LinkPoll', body:"Hi {name},\n\nLinkPoll will be briefly unavailable on [date] from [time] to [time] for scheduled maintenance.\n\nThanks for your patience!\n\n— The LinkPoll team"},
];
function adminEmail(){
  const body=$('#admin-body'); if(!body) return;
  const audOpts=[['all','👥 All users'],['promotional','📣 Promotional opt-in'],['activity','🔔 Activity opt-in'],
    ['verified','✅ Verified only'],['new','🌱 New users (last 30 days)'],['selected','🎯 Specific users…'],['direct','✉️ Direct (single user)']];
  body.innerHTML=`<div class="ae-grid">
    <div class="panel ae-test">
      <h2 style="font-size:18px">📮 Test delivery</h2>
      <div class="desc">Send yourself a test email to confirm SMTP works. If it doesn't arrive (check spam), verification codes won't send either — configure SMTP in <b>config.php</b>.</div>
      <div class="f"><label>Send test to</label><input class="field" id="te-to" type="email" placeholder="you@example.com" value="${ME&&ME.email?esc(ME.email):''}"></div>
      <button class="btn btn-dark btn-block" id="te-go">Send test email</button>
      <div id="te-result" style="margin-top:12px;font-size:13.5px"></div>
    </div>
    <div class="panel ae-compose">
      <h2 style="font-size:18px">Compose email</h2>
      <div class="desc">Pick a template to start, choose who receives it, then edit and send. Use <code>{name}</code> to personalise with the recipient's name.</div>
      <div class="ae-row">
        <div class="f"><label>Template</label>
          <select class="field" id="em-tpl">${EMAIL_TEMPLATES.map((t)=>`<option value="${t.id}">${esc(t.name)}</option>`).join('')}</select></div>
        <div class="f"><label>Audience</label>
          <select class="field" id="em-type">${audOpts.map(([v,l])=>`<option value="${v}">${l}</option>`).join('')}</select></div>
      </div>
      <div class="ae-aud" id="em-count">…</div>
      <div class="f" id="em-userwrap" style="display:none"><label>Username</label><input class="field" id="em-user" placeholder="username (no @)"></div>
      <div class="f" id="em-selwrap" style="display:none"><label>Usernames (comma or space separated)</label>
        <textarea class="field" id="em-sel" style="min-height:64px" placeholder="e.g. zaheer, alex, sara"></textarea>
        <div class="ae-selcount" id="em-selcount"></div></div>
      <div class="f"><label>Subject</label><input class="field" id="em-subject" maxlength="150" placeholder="Subject line"></div>
      <div class="f"><label>Message</label><textarea class="field" id="em-body" style="min-height:170px" placeholder="Write your message… (plain text — line breaks are kept)"></textarea></div>
      <button class="btn btn-dark btn-block" id="em-send">Send email</button>
      <div id="em-result" style="margin-top:12px;font-size:13.5px"></div>
    </div>
  </div>`;
  $('#te-go').onclick=async()=>{
    const to=$('#te-to').value.trim(); if(!to){ toast('Enter an email'); return; }
    const btn=$('#te-go'); btn.disabled=true; btn.textContent='Sending…';
    try{ const r=await api('/admin/test-email',{method:'POST',body:{to}});
      $('#te-result').innerHTML=`<span style="color:${r.ok?'#5c7a13':'var(--red)'}">${r.ok?'✓ ':'✗ '}${esc(r.message)}</span>`; }
    catch(e){ $('#te-result').innerHTML=`<span style="color:var(--red)">${esc(e.message)}</span>`; }
    btn.disabled=false; btn.textContent='Send test email';
  };
  // templates
  $('#em-tpl').onchange=()=>{ const t=EMAIL_TEMPLATES.find((x)=>x.id===$('#em-tpl').value); if(!t)return;
    const subj=$('#em-subject'), bd=$('#em-body');
    if(t.id==='' ){ return; }
    if(!subj.value.trim() || confirm('Replace the current subject and message with this template?')){ subj.value=t.subject; bd.value=t.body; } };
  const type=$('#em-type'), uw=$('#em-userwrap'), sw=$('#em-selwrap');
  const refreshCount=async()=>{ const t=type.value; const el=$('#em-count');
    if(t==='direct'){ el.textContent='Sends to one specific user.'; return; }
    if(t==='selected'){ el.textContent='Sends to the usernames you list below.'; return; }
    el.textContent='Counting recipients…';
    try{ const r=await api('/admin/email/count',{query:{type:t}}); el.innerHTML=`Will send to <b>${fmt(r.count)}</b> recipient${r.count===1?'':'s'}.`; }
    catch(e){ el.textContent=''; } };
  type.onchange=()=>{ uw.style.display=type.value==='direct'?'':'none'; sw.style.display=type.value==='selected'?'':'none'; refreshCount(); };
  const parseSel=()=>((($('#em-sel')||{}).value)||'').split(/[\s,]+/).map((x)=>x.trim().replace(/^@/,'')).filter(Boolean);
  if($('#em-sel')) $('#em-sel').addEventListener('input',()=>{ const n=parseSel().length; $('#em-selcount').textContent=n?`${n} user${n===1?'':'s'} selected`:''; });
  refreshCount();
  $('#em-send').onclick=async()=>{
    const t=type.value, subject=$('#em-subject').value.trim(), msg=$('#em-body').value.trim();
    if(!subject||!msg){ toast('Subject and message are required.'); return; }
    const payload={type:t,subject,body:msg};
    if(t==='direct'){ payload.username=$('#em-user').value.trim(); if(!payload.username){ toast('Enter a username.'); return; } }
    if(t==='selected'){ payload.usernames=parseSel(); if(!payload.usernames.length){ toast('List at least one username.'); return; } }
    if(!confirm('Send this email now?')) return;
    const btn=$('#em-send'); btn.disabled=true; btn.textContent='Sending…';
    try{ const r=await api('/admin/email',{method:'POST',body:payload});
      $('#em-result').innerHTML=`<span style="color:#5c7a13">✓ Queued to ${fmt(r.recipients)} recipient${r.recipients===1?'':'s'} — ${fmt(r.sent)} sent${r.failed?`, ${fmt(r.failed)} failed`:''}.</span>`;
    }catch(e){ $('#em-result').innerHTML=`<span style="color:var(--red)">${esc(e.message)}</span>`; }
    btn.disabled=false; btn.textContent='Send email';
  };
}
async function adminOverview(){
  const body=$('#admin-body'); if(!body) return; body.innerHTML=skList(2);
  let d; try{ d=await api('/admin/overview'); }catch(e){ body.innerHTML=empt('🛡️',e.message); return; }
  if(route!=='admin'||adminTab!=='overview') return; const o=d.overview;
  const card=(label,val,accent)=>`<div class="ostat ${accent||''}"><b>${fmt(val)}</b><span>${label}</span></div>`;
  body.innerHTML=`<div class="ogrid">
    ${card('Total users',o.users)}
    ${card('Polls',o.polls)}
    ${card('Votes cast',o.votes)}
    ${card('Comments',o.comments)}
    ${card('Verified users',o.verified)}
    ${card('New users (24h)',o.newUsers,'good')}
    ${card('New polls (24h)',o.newPolls,'good')}
    ${card('Open reports',o.openReports, o.openReports?'warn':'')}
    ${card('Suspended',o.banned, o.banned?'bad':'')}
  </div>
  <div class="oquick">
    <button class="btn btn-ghost btn-sm" data-goto="reports">Review reports →</button>
    <button class="btn btn-ghost btn-sm" data-goto="users">Manage users →</button>
  </div>`;
  $$('[data-goto]',body).forEach((b)=>b.onclick=()=>{ adminTab=b.dataset.goto; renderAdmin(); });
}

async function adminFeedback(){
  const body=$('#admin-body'); if(!body) return; body.innerHTML=skList(3);
  let d; try{ d=await api('/admin/feedback'); }catch(e){ if(body)body.innerHTML=empt('💬',e.message); return; }
  if(route!=='admin'||adminTab!=='feedback') return; const b=$('#admin-body'); if(!b) return;
  if(!d.feedback.length){ b.innerHTML=empt('💬','No feedback yet','When people send feedback, it will appear here.'); return; }
  b.innerHTML=`<div class="afb-head">${d.counts.new} new · ${d.counts.total} total</div>`+
    d.feedback.map((f)=>`<div class="afb-item ${f.status==='new'?'is-new':''}">
      <div class="afb-top"><span class="afb-cat">${esc(f.category)}</span>
        <span class="afb-who">${esc(f.name||'Guest')}${f.email?` · <a href="mailto:${esc(f.email)}">${esc(f.email)}</a>`:''}</span>
        <span class="afb-time">${timeAgo(f.at*1000)}</span></div>
      <div class="afb-msg">${esc(f.message)}</div>
      ${f.page?`<div class="afb-page">on ${esc(f.page)}</div>`:''}
      <div class="afb-acts">
        <button class="btn btn-ghost btn-sm" data-fbdone="${f.id}">${f.status==='new'?'Mark done':'Reopen'}</button>
        <button class="btn btn-ghost btn-sm danger" data-fbdel="${f.id}">Delete</button>
      </div></div>`).join('');
  b.querySelectorAll('[data-fbdone]').forEach((x)=>x.onclick=async()=>{ const f=d.feedback.find((z)=>z.id==x.dataset.fbdone);
    try{ await api('/admin/feedback',{method:'POST',body:{id:+x.dataset.fbdone,action:f&&f.status==='new'?'done':'reopen'}}); adminFeedback(); }catch(e){ toast(e.message); } });
  b.querySelectorAll('[data-fbdel]').forEach((x)=>x.onclick=async()=>{ if(!confirm('Delete this feedback?')) return;
    try{ await api('/admin/feedback',{method:'POST',body:{id:+x.dataset.fbdel,action:'delete'}}); adminFeedback(); }catch(e){ toast(e.message); } });
}
async function adminReports(){
  let data; try{ data=await api('/admin/reports'); }catch(e){ const b=$('#admin-body'); if(b)b.innerHTML=empt('\u{1F6E1}\uFE0F',e.message); return; }
  if(route!=='admin'||adminTab!=='reports') return; const body=$('#admin-body'); if(!body) return;
  const s=data.stats;
  const stats=`<div class="stats" style="margin-bottom:14px">
    <div class="stat"><b>${fmt(s.openReports)}</b><span>Open reports</span></div>
    <div class="stat"><b>${fmt(s.totalPolls)}</b><span>Polls</span></div>
    <div class="stat"><b>${fmt(s.totalComments)}</b><span>Comments</span></div>
    <div class="stat"><b>${fmt(s.totalUsers)}</b><span>Users</span></div></div>`;
  if(!data.reports.length){ body.innerHTML=stats+empt('\u2705','No open reports. All clear!'); return; }
  body.innerHTML=stats+data.reports.map((r)=>{
    const t=r.target;
    const head=`<span class="mr-type ${r.type}">${r.type}</span> <span class="mr-count">${r.count} report${r.count===1?'':'s'}</span>`;
    const who=t?`by <b data-author="${esc(t.username)}" style="cursor:pointer">${esc(t.author)}</b>`:'';
    const txt=t?`<div class="mr-body">${esc((t.text||'').slice(0,200))}${(t.text||'').length>200?'\u2026':''}</div>`:'<div class="mr-body gone">This content was already removed.</div>';
    const reasons=r.reasons.length?`<div class="mr-reasons">${r.reasons.map((x)=>`<span class="rchip">${esc(x.slice(0,40))}</span>`).join('')}</div>`:'';
    const openLink=(t&&t.code)?`<button class="btn btn-ghost btn-sm" data-open="${esc(t.code)}">${r.type==='poll'?'View poll':'View thread'}</button>`:'';
    return `<div class="mr-card" data-mr="${r.type}:${r.targetId}"><div class="mr-head">${head} <span class="mr-who">${who}</span></div>
      ${txt}${reasons}<div class="mr-acts">${openLink}
        <button class="btn btn-sm" style="background:#fde8e8;color:var(--red)" data-mremove>${ic('out',14)} Remove content</button>
        <button class="btn btn-ghost btn-sm" data-mdismiss>Dismiss</button></div></div>`;
  }).join('');
  body.onclick=async(e)=>{
    const vrx=e.target.closest('[data-results]'); if(vrx){ const p=POLLS[vrx.dataset.results]; if(p) openResults(p); return; }
    const cmx=e.target.closest('[data-comments]'); if(cmx){ openComments(cmx.dataset.comments, cmx); return; }
    const open=e.target.closest('[data-open]'); if(open){ go('#/poll/'+open.dataset.open); return; }
    const card=e.target.closest('[data-mr]'); if(!card) return; const [type,id]=card.dataset.mr.split(':');
    if(e.target.closest('[data-mremove]')){ if(!confirm('Remove this content permanently?'))return;
      try{ await api('/admin/resolve',{method:'POST',body:{type,id:+id,action:'remove'}}); toast('Content removed'); renderAdmin(); }catch(err){ toast(err.message); } }
    else if(e.target.closest('[data-mdismiss]')){
      try{ await api('/admin/resolve',{method:'POST',body:{type,id:+id,action:'dismiss'}}); toast('Report dismissed'); renderAdmin(); }catch(err){ toast(err.message); } }
  };
}

async function adminUsers(q){
  const body=$('#admin-body'); if(!body) return;
  body.innerHTML=`<div class="asearch"><input class="field" id="ausq" placeholder="Search users by name, @username or email" value="${esc(q||'')}"></div><div id="aulist">${skList(3)}</div>`;
  const run=async(term)=>{ const list=$('#aulist'); if(list)list.innerHTML=skList(3);
    let d; try{ d=await api('/admin/users',{query:term?{q:term}:{}}); }catch(e){ if(list)list.innerHTML=empt('\u{1F6E1}\uFE0F',e.message); return; }
    if(route!=='admin'||adminTab!=='users') return; const L=$('#aulist'); if(!L) return;
    if(!d.users.length){ L.innerHTML=empt('\u{1F50D}','No users found.'); return; }
    L.innerHTML=d.users.map((u)=>`<div class="urow ${u.banned?'banned':''}" data-uid="${u.id}">
      ${avatar(u,'s40',false)}
      <div class="uinfo"><div class="un"><b data-author="${esc(u.username)}" style="cursor:pointer">${esc(u.name)}</b>${u.verified?vbadge(1):''}${u.admin?'<span class="abadge">ADMIN</span>':''}${u.banned?'<span class="abadge bad">SUSPENDED</span>':''}</div>
        <div class="us">@${esc(u.username)} \u00b7 ${esc(u.email||'')} \u00b7 ${u.polls} poll${u.polls===1?'':'s'} \u00b7 joined ${new Date(u.createdAt).toLocaleDateString()}</div></div>
      <div class="uacts">
        <button class="btn btn-ghost btn-sm" data-uverify>${u.verified?'Unverify':'Verify'}</button>
        <button class="btn btn-ghost btn-sm" data-uadmin>${u.admin?'Remove admin':'Make admin'}</button>
        <button class="btn btn-ghost btn-sm" data-uban>${u.banned?'Unsuspend':'Suspend'}</button>
        <button class="btn btn-sm" style="background:#fde8e8;color:var(--red)" data-udel>Delete</button>
      </div></div>`).join('');
    L.onclick=async(e)=>{
      const au=e.target.closest('[data-author]'); if(au){ go('#/u/'+au.dataset.author); return; }
      const row=e.target.closest('[data-uid]'); if(!row) return; const id=+row.dataset.uid;
      const act=async(action,confirmMsg)=>{ if(confirmMsg&&!confirm(confirmMsg))return;
        try{ await api('/admin/user',{method:'POST',body:{userId:id,action}}); run($('#ausq')?$('#ausq').value.trim():''); }catch(err){ toast(err.message); } };
      if(e.target.closest('[data-uverify]')){ const isV=/Unverify/.test(e.target.closest('[data-uverify]').textContent); act(isV?'unverify':'verify'); toast(isV?'Verification removed':'User verified'); }
      else if(e.target.closest('[data-uadmin]')){ const isA=/Remove/.test(e.target.closest('[data-uadmin]').textContent); act(isA?'removeAdmin':'makeAdmin', isA?'Remove admin access from this user?':'Grant admin access to this user?'); }
      else if(e.target.closest('[data-uban]')){ const isB=/Unsuspend/.test(e.target.closest('[data-uban]').textContent);
        act(isB?'unban':'ban', isB?'Restore this user\u2019s access?':'Suspend this user? They will be logged out and unable to sign in until unsuspended.'); }
      else if(e.target.closest('[data-udel]')){ act('delete','Permanently delete this user and ALL their polls, votes and comments?'); }
    };
  };
  let t; $('#ausq').addEventListener('input',(e)=>{ clearTimeout(t); const v=e.target.value.trim(); t=setTimeout(()=>run(v),250); });
  run(q||'');
}

async function adminPolls(){
  const body=$('#admin-body'); if(!body) return;
  body.innerHTML=`<div class="asearch"><input class="field" id="apq" placeholder="Search polls by question or category"></div><div id="aplist">${skList(3)}</div>`;
  const run=async(term)=>{ const list=$('#aplist'); if(list)list.innerHTML=skList(3);
    let d; try{ d=await api('/admin/polls',{query:term?{q:term}:{}}); }catch(e){ if(list)list.innerHTML=empt('\u{1F6E1}\uFE0F',e.message); return; }
    if(route!=='admin'||adminTab!=='polls') return; const L=$('#aplist'); if(!L) return;
    if(!d.polls.length){ L.innerHTML=empt('\u{1F50D}','No polls found.'); return; }
    L.innerHTML=d.polls.map((p)=>`<div class="prow" data-pid="${p.id}" data-code="${esc(p.code)}">
      <div class="pinfo"><b data-open="${esc(p.code)}" style="cursor:pointer">${esc(p.question)}</b>
        <div class="us">by ${esc(p.author.name)} \u00b7 ${fmt(p.totalVotes)} votes \u00b7 ${fmt(p.comments)} comments${p.closed?' \u00b7 closed':''}</div></div>
      <div class="uacts">${p.closed?'':'<button class="btn btn-ghost btn-sm" data-pclose>Close</button>'}
        <button class="btn btn-sm" style="background:#fde8e8;color:var(--red)" data-pdel>Delete</button></div></div>`).join('');
    L.onclick=async(e)=>{
      const vry=e.target.closest('[data-results]'); if(vry){ const p=POLLS[vry.dataset.results]; if(p) openResults(p); return; }
      const cmy=e.target.closest('[data-comments]'); if(cmy){ openComments(cmy.dataset.comments, cmy); return; }
      const op=e.target.closest('[data-open]'); if(op){ go('#/poll/'+op.dataset.open); return; }
      const row=e.target.closest('[data-pid]'); if(!row) return; const id=+row.dataset.pid;
      if(e.target.closest('[data-pclose]')){ try{ await api('/poll/close',{method:'POST',body:{pollId:id}}); toast('Poll closed'); run($('#apq')?$('#apq').value.trim():''); }catch(err){ toast(err.message); } }
      else if(e.target.closest('[data-pdel]')){ if(!confirm('Delete this poll permanently?'))return;
        try{ await api('/poll/delete',{method:'POST',body:{pollId:id}}); toast('Poll deleted'); run($('#apq')?$('#apq').value.trim():''); }catch(err){ toast(err.message); } }
    };
  };
  let t; $('#apq').addEventListener('input',(e)=>{ clearTimeout(t); const v=e.target.value.trim(); t=setTimeout(()=>run(v),250); });
  run('');
}

async function adminComments(){
  const body=$('#admin-body'); if(!body) return;
  body.innerHTML=`<div class="asearch"><input class="field" id="acq" placeholder="Search comments or @username"></div><div id="aclist">${skList(3)}</div>`;
  const run=async(term)=>{ const list=$('#aclist'); if(list)list.innerHTML=skList(3);
    let d; try{ d=await api('/admin/comments',{query:term?{q:term}:{}}); }catch(e){ if(list)list.innerHTML=empt('\u{1F6E1}\uFE0F',e.message); return; }
    if(route!=='admin'||adminTab!=='comments') return; const L=$('#aclist'); if(!L) return;
    if(!d.comments.length){ L.innerHTML=empt('\u{1F50D}','No comments found.'); return; }
    L.innerHTML=d.comments.map((c)=>`<div class="crow" data-cid="${c.id}" data-code="${esc(c.pollCode)}">
      <div class="pinfo"><div class="cbody2">${linkifyHashtags(linkifyMentions(esc(c.body)))}</div>
        <div class="us">${esc(c.author)}${c.verified?' \u2713':''} \u00b7 on \u201c${esc((c.pollQuestion||'').slice(0,50))}${(c.pollQuestion||'').length>50?'\u2026':''}\u201d</div></div>
      <div class="uacts"><button class="btn btn-ghost btn-sm" data-cview>View</button>
        <button class="btn btn-sm" style="background:#fde8e8;color:var(--red)" data-cdelete>Delete</button></div></div>`).join('');
    L.onclick=async(e)=>{
      const row=e.target.closest('[data-cid]'); if(!row) return; const id=+row.dataset.cid; const code=row.dataset.code;
      if(e.target.closest('[data-cview]')){ go('#/poll/'+code); return; }
      if(e.target.closest('[data-cdelete]')){ if(!confirm('Delete this comment?'))return;
        try{ await api('/comments/delete',{method:'POST',body:{commentId:id}}); toast('Comment deleted'); run($('#acq')?$('#acq').value.trim():''); }catch(err){ toast(err.message); } }
    };
  };
  let t; $('#acq').addEventListener('input',(e)=>{ clearTimeout(t); const v=e.target.value.trim(); t=setTimeout(()=>run(v),250); });
  run('');
}
let authMode=(function(){ try{ return localStorage.getItem('lp_returning')?'signin':'signup'; }catch(e){ return 'signup'; } })();
function openAuth(){
  closeDrawer(); lockScroll();
  const ov=$('#overlay');
  ov.innerHTML=`<div class="auth"><div class="auth-left"><div class="wm">Link<em>Poll</em></div>
    <div class="auth-hero"><h1>The world's<br>opinion, <em>live.</em></h1>
      <p>Create polls in seconds. Share with anyone. See results in real time. The dedicated home for quick opinions.</p>
      <div class="auth-stats"><div><b>Live</b><span>Real-time</span></div><div><b>Open</b><span>For everyone</span></div><div><b>Free</b><span>Always</span></div></div></div></div>
    <div class="auth-right"><div class="auth-form"><div class="auth-mobile-brand"><img class="logo" src="logo-icon.png" alt="LinkPoll" /> LinkPoll</div><h2 id="at">Welcome back</h2><div class="lead" id="al">Sign in to your LinkPoll account</div>
      <div class="seg"><button data-mode="signin" class="${authMode==='signin'?'active':''}">Sign In</button>
        <button data-mode="signup" class="${authMode==='signup'?'active':''}">Create Account</button></div>
      <div id="af"></div>
      <button class="btn btn-dark btn-block" id="ago" style="padding:14px;margin-top:6px">${ic('signin',16).replace('class="ico"','class="ico i-lime"')} <span></span></button>
      <div class="auth-or"><span>or</span></div>
      <button class="btn btn-google btn-block" id="agoogle"><span class="g-logo">G</span> Continue with Google</button>
      <div style="text-align:center;margin-top:10px"><button class="btn btn-ghost btn-sm" id="a-forgot" style="font-size:13px">Forgot password?</button></div>
      <div style="text-align:center;margin-top:8px"><button class="btn btn-ghost btn-sm" data-x>← Browse without account</button></div></div></div></div>`;
  paintAuth(ov);
  $$('[data-mode]',ov).forEach((b)=>b.onclick=()=>{ authMode=b.dataset.mode; openAuth(); });
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  const fg=$('#a-forgot',ov); if(fg) fg.onclick=()=>{ openForgot(); };
  $('#ago',ov).onclick=doAuth;
  const gb=$('#agoogle',ov); if(gb) gb.onclick=()=>{ location.href=API+'?path='+encodeURIComponent('/auth/google'); };
}
function paintAuth(ov){
  const up=authMode==='signup';
  $('#at',ov).textContent=up?'Join LinkPoll':'Welcome back';
  $('#al',ov).textContent=up?'Ask the world anything — one poll at a time.':'Sign in to your LinkPoll account';
  $('#ago',ov).querySelector('span').textContent=up?'Create Account':'Sign In';
  $('#af',ov).innerHTML=(up?`<span class="lab">Full name</span><input class="inp" id="aname" placeholder="Your name">`:'')+
    `<span class="lab">Email</span><input class="inp" id="aemail" type="email" placeholder="you@example.com">
     <span class="lab">Password</span><div class="pw"><input class="inp" id="apass" type="password" placeholder="${up?'at least 6 characters':'••••••'}"><button class="eye" data-eye>${ic('eye',16)}</button></div>`;
  const eye=$('[data-eye]',ov); if(eye)eye.onclick=()=>{ const i=$('#apass'); i.type=i.type==='password'?'text':'password'; };
  $('#apass',ov).addEventListener('keydown',(e)=>{ if(e.key==='Enter')doAuth(); });
}
async function doAuth(){
  const email=$('#aemail').value.trim(); const pass=$('#apass').value; const btn=$('#ago'); btn.disabled=true;
  try{
    if(authMode==='signup'){ const name=$('#aname').value.trim();
      const r=await api('/auth/register',{method:'POST',body:{name,email,password:pass}});
      if(r.needsVerification){ openVerifyCode(r.email); return; }
      ME=r.user; finishAuth('Welcome, '+ME.name+'!'); setTimeout(()=>openOnboarding(),350); }
    else { const r=await api('/auth/login',{method:'POST',body:{email,password:pass}});
      if(r.needsVerification){ openVerifyCode(r.email); return; }
      ME=r.user; finishAuth('Hi '+ME.name+'!'); }
  }catch(e){ toast(e.message); btn.disabled=false; }
}
function openVerifyCode(email){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="auth"><div class="auth-left"><div class="wm">Link<em>Poll</em></div>
    <div class="auth-hero"><h1>Check your<br><em>inbox.</em></h1><p>We sent a 6-digit code to verify it's really you. Enter it to finish setting up your account.</p></div></div>
    <div class="auth-right"><div class="auth-form"><div class="auth-mobile-brand"><img class="logo" src="logo-icon.png" alt="LinkPoll" /> LinkPoll</div>
      <h2>Verify your email</h2><div class="lead">Enter the 6-digit code we sent to<br><b>${esc(email)}</b></div>
      <input class="inp code-inp" id="vcode" inputmode="numeric" maxlength="6" placeholder="••••••" autocomplete="one-time-code">
      <button class="btn btn-dark btn-block" id="vgo" style="padding:14px;margin-top:12px">Verify & continue</button>
      <div class="vc-row"><span>Didn't get it?</span> <button class="btn btn-ghost btn-sm" id="vresend">Resend code</button></div>
      <div style="text-align:center;margin-top:6px"><button class="btn btn-ghost btn-sm" id="vback">← Use a different email</button></div>
    </div></div></div>`;
  const inp=$('#vcode'); inp.focus();
  inp.addEventListener('input',()=>{ inp.value=inp.value.replace(/\D/g,'').slice(0,6); if(inp.value.length===6) submit(); });
  inp.addEventListener('keydown',(e)=>{ if(e.key==='Enter') submit(); });
  async function submit(){ const code=inp.value.trim(); if(code.length!==6){ toast('Enter the 6-digit code'); return; }
    const btn=$('#vgo'); btn.disabled=true;
    try{ const{user}=await api('/auth/verify-email',{method:'POST',body:{code}}); ME=user; finishAuth('Email verified — welcome! 🎉'); setTimeout(()=>openOnboarding(),350); }
    catch(e){ toast(e.message); btn.disabled=false; inp.focus(); inp.select(); }
  }
  $('#vgo').onclick=submit;
  $('#vresend').onclick=async()=>{ const b=$('#vresend'); b.disabled=true; b.textContent='Sending…';
    try{ await api('/auth/resend-code',{method:'POST'}); toast('New code sent 📧'); }catch(e){ toast(e.message); }
    let s=30; b.textContent='Resend in '+s+'s'; const t=setInterval(()=>{ s--; if(s<=0){ clearInterval(t); b.disabled=false; b.textContent='Resend code'; } else b.textContent='Resend in '+s+'s'; },1000); };
  $('#vback').onclick=()=>{ openAuth(); };
}
/* ---------- complete-profile nudge ---------- */
function profileMissing(){ if(!ME) return false; return !ME.location || !ME.age || !ME.gender; }
function renderProfileNudge(){
  const el=$('#profile-nudge'); if(!el) return;
  let dismissed=false; try{ dismissed=localStorage.getItem('lp_profile_nudge')==='1'; }catch(e){}
  if(!ME || !profileMissing() || dismissed){ el.innerHTML=''; return; }
  el.innerHTML=`<div class="nudge">
    <span class="nudge-ic">✨</span>
    <div class="nudge-txt"><b>Complete your profile</b><span>Add your age, gender and location so we can tailor LinkPoll to you.</span></div>
    <button class="btn btn-dark btn-sm" id="nudge-go">Complete</button>
    <button class="nudge-x" id="nudge-x" aria-label="Dismiss">✕</button></div>`;
  $('#nudge-go').onclick=()=>openOnboarding();
  $('#nudge-x').onclick=()=>{ try{ localStorage.setItem('lp_profile_nudge','1'); }catch(e){} el.innerHTML=''; };
}

/* ---------- detect real location via GPS + reverse geocode ---------- */
let GEO_LL=null; // {lat,lng} captured on last detect
function detectLocation(inputEl, btnEl){
  if(!navigator.geolocation){ toast('Location not supported on this device.'); return; }
  const orig=btnEl?btnEl.textContent:''; if(btnEl){ btnEl.disabled=true; btnEl.textContent='Locating…'; }
  navigator.geolocation.getCurrentPosition(async(pos)=>{
    const {latitude:lat, longitude:lng}=pos.coords; GEO_LL={lat,lng};
    try{
      const r=await fetch('https://api.bigdatacloud.net/data/reverse-geocode-client?latitude='+lat+'&longitude='+lng+'&localityLanguage=en');
      const g=await r.json();
      const city=g.city||g.locality||g.principalSubdivision||'';
      const place=[city, g.countryName].filter(Boolean).join(', ');
      if(inputEl) inputEl.value=place||(lat.toFixed(3)+', '+lng.toFixed(3));
      toast('Location detected 📍');
    }catch(e){ if(inputEl) inputEl.value=lat.toFixed(3)+', '+lng.toFixed(3); toast('Location set from GPS'); }
    if(btnEl){ btnEl.disabled=false; btnEl.textContent=orig; }
  }, (err)=>{ toast(err.code===1?'Location permission denied.':'Could not get location.'); if(btnEl){ btnEl.disabled=false; btnEl.textContent=orig; } }, {enableHighAccuracy:true, timeout:10000});
}

/* ---------- post-signup onboarding ---------- */
function openOnboarding(){
  if(!ME) return; lockScroll();
  const ov=$('#overlay');
  const genders=['Female','Male','Non-binary','Prefer not to say'];
  ov.innerHTML=`<div class="scrim" data-scrim><div class="modal" data-keepmenu style="max-width:460px">
    <div class="mhead"><h3>Tell us about you</h3></div>
    <div class="desc">This helps us tailor polls and people to you. All optional — you can change it anytime in Settings.</div>
    <div class="f"><label>Location</label><div class="loc-row"><input class="field" id="ob-loc" placeholder="City, Country" value="${esc(ME.location||'')}"><button type="button" class="btn btn-ghost btn-sm loc-btn" id="ob-loc-detect">📍 Detect</button></div></div>
    <div class="f"><label>Profession</label><input class="field" id="ob-prof" placeholder="e.g. Student, Designer, Engineer" value="${esc(ME.profession||'')}"></div>
    <div class="frow"><div class="f"><label>Age</label><input class="field" id="ob-age" type="number" min="13" max="120" inputmode="numeric" placeholder="Age" value="${ME.age||''}"></div>
      <div class="f"><label>Gender</label><select class="field" id="ob-gender">
        <option value="">Select…</option>${genders.map((g)=>`<option ${ME.gender===g?'selected':''}>${g}</option>`).join('')}</select></div></div>
    <div class="mfoot"><button class="btn btn-ghost" id="ob-skip">Skip for now</button>
      <button class="btn btn-dark" id="ob-save"><span style="color:var(--lime)">✓ Save</span></button></div>
  </div></div>`;
  $('#ob-skip').onclick=closeOverlay;
  const obd=$('#ob-loc-detect'); if(obd) obd.onclick=()=>detectLocation($('#ob-loc'), obd);
  $('#ob-save').onclick=async()=>{
    const body={ name:ME.name, username:ME.username, bio:ME.bio||'', website:ME.website||'', color:ME.color, avatar:ME.avatar||'',
      location:$('#ob-loc').value, profession:$('#ob-prof').value, age:$('#ob-age').value, gender:$('#ob-gender').value };
    if(GEO_LL){ body.lat=GEO_LL.lat; body.lng=GEO_LL.lng; }
    try{ const{user}=await api('/user/update',{method:'POST',body}); ME=user; try{ localStorage.removeItem('lp_profile_nudge'); }catch(e){} closeOverlay(); chrome(); renderProfileNudge(); toast('All set! 🎉'); }
    catch(e){ toast(e.message); }
  };
}

/* ---------- password reset ---------- */
function openForgot(){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim" data-scrim><div class="modal" data-keepmenu style="max-width:420px">
    <div class="mhead"><h3>Reset your password</h3><button class="close" data-x>✕</button></div>
    <div class="desc">Enter your account email and we'll send you a link to set a new password.</div>
    <div class="f"><label>Email</label><input class="field" id="fg-email" type="email" placeholder="you@example.com"></div>
    <div class="mfoot"><button class="btn btn-ghost" data-x>Cancel</button>
      <button class="btn btn-dark" id="fg-send"><span style="color:var(--lime)">Send reset link</span></button></div>
  </div></div>`;
  $$('[data-x]',ov).forEach((b)=>b.onclick=closeOverlay);
  $('[data-scrim]',ov).onclick=(e)=>{ if(e.target===e.currentTarget)closeOverlay(); };
  $('#fg-send').onclick=async()=>{
    const email=$('#fg-email').value.trim(); if(!email){ toast('Enter your email'); return; }
    const btn=$('#fg-send'); btn.disabled=true;
    try{ await api('/auth/forgot',{method:'POST',body:{email}}); closeOverlay();
      toast('If that email is registered, a reset link is on its way.'); }
    catch(e){ toast(e.message); btn.disabled=false; }
  };
}
function openReset(token){
  lockScroll(); const ov=$('#overlay');
  ov.innerHTML=`<div class="scrim" data-scrim><div class="modal" data-keepmenu style="max-width:420px">
    <div class="mhead"><h3>Choose a new password</h3></div>
    <div class="desc">Enter a new password for your LinkPoll account.</div>
    <div class="f"><label>New password</label><div class="pw"><input class="field" id="rs-pass" type="password" placeholder="at least 6 characters"><button class="eye" data-eye>${ic('eye',16)}</button></div></div>
    <div class="mfoot"><button class="btn btn-ghost" id="rs-cancel">Cancel</button>
      <button class="btn btn-dark" id="rs-go"><span style="color:var(--lime)">Reset password</span></button></div>
  </div></div>`;
  const clear=()=>{ try{ history.replaceState(null,'',location.pathname+location.hash); }catch(e){} };
  $('#rs-cancel').onclick=()=>{ clear(); closeOverlay(); };
  const eye=$('[data-eye]',ov); if(eye) eye.onclick=()=>{ const i=$('#rs-pass'); i.type=i.type==='password'?'text':'password'; };
  $('#rs-go').onclick=async()=>{
    const pass=$('#rs-pass').value; if(pass.length<6){ toast('Password must be at least 6 characters'); return; }
    const btn=$('#rs-go'); btn.disabled=true;
    try{ const{user}=await api('/auth/reset',{method:'POST',body:{token,password:pass}}); ME=user; clear(); finishAuth('Password updated — you\'re signed in.'); }
    catch(e){ toast(e.message); btn.disabled=false; }
  };
}

async function finishAuth(msg){ closeOverlay(); clearViews(); try{ const m=await api('/auth/me'); ME=m.user; UNREAD=m.unread||0; NOTIF_SEEN=m.lastNotif||0; MSG_UNREAD=m.unreadMessages||0; MSG_SEEN=m.lastMsg||0; }catch(e){} chrome(); router(); toast(msg); maybeAskNotify(); }
async function signOut(){ try{ await api('/auth/logout',{method:'POST'}); }catch(e){} ME=null; UNREAD=0; clearViews(); closeMenus(); go('#/'); chrome(); toast('Signed out'); }

/* =====================================================================
   OVERLAY / MENUS / GLOBAL CLICKS
   ===================================================================== */
function closeOverlay(){ $('#overlay').innerHTML=''; unlockScroll(); }
function openUserMenu(anchor){
  closeMenus(); const r=anchor.getBoundingClientRect();
  const m=document.createElement('div'); m.className='menu'; m.dataset.keepmenu='1';
  m.style.left=r.left+'px'; m.style.bottom=(window.innerHeight-r.top+8)+'px';
  m.innerHTML=`<button data-mi="profile">${ic('user',16)} My profile</button>
    <button data-mi="settings">${ic('gear',16)} Settings</button>
    <button class="danger" data-mi="signout">${ic('out',16)} Sign Out</button>`;
  document.body.appendChild(m);
  m.querySelector('[data-mi="profile"]').onclick=()=>{ closeMenus(); go('#/u/'+ME.username); };
  m.querySelector('[data-mi="settings"]').onclick=()=>{ closeMenus(); go('#/settings'); };
  m.querySelector('[data-mi="signout"]').onclick=signOut;
}
document.addEventListener('click',(e)=>{
  if(e.target.closest('[data-create]')){ openCreate(); return; }
  if(e.target.closest('[data-auth]')){ openAuth(); return; }
  if(e.target.closest('[data-signout]')){ signOut(); return; }
  const um=e.target.closest('[data-usermenu]'); if(um){ openUserMenu(um); return; }
  const f=e.target.closest('[data-follow]'); if(f){ quickFollow(f); return; }
  const s=e.target.closest('[data-search]'); if(s){ query=s.dataset.search.replace(/^#/,''); go('#/explore'); return; }
  const tg=e.target.closest('[data-tag]'); if(tg && !e.target.closest('.card')){ closeOverlay(); go('#/tag/'+tg.dataset.tag); return; }
  const a=e.target.closest('[data-author]'); if(a && !e.target.closest('.card') && !e.target.closest('#clist')){ closeOverlay(); go('#/u/'+a.dataset.author); return; }
});
/* Keep every on-screen follow button in sync AND drop cached pages that now
   show a stale follow state, so returning to a profile reflects the change
   without a manual reload. */
function afterFollow(username, following){
  $$('[data-follow]').forEach((b)=>{ if(b.dataset.follow===username){ b.textContent=following?'Following':'Follow'; b.className='btn btn-sm '+(following?'btn-ghost':'btn-dark'); } });
  $$('[data-cfollow]').forEach((b)=>{ if(b.dataset.cfollow===username){ b.style.display=following?'none':''; b.textContent='+ Follow'; } });
  $$('[data-sfollow]').forEach((b)=>{ if(b.dataset.sfollow===username){ b.textContent=following?'Following':'Follow'; b.className='btn btn-sm '+(following?'btn-ghost':'btn-dark'); } });
  $$('[data-xfollow]').forEach((b)=>{ if(b.dataset.xfollow===username){ b.textContent=following?'Following':'Follow'; b.className='btn btn-sm '+(following?'btn-ghost':'btn-dark')+' ex-follow'; } });
  try{ Object.values(POLLS).forEach((p)=>{ if(p.author&&p.author.username===username)p.author.following=following; }); }catch(e){}
  // these cached views are now stale — force a refetch next time they're opened
  try{ dropView('/'+username); if(ME&&ME.username) dropView('/'+ME.username);
    dropView('/network'); dropView('/network/suggested'); dropView('/network/following'); dropView('/network/followers'); }catch(e){}
}
async function quickFollow(btn){ if(!ME){ openAuth(); return; }
  try{ const r=await api('/follow',{method:'POST',body:{username:btn.dataset.follow}}); afterFollow(btn.dataset.follow, r.following); }catch(e){ toast(e.message); } }

/* =====================================================================
   ROUTER + BOOT
   ===================================================================== */
function router(){
  const key=viewKey();
  cacheView();                 // freeze the page we're leaving (DOM + scroll, handlers intact)
  VIEW_LAST_KEY=key;
  closeMenus(); closeDrawer();
  const hit=VIEW_CACHE.get(key);
  if(hit && (Date.now()-hit.ts)<VIEW_TTL && (hit.route!=='home'||hit.tab===feedTab)){
    hit.key=key; VIEW_CACHE.delete(key);   // taking it out of the cache; re-frozen when we leave again
    return restoreView(hit);               // instant: no refetch, no scroll jump
  }
  window.scrollTo(0,0);
  const parts=pathToParts(location.pathname);
  if(!parts.length){ route='home'; return renderHome(); }
  switch(parts[0]){
    case 'explore': route='explore'; return renderExplore();
    case 'following': route='following'; return renderFeedList('following');
    case 'saved': route='saved'; return renderSaved();
    case 'mypolls': route='mypolls'; return renderMyPolls();
    case 'notifications': route='notifications'; return renderNotifications();
    case 'settings': route='settings'; settingsView = (parts[1]&&['profile','password','appearance','account'].includes(parts[1]))?parts[1]:null; return renderSettings();
    case 'insights': route='insights'; return renderInsights();
    case 'points': route='points'; return renderPoints();
    case 'leaderboard': route='leaderboard'; return renderLeaderboard();
    case 'discover': location.hash='#/explore'; return;
    case 'quiz': route='quiz'; return renderQuiz();
    case 'papers': route='papers'; return renderPapers();
    case 'paper': route='paper';
      if(parts[2]==='results') return renderPaperResults(parts[1]);
      return renderPaperTake(parts[1]);
    case 'network': route='network'; return renderNetwork(parts[1]);
    case 'communities': route='communities'; return renderCommunities();
    case 'c': route='community'; return renderCommunity(parts[1]);
    case 'help': case 'about': case 'privacy': case 'terms': case 'guidelines': case 'cookies': case 'updates':
      route=parts[0]; return renderInfoPage(parts[0]);
    case 'support': route='help'; return renderInfoPage('help');
    case 'feedback': route='feedback'; return renderFeedback();
    case 'admin': location.hash='#/'; return;    case 'poll': route='poll'; return renderDetail(parts[1]);
    case 'p': route='poll'; return renderDetail(parts[1]);
    case 'tag': route='tag'; return renderTag(parts[1]);
    case 'messages': route='messages';
      if(parts[1]==='g'&&parts[2]) return renderGroupChat(parts[2]);
      return parts[1]?renderChat(decodeURIComponent(parts[1])):renderInbox();
    case 'u': route='profile'; return renderProfile(parts[1]);
    case 'b': route='biocard'; return renderBioCard(parts[1]);
    case 'links': route='links'; return renderLinksPage();
    case 'reviews': route='myreviews'; return renderReviewsManagePage();
    case 'r': route='reviews'; return renderReviewsPage(parts[1]);
    case 'faq': route='faq'; return renderFaqPage(parts[1]);
    case 'faqs': route='faqs'; return renderFaqsPage();
    case 'widgets': route='widgets'; return renderWidgetsPage();
    default: return renderNotFound();
  }
}
(async function boot(){
  try{ if('scrollRestoration' in history) history.scrollRestoration='manual'; }catch(e){}
  initTheme();
  renderShell();
  registerSW();
  try{ const m=await api('/auth/me'); ME=m.user; UNREAD=m.unread||0; NOTIF_SEEN=m.lastNotif||0; MSG_UNREAD=m.unreadMessages||0; MSG_SEEN=m.lastMsg||0; }catch(e){}
  window.addEventListener('popstate',router);
  window.addEventListener('hashchange',()=>{ if(location.hash&&location.hash.startsWith('#/')){ const p=hashToPath(location.hash);
    try{ history.replaceState({},'',p); }catch(e){} } router(); });
  // intercept internal links so they use clean URLs without a page reload
  document.addEventListener('click',(e)=>{
    const a=e.target.closest&&e.target.closest('a[href]'); if(!a) return;
    if(a.target==='_blank'||a.hasAttribute('download')||e.metaKey||e.ctrlKey||e.shiftKey||e.button) return;
    const href=a.getAttribute('href')||'';
    if(href.startsWith('#/')){ e.preventDefault(); go(href); return; }
    if(href.startsWith('/') && !href.startsWith('//')){ e.preventDefault(); go(href); }
  });
  window.addEventListener('resize',()=>{ if(window.innerWidth>820) closeDrawer(); });
  if(location.hash && location.hash.startsWith('#/')){ try{ history.replaceState({},'',hashToPath(location.hash)); }catch(e){} }
  else if(window.__BOOT && window.__BOOT!=='/' && location.pathname==='/'){ try{ history.replaceState({},'',window.__BOOT); }catch(e){} }
  else if(window.__POLL){ try{ history.replaceState({},'','/p/'+window.__POLL); }catch(e){} }
  router();
  startLive();
  hideSplash();
  /* first-visit tour is opt-in now (see onboarding layer: welcome card -> How it works) */
  // password-reset deep link: /?reset=TOKEN
  try{ const rt=new URLSearchParams(location.search).get('reset'); if(rt) setTimeout(()=>openReset(rt),200); }catch(e){}
  try{ const ge=new URLSearchParams(location.search).get('googleerr'); if(ge){ const msgs={
      notconfigured:'Google sign-in isn’t set up yet. (Admin: add your Client ID/Secret in config.php.)',
      state:'Google sign-in session expired — please try again.',
      access_denied:'You cancelled Google sign-in.',
      redirect_uri_mismatch:'Google redirect URI mismatch — check the authorized URI in Google Cloud Console.',
      token:'Couldn’t verify with Google (token error). Check your Client Secret & redirect URI.',
      email:'Google didn’t share an email for this account.',
      banned:'This account has been suspended.',
      nocode:'Google sign-in was interrupted — please try again.' };
    setTimeout(()=>toast(msgs[ge]||('Google sign-in failed ('+ge+'). Please try email sign-in.')),500);
    history.replaceState(null,'',location.pathname); } }catch(e){}
  document.addEventListener('visibilitychange',()=>{ if(!document.hidden) liveTick(); });
  initScrollHideNav();
  initBackToTop();
  initSheetGestures();
  initEdgeBack();
  initPullRefresh();
  initMessageSwipe();
  initLongPressCard();
})();

const gBusy=()=> document.body.classList.contains('locked') || ($('#side')&&$('#side').classList.contains('open')) || ($('#overlay')&&$('#overlay').innerHTML);

/* (1) Edge-swipe-back: start near the left edge, swipe right → go back.
   Only on non-tab, non-messages routes (those use tab-swipe / message-swipe). */
function initEdgeBack(){
  let x0=0,y0=0,t0=0,live=false;
  document.addEventListener('touchstart',(e)=>{ live=false;
    if(e.touches.length!==1||window.innerWidth>820||gBusy()) return;
    if(SWIPE_TABS.includes(route)||route==='messages') return;
    const t=e.touches[0]; if(t.clientX>26) return;            // must start at the left edge
    x0=t.clientX; y0=t.clientY; t0=Date.now(); live=true;
  },{passive:true});
  document.addEventListener('touchend',(e)=>{ if(!live) return; live=false;
    const t=e.changedTouches[0]; const dx=t.clientX-x0, dy=t.clientY-y0, dt=Date.now()-t0;
    if(dt>700||dx<70||Math.abs(dx)<Math.abs(dy)*1.8) return;
    const bb=$('.back-btn[data-back],.cback[data-back],[data-homeback]');
    try{ if(navigator.vibrate) navigator.vibrate(6); }catch(_){}
    if(bb) bb.click(); else if(history.length>1) history.back(); else go('#/');
  },{passive:true});
}

/* (2) Pull-to-refresh on feed/list pages. */
const PTR_ROUTES=['home','explore','following','network','communities','notifications','saved','mypolls','leaderboard','tag','community','quiz'];
function initPullRefresh(){
  const ind=$('#ptr'); let y0=0, pulling=false, dist=0;
  const scrollTop=()=> (window.scrollY || document.scrollingElement?.scrollTop || document.documentElement.scrollTop || document.body.scrollTop || 0);
  document.addEventListener('touchstart',(e)=>{ pulling=false;
    if(e.touches.length!==1||window.innerWidth>820||gBusy()) return;
    if(!PTR_ROUTES.includes(route)) return;
    if(scrollTop()>2) return;
    y0=e.touches[0].clientY; pulling=true;
  },{passive:true});
  document.addEventListener('touchmove',(e)=>{ if(!pulling||!ind) return;
    dist=e.touches[0].clientY-y0;
    if(dist>0 && scrollTop()<=2){
      const pull=Math.min(dist*0.5,90);
      ind.style.transform='translateX(-50%) translateY('+pull+'px)';
      ind.classList.toggle('ready',pull>60);
      if(dist>8 && e.cancelable) e.preventDefault();
    } else { pulling=false; ind.classList.remove('ready'); ind.style.transform=''; }
  },{passive:false});
  document.addEventListener('touchend',()=>{ if(!pulling||!ind) return; pulling=false;
    const trigger=dist>120;
    if(trigger){ ind.classList.add('spinning'); ind.style.transform='translateX(-50%) translateY(60px)';
      try{ if(navigator.vibrate) navigator.vibrate(8); }catch(_){}
      setTimeout(()=>{ invalidateViews(false); router(); setTimeout(()=>{ ind.classList.remove('spinning','ready'); ind.style.transform=''; },400); },300);
    } else { ind.classList.remove('ready'); ind.style.transform=''; }
    dist=0;
  },{passive:true});
}

/* (3) Swipe a chat message: left on your own → unsend, right → quote-reply. */
function initMessageSwipe(){
  let msg=null,x0=0,y0=0,moved=0,horiz=false;
  document.addEventListener('touchstart',(e)=>{ msg=null; horiz=false;
    if(route!=='messages'||window.innerWidth>820) return;
    const b=e.target.closest?.('.msg[data-mid]'); if(!b) return;
    if(e.target.closest('a,button,.cp-card')) return;
    msg=b; x0=e.touches[0].clientX; y0=e.touches[0].clientY; moved=0;
  },{passive:true});
  document.addEventListener('touchmove',(e)=>{ if(!msg) return;
    const dx=e.touches[0].clientX-x0, dy=e.touches[0].clientY-y0;
    if(!horiz){ if(Math.abs(dx)>10 && Math.abs(dx)>Math.abs(dy)*1.4) horiz=true; else if(Math.abs(dy)>10){ msg=null; return; } }
    if(horiz){ moved=dx; const own=msg.classList.contains('me');
      const clamp = own ? Math.max(-70,Math.min(20,dx)) : Math.max(-20,Math.min(70,dx));
      msg.style.transform='translateX('+clamp+'px)'; msg.style.transition='none';
      if(e.cancelable) e.preventDefault(); }
  },{passive:false});
  document.addEventListener('touchend',()=>{ if(!msg) return; const b=msg; msg=null;
    b.style.transition='transform .18s ease'; b.style.transform='';
    if(!horiz) return; const own=b.classList.contains('me'); const mid=+b.dataset.mid;
    if(own && moved<-45){ unsendMessage(mid, b); }
    else if(moved>45){ quoteReply(b); }
  },{passive:true});
}
function unsendMessage(mid, el){
  if(!confirm('Unsend this message?')) return;
  const url = GROUP_ID ? '/group/message/delete' : '/messages/delete';
  api(url,{method:'POST',body:{id:mid}}).then(()=>{ el.remove(); }).catch((e)=>toast(e.message));
}
function quoteReply(el){
  const txt=(el.querySelector('.bub')?.textContent||'').trim(); if(!txt) return;
  const ta=$('#chat-text'); if(!ta) return;
  const snip = txt.length>80?txt.slice(0,80)+'…':txt;
  ta.value = '↪ '+snip+'\n'+ta.value;
  ta.focus(); ta.style.height='auto'; ta.style.height=Math.min(ta.scrollHeight,120)+'px';
  try{ if(navigator.vibrate) navigator.vibrate(5); }catch(_){}
}

/* (4) Long-press a poll card → quick actions (react / save / share). */
let LP_SUPPRESS_CLICK=false;
function initLongPressCard(){
  document.addEventListener('click',(e)=>{ if(LP_SUPPRESS_CLICK){ LP_SUPPRESS_CLICK=false; e.preventDefault(); e.stopPropagation(); } }, true);
  let timer=null, card=null, sx=0, sy=0;
  const clear=()=>{ if(timer){ clearTimeout(timer); timer=null; } card=null; };
  document.addEventListener('touchstart',(e)=>{ clear();
    if(window.innerWidth>820||gBusy()) return;
    const c=e.target.closest?.('.card[id^="card-"]'); if(!c) return;
    if(e.target.closest('a,button,textarea,input,select,.opt,.gopt,[data-vote],[data-react],[data-share]')) return;
    card=c; sx=e.touches[0].clientX; sy=e.touches[0].clientY;
    timer=setTimeout(()=>{ timer=null; if(!card) return;
      const code=card.id.replace('card-',''); const poll=POLLS[code]; if(!poll) return;
      LP_SUPPRESS_CLICK=true; setTimeout(()=>{ LP_SUPPRESS_CLICK=false; },800);
      try{ if(navigator.vibrate) navigator.vibrate(12); }catch(_){}
      cardQuickActions(code);
    },480);
  },{passive:true});
  document.addEventListener('touchmove',(e)=>{ if(!timer) return;
    if(Math.abs(e.touches[0].clientX-sx)>10||Math.abs(e.touches[0].clientY-sy)>10) clear();
  },{passive:true});
  document.addEventListener('touchend',clear,{passive:true});
}
function cardQuickActions(code){
  const poll=POLLS[code]; if(!poll) return;
  actionSheet([
    {label: poll.myReaction?'Change reaction':'React ❤️', icon:ic('heart',16), onClick:()=>doReact(code, poll.myReaction||'love')},
    {label: poll.saved?'Remove bookmark':'Save', icon:ic('saved',16), onClick:()=>doSave(code)},
    {label:'Share', icon:ic('share',16), onClick:()=>doShare(code)},
    {label:'Copy link', icon:ic('link',16), onClick:async()=>{ try{ await navigator.clipboard.writeText(poll.shareUrl); toast('Link copied!'); }catch(e){ prompt('Copy:',poll.shareUrl); } }},
  ], null, poll.question ? ('“'+(poll.question.length>44?poll.question.slice(0,44)+'…':poll.question)+'”') : 'Poll');
}

/* Swipe a bottom sheet downward to dismiss it (only when its content is
   scrolled to the top, so inner lists still scroll normally). */
function initSheetGestures(){
  let sheet=null, y0=0, x0=0, dragging=false, decided=false, scroller=null;
  // find the nearest actually-scrollable ancestor inside the sheet
  const findScroller=(el, stop)=>{
    while(el && el!==document.body){
      try{ const s=getComputedStyle(el);
        if(/(auto|scroll)/.test(s.overflowY) && el.scrollHeight>el.clientHeight+2) return el;
      }catch(_){}
      if(el===stop) break;
      el=el.parentElement;
    }
    return null;
  };
  document.addEventListener('touchstart',(e)=>{
    dragging=false; decided=false; sheet=e.target.closest?.('.sheet'); if(!sheet) return;
    // never start a dismiss-drag from a text field (keyboard/caret) 
    if(e.target.closest('input,textarea,select,button,a')) { /* still allow, but let taps work */ }
    scroller=findScroller(e.target, sheet);
    y0=e.touches[0].clientY; x0=e.touches[0].clientX;
  },{passive:true});
  document.addEventListener('touchmove',(e)=>{
    if(!sheet) return;
    const dy=e.touches[0].clientY-y0, dx=e.touches[0].clientX-x0;
    // decide gesture once: if the inner list can still scroll, let it — don't drag the sheet
    if(!decided){
      if(Math.abs(dy)<3 && Math.abs(dx)<3) return;         // too small to tell yet
      const canScrollUp = scroller && scroller.scrollTop>0;  // there's content above → user is scrolling
      // only claim the drag when pulling DOWN from the very top of the content
      if(dy>0 && !canScrollUp && Math.abs(dy)>Math.abs(dx)){ dragging=true; }
      decided=true;
    }
    if(dragging){ const d=Math.max(0,dy);
      sheet.style.transition='none'; sheet.style.transform='translateY('+d+'px)';
      if(e.cancelable) e.preventDefault();
    }
    // otherwise: do nothing → native scrolling of the inner list works normally
  },{passive:false});
  document.addEventListener('touchend',()=>{
    if(!sheet){ return; } const s=sheet; sheet=null;
    if(!dragging){ decided=false; return; } dragging=false; decided=false;
    const m=/translateY\(([-\d.]+)px\)/.exec(s.style.transform||''); const dy=m?parseFloat(m[1]):0;
    s.style.transition='transform .22s cubic-bezier(.22,1,.3,1)';
    if(dy>90){ s.style.transform='translateY(100%)'; setTimeout(()=>closeOverlay(),180); }
    else { s.style.transform='translateY(0)'; }
  },{passive:true});
}

/* ---------- mobile swipe-between-tabs + tactile feedback ---------- */
const SWIPE_TABS=['home','explore','notifications','profile'];
function tabHash(r){ if(r==='home')return '#/'; if(r==='profile')return ME?('#/u/'+ME.username):'#/'; return '#/'+r; }
function animateMain(dir){ const m=$('#main'); if(!m)return; const cls=dir>0?'swipe-in-right':'swipe-in-left';
  m.classList.remove('swipe-in-right','swipe-in-left'); void m.offsetWidth; m.classList.add(cls);
  setTimeout(()=>m.classList.remove(cls),320); }
function initSwipeNav(){
  let x0=0,y0=0,t0=0,tracking=false;
  const blocked=(el)=> !el||!el.closest ? false : !!el.closest('.story-tray,.stories,.chat-wrap,.opts.grid,.gopt,input,textarea,select,[contenteditable],.no-swipe,.sheet,.modal,.comm-scroll,.lb-list');
  document.addEventListener('touchstart',(e)=>{
    tracking=false;
    if(e.touches.length!==1 || window.innerWidth>820) return;
    if(document.body.classList.contains('locked')) return;         // modal / drawer open
    if($('#side')?.classList.contains('open')) return;
    if($('#overlay') && $('#overlay').innerHTML) return;
    if(!SWIPE_TABS.includes(route) || route==='home') return;        // home is handled by the feed-tab swipe
    if(blocked(e.target)) return;                                    // not on horizontal scrollers/inputs
    const t=e.touches[0]; x0=t.clientX; y0=t.clientY; t0=Date.now(); tracking=true;
  },{passive:true});
  document.addEventListener('touchend',(e)=>{
    if(!tracking) return; tracking=false;
    const t=e.changedTouches[0]; const dx=t.clientX-x0, dy=t.clientY-y0, dt=Date.now()-t0;
    if(dt>600 || Math.abs(dx)<60 || Math.abs(dx)<Math.abs(dy)*1.6) return; // fast, far enough, mostly horizontal
    const cur=SWIPE_TABS.indexOf(route); if(cur<0) return;
    const ni = dx<0 ? cur+1 : cur-1; if(ni<0||ni>=SWIPE_TABS.length) return;
    try{ if(navigator.vibrate) navigator.vibrate(8); }catch(_){}
    animateMain(dx<0?1:-1);
    go(tabHash(SWIPE_TABS[ni]));
  },{passive:true});
}

/* Mobile: hide top header + bottom nav while scrolling down, reveal when
   scrolling up (or near the top). Desktop is unaffected (nav is a sidebar). */
function initScrollHideNav(){
  let last=window.scrollY||0, ticking=false;
  const THRESH=6;
  const update=()=>{
    ticking=false;
    const y=window.scrollY||0;
    // always show near the very top
    if(y<40){ document.body.classList.remove('nav-hidden'); last=y; return; }
    const dy=y-last;
    if(Math.abs(dy)<THRESH){ return; }
    if(dy>0) document.body.classList.add('nav-hidden');      // scrolling down → hide
    else document.body.classList.remove('nav-hidden');       // scrolling up → show
    last=y;
  };
  window.addEventListener('scroll',()=>{ if(!ticking){ ticking=true; requestAnimationFrame(update); } }, {passive:true});
}

/* UX: Enter/Space activates div-based buttons (poll options etc.) */
document.addEventListener('keydown',e=>{ if(e.key!=='Enter'&&e.key!==' ')return; const t=e.target; if(!t||!t.matches||!t.matches('[role="button"]:not(button):not(a)'))return; e.preventDefault(); t.click(); });

/* ===== LinkPoll layout helpers (design system layer v1) ===== */
(function(){
  // route name on <body> so CSS can adapt the layout per page
  function setRoute(){ var r=(typeof route!=='undefined'&&route)||location.pathname.split('/')[1]||(location.hash.replace(/^#\/?/,'').split(/[\/?]/)[0])||'home'; if(document.body) document.body.setAttribute('data-route', r); }
  window.addEventListener('hashchange', setRoute); window.addEventListener('popstate', setRoute); setRoute();
  // footer links live at the bottom of the right column (not the left nav)
  var FOOT=[['help','Help'],['about','About'],['updates',"What's new"],['feedback','Send feedback'],['status','Status'],['developers','Developers'],['guidelines','Guidelines'],['privacy','Privacy'],['terms','Terms'],['cookie-settings','Cookie settings']];
  function ensureFoot(){
    var r=document.getElementById('rail'); if(!r || r.querySelector(':scope > .rail-foot')) return;
    var f=document.createElement('footer'); f.className='rail-foot';
    f.innerHTML='<nav aria-label="Footer">'+FOOT.map(function(x){return '<a href="#/'+x[0]+'">'+x[1]+'</a>';}).join('')+'</nav><p>LinkPoll © '+new Date().getFullYear()+'</p>';
    r.appendChild(f);
  }
  var t=null; new MutationObserver(function(){ if(t) return; t=setTimeout(function(){ t=null; ensureFoot(); setRoute(); }, 60); }).observe(document.documentElement,{childList:true,subtree:true});
  ensureFoot();
})();

/* ===== LinkPoll motion layer (Nov 2026) - see README.md "Motion" =====
   - sliding underline for every .tabs bar (keeps position across re-renders)
   - content slides in from the direction of the tab you picked
   - page-enter fade when the route changes
   - swipe left/right on touch screens switches tabs
   - scroll position restored when you go back
*/
(function(){
  var RM = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var main = function(){ return document.getElementById('main'); };
  var lastInk = {};          // tabs signature -> {x,w}
  var lastTabIdx = {};       // tabs signature -> active index
  var pendingDir = 0;        // +1 moved right, -1 moved left
  var lastPath = location.pathname + location.search;

  function sig(t){ return (location.pathname.split('/')[1]||'home') + '|' + tabButtons(t).length + '|' + (t.className||''); }
  function tabButtons(t){ return [].slice.call(t.children).filter(function(c){ return /^(A|BUTTON)$/.test(c.tagName); }); }
  function activeIdx(t){ var b=tabButtons(t); for(var i=0;i<b.length;i++){ if(b[i].classList.contains('active')||b[i].classList.contains('on')||b[i].getAttribute('aria-selected')==='true') return i; } return -1; }

  function placeInk(t, animate){
    var b=tabButtons(t), i=activeIdx(t); if(i<0) return;
    var ink=t.querySelector(':scope > .tab-ink');
    if(!ink){ ink=document.createElement('span'); ink.className='tab-ink'; ink.setAttribute('aria-hidden','true'); t.appendChild(ink); t.classList.add('has-ink'); }
    var a=b[i], x=a.offsetLeft, w=a.offsetWidth, k=sig(t), prev=lastInk[k];
    if(animate && prev && !RM && (prev.x!==x || prev.w!==w)){
      ink.style.transition='none'; ink.style.transform='translateX('+prev.x+'px)'; ink.style.width=prev.w+'px';
      void ink.offsetWidth; ink.style.transition='';
    }
    ink.style.transform='translateX('+x+'px)'; ink.style.width=w+'px';
    if(lastTabIdx[k]!==undefined && lastTabIdx[k]!==i && !pendingDir) pendingDir = i>lastTabIdx[k]?1:-1;
    lastInk[k]={x:x,w:w}; lastTabIdx[k]=i;
  }
  function syncAll(animate){ [].forEach.call(document.querySelectorAll('.tabs'), function(t){ if(t.offsetParent) placeInk(t, animate); }); }

  // remember direction when a tab is clicked (before the app re-renders)
  document.addEventListener('click', function(e){
    var btn=e.target.closest && e.target.closest('.tabs > a, .tabs > button'); if(!btn) return;
    var t=btn.parentElement, b=tabButtons(t), to=b.indexOf(btn), from=activeIdx(t);
    if(to>=0 && from>=0 && to!==from) pendingDir = to>from?1:-1;
    setTimeout(function(){ syncAll(true); playTabIn(); }, 0);
  }, true);

  function playTabIn(){
    if(RM || !pendingDir) return; var m=main(); if(!m) return;
    var t=m.querySelector('.tabs'); var nodes=[].slice.call(m.children).filter(function(c){ return c!==t && !c.contains(t); });
    var cls = pendingDir>0 ? 'tab-in-r' : 'tab-in-l'; pendingDir=0;
    nodes.forEach(function(n){ n.classList.remove('tab-in-r','tab-in-l'); void n.offsetWidth; n.classList.add(cls); setTimeout(function(){ n.classList.remove(cls); }, 320); });
  }
  function playPageIn(){
    if(RM) return; var m=main(); if(!m) return;
    m.classList.remove('pg-in'); void m.offsetWidth; m.classList.add('pg-in'); setTimeout(function(){ m.classList.remove('pg-in'); }, 300);
  }

  // ----- scroll memory -----
  var scrollMem = {};
  function remember(){ scrollMem[location.pathname + location.search] = window.scrollY; }
  var _push = history.pushState;
  history.pushState = function(){ remember(); return _push.apply(this, arguments); };
  var restoreTo = null;
  window.addEventListener('popstate', function(){ var y=scrollMem[location.pathname+location.search]; restoreTo = (typeof y==='number') ? y : null; });
  function tryRestore(){
    if(restoreTo===null) return; var y=restoreTo;
    [60,250,600,1100].forEach(function(ms){ setTimeout(function(){ if(restoreTo!==y) return; if(document.documentElement.scrollHeight - innerHeight >= y - 4){ window.scrollTo(0,y); } }, ms); });
    setTimeout(function(){ if(restoreTo===y) restoreTo=null; }, 1200);
  }

  // ----- react to app renders -----
  var timer=null;
  new MutationObserver(function(){
    if(timer) return;
    timer=setTimeout(function(){
      timer=null;
      var p=location.pathname+location.search;
      if(p!==lastPath){ lastPath=p; pendingDir=0; playPageIn(); tryRestore(); syncAll(false); }
      else { syncAll(true); playTabIn(); }
    }, 30);
  }).observe(document.documentElement, {childList:true, subtree:true});
  window.addEventListener('resize', function(){ syncAll(false); });

  // ----- swipe between tabs (touch) -----
  var sx=0, sy=0, st=0, ok=false;
  document.addEventListener('touchstart', function(e){
    var m=main(); if(!m || !m.contains(e.target) || e.touches.length!==1){ ok=false; return; }
    if(e.target.closest('input,textarea,select,[contenteditable],.story-tray,.trend-strip,.tabs,[data-noswipe]')){ ok=false; return; }
    var el=e.target; while(el && el!==m){ if(el.scrollWidth>el.clientWidth+4){ var ox=getComputedStyle(el).overflowX; if(ox==='auto'||ox==='scroll'){ ok=false; return; } } el=el.parentElement; }
    sx=e.touches[0].clientX; sy=e.touches[0].clientY; st=Date.now(); ok=true;
  }, {passive:true});
  document.addEventListener('touchend', function(e){
    if(!ok) return; ok=false;
    var dx=e.changedTouches[0].clientX-sx, dy=e.changedTouches[0].clientY-sy;
    if(Math.abs(dx)<70 || Math.abs(dy)>Math.abs(dx)*0.6 || Date.now()-st>700) return;
    var t=main() && main().querySelector('.tabs'); if(!t) return;
    var b=tabButtons(t), i=activeIdx(t), j=i+(dx<0?1:-1);
    if(i<0 || j<0 || j>=b.length) return;
    b[j].click();
    try{ b[j].scrollIntoView({block:'nearest', inline:'center', behavior: RM?'auto':'smooth'}); }catch(_){}
  }, {passive:true});

  syncAll(false);
})();

/* ===== LinkPoll loading layer (Nov 2026) - see README.md "Loading" =====
   - skeleton post cards while a feed loads (3 on first load / tab switch, 1 at the bottom for the next page)
   - thin progress bar at the top while API requests are in flight
   - images fade in when they finish loading
*/
(function(){
  // ---------- skeleton cards ----------
  function skel(){
    var d=document.createElement('div'); d.className='lp-skel'; d.setAttribute('aria-hidden','true');
    d.innerHTML='<div class="sk-h"><i class="sk-av"></i><div class="sk-hl"><i class="sk-l" style="width:38%"></i><i class="sk-l" style="width:18%"></i></div></div>'+
      '<i class="sk-t"></i><i class="sk-o"></i><i class="sk-o"></i><i class="sk-o" style="width:72%"></i>'+
      '<div class="sk-f"><i class="sk-l"></i><i class="sk-l"></i><i class="sk-l"></i></div>';
    return d;
  }
  function syncSkel(){
    // The app renders one shimmer card (.sk-wrap > .sk-card) inside .feed-more while loading.
    // On an empty feed (first load / tab switch) show three, like other social apps.
    [].forEach.call(document.querySelectorAll('#main .feed-more .sk-wrap:not([data-lpx])'), function(w){
      w.setAttribute('data-lpx','1');
      var feed=w.closest('.feed-more') && w.closest('.feed-more').parentElement;
      if(!feed || feed.querySelector(':scope > .card, :scope > article')) return;
      var c=w.querySelector('.sk-card'); if(!c || w.querySelectorAll('.sk-card').length>1) return;
      w.appendChild(c.cloneNode(true)); w.appendChild(c.cloneNode(true));
    });
  }

  // ---------- image fade-in ----------
  function fadeImgs(){
    [].forEach.call(document.querySelectorAll('#main img:not([data-lpf]), #rail img:not([data-lpf])'), function(img){
      img.setAttribute('data-lpf','1');
      if(img.complete && img.naturalWidth) return;
      img.classList.add('lp-fade');
      var done=function(){ img.classList.add('lp-in'); };
      img.addEventListener('load', done, {once:true}); img.addEventListener('error', done, {once:true});
    });
  }

  // ---------- top progress bar ----------
  var bar=document.createElement('div'); bar.id='lp-progress'; bar.setAttribute('aria-hidden','true');
  (document.body||document.documentElement).appendChild(bar);
  var inflight=0, showT=null, doneT=null;
  function start(){ inflight++; if(inflight===1){ clearTimeout(doneT); showT=setTimeout(function(){ bar.className='run'; }, 150); } }
  function end(){ inflight=Math.max(0,inflight-1); if(inflight===0){ clearTimeout(showT); if(bar.className==='run'){ bar.className='done'; doneT=setTimeout(function(){ bar.className=''; }, 350); } } }
  if(window.fetch){
    var _f=window.fetch;
    window.fetch=function(input){
      var url=(typeof input==='string')?input:(input&&input.url)||'';
      var track=/api\.php/.test(url) && !/path=%2F(live|notifications\/count|messages\/unread)|path=\/(live|notifications\/count|messages\/unread)/.test(url);
      if(track) start();
      var p=_f.apply(this, arguments);
      if(track) p.then(end, end);
      return p;
    };
  }

  var t=null;
  new MutationObserver(function(){ if(t) return; t=setTimeout(function(){ t=null; syncSkel(); fadeImgs(); }, 16); })
    .observe(document.documentElement,{childList:true,subtree:true});
  syncSkel(); fadeImgs();
})();

/* ===== LinkPoll links & titles layer (Nov 2026) - see README.md "Links & titles" =====
   - names, avatars and poll titles become real <a href> links:
     hover shows the URL, Ctrl/Cmd/middle-click opens a new tab, right-click "Copy link address" works.
     A plain click still navigates inside the app (no page reload).
   - every page gets its own browser-tab title (also used by history and bookmarks)
*/
(function(){
  function hrefFor(el){
    if(el.hasAttribute('data-author')){ var u=el.getAttribute('data-author'); return u ? '/'+encodeURIComponent(u) : null; }
    if(el.hasAttribute('data-open')){ var c=el.getAttribute('data-open'); return /^[A-Za-z0-9_-]{3,40}$/.test(c) ? '/p/'+c : null; }
    return null;
  }
  function linkify(){
    [].forEach.call(document.querySelectorAll('[data-author]:not([data-lpl]),[data-open]:not([data-lpl])'), function(el){
      el.setAttribute('data-lpl','1');
      var h=hrefFor(el); if(!h || el.closest('a') || el.querySelector('a,button,input')) return;
      var a=document.createElement('a'); a.href=h; a.className='lp-link'+(el.classList.contains('av')?' lp-link-av':'');
      a.setAttribute('tabindex','-1');
      while(el.firstChild) a.appendChild(el.firstChild);
      el.appendChild(a);
    });
  }
  // plain click -> let the app handle it (SPA); modified click -> browser opens a new tab
  document.addEventListener('click', function(e){
    var a=e.target.closest && e.target.closest('a.lp-link'); if(!a) return;
    if(e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.button!==0){ e.stopImmediatePropagation(); return; }
    e.preventDefault();
  }, true);

  // ---------- per-page titles ----------
  var LABEL={home:'',explore:'Explore',faqs:'Discussions',communities:'Communities',quiz:'Quizzes',papers:'Mock Papers',paper:'Mock Paper',
    mypolls:'My Polls',insights:'Insights',saved:'Saved',network:'Network',points:'Points & badges',leaderboard:'Leaderboard',
    links:'Link in bio',widgets:'Widgets',reviews:'Reviews',settings:'Settings',messages:'Messages',notifications:'Notifications',
    help:'Help Center',about:'About',updates:"What's new",feedback:'Send feedback',privacy:'Privacy Policy',terms:'Terms of Service',
    guidelines:'Community Guidelines',following:'Following',discover:'Explore',support:'Support',faq:'Discussion'};
  var BRAND='LinkPoll', HOME_TITLE="LinkPoll — the world's opinion, live";
  function txt(el){ return el ? el.textContent.replace(/\s+/g,' ').trim() : ''; }
  function pageTitle(){
    var m=document.getElementById('main'); if(!m) return null;
    var seg=(location.pathname.split('/')[1]||'').toLowerCase();
    var r=(typeof route!=='undefined' && route) || seg || 'home';
    var pn=m.querySelector('h1.profile-name');
    if(pn){ var hd=txt(m.querySelector('.handle')).split(/\s/)[0]; return txt(pn)+(hd?' ('+hd+')':''); }
    if(seg==='p' || r==='poll' || r==='p'){ var q=m.querySelector('.card .q, .q'); if(q){ var c=q.cloneNode(true); [].forEach.call(c.querySelectorAll('.badge,[class*="badge"],[class*="tag"],[class*="chip"]'),function(b){b.remove();}); var t=txt(c); if(t) return t.length>70?t.slice(0,67)+'…':t; } return 'Poll'; }
    if(r==='c' || seg==='c'){ var h=txt(m.querySelector('h1,h2')); return h || 'Community'; }
    if(r==='tag' || seg==='tag'){ return '#'+decodeURIComponent(location.pathname.split('/')[2]||''); }
    if(r==='home' || seg==='') return '';
    if(LABEL[r]!==undefined) return LABEL[r];
    var h1=txt(m.querySelector('h1')); return h1 || null;
  }
  function syncTitle(){
    var t=pageTitle(); if(t===null) return;
    var want = t ? t+' — '+BRAND : HOME_TITLE;
    if(document.title!==want) document.title=want;
    var can=document.querySelector('link[rel="canonical"]'); var url=location.origin+location.pathname;
    if(can && can.getAttribute('href')!==url) can.setAttribute('href',url);
  }

  var t=null;
  new MutationObserver(function(){ if(t) return; t=setTimeout(function(){ t=null; linkify(); syncTitle(); }, 40); })
    .observe(document.documentElement,{childList:true,subtree:true});
  window.addEventListener('popstate', function(){ setTimeout(syncTitle, 60); });
  linkify(); syncTitle();
})();

/* ===== LinkPoll onboarding layer (Nov 2026) - see README.md "New users" =====
   Built for people arriving from LinkedIn, Reddit and Quora: content first, explain in one line,
   let them vote without an account, then invite them to join at the right moment. */
(function(){
  var LS={get:function(k){try{return localStorage.getItem(k);}catch(e){return null;}},set:function(k,v){try{localStorage.setItem(k,v);}catch(e){}}};
  var SS={get:function(k){try{return sessionStorage.getItem(k);}catch(e){return null;}},set:function(k,v){try{sessionStorage.setItem(k,v);}catch(e){}}};
  function me(){ try{ return (typeof ME!=='undefined' && ME) ? ME : null; }catch(e){ return null; } }
  function rt(){ try{ return (typeof route!=='undefined' && route) || (location.pathname.split('/')[1]||'home'); }catch(e){ return 'home'; } }
  function isHome(){ var r=rt(); return r==='home' || r==='' || location.pathname==='/'; }
  function join(){ try{ authMode='signup'; }catch(e){} try{ openAuth(); }catch(e){ location.href='/'; } }
  function tour(){ try{ showOnboarding(true); }catch(e){} }
  function el(html){ var d=document.createElement('div'); d.innerHTML=html.trim(); return d.firstChild; }
  var I={ vote:'<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>',
          users:'<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
          ask:'<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
          check:'<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>' };

  // ---------- remember returning members (sign-in vs create-account default) ----------
  function syncAuthDefault(){
    var m=me(); if(m){ LS.set('lp_returning','1'); return; }
    var at=document.getElementById('at'); if(!at) return;
    var form=at.closest('.auth-form')||document;
    var up=/Join/i.test(at.textContent);
    var fg=form.querySelector('#a-forgot'); if(fg) fg.parentElement.style.display = up ? 'none' : '';
    var n=form.querySelector('#aname'), e=form.querySelector('#aemail'), p=form.querySelector('#apass');
    if(n){ n.setAttribute('autocomplete','name'); }
    if(e){ e.setAttribute('autocomplete','email'); e.setAttribute('inputmode','email'); e.setAttribute('autocapitalize','off'); e.setAttribute('spellcheck','false'); }
    if(p){ p.setAttribute('autocomplete', up ? 'new-password' : 'current-password'); }
  }

  // ---------- guests: menu shows only what works without an account ----------
  var GUEST_HIDE=['#/mypolls','#/insights','#/saved','#/network','#/points','#/links','#/widgets','#/reviews','#/settings'];
  function syncGuest(){
    var g=!me(); document.body.classList.toggle('lp-guest', g);
    [].forEach.call(document.querySelectorAll('#side .nav-group:not([data-g])'), function(x){ x.setAttribute('data-g', x.textContent.trim().toLowerCase().replace(/\s+/g,'-')||'gap'); });
    [].forEach.call(document.querySelectorAll('#side .nav a'), function(a){ a.classList.toggle('lp-member-only', GUEST_HIDE.indexOf(a.getAttribute('href'))>=0); });
    var side=document.querySelector('#side .nav');
    if(side && g && !side.querySelector('.lp-guest-cta')){
      side.appendChild(el('<div class="lp-guest-cta"><b>Your polls, saved posts and network live here.</b><span>Free account · takes 20 seconds</span><button type="button" class="btn btn-lime btn-sm" data-lp-join>Join LinkPoll</button></div>'));
    }
  }

  // ---------- guests: welcome card on the home feed ----------
  function syncWelcome(){
    var m=document.getElementById('main'); if(!m) return;
    var ex=m.querySelector('.lp-welcome');
    var want = !me() && isHome() && !LS.get('lp_welcome_x');
    if(!want){ if(ex) ex.remove(); return; }
    if(ex) return;
    var card=el('<section class="lp-welcome" aria-label="Welcome to LinkPoll">'+
      '<button type="button" class="lp-x" aria-label="Dismiss" data-lp-welcome-x>✕</button>'+
      '<h2>New to LinkPoll?</h2>'+
      '<p class="lp-lead">Every post here is a question with a vote — like a LinkedIn poll, a Reddit thread or a Quora question, with live results.</p>'+
      '<ul class="lp-points">'+
        '<li>'+I.vote+'<span><b>Vote in one tap</b> — no account needed</span></li>'+
        '<li>'+I.users+'<span><b>Join communities</b> on topics you care about</span></li>'+
        '<li>'+I.ask+'<span><b>Ask your own question</b> and watch answers come in live</span></li>'+
      '</ul>'+
      '<div class="lp-acts"><button type="button" class="btn btn-lime" data-lp-join>Join free</button><button type="button" class="btn btn-ghost" data-lp-tour>How it works</button></div>'+
    '</section>');
    var tabs=m.querySelector(':scope > .tabs');
    if(tabs && tabs.nextSibling) m.insertBefore(card, tabs.nextSibling); else m.insertBefore(card, m.firstChild);
  }

  // ---------- guests arriving from another site ----------
  function source(){
    var r=document.referrer||''; if(!r) return null;
    try{ var h=new URL(r).hostname.replace(/^www\./,''); if(h===location.hostname) return null;
      if(/linkedin|lnkd\.in/.test(h)) return 'LinkedIn'; if(/reddit|redd\.it/.test(h)) return 'Reddit'; if(/quora/.test(h)) return 'Quora';
      if(/facebook|fb\.com|fb\.me/.test(h)) return 'Facebook'; if(/(^|\.)x\.com$|twitter|t\.co$/.test(h)) return 'X'; if(/whatsapp/.test(h)) return 'WhatsApp';
      if(/google|bing|duckduckgo|yahoo/.test(h)) return null; return 'there'; }catch(e){ return null; }
  }
  var SRC=source();
  function syncArrival(){
    var m=document.getElementById('main'); if(!m) return;
    var ex=m.querySelector('.lp-arrive');
    if(me() || !SRC || SS.get('lp_arrive_x')){ if(ex) ex.remove(); return; }
    if(ex) return;
    var r=rt(); var onPoll=(r==='p'||r==='poll'||location.pathname.indexOf('/p/')===0);
    var msg = onPoll ? 'Tap an option to vote — no sign-up needed. You’ll see the live results instantly.'
                     : 'LinkPoll is where people ask questions as polls. Vote on anything — no sign-up needed.';
    var b=el('<div class="lp-arrive" role="status"><span class="lp-wave" aria-hidden="true">👋</span><span><b>Welcome'+(SRC==='there'?'':' from '+SRC)+'!</b> '+msg+'</span><button type="button" class="lp-x" aria-label="Dismiss" data-lp-arrive-x>✕</button></div>');
    m.insertBefore(b, m.firstChild);
  }

  // ---------- after a guest votes: invite them in (once per visit) ----------
  var lastCard=null;
  document.addEventListener('click', function(e){
    var o=e.target.closest && e.target.closest('.opt[data-vote]'); if(o) lastCard=(o.getAttribute('data-vote')||'').split('|')[0];
    var t=e.target.closest && e.target.closest('[data-lp-join],[data-lp-tour],[data-lp-welcome-x],[data-lp-arrive-x],[data-lp-after-x],[data-lp-check-x],[data-lp-ask]');
    if(!t) return;
    e.preventDefault();
    if(t.hasAttribute('data-lp-join')) join();
    else if(t.hasAttribute('data-lp-tour')) tour();
    else if(t.hasAttribute('data-lp-welcome-x')){ LS.set('lp_welcome_x','1'); var w=t.closest('.lp-welcome'); if(w) w.remove(); }
    else if(t.hasAttribute('data-lp-arrive-x')){ SS.set('lp_arrive_x','1'); var a=t.closest('.lp-arrive'); if(a) a.remove(); }
    else if(t.hasAttribute('data-lp-after-x')){ var c=t.closest('.lp-after'); if(c) c.remove(); }
    else if(t.hasAttribute('data-lp-check-x')){ LS.set('lp_check_x','1'); var k=t.closest('.lp-check'); if(k) k.remove(); }
    else if(t.hasAttribute('data-lp-ask')){ var cb=document.querySelector('.dt-create,[data-create]'); if(cb) cb.click(); }
  }, true);
  function afterGuestVote(){
    var card=lastCard && document.getElementById('card-'+lastCard); if(me() || SS.get('lp_after_shown') || !card) return;
    SS.set('lp_after_shown','1');
    var c=el('<div class="lp-after" role="status"><div class="lp-after-ic">'+I.check+'</div><div class="lp-after-body"><b>Vote counted!</b><span>Got a question of your own? Create a free account to post polls, follow topics and see how your network votes.</span><div class="lp-acts"><button type="button" class="btn btn-lime btn-sm" data-lp-join>Create free account</button><button type="button" class="btn btn-ghost btn-sm" data-lp-after-x>Keep browsing</button></div></div></div>');
    card.parentNode.insertBefore(c, card.nextSibling);
  }

  // ---------- new members: "Get started" checklist ----------
  function n(k){ return parseInt(LS.get(k)||'0',10)||0; }
  function bump(k){ LS.set(k, String(n(k)+1)); }
  function syncChecklist(){
    var m=document.getElementById('main'); if(!m) return;
    var ex=m.querySelector('.lp-check'); var u=me();
    var steps = u ? [
      ['Create your account', true, ''],
      ['Vote on 3 polls', n('lp_ob_votes')>=3, n('lp_ob_votes')+'/3'],
      ['Follow 3 people', n('lp_ob_follows')>=3, n('lp_ob_follows')+'/3', '/network'],
      ['Join a community', n('lp_ob_joins')>=1, '', '/communities'],
      ['Ask your first question', n('lp_ob_polls')>=1, '', 'ask']
    ] : [];
    var done=steps.filter(function(s){return s[1];}).length;
    var want = u && isHome() && !LS.get('lp_check_x') && done<steps.length && (u.points||0) < 200;
    if(!want){ if(ex) ex.remove(); return; }
    var sig=done+'|'+steps.map(function(s){return s[2];}).join(',');
    if(ex && ex.getAttribute('data-sig')===sig) return;
    var pct=Math.round(done/steps.length*100);
    var li=steps.map(function(s){
      var act = s[1] ? '' : (s[3]==='ask' ? '<button type="button" class="lp-step-go" data-lp-ask>Ask</button>' : (s[3] ? '<a class="lp-step-go" href="'+s[3]+'">Go</a>' : ''));
      return '<li class="'+(s[1]?'done':'')+'"><span class="lp-tick">'+(s[1]?I.check:'')+'</span><span class="lp-step">'+s[0]+(s[2]&&!s[1]?' <em>'+s[2]+'</em>':'')+'</span>'+act+'</li>';
    }).join('');
    var c=el('<section class="lp-check" data-sig="'+sig+'" aria-label="Get started"><button type="button" class="lp-x" aria-label="Dismiss" data-lp-check-x>✕</button>'+
      '<div class="lp-check-h"><h2>Get started on LinkPoll</h2><span>'+done+' of '+steps.length+'</span></div>'+
      '<div class="lp-bar"><i style="width:'+pct+'%"></i></div><ul>'+li+'</ul></section>');
    if(ex){ ex.replaceWith(c); return; }
    var tabs=m.querySelector(':scope > .tabs');
    if(tabs && tabs.nextSibling) m.insertBefore(c, tabs.nextSibling); else m.insertBefore(c, m.firstChild);
  }

  // ---------- watch actions (works for any API call style) ----------
  if(window.fetch){
    var _f=window.fetch;
    window.fetch=function(input, init){
      var url=''; try{ url=decodeURIComponent((typeof input==='string')?input:(input&&input.url)||''); }catch(e){}
      var method=((init&&init.method)||(input&&input.method)||'GET').toUpperCase();
      var p=_f.apply(this, arguments);
      if(method==='POST' && /api\.php/.test(url)){
        p.then(function(r){ if(!r || !r.ok) return;
          if(/path=\/vote(\b|&|$)/.test(url)){ bump('lp_ob_votes'); setTimeout(afterGuestVote, 700); }
          else if(/path=\/follow(\b|&|$)/.test(url)) bump('lp_ob_follows');
          else if(/path=\/community\/join/.test(url)) bump('lp_ob_joins');
          else if(/path=\/polls(\b|&|$)/.test(url)) bump('lp_ob_polls');
          setTimeout(syncChecklist, 50);
        }, function(){});
      }
      return p;
    };
  }

  var t=null;
  function syncAll(){ syncAuthDefault(); syncGuest(); syncArrival(); syncWelcome(); syncChecklist(); }
  new MutationObserver(function(){ if(t) return; t=setTimeout(function(){ t=null; syncAll(); }, 80); })
    .observe(document.documentElement,{childList:true,subtree:true});
  syncAll();
})();

/* ===== LinkPoll options layer (Nov 2026) - see README.md "Options" =====
   1) Poll "⋯" menu: grouped (Share · Person · Feedback) with new helpful items
   2) Create Poll: one-tap answer templates, question ideas, clear "add image" button
   3) Hide posts from a person (local, with Undo) */
(function(){
  var SITE=location.origin;
  function say(m){ try{ toast(m); }catch(e){} }
  function copy(t){ if(navigator.clipboard && navigator.clipboard.writeText) return navigator.clipboard.writeText(t); var a=document.createElement('textarea'); a.value=t; document.body.appendChild(a); a.select(); try{document.execCommand('copy');}catch(e){} a.remove(); return Promise.resolve(); }
  function LSget(k,d){ try{ return JSON.parse(localStorage.getItem(k)||'null')||d; }catch(e){ return d; } }
  function LSset(k,v){ try{ localStorage.setItem(k, JSON.stringify(v)); }catch(e){} }
  var SVG=function(p){ return '<svg class="ico" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'+p+'</svg>'; };
  var IC={
    post:SVG('<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>'),
    li:SVG('<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 10v7M8 7v.01M12 17v-4a2 2 0 0 1 4 0v4M12 10v7"/>'),
    rd:SVG('<circle cx="12" cy="13" r="7"/><circle cx="9.5" cy="12.5" r=".8"/><circle cx="14.5" cy="12.5" r=".8"/><path d="M9.5 15.5c1.5 1 3.5 1 5 0M12 6l1-3 4 1"/><circle cx="18" cy="4" r="1"/>'),
    tab:SVG('<path d="M14 3h7v7M10 14L21 3M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"/>'),
    mute:SVG('<path d="M17.94 17.94A10 10 0 0 1 12 20C5 20 1 12 1 12a18 18 0 0 1 5.06-5.94M9.9 4.24A9 9 0 0 1 12 4c7 0 11 8 11 8a18 18 0 0 1-2.16 3.19M1 1l22 22"/>'),
    img:SVG('<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>')
  };

  // ---------------- 1) poll menu ----------------
  var ctx=null; // {code, author, q, el, t}
  document.addEventListener('click', function(e){
    var b=e.target.closest && e.target.closest('.pmenu-btn'); if(!b) return;
    var card=b.closest('.card'); if(!card) return;
    var code=(card.id||'').replace(/^card-/,'');
    var au=card.querySelector('[data-author]');
    var q=card.querySelector('.q'); var qt='';
    if(q){ var c=q.cloneNode(true); [].forEach.call(c.querySelectorAll('[class*="chip"],[class*="badge"]'),function(x){x.remove();}); qt=c.textContent.replace(/\s+/g,' ').trim(); }
    ctx={code:code, author:au?au.getAttribute('data-author'):'', q:qt, el:card, t:Date.now()};
  }, true);
  function pollText(){
    var lines=[ctx.q, ''];
    [].forEach.call(ctx.el.querySelectorAll('.opts .opt'), function(o){
      var lbl=(o.querySelector('.lbl,.otext')||o).textContent.replace(/\s+/g,' ').trim();
      var pct=(o.textContent.match(/(\d{1,3})%/)||[])[1];
      lines.push('• '+lbl.replace(/\s*\d{1,3}%\s*$/,'')+(pct?' — '+pct+'%':''));
    });
    lines.push('', 'Vote here 👉 '+SITE+'/p/'+ctx.code);
    return lines.join('\n');
  }
  function mi(label, icon, fn, cls){ var b=document.createElement('button'); b.type='button'; b.className='lp-mi'+(cls?' '+cls:''); b.innerHTML=icon+'<span>'+label+'</span>'; b.addEventListener('click', function(ev){ ev.stopPropagation(); fn(); closeMenus(); }); return b; }
  function sep(){ var s=document.createElement('div'); s.className='lp-msep'; s.setAttribute('role','separator'); return s; }
  function closeMenus(){ [].forEach.call(document.querySelectorAll('.menu[data-lpm]'), function(m){ m.remove(); }); }
  function enhanceMenu(m){
    if(m.hasAttribute('data-lpm')) return; m.setAttribute('data-lpm','1');
    if(!ctx || Date.now()-ctx.t>3000 || !ctx.code) return;
    var c=ctx, btns=[].slice.call(m.querySelectorAll(':scope > button'));
    var find=function(re){ return btns.filter(function(b){ return re.test(b.textContent.trim()); })[0]; };
    var copyB=find(/^Copy link/i), prof=find(/^View profile/i), fol=find(/^(Follow|Unfollow|Following)/i), ni=find(/^Not interested/i), rep=find(/^Report/i);
    var url=SITE+'/p/'+c.code;
    var share=[
      copyB,
      mi('Copy as post (LinkedIn, Reddit…)', IC.post, function(){ copy(pollText()).then(function(){ say('Poll copied — paste it into your post'); }); }),
      mi('Share on LinkedIn', IC.li, function(){ window.open('https://www.linkedin.com/sharing/share-offsite/?url='+encodeURIComponent(url),'_blank','noopener,width=640,height=640'); }),
      mi('Share on Reddit', IC.rd, function(){ window.open('https://www.reddit.com/submit?url='+encodeURIComponent(url)+'&title='+encodeURIComponent(c.q),'_blank','noopener'); }),
      mi('Open in new tab', IC.tab, function(){ window.open(url,'_blank','noopener'); })
    ].filter(Boolean);
    var meU=''; try{ meU=(typeof ME!=='undefined'&&ME)?ME.username:''; }catch(e){}
    var person=[prof, fol].filter(Boolean);
    if(c.author && c.author!==meU) person.push(mi('Hide posts from @'+c.author, IC.mute, function(){ mute(c.author); }));
    var other=btns.filter(function(b){ return [copyB,prof,fol,ni,rep].indexOf(b)<0; }); // owner items etc. keep as-is
    var fb=[ni, rep].filter(Boolean);
    m.innerHTML='';
    [share, person, other, fb].filter(function(g){ return g.length; }).forEach(function(g,i){ if(i) m.appendChild(sep()); g.forEach(function(b){ m.appendChild(b); }); });
    m.classList.add('lp-menu');
    // keep the bigger menu on screen
    requestAnimationFrame(function(){ var r=m.getBoundingClientRect(); if(r.bottom>innerHeight-8){ var top=parseFloat(getComputedStyle(m).top)||r.top; m.style.top=Math.max(8, top-(r.bottom-innerHeight+12))+'px'; } });
  }

  // ---------------- 3) hide posts from a person ----------------
  function mute(u){
    var l=LSget('lp_muted',[]); if(l.indexOf(u)<0) l.push(u); LSset('lp_muted',l);
    var first=null;
    [].forEach.call(document.querySelectorAll('#main .card'), function(card){
      var a=card.querySelector('[data-author]'); if(a && a.getAttribute('data-author')===u){ if(!first) first=card; card.classList.add('lp-muted'); }
    });
    if(first){
      var n=document.createElement('div'); n.className='lp-mute-note'; n.setAttribute('data-u',u);
      n.innerHTML='<span>You won’t see posts from <b>@'+u.replace(/[<>&"]/g,'')+'</b> in your feed.</span><button type="button" class="lp-undo">Undo</button>';
      first.parentNode.insertBefore(n, first);
      n.querySelector('.lp-undo').addEventListener('click', function(){ unmute(u); });
    }
    say('Posts from @'+u+' hidden');
  }
  function unmute(u){
    LSset('lp_muted', LSget('lp_muted',[]).filter(function(x){ return x!==u; }));
    [].forEach.call(document.querySelectorAll('.card.lp-muted'), function(c){ var a=c.querySelector('[data-author]'); if(a && a.getAttribute('data-author')===u) c.classList.remove('lp-muted'); });
    [].forEach.call(document.querySelectorAll('.lp-mute-note'), function(n){ if(n.getAttribute('data-u')===u) n.remove(); });
    say('Showing posts from @'+u+' again');
  }
  function applyMutes(){
    var l=LSget('lp_muted',[]); if(!l.length) return;
    var onProfile=!!document.querySelector('#main h1.profile-name');
    [].forEach.call(document.querySelectorAll('#main .card:not(.lp-muted)'), function(card){
      if(onProfile || card.closest('.lp-mute-skip')) return;
      var a=card.querySelector('[data-author]'); if(a && l.indexOf(a.getAttribute('data-author'))>=0) card.classList.add('lp-muted');
    });
  }

  // ---------------- 2) create poll helpers ----------------
  var TPL=[
    ['Yes / No', ['Yes','No']],
    ['Agree / Disagree', ['Agree','Disagree']],
    ['Agree · Neutral · Disagree', ['Agree','Neutral','Disagree']],
    ['Rate 1–5', ['1 — Poor','2','3','4','5 — Excellent']],
    ['This or that', ['','']],
    ['Yes / No / Not sure', ['Yes','No','Not sure']]
  ];
  var IDEAS=['What’s the biggest challenge in your work right now?','Remote, hybrid or office — what works best for you?','Which skill will matter most in the next 2 years?','How do you usually find new clients or jobs?','What should I learn next?'];
  function setVal(inp, v){ var d=Object.getOwnPropertyDescriptor(HTMLInputElement.prototype,'value')||Object.getOwnPropertyDescriptor(HTMLTextAreaElement.prototype,'value'); var proto=inp.tagName==='TEXTAREA'?HTMLTextAreaElement.prototype:HTMLInputElement.prototype; Object.getOwnPropertyDescriptor(proto,'value').set.call(inp,v); inp.dispatchEvent(new Event('input',{bubbles:true})); inp.dispatchEvent(new Event('change',{bubbles:true})); }
  function applyTpl(list){
    var box=document.getElementById('copts'), add=document.getElementById('cadd'); if(!box) return;
    var rows=function(){ return [].slice.call(box.querySelectorAll('.opt-edit')); };
    var guard=0;
    while(rows().length<list.length && add && guard++<10) add.click();
    guard=0;
    while(rows().length>list.length && guard++<10){ var r=rows(); var rm=r[r.length-1].querySelector('.rm'); if(!rm) break; rm.click(); }
    rows().forEach(function(r,i){ var inp=r.querySelector('input.field'); if(inp && i<list.length) setVal(inp, list[i]); });
    var firstEmpty=rows().map(function(r){return r.querySelector('input.field');}).filter(function(i){return i && !i.value;})[0];
    (firstEmpty || document.getElementById('cq')).focus();
  }
  function enhanceCreate(){
    var cq=document.getElementById('cq'), box=document.getElementById('copts');
    if(cq && !document.querySelector('.lp-ideas')){
      var ideas=document.createElement('div'); ideas.className='lp-ideas';
      ideas.innerHTML='<span class="lp-chip-lab">Need an idea?</span>'+IDEAS.map(function(q,i){ return '<button type="button" class="lp-chip" data-idea="'+i+'">'+q+'</button>'; }).join('');
      cq.parentNode.insertBefore(ideas, cq.nextSibling);
      ideas.addEventListener('click', function(e){ var b=e.target.closest('[data-idea]'); if(!b) return; setVal(cq, IDEAS[+b.getAttribute('data-idea')]); cq.focus(); syncIdeas(); });
      cq.addEventListener('input', syncIdeas);
    }
    if(box && !document.querySelector('.lp-tpl')){
      var t=document.createElement('div'); t.className='lp-tpl';
      t.innerHTML='<span class="lp-chip-lab">Quick answers</span>'+TPL.map(function(x,i){ return '<button type="button" class="lp-chip" data-tpl="'+i+'">'+x[0]+'</button>'; }).join('');
      box.parentNode.insertBefore(t, box);
      t.addEventListener('click', function(e){ var b=e.target.closest('[data-tpl]'); if(!b) return; applyTpl(TPL[+b.getAttribute('data-tpl')][1]); });
    }
    [].forEach.call(document.querySelectorAll('#copts .opt-img-btn:not([data-lpimg])'), function(b){ b.setAttribute('data-lpimg','1'); b.innerHTML=IC.img; b.title='Add an image to this option'; b.setAttribute('aria-label','Add an image to this option'); });
    syncIdeas();
  }
  function syncIdeas(){ var cq=document.getElementById('cq'), i=document.querySelector('.lp-ideas'); if(cq && i) i.hidden = !!cq.value.trim(); }

  var t=null;
  new MutationObserver(function(muts){
    for(var k=0;k<muts.length;k++){ var a=muts[k].addedNodes; for(var j=0;j<a.length;j++){ var n=a[j]; if(n.nodeType!==1) continue;
      var m = n.matches && n.matches('.menu') ? n : (n.querySelector && n.querySelector('.menu:not([data-lpm])'));
      if(m) enhanceMenu(m); } }
    if(t) return; t=setTimeout(function(){ t=null; enhanceCreate(); applyMutes(); }, 60);
  }).observe(document.documentElement,{childList:true,subtree:true});
})();

/* ===== LinkPoll growth layer (Nov 2026) - see README.md "Growth" =====
   - "Your poll is live" share sheet right after a poll is created (creators sharing = new visitors)
   - poll menu: Share on WhatsApp, Download results image
   - share cards are rendered by /og.php (1200x630) */
(function(){
  var SITE=location.origin;
  function say(m){ try{ toast(m); }catch(e){} }
  function copy(t){ if(navigator.clipboard && navigator.clipboard.writeText) return navigator.clipboard.writeText(t); var a=document.createElement('textarea'); a.value=t; document.body.appendChild(a); a.select(); try{document.execCommand('copy');}catch(e){} a.remove(); return Promise.resolve(); }
  function esc(s){ return String(s||'').replace(/[&<>"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); }
  var S=function(p){ return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'+p+'</svg>'; };
  var IC={ wa:S('<path d="M3 21l1.7-5A8.5 8.5 0 1 1 8 19.3z"/><path d="M9 9.5c.3 1.6 1.8 3.5 3.6 4.3l1.2-1.1 2 .9c-.2 1-1 1.7-2 1.7C10.9 15.3 8.7 13 8.6 10.1c0-1 .7-1.8 1.7-2l.9 2z"/>'),
          li:S('<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 10v7M8 7v.01M12 17v-4a2 2 0 0 1 4 0v4M12 10v7"/>'),
          rd:S('<circle cx="12" cy="13" r="7"/><path d="M9.5 15.5c1.5 1 3.5 1 5 0M12 6l1-3 4 1"/>'),
          x:S('<path d="M4 4l16 16M20 4L4 20"/>'),
          fb:S('<path d="M15 8h-2a2 2 0 0 0-2 2v11M8 13h6"/>'),
          link:S('<path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/>'),
          post:S('<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>'),
          img:S('<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>') };
  function targets(code, q){
    var url=SITE+'/p/'+code, t=q||'Vote on my poll';
    return {
      wa:'https://wa.me/?text='+encodeURIComponent(t+'\n\nVote here 👉 '+url),
      li:'https://www.linkedin.com/sharing/share-offsite/?url='+encodeURIComponent(url),
      rd:'https://www.reddit.com/submit?url='+encodeURIComponent(url)+'&title='+encodeURIComponent(t),
      x:'https://x.com/intent/post?text='+encodeURIComponent(t)+'&url='+encodeURIComponent(url),
      fb:'https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(url),
      url:url, post:t+'\n\nVote here 👉 '+url+'\n\n#poll #LinkPoll',
      img:SITE+'/og.php?p='+encodeURIComponent(code)+'&dl=1', imgR:SITE+'/og.php?p='+encodeURIComponent(code)+'&r=1&dl=1'
    };
  }
  function open(u){ window.open(u,'_blank','noopener,width=680,height=640'); }
  function shareImage(code, results){
    var u=SITE+'/og.php?p='+encodeURIComponent(code)+(results?'&r=1':'');
    // phones: hand the image straight to WhatsApp / Instagram / LinkedIn apps; desktop: download
    if(navigator.canShare && window.File){
      fetch(u).then(function(r){ return r.blob(); }).then(function(b){
        var f=new File([b],'linkpoll-'+code+(results?'-results':'')+'.png',{type:'image/png'});
        if(navigator.canShare({files:[f]})) return navigator.share({files:[f], text:SITE+'/p/'+code});
        throw 0;
      }).catch(function(){ location.href=u+'&dl=1'; });
    } else location.href=u+'&dl=1';
  }

  // ---------- "Your poll is live" sheet ----------
  function closeSheet(){ var s=document.getElementById('lp-share'); if(s) s.remove(); document.removeEventListener('keydown', onKey); }
  function onKey(e){ if(e.key==='Escape') closeSheet(); }
  function showSheet(code, q){
    if(!code || document.getElementById('lp-share')) return;
    var t=targets(code, q);
    var el=document.createElement('div'); el.id='lp-share'; el.setAttribute('role','dialog'); el.setAttribute('aria-modal','true'); el.setAttribute('aria-labelledby','lp-share-h');
    el.innerHTML='<div class="lp-share-back" data-x></div><div class="lp-share-card">'+
      '<button type="button" class="lp-x" aria-label="Close" data-x>✕</button>'+
      '<h2 id="lp-share-h">🎉 Your poll is live!</h2>'+
      '<p>Polls get most of their votes in the first hour. Share it now — this is the preview people will see:</p>'+
      '<img class="lp-share-img" alt="Preview of your poll" src="'+SITE+'/og.php?p='+encodeURIComponent(code)+'&v='+Date.now()+'">'+
      '<div class="lp-share-grid">'+
        '<a class="lp-sb wa" href="'+t.wa+'" target="_blank" rel="noopener">'+IC.wa+'<span>WhatsApp</span></a>'+
        '<a class="lp-sb li" href="'+t.li+'" target="_blank" rel="noopener">'+IC.li+'<span>LinkedIn</span></a>'+
        '<a class="lp-sb rd" href="'+t.rd+'" target="_blank" rel="noopener">'+IC.rd+'<span>Reddit</span></a>'+
        '<a class="lp-sb x" href="'+t.x+'" target="_blank" rel="noopener">'+IC.x+'<span>X</span></a>'+
        '<a class="lp-sb fb" href="'+t.fb+'" target="_blank" rel="noopener">'+IC.fb+'<span>Facebook</span></a>'+
        '<button type="button" class="lp-sb" data-copy="url">'+IC.link+'<span>Copy link</span></button>'+
        '<button type="button" class="lp-sb" data-copy="post">'+IC.post+'<span>Copy post text</span></button>'+
        '<button type="button" class="lp-sb" data-img>'+IC.img+'<span>Save image</span></button>'+
      '</div>'+
      '<p class="lp-share-tip"><b>LinkedIn tip:</b> post the image with your question, then put the link in the first comment — LinkedIn shows posts with links to fewer people.</p>'+
      '<button type="button" class="btn btn-dark lp-share-done" data-x>Done</button>'+
    '</div>';
    document.body.appendChild(el);
    el.addEventListener('click', function(e){
      var b=e.target.closest('[data-x],[data-copy],[data-img]'); if(!b) return;
      if(b.hasAttribute('data-x')){ closeSheet(); return; }
      if(b.hasAttribute('data-copy')){ var k=b.getAttribute('data-copy'); copy(t[k]).then(function(){ say(k==='url'?'Link copied':'Post text copied — paste it anywhere'); }); return; }
      if(b.hasAttribute('data-img')) shareImage(code, false);
    });
    document.addEventListener('keydown', onKey);
    var f=el.querySelector('.lp-sb'); if(f) f.focus();
  }

  if(window.fetch){
    var _f=window.fetch;
    window.fetch=function(input, init){
      var url=''; try{ url=decodeURIComponent((typeof input==='string')?input:(input&&input.url)||''); }catch(e){}
      var method=((init&&init.method)||(input&&input.method)||'GET').toUpperCase();
      var p=_f.apply(this, arguments);
      if(method==='POST' && /api\.php/.test(url) && /path=\/polls(\b|&|$)/.test(url)){
        p.then(function(r){ if(!r||!r.ok) return; r.clone().json().then(function(j){
          var pl=j && (j.poll||j); if(pl && pl.code) setTimeout(function(){ showSheet(pl.code, pl.question||''); }, 700);
        }).catch(function(){}); }, function(){});
      }
      return p;
    };
  }

  // ---------- poll menu: WhatsApp + results image ----------
  var ctx=null;
  document.addEventListener('click', function(e){
    var b=e.target.closest && e.target.closest('.pmenu-btn'); if(!b) return;
    var card=b.closest('.card'); if(!card) return;
    var q=card.querySelector('.q'), qt='';
    if(q){ var c=q.cloneNode(true); [].forEach.call(c.querySelectorAll('[class*="chip"],[class*="badge"]'),function(x){x.remove();}); qt=c.textContent.replace(/\s+/g,' ').trim(); }
    ctx={code:(card.id||'').replace(/^card-/,''), q:qt, t:Date.now()};
  }, true);
  function item(label, icon, fn){ var b=document.createElement('button'); b.type='button'; b.className='lp-mi'; b.innerHTML=icon+'<span>'+label+'</span>'; b.addEventListener('click', function(ev){ ev.stopPropagation(); fn(); var m=b.closest('.menu'); if(m) m.remove(); }); return b; }
  function addItems(m){
    if(m.hasAttribute('data-lpg') || !ctx || Date.now()-ctx.t>3000 || !ctx.code) return;
    m.setAttribute('data-lpg','1');
    var c=ctx, t=targets(c.code, c.q);
    var after=[].slice.call(m.children).filter(function(b){ return /Share on Reddit/.test(b.textContent); })[0];
    var wa=item('Share on WhatsApp', IC.wa, function(){ open(t.wa); });
    var im=item('Download results image', IC.img, function(){ shareImage(c.code, true); });
    if(after){ after.parentNode.insertBefore(im, after.nextSibling); after.parentNode.insertBefore(wa, after.nextSibling); }
    else { m.appendChild(wa); m.appendChild(im); }
  }
  new MutationObserver(function(){
    var m=document.querySelector('.menu.lp-menu:not([data-lpg])'); if(m) addItems(m);
  }).observe(document.documentElement,{childList:true,subtree:true,attributes:true,attributeFilter:['class']});
})();

/* ===== LinkPoll sites layer (Nov 2026) - see README "Sites" =====
   Help, About & Policies, Developers and Status live on subdomains:
   help. / about. / docs. / status.link-poll.com. In-app links to the old
   pages point there, and the old in-app routes forward there. */
(function(){
  var A='https://about.link-poll.com';
  var MAP={help:'https://help.link-poll.com/',about:A+'/',privacy:A+'/policies/privacy',terms:A+'/policies/terms',guidelines:A+'/policies/community-guidelines',cookies:A+'/policies/cookies',contact:A+'/contact',status:'https://status.link-poll.com/',developers:'https://docs.link-poll.com/',docs:'https://docs.link-poll.com/'};
  var RE=/^(?:#\/|\/)(help|about|privacy|terms|guidelines|cookies|contact|status|developers|docs|cookie-settings)\/?$/;
  function fix(){
    var as=document.querySelectorAll('a[href]:not([data-lpx])');
    for(var i=0;i<as.length;i++){ var el=as[i], m=(el.getAttribute('href')||'').match(RE); if(!m) continue; el.setAttribute('data-lpx','1');
      if(m[1]==='cookie-settings'){ el.setAttribute('href','#'); el.setAttribute('data-lp-consent',''); el.setAttribute('role','button'); }
      else { el.setAttribute('href',MAP[m[1]]); } }
  }
  function guard(){ var r=''; try{ r=(typeof route!=='undefined'&&route)||''; }catch(e){} if(!r||!MAP[r]) r=(location.pathname.replace(/^\/|\/$/g,'')||''); if(MAP[r]) location.replace(MAP[r]); }
  document.addEventListener('click',function(e){ var el=e.target.closest&&e.target.closest('a[data-lpx]'); if(!el||el.hasAttribute('data-lp-consent')) return; e.stopPropagation(); },true);
  var t=null; new MutationObserver(function(){ if(t) return; t=setTimeout(function(){ t=null; fix(); guard(); },80); }).observe(document.documentElement,{childList:true,subtree:true,attributes:true,attributeFilter:['data-route']});
  fix(); guard();
})();


/* ===== LinkPoll design system v3 behaviours (Nov 2026) - see README "Design system v3" =====
   - initials avatars: readable text colour picked from the background
   - "more" buttons: ⋯ icon + label; icon-only buttons get hover tooltips
   - floating AI button: hidden where it covers content, and while typing or scrolling down
   - onboarding: one prompt at a time; compact checklist on phones */
(function(){
  function lum(c){ var m=c.match(/\d+(\.\d+)?/g); if(!m||m.length<3) return null; var a=m.slice(0,3).map(function(v){ v=+v/255; return v<=.03928?v/12.92:Math.pow((v+.055)/1.055,2.4); }); return .2126*a[0]+.7152*a[1]+.0722*a[2]; }
  var DOTS='<svg class="ico" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg>';
  function avatars(){
    var els=document.querySelectorAll('.av:not([data-lpc])');
    for(var i=0;i<els.length;i++){ var e=els[i]; e.setAttribute('data-lpc','1'); if(e.querySelector('img')) continue;
      var L=lum(getComputedStyle(e).backgroundColor||''); if(L===null) continue;
      e.style.color = (L+.05)/0.055 >= 1.05/(L+.05) ? '#0f0f0d' : '#ffffff'; }
  }
  function menus(){
    var b=document.querySelectorAll('.pmenu-btn:not([data-lpd])');
    for(var i=0;i<b.length;i++){ b[i].setAttribute('data-lpd','1'); b[i].innerHTML=DOTS; if(!b[i].getAttribute('aria-label')) b[i].setAttribute('aria-label','More options'); }
  }
  var TIP={react:'Like',cmt:'Comment',share:'Share','act-ai':'Ask AI about this poll',count:'Votes'};
  function tips(){
    if(!matchMedia('(hover:hover)').matches) return;
    var b=document.querySelectorAll('.iconbtn:not([data-tip]),.act:not([data-tip]),.pmenu-btn:not([data-tip])');
    for(var i=0;i<b.length;i++){ var e=b[i], t=e.getAttribute('aria-label')||e.getAttribute('title')||'';
      if(!t){ for(var k in TIP){ if(e.classList.contains(k)){ t=TIP[k]; break; } } }
      if(!t && e.querySelector('svg') && e.textContent.trim()==='' ) t='';
      if(t){ if(getComputedStyle(e).position==='static') e.style.position='relative'; e.setAttribute('data-tip',t); if(e.getAttribute('title')) e.removeAttribute('title'); if(!e.getAttribute('aria-label')) e.setAttribute('aria-label',t); }
      else e.setAttribute('data-tip-none','1'); }
  }
  /* floating AI button */
  var HIDE_ROUTES={explore:1,search:1,poll:1,detail:1,p:1,messages:1,chat:1,group:1,settings:1,profile:1,u:1,create:1,widgets:1,links:1,biocard:1,paper:1,quiz:1};
  var lastY=scrollY, down=false;
  function fab(){
    var f=document.querySelector('.ai-fab'); if(!f) return;
    var r=''; try{ r=(typeof route!=='undefined'&&route)||''; }catch(e){}
    var small=innerWidth<=760;
    var typing=document.activeElement&&/^(INPUT|TEXTAREA)$/.test(document.activeElement.tagName)||document.activeElement&&document.activeElement.isContentEditable;
    var other=document.querySelector('.fab-create'); var otherShown=other&&other.offsetParent!==null;
    var hide=small&&(HIDE_ROUTES[r]||typing||down||otherShown) || (!small&&typing&&false);
    f.classList.toggle('lp-fab-hide',!!hide);
  }
  addEventListener('scroll',function(){ var y=scrollY; if(Math.abs(y-lastY)>12){ down=y>lastY&&y>200; lastY=y; fab(); } },{passive:true});
  document.addEventListener('focusin',fab); document.addEventListener('focusout',function(){ setTimeout(fab,50); });
  /* onboarding */
  function onboarding(){
    var c=document.querySelector('.lp-check'); document.body.classList.toggle('lp-has-check',!!c); if(!c) return;
    var lis=c.querySelectorAll('ul li'), nx=null;
    for(var i=0;i<lis.length;i++){ lis[i].classList.remove('lp-next'); if(!nx && !lis[i].classList.contains('done')) nx=lis[i]; }
    if(nx) nx.classList.add('lp-next');
    if(!c.querySelector('.lp-check-more')){ var b=document.createElement('button'); b.type='button'; b.className='lp-check-more'; b.textContent='Show all steps';
      b.addEventListener('click',function(){ var o=c.classList.toggle('lp-open'); b.textContent=o?'Show less':'Show all steps'; }); c.appendChild(b); }
  }
  function run(){ try{ avatars(); menus(); tips(); fab(); onboarding(); }catch(e){} }
  var t=null; new MutationObserver(function(){ if(t) return; t=setTimeout(function(){ t=null; run(); },90); }).observe(document.documentElement,{childList:true,subtree:true});
  addEventListener('resize',fab); run();
})();
