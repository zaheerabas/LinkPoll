<?php
/* LinkPoll SEO module (Nov 2026) - see README.md "SEO".
   Called by view.php just before the HTML is printed:
     [$LP_ROBOTS, $ssr, $jsonld] = lp_seo($path, $notFound, $ssr, $jsonld);
   - robots: noindex for private pages, 404s, banned users and demo content
   - ssr: adds crawlable content + internal links inside #root (the app replaces it on boot)
   - jsonld: Organization + WebSite on every page, plus DiscussionForumPosting / ProfilePage / CollectionPage / BreadcrumbList
*/
if (!defined('LP_SEO_INDEX_DEMO')) define('LP_SEO_INDEX_DEMO', false); // set true to let Google index demo accounts and their polls
const LP_SEO_SITE = 'https://link-poll.com';
const LP_SEO_PRIVATE = ['settings','messages','notifications','saved','insights','mypolls','points','links','widgets','reviews','network','following','support'];
const LP_SEO_ROUTES = ['explore','following','saved','mypolls','notifications','settings','insights','points','leaderboard','discover','quiz','papers','paper','network','communities','c','updates','support','feedback','poll','p','tag','messages','u','b','links','reviews','r','faq','faqs','widgets','help','about','privacy','terms','guidelines','search','login','signup','register','admin'];

function lp_seo_e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function lp_seo_demo_sql($alias){ return LP_SEO_INDEX_DEMO ? '1=1' : "$alias.email NOT LIKE '%@demo.linkpoll'"; }
function lp_seo_iso($d){ $t = strtotime((string)$d); return $t ? date('c', $t) : null; }

function lp_seo_polls_html(PDO $p, string $heading, string $where = '1=1', array $args = [], int $limit = 12, string $order = 'votes DESC, po.id DESC'){
  try {
    $sql = "SELECT po.code, po.question, (SELECT COUNT(*) FROM votes v WHERE v.poll_id=po.id) AS votes
            FROM polls po JOIN users us ON us.id=po.user_id
            LEFT JOIN communities cm ON cm.id=po.community_id
            WHERE us.banned=0 AND (cm.id IS NULL OR cm.privacy IS NULL OR cm.privacy IN ('public','open','')) AND $where
            ORDER BY $order LIMIT ".(int)$limit;
    $st = $p->prepare($sql); $st->execute($args); $rows = $st->fetchAll();
  } catch (Throwable $e) { return ''; }
  if (!$rows) return '';
  $li = '';
  foreach ($rows as $r) $li .= '<li><a href="/p/'.lp_seo_e($r['code']).'">'.lp_seo_e($r['question']).'</a> <span class="ssr-votes">'.(int)$r['votes'].' votes</span></li>';
  return '<section><h2>'.lp_seo_e($heading).'</h2><ul class="ssr-opts">'.$li.'</ul></section>';
}

function lp_seo_nav(){
  $l = [['/','Home'],['/explore','Explore polls'],['/faqs','Discussions'],['/communities','Communities'],['/quiz','Quizzes'],['/papers','Mock papers'],['/leaderboard','Leaderboard'],['/about','About'],['/help','Help'],['/guidelines','Guidelines'],['/privacy','Privacy'],['/terms','Terms']];
  $a = ''; foreach ($l as $x) $a .= '<a href="'.$x[0].'">'.$x[1].'</a> ';
  return '<nav class="ssr-nav" aria-label="LinkPoll">'.$a.'</nav>';
}
function lp_seo_crumbs(array $items){ // [[name,url],...]
  $html = []; $ld = [];
  foreach ($items as $i => $it) {
    $html[] = $i < count($items)-1 ? '<a href="'.lp_seo_e(parse_url($it[1], PHP_URL_PATH) ?: '/').'">'.lp_seo_e($it[0]).'</a>' : '<span>'.lp_seo_e($it[0]).'</span>';
    $ld[] = ['@type'=>'ListItem','position'=>$i+1,'name'=>$it[0],'item'=>$it[1]];
  }
  return ['<nav class="ssr-crumbs" aria-label="Breadcrumb">'.implode(' › ', $html).'</nav>', ['@type'=>'BreadcrumbList','itemListElement'=>$ld]];
}

function lp_seo(string $path, bool $notFound, string $ssr, $jsonld){
  $path = trim($path, '/');
  $seg = explode('/', $path);
  $s0 = strtolower($seg[0] ?? '');
  $robots = 'index,follow,max-image-preview:large,max-snippet:-1';
  $graph = [
    ['@type'=>'Organization','@id'=>LP_SEO_SITE.'/#org','name'=>'LinkPoll','url'=>LP_SEO_SITE.'/','logo'=>LP_SEO_SITE.'/logo-icon.png'],
    ['@type'=>'WebSite','@id'=>LP_SEO_SITE.'/#website','name'=>'LinkPoll','url'=>LP_SEO_SITE.'/','publisher'=>['@id'=>LP_SEO_SITE.'/#org'],'inLanguage'=>'en'],
  ];
  $extra = '';
  if ($notFound) return ['noindex,follow', $ssr.lp_seo_nav(), ['@context'=>'https://schema.org','@graph'=>$graph]];
  if (in_array($s0, LP_SEO_PRIVATE, true)) $robots = 'noindex,follow';

  try { [$p] = db(); } catch (Throwable $e) { $p = null; }
  if ($p) {
    try {
      if (($s0 === 'p' || $s0 === 'poll') && !empty($seg[1])) {
        // ---------- poll ----------
        $st = $p->prepare("SELECT po.*, us.username, us.name AS uname, us.email AS uemail, us.banned,
            (SELECT COUNT(*) FROM votes v WHERE v.poll_id=po.id) AS votes,
            (SELECT COUNT(*) FROM comments c WHERE c.poll_id=po.id) AS ncom,
            cm.privacy AS cpriv, cm.name AS cname, cm.slug AS cslug
          FROM polls po JOIN users us ON us.id=po.user_id LEFT JOIN communities cm ON cm.id=po.community_id WHERE po.code=? LIMIT 1");
        $st->execute([$seg[1]]); $r = $st->fetch();
        if ($r) {
          $demo = substr((string)$r['uemail'], -14) === '@demo.linkpoll';
          if ((int)$r['banned'] || ($demo && !LP_SEO_INDEX_DEMO) || ($r['cpriv'] && !in_array($r['cpriv'], ['public','open'], true))) $robots = 'noindex,follow';
          $url = LP_SEO_SITE.'/p/'.$r['code'];
          $opts = $p->prepare("SELECT o.label, (SELECT COUNT(*) FROM votes v WHERE v.option_id=o.id) AS n FROM options o WHERE o.poll_id=? ORDER BY o.position, o.id");
          $opts->execute([$r['id']]); $opts = $opts->fetchAll();
          $anon = !empty($r['anonymous']);
          $author = $anon ? ['@type'=>'Person','name'=>'Anonymous'] : ['@type'=>'Person','name'=>$r['uname'] ?: '@'.$r['username'],'url'=>LP_SEO_SITE.'/'.$r['username']];
          $text = $r['question'].' Options: '.implode('; ', array_map(function($o){ return $o['label']; }, $opts)).'.';
          $post = ['@type'=>'DiscussionForumPosting','@id'=>$url.'#post','mainEntityOfPage'=>$url,'url'=>$url,'headline'=>mb_substr($r['question'],0,110),
            'text'=>$text,'author'=>$author,'datePublished'=>lp_seo_iso($r['created_at']),
            'interactionStatistic'=>[
              ['@type'=>'InteractionCounter','interactionType'=>'https://schema.org/VoteAction','userInteractionCount'=>(int)$r['votes']],
              ['@type'=>'InteractionCounter','interactionType'=>'https://schema.org/CommentAction','userInteractionCount'=>(int)$r['ncom']],
              ['@type'=>'InteractionCounter','interactionType'=>'https://schema.org/ShareAction','userInteractionCount'=>(int)($r['shares'] ?? 0)]]];
          if (!empty($r['image'])) $post['image'] = (strpos($r['image'],'http')===0 ? $r['image'] : LP_SEO_SITE.'/'.ltrim($r['image'],'/'));
          if (!empty($r['category'])) $post['keywords'] = $r['category'];
          $graph[] = $post;
          $cr = [['Home', LP_SEO_SITE.'/']];
          if ($r['cslug'] && $r['cname']) $cr[] = [$r['cname'], LP_SEO_SITE.'/c/'.$r['cslug']]; else $cr[] = ['Polls', LP_SEO_SITE.'/explore'];
          $cr[] = [mb_substr($r['question'],0,60), $url];
          [$ch, $cl] = lp_seo_crumbs($cr); $graph[] = $cl;
          $by = $anon ? 'Anonymous' : '<a href="/'.lp_seo_e($r['username']).'">'.lp_seo_e($r['uname'] ?: '@'.$r['username']).'</a>';
          $extra .= '<p class="ssr-by">Asked by '.$by.' · <time datetime="'.lp_seo_e(lp_seo_iso($r['created_at'])).'">'.lp_seo_e(date('M j, Y', strtotime($r['created_at']))).'</time> · '.(int)$r['votes'].' votes · '.(int)$r['ncom'].' comments</p>';
          if (!$anon) $extra .= lp_seo_polls_html($p, 'More polls by '.($r['uname'] ?: '@'.$r['username']), 'po.user_id=? AND po.id<>?', [$r['user_id'], $r['id']], 6, 'po.id DESC');
          $extra .= lp_seo_polls_html($p, 'Trending polls', 'po.id<>?', [$r['id']], 8);
          $ssr = $ch.$ssr;
        }
      } elseif ($s0 !== '' && !in_array($s0, LP_SEO_ROUTES, true) && count($seg) === 1 || ($s0 === 'u' && !empty($seg[1]))) {
        // ---------- profile ----------
        $un = $s0 === 'u' ? $seg[1] : $seg[0];
        $st = $p->prepare("SELECT u.*, (SELECT COUNT(*) FROM follows f WHERE f.following_id=u.id) AS fol,
            (SELECT COUNT(*) FROM polls po WHERE po.user_id=u.id) AS np FROM users u WHERE u.username=? LIMIT 1");
        $st->execute([$un]); $u = $st->fetch();
        if ($u) {
          $demo = substr((string)$u['email'], -14) === '@demo.linkpoll';
          if ((int)$u['banned'] || ($demo && !LP_SEO_INDEX_DEMO) || (int)$u['np'] === 0) $robots = 'noindex,follow';
          $url = LP_SEO_SITE.'/'.$u['username'];
          $person = ['@type'=>'Person','@id'=>$url.'#person','name'=>$u['name'] ?: '@'.$u['username'],'alternateName'=>'@'.$u['username'],'url'=>$url,
            'interactionStatistic'=>[['@type'=>'InteractionCounter','interactionType'=>'https://schema.org/FollowAction','userInteractionCount'=>(int)$u['fol']]],
            'agentInteractionStatistic'=>['@type'=>'InteractionCounter','interactionType'=>'https://schema.org/WriteAction','userInteractionCount'=>(int)$u['np']]];
          if (!empty($u['bio'])) $person['description'] = $u['bio'];
          if (!empty($u['avatar'])) $person['image'] = (strpos($u['avatar'],'http')===0 ? $u['avatar'] : LP_SEO_SITE.'/'.ltrim($u['avatar'],'/'));
          $graph[] = ['@type'=>'ProfilePage','@id'=>$url,'url'=>$url,'dateCreated'=>lp_seo_iso($u['created_at']),'mainEntity'=>$person];
          [$ch, $cl] = lp_seo_crumbs([['Home', LP_SEO_SITE.'/'], [$u['name'] ?: '@'.$u['username'], $url]]); $graph[] = $cl;
          $extra .= lp_seo_polls_html($p, 'Polls by '.($u['name'] ?: '@'.$u['username']), 'po.user_id=?', [$u['id']], 20, 'po.id DESC');
          $ssr = $ch.$ssr;
        }
      } elseif ($s0 === 'c' && !empty($seg[1])) {
        // ---------- community ----------
        $st = $p->prepare("SELECT c.*, u.email AS cemail FROM communities c LEFT JOIN users u ON u.id=c.creator_id WHERE c.slug=? LIMIT 1"); $st->execute([$seg[1]]); $c = $st->fetch();
        if ($c) {
          if (($c['privacy'] && !in_array($c['privacy'], ['public','open'], true)) || (!LP_SEO_INDEX_DEMO && substr((string)$c['cemail'], -14) === '@demo.linkpoll')) $robots = 'noindex,follow';
          $url = LP_SEO_SITE.'/c/'.$c['slug'];
          $graph[] = ['@type'=>'CollectionPage','@id'=>$url,'url'=>$url,'name'=>$c['name'],'description'=>$c['description'] ?: null,'isPartOf'=>['@id'=>LP_SEO_SITE.'/#website']];
          [$ch, $cl] = lp_seo_crumbs([['Home', LP_SEO_SITE.'/'], ['Communities', LP_SEO_SITE.'/communities'], [$c['name'], $url]]); $graph[] = $cl;
          $extra .= lp_seo_polls_html($p, 'Polls in '.$c['name'], 'po.community_id=?', [$c['id']], 20, 'po.id DESC');
          $ssr = $ch.$ssr;
        }
      } elseif ($s0 === '' || $s0 === 'explore' || $s0 === 'discover') {
        // ---------- home / explore ----------
        $graph[] = ['@type'=>'CollectionPage','@id'=>LP_SEO_SITE.'/'.($s0 ? $s0 : ''),'url'=>LP_SEO_SITE.'/'.($s0 ? $s0 : ''),'name'=>$s0 ? 'Explore polls' : 'LinkPoll','isPartOf'=>['@id'=>LP_SEO_SITE.'/#website']];
        $extra .= lp_seo_polls_html($p, 'Trending polls', '1=1', [], 20);
        $extra .= lp_seo_polls_html($p, 'Newest polls', '1=1', [], 10, 'po.id DESC');
      } elseif ($s0 === 'communities') {
        try {
          $rows = $p->query("SELECT slug,name FROM communities WHERE privacy IS NULL OR privacy IN ('public','open','') ORDER BY id DESC LIMIT 40")->fetchAll();
          if ($rows) { $li=''; foreach ($rows as $r) $li .= '<li><a href="/c/'.lp_seo_e($r['slug']).'">'.lp_seo_e($r['name']).'</a></li>'; $extra .= '<section><h2>Communities</h2><ul class="ssr-opts">'.$li.'</ul></section>'; }
        } catch (Throwable $e) {}
      }
    } catch (Throwable $e) { /* never break the page for SEO */ }
  }
  if (stripos($ssr.$extra, '<h1') === false) $ssr = ($s0 === '' ? '<h1>LinkPoll — the world\'s opinion, live</h1><p>Create polls, vote in seconds and see what people really think, with live results.</p>' : '<h1>'.lp_seo_e(ucfirst($s0)).'</h1>').$ssr;
  $graph = array_values(array_map(function($n){ return array_filter($n, function($v){ return $v !== null && $v !== ''; }); }, $graph));
  return [$robots, $ssr.$extra.lp_seo_nav(), ['@context'=>'https://schema.org','@graph'=>$graph]];
}
