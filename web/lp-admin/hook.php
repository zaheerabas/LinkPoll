<?php
/* =====================================================================
   LinkPoll Admin — hook.php   (lives outside the public web folders)
   Included by the platform's api.php (one line, after $path is set). Fail-safe:
   any error here is swallowed so the public site can never break.
   ===================================================================== */
try {
  require_once __DIR__.'/lib.php';
  $admNow = time();
  // make sure the admin tables exist even before anyone opens the panel (checked once per server)
  $admSchemaStamp = sys_get_temp_dir().'/lp_adm_schema_v1_'.md5(__DIR__);
  if (!is_file($admSchemaStamp)) { adm_schema($pdo); @touch($admSchemaStamp); }

  // 1) lift expired suspensions (at most once per minute, file-throttled)
  $admStamp = sys_get_temp_dir().'/lp_adm_lift_'.md5(__DIR__);
  if (!is_file($admStamp) || $admNow - (int)@filemtime($admStamp) > 60) { @touch($admStamp); adm_lift_expired(); }

  $admUid = uid();
  $admTeam = false;
  if ($admUid) {
    // 2) forced logout ("kick") — session versioning
    $ver = 0; try { $k = $pdo->prepare('SELECT ver FROM adm_kick WHERE user_id=?'); $k->execute([$admUid]); $ver = (int)$k->fetchColumn(); } catch (Throwable $e) {}
    if (!isset($_SESSION['adm_sv']) || (int)($_SESSION['adm_sv']['u'] ?? 0) !== $admUid) {
      $_SESSION['adm_sv'] = ['u' => $admUid, 'v' => $ver];
    } elseif ((int)$_SESSION['adm_sv']['v'] < $ver) {
      unset($_SESSION['uid'], $_SESSION['adm_sv']);
      if (function_exists('clear_remember')) clear_remember();
      $admUid = null;
    }
  }
  if ($admUid) {
    // 3) remember where accounts sign in from (for duplicate-account detection) — every 10 min per session
    if ((int)($_SESSION['adm_seen'] ?? 0) < $admNow - 600) {
      $ip = adm_ip(); $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 200);
      try {
        $u = $pdo->prepare('UPDATE adm_seen SET last_seen=?, ua=?, hits=hits+1 WHERE user_id=? AND ip=?'); $u->execute([$admNow, $ua, $admUid, $ip]);
        if ($u->rowCount() === 0) $pdo->prepare('INSERT INTO adm_seen (user_id,ip,ua,first_seen,last_seen,hits) VALUES (?,?,?,?,?,1)')->execute([$admUid, $ip, $ua, $admNow, $admNow]);
        $_SESSION['adm_seen'] = $admNow;
      } catch (Throwable $e) { @unlink($admSchemaStamp); }   // table missing → recreate on next request
    }
    $admTeam = false;   // no in-app moderators any more — maintenance mode applies to every member
  }

  $admMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
  $admPath = isset($path) ? (string)$path : '/'.trim((string)($_GET['path'] ?? ''), '/');

  // 4) new sign-ups paused
  if ($admPath === '/auth/register' && adm_get('signups', '1') === '0') {
    fail('New sign-ups are paused right now. Please try again later.', 403);
  }
  // 5) read-only (maintenance) mode — in-app moderators are not affected
  if (!$admTeam && ($admMethod === 'POST' || $admMethod === 'DELETE') && adm_get('readonly', '0') === '1') {
    $allow = ['/auth/login', '/auth/logout', '/auth/verify-email', '/auth/resend-code', '/auth/forgot', '/auth/reset'];
    if (!in_array($admPath, $allow, true)) {
      $msg = trim(adm_get('ro_msg', '')); if ($msg === '') $msg = 'LinkPoll is in maintenance mode. Posting, voting and commenting are paused for a short while.';
      fail($msg, 503);
    }
  }
 // 6) user behaviour: restrictions, speed limits, link rules (see behaviour.php)
 if ($admUid) { require_once __DIR__.'/behaviour.php'; beh_hook($pdo, (int)$admUid, (string)$admMethod, (string)$admPath); }
} catch (Throwable $e) {
  // never let the admin layer break the public site
}
