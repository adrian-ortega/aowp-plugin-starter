<?php

$plugin_file = realpath( __DIR__ . '/../index.php' );

return [
    'base'       => $plugin_path = plugin_dir_path($plugin_file),
    'base_url'   => $plugin_url = plugin_dir_url($plugin_file),
    'templates'  => "{$plugin_path}templates",
    'assets'     => "{$plugin_path}assets",
    'assets_url' => "{$plugin_url}assets",
    'lib_path'   => "{$plugin_path}lib"
];
