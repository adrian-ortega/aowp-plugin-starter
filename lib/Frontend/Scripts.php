<?php

namespace AOD\Plugin\Frontend;

use AOD\Plugin\Core\Assets\ScriptsAndStyles;
use AOD\Plugin\Core\Container;

class Scripts {

	public function __invoke( Container &$container ) {
		$assets = new ScriptsAndStyles( $container );
		$assets->script( 'main', $container->get( 'plugin_assets' ) . 'scripts/main.js' );

		return $assets;
	}
}