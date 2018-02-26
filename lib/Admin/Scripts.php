<?php

namespace AOD\Plugin\Admin;

use AOD\Plugin\Core\Assets\AdminScriptsAndStyles;
use AOD\Plugin\Core\Container;

class Scripts {
	public function __invoke( Container $container ) {
		$assets = new AdminScriptsAndStyles( $container );
		$assets->script( 'admin', $container->get( 'plugin_assets' ) . 'scripts/admin.js' );

		return $assets;
	}
}