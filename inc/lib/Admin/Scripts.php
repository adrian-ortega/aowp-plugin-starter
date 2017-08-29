<?php

namespace AOD\Admin;

use AOD\Core\Assets\AdminScriptAndStyles;
use AOD\Core\Container;

class Scripts
{
	public function __invoke( Container $container ) {
		$assets = new AdminScriptAndStyles($container);
		$assets->script('test', $container->get('plugin_url') . 'assets/scripts/test.js');

		return $assets;
	}
}