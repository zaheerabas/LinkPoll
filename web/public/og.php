<?php
/* LinkPoll share cards (Nov 2026) - see README.md "Growth".
   /og.php?p=CODE            1200x630 preview used by LinkedIn / WhatsApp / X / Facebook link previews
                             (question + options, no results -> people click to vote)
   /og.php?p=CODE&r=1        same card WITH live results (for "Download results image")
   &dl=1                     send as a download
   Cached in uploads/og/ for 10 minutes (preview) / 2 minutes (results). */
require __DIR__.'/config.php';

$code = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($_GET['p'] ?? ''));
$res  = !empty($_GET['r']);
$dl   = !empty($_GET['dl']);
if ($code === '') { http_response_code(404); exit; }

$dir = __DIR__.'/uploads/og';
if (!is_dir($dir)) @mkdir($dir, 0755, true);
$file = $dir.'/'.$code.($res ? '-r' : '').'.png';
$ttl  = $res ? 120 : 600;

if (!is_file($file) || filemtime($file) < time() - $ttl) {
  try {
    [$pdo] = db();
    $st = $pdo->prepare("SELECT id, question FROM polls WHERE code=? LIMIT 1"); $st->execute([$code]); $poll = $st->fetch();
    if (!$poll) { http_response_code(404); exit; }
    $st = $pdo->prepare("SELECT o.label, (SELECT COUNT(*) FROM votes v WHERE v.option_id=o.id) n FROM options o WHERE o.poll_id=? ORDER BY o.position, o.id");
    $st->execute([$poll['id']]); $opts = $st->fetchAll();
    $total = 0; foreach ($opts as $o) $total += (int)$o['n'];
  } catch (Throwable $e) { http_response_code(500); exit; }

  $font = function($bold){
    $c = $bold ? ['/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf','/usr/share/fonts/dejavu-sans-fonts/DejaVuSans-Bold.ttf','/usr/share/fonts/google-droid/DroidSans-Bold.ttf']
               : ['/usr/share/fonts/dejavu/DejaVuSans.ttf','/usr/share/fonts/dejavu-sans-fonts/DejaVuSans.ttf','/usr/share/fonts/google-droid/DroidSans.ttf'];
    foreach ($c as $f) if (is_file($f)) return $f;
    $g = glob('/usr/share/fonts/*/*'.($bold ? 'Bold' : 'Sans').'*.ttf'); return $g ? $g[0] : null;
  };
  $FB = $font(true); $FR = $font(false);

  $W = 1200; $H = 630; $im = imagecreatetruecolor($W, $H); imageantialias($im, true);
  $rgb = function($h) use ($im){ $h = ltrim($h, '#'); return imagecolorallocate($im, hexdec(substr($h,0,2)), hexdec(substr($h,2,2)), hexdec(substr($h,4,2))); };
  $bg = $rgb('#131419'); $card = $rgb('#1c1d24'); $track = $rgb('#262832'); $lime = $rgb('#c2f23a'); $white = $rgb('#ffffff'); $muted = $rgb('#a7a294'); $ink = $rgb('#15140f'); $limeDim = $rgb('#3d4a1a');
  imagefilledrectangle($im, 0, 0, $W, $H, $bg);
  $rr = function($x1,$y1,$x2,$y2,$r,$c) use ($im){
    if ($x2 - $x1 < 2*$r) $r = (int)floor(($x2-$x1)/2);
    imagefilledrectangle($im, $x1+$r, $y1, $x2-$r, $y2, $c); imagefilledrectangle($im, $x1, $y1+$r, $x2, $y2-$r, $c);
    foreach ([[$x1+$r,$y1+$r],[$x2-$r,$y1+$r],[$x1+$r,$y2-$r],[$x2-$r,$y2-$r]] as $p) imagefilledellipse($im, $p[0], $p[1], 2*$r, 2*$r, $c);
  };
  $tw = function($size,$font,$t){ $b = imagettfbbox($size, 0, $font, $t); return $b[2]-$b[0]; };
  $wrap = function($text,$size,$font,$max,$lines) use ($tw){
    $words = preg_split('/\s+/u', trim($text)); $out = []; $cur = '';
    foreach ($words as $w) { $try = $cur === '' ? $w : $cur.' '.$w; if ($tw($size,$font,$try) <= $max) $cur = $try; else { if ($cur !== '') $out[] = $cur; $cur = $w; } }
    if ($cur !== '') $out[] = $cur;
    if (count($out) > $lines) { $out = array_slice($out, 0, $lines); $l = $out[$lines-1]; while ($l !== '' && $tw($size,$font,$l.'…') > $max) $l = mb_substr($l, 0, -1); $out[$lines-1] = rtrim($l).'…'; }
    return $out;
  };
  $fit = function($text,$size,$font,$max) use ($tw){ if ($tw($size,$font,$text) <= $max) return $text; while ($text !== '' && $tw($size,$font,$text.'…') > $max) $text = mb_substr($text,0,-1); return rtrim($text).'…'; };

  // brand row
  $logo = __DIR__.'/logo-icon.png';
  if (is_file($logo) && ($lg = @imagecreatefrompng($logo))) { imagecopyresampled($im, $lg, 64, 48, 0, 0, 52, 52, imagesx($lg), imagesy($lg)); imagedestroy($lg); }
  imagettftext($im, 26, 0, 130, 86, $white, $FB, 'LinkPoll');
  $tag = $res ? 'LIVE RESULTS' : 'POLL';
  $tW = $tw(15, $FB, $tag) + 36; $rr($W-64-$tW, 52, $W-64, 96, 22, $lime); imagettftext($im, 15, 0, $W-64-$tW+18, 81, $ink, $FB, $tag);

  // question (auto size)
  $q = trim(preg_replace('/\s+/u', ' ', $poll['question']));
  $nOpt = min(count($opts), 4);
  $len = mb_strlen($q);
  $qSize = $len > 110 ? 30 : ($len > 70 ? 34 : ($len > 45 ? 40 : 46));
  $qLines = $wrap($q, $qSize, $FB, $W-128, 3);
  $y = 148 + $qSize;
  foreach ($qLines as $l) { imagettftext($im, $qSize, 0, 64, $y, $white, $FB, $l); $y += (int)round($qSize*1.3); }

  // options: share whatever height is left above the footer
  $top = $y + 10; $bottom = $H - 92 - (count($opts) > 4 ? 26 : 0); $gap = 10;
  $rowH = $nOpt ? (int)floor(($bottom - $top - $gap*($nOpt-1)) / $nOpt) : 0;
  $rowH = max(40, min(68, $rowH));
  $oSize = $rowH >= 58 ? 24 : ($rowH >= 48 ? 21 : 18);
  $y = $top;
  $maxN = 0; foreach ($opts as $o) $maxN = max($maxN, (int)$o['n']);
  for ($i = 0; $i < $nOpt; $i++) {
    $o = $opts[$i]; $x1 = 64; $x2 = $W-64; $y1 = $y; $y2 = $y + $rowH;
    $rr($x1, $y1, $x2, $y2, 14, $res ? $track : $card);
    $pct = $total ? (int)round(100*(int)$o['n']/$total) : 0;
    if ($res && $pct > 0) { $fillW = max(28, (int)(($x2-$x1)*$pct/100)); $rr($x1, $y1, $x1+$fillW, $y2, 14, ((int)$o['n'] === $maxN) ? $lime : $limeDim); }
    $lc = ($res && (int)$o['n'] === $maxN && $pct >= 40) ? $ink : $white;
    $base = $y1 + (int)round($rowH/2 + $oSize*0.38);
    $label = $fit($o['label'], $oSize, $FR, $x2-$x1-($res ? 170 : 60));
    imagettftext($im, $oSize, 0, $x1+22, $base, $lc, $FR, $label);
    if ($res) { $p = $pct.'%'; imagettftext($im, $oSize, 0, $x2-22-$tw($oSize,$FB,$p), $base, $white, $FB, $p); }
    $y = $y2 + $gap;
  }
  if (count($opts) > 4) { imagettftext($im, 17, 0, 64, $y+14, $muted, $FR, '+'.(count($opts)-4).' more options'); }

  // footer
  $foot = ($total === 1 ? '1 vote' : number_format($total).' votes').'   ·   '.($res ? 'See the poll at ' : 'Tap to vote & see live results  →  ').'link-poll.com/p/'.$code;
  imagettftext($im, 19, 0, 64, $H-40, $lime, $FB, $fit($foot, 19, $FB, $W-128));

  imagepng($im, $file.'.tmp', 6); imagedestroy($im); @rename($file.'.tmp', $file);
}

header('Content-Type: image/png');
header('Cache-Control: public, max-age='.$ttl);
header('X-Robots-Tag: noindex');
if ($dl) header('Content-Disposition: attachment; filename="linkpoll-'.$code.($res ? '-results' : '').'.png"');
readfile($file);
