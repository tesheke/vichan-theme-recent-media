<?php
$theme = Array();

// Theme name
$theme['name'] = 'RecentMedia(dir:' . basename(dirname(__FILE__)) . ')';
// Description (you can use Tinyboard markup here)
$theme['description'] = '- Show recent media (default /recent_media.html).
- (After updating this theme, please "Rebuild Tehemes" in "mod.php/?/rebuild".)
- (You can run multiple RecentMedia on one vichan instance by duplicating this theme folder.)';
$theme['version'] = 'v2023.10.18';

// Theme configuration
$theme['config'] = Array();

$theme['config'][] = Array(
  'title' => 'Title',
  'name' => 'title',
  'type' => 'text',
  'default' => 'Media'
);

$theme['config'][] = Array(
  'title' => 'Excluded boards',
  'name' => 'exclude',
  'type' => 'text',
  'comment' => '(space seperated)'
);

$theme['config'][] = Array(
  'title' => 'number of recent media',
  'name' => 'limit_media',
  'type' => 'text',
  'default' => '3',
  'comment' => '(maximum media to display)'
);

$theme['config'][] = Array(
  'title' => 'CSS file',
  'name' => 'refcss',
  'type' => 'text',
  'default' => '',
  'comment' => '(css referenced by HTML file. relative path from vichan-root($config[\'root\']))'
);

$theme['config'][] = Array(
  'title' => 'Inline CSS',
  'name' => 'inlinecss',
  'type' => 'text',
  'default' => '',
  'comment' => '(css expression referenced by HTML file.)'
);


$theme['config'][] = Array(
  'title' => 'Use vichan CSS',
  'name' => 'use_vichan_css',
  'type' => 'text',
  'default' => '1',
  'comment' => '(1:yes, 0:no)'
);

$theme['config'][] = Array(
  'title' => 'HTML file',
  'name' => 'html',
  'type' => 'text',
  'default' => 'recent_media.html',
  'comment' => '(output destination. eg. "recent_media.html")'
);

// Unique function name for building everything
$theme['build_function'] = 'RecentMedia::build';
$theme['install_callback'] = 'recent_media_install';

if (!function_exists('recent_media_install')) {
  function recent_media_install($settings) {
    if (!is_numeric($settings['limit_media']) || $settings['limit_media'] < 0)
      return Array(false, '<strong>' . utf8tohtml($settings['limit_media']) . '</strong> is not a non-negative integer.');
  };
};
