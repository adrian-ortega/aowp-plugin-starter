<?php

/**
 * PSR4 AutoLoader
 * http://www.php-fig.org/psr/psr-4
 */
spl_autoload_register(function($class) {
	$prefix = 'AOD\\';
	$d = DIRECTORY_SEPARATOR;
	$base = __DIR__ . "{$d}lib{$d}";

	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0)
		return;

	$relative_class = substr($class, $len);
	$file = $base . str_replace('\\', $d, $relative_class) . '.php';

	if (file_exists($file))
		require $file;

	return;
});

/**
 * Include all helper files within the current directory
 * that are not this file since they're not autoloaded
 */
array_map(function($file) {
	require_once $file;
}, array_values(array_filter(array_map(function($file){
	return strpos($file, 'autoload.php') === false ? $file : false;
}, glob(__DIR__. '/*.php')))));