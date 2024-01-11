<?php
$theme = Array();

// Theme name
$theme['name'] = 'RecentMedia(dir:' . basename(dirname(__FILE__)) . ')';
// Description (you can use Tinyboard markup here)
$theme['description'] = '- Show recent media (default /recent_media.html).
- (After updating this theme or editing template html, please "Rebuild Tehemes" and "Flush cache" in "mod.php?/rebuild".)
- (You can run multiple RecentMedia on one vichan instance by duplicating this theme folder.)';
$theme['version'] = 'v2024.01.11';

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
  'comment' => '(CSS file referenced by output HTML. relative path from vichan-root($config[\'root\']))'
);

$theme['config'][] = Array(
  'title' => 'Inline CSS',
  'name' => 'inlinecss',
  'type' => 'text',
  'default' => '',
  'comment' => '(CSS expression embedded in output HTML.)'
);

$theme['config'][] = Array(
  'title' => 'Use vichan header.html',
  'name' => 'use_vichan_header',
  'type' => 'checkbox',
  'default' => '1',
  'comment' => '(include header.html template)'
);

$theme['config'][] = Array(
  'title' => 'HTML file',
  'name' => 'html',
  'type' => 'text',
  'default' => 'recent_media.html',
  'comment' => '(output destination. eg. "recent_media.html".  relative path from vichan-root($config[\'root\']))'
);

$theme['config'][] = Array(
  'title' => 'HTML template',
  'name' => 'template',
  'type' => 'text',
  'default' => 'template.html',
  'comment' => '(input source. relative path from this theme directory.)'
);

$theme['config'][] = Array(
  'title' => 'This theme dir',
  'name' => 'themedir',
  'type' => 'text',
  'default' => dirname(__FILE__),
  'comment' => ''
);

$theme['config'][] = Array(
  'title' => 'Files sort asc',
  'name' => 'files_sort_asc',
  'type' => 'checkbox',
  'default' => '1',
  'comment' => '(sort order of files within a single post. checked:ascending, unchecked:descending)'
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
