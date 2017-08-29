<?php

namespace AOD;

use AOD\Core\Container;
use AOD\Core\Exceptions\RuntimeException;
use AOD\Core\Loader;
use AOD\Core\Localization;

/**
 * Class Plugin
 * @package AOD
 */

class Plugin {
	/**
	 * @var Container
	 */
	protected $container;

	public function __construct( $plugin_file = null ) {

		if ( empty( $pluginFile ) ) {
			$plugin_file = realpath(__DIR__ . '/../../index.php');
		}

		$this->container = new Container( [
			'plugin_file'     => $plugin_file,
			'plugin_path'     => plugin_dir_path( $plugin_file ),
			'plugin_url'      => plugin_dir_url( $plugin_file ),
			'plugin_basename' => plugin_basename( $plugin_file ),
			'loader'          => function ( Container &$c ) {
				return new Loader( $c );
			},
		] );
	}

	public function init( $name, $version = '2017.0.1' ) {
		if(empty($name))
			throw new RuntimeException('Please provide a plugin name');

		$this->container->set( 'plugin_name', $name );
		$this->container->set( 'plugin_version', $version );
		$this->container->set( 'plugin_text_domain', sanitize_title( $name ) );
		$this->container->set( 'localization', function(Container &$c) {
			return new Localization($c);
		});

		return $this;
	}

	/**
	 * Adds a runable
	 * @param string $name
	 * @param mixed|callable $class
	 *
	 * @return $this
	 */
	public function load( $name, $class ) {
		$this->container->loader->addRunable($name, $class);
		return $this;
	}

	/**
	 * Run
	 */
	public function run() {
		foreach($this->container->loader->getRunables() as $callable) {

			if(method_exists($callable, 'run')) {
				$callable->run();
			}
		}

		$this->container->loader->run();
	}
}