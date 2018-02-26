<?php

namespace AOD\Plugin;

use AOD\Plugin\Core\Container;
use AOD\Plugin\Core\Exceptions\RuntimeException;
use AOD\Plugin\Core\Loader;
use AOD\Plugin\Core\Localization;

/**
 * Class Plugin
 * @package AOD
 */
class Plugin {
	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * Plugin constructor.
	 *
	 * @param null|string $plugin_file
	 * @param null $plugin_name
	 * @param string $plugin_version
	 */
	public function __construct( $plugin_file = null, $plugin_name = null, $plugin_version = '2018.0.1' ) {
		if ( empty( $pluginFile ) ) {
			$plugin_file = realpath( __DIR__ . '/../index.php' );
		}

		$this->container = new Container( [
			'plugin_file'     => $plugin_file,
			'plugin_path'     => plugin_dir_path( $plugin_file ),
			'plugin_url'      => plugin_dir_url( $plugin_file ),
			'plugin_assets'   => plugin_dir_url( $plugin_file ) . 'assets/',
			'plugin_basename' => plugin_basename( $plugin_file ),
			'loader'          => function ( Container &$c ) {
				return new Loader( $c );
			},
		] );

		if ( ! empty( $plugin_name ) ) {
			$this->init( $plugin_name, $plugin_version );
		}
	}

	/**
	 * Initializes the plugin
	 *
	 * @param null|string $name
	 * @param string $version
	 *
	 * @return $this
	 */
	public function init( $name = null, $version = '2018.0.1' ) {
		if ( empty( $name ) ) {
			throw new RuntimeException( 'Please provide a plugin name' );
		}

		$this->container->set( 'plugin_name', $name );
		$this->container->set( 'plugin_version', $version );
		$this->container->set( 'plugin_text_domain', sanitize_title( $name ) );
		$this->container->set( 'localization', function ( Container &$c ) {
			return new Localization( $c );
		} );

		return $this;
	}

	/**
	 * Adds a runable
	 *
	 * @param string $name
	 * @param mixed|callable $class
	 *
	 * @return $this
	 */
	public function load( $name, $class ) {
		$this->container->loader->addRunable( $name, $class );

		return $this;
	}

	/**
	 * Run
	 */
	public function run() {
		foreach ( $this->container->loader->getRunnables() as $callable ) {

			if ( method_exists( $callable, 'run' ) ) {
				$callable->run();
			}
		}

		$this->container->loader->run();
	}
}