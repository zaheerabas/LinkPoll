<?php
/* Dynamic web app manifest — colours follow the user's chosen light/dark theme.
   app.js mirrors the theme into an `lp_theme` cookie so the install splash and
   PWA chrome match the theme the user had when they installed. */
header('Content-Type: application/manifest+json; charset=utf-8');
header('Cache-Control: no-cache');

$theme = isset($_COOKIE['lp_theme']) ? $_COOKIE['lp_theme'] : '';
$dark  = ($theme === 'dark');

$bg = $dark ? '#0f0f12' : '#f3f1ea';   // splash background
$tc = $dark ? '#0f0f12' : '#f3f1ea';   // title/status bar

echo json_encode([
  'name'             => 'LinkPoll',
  'short_name'       => 'LinkPoll',
  'description'      => 'A social network built entirely around polls. Ask the world anything.',
  'start_url'        => './',
  'scope'            => './',
  'display'          => 'standalone',
  'background_color' => $bg,
  'theme_color'      => $tc,
  'icons'            => [
    ['src' => 'icon-192.png',          'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
    ['src' => 'icon-512.png',          'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
    ['src' => 'icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
  ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
