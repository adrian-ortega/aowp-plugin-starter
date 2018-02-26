<?php

namespace AOD\Plugin\Core\Abstracts;

use AOD\Plugin\Core\Container;
use AOD\Plugin\Core\Loader;

abstract class Runnable {
	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * @var Loader|null
	 */
	protected $loader;

	public function __construct( Container &$container ) {
		$this->init( $container );
	}

	/**
	 * Sets the container and loader. Can be used when overriding the __construct method.
	 *
	 * @param Container $container
	 *
	 * @return $this
	 */
	protected function init( Container $container ) {
		$this->container = $container;
		$this->loader    = $container->get( 'loader' );

		return $this;
	}

	abstract public function run();
}