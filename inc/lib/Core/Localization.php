<?php

namespace AOD\Core;

use AOD\Core\Traits\Runable;

class Localization
{
	use Runable;
	/**
	 * @var string
	 */
	private $domain;

	/**
	 * The location of the Languages directory;
	 * @var string
	 */
	private $path;

	public function __construct( Container $container ) {
		$this->init($container);
		$this->path = $this->container->get('plugin_path') . '/languages';
		$this->domain = $this->container->get( 'plugin_text_domain' );
	}

	/**
	 * Loads the plugin domain for translations
	 */
	public function loadTextDomain()
	{
		load_plugin_textdomain($this->domain, false, $this->path);
	}

	/**
	 * Returns the text domain for the plugin
	 * @return string
	 */
	public function getDomain()
	{
		return $this->domain;
	}

	/**
	 * Run
	 */
	public function run()
	{
		$this->loader->addAction('plugins_loader', [$this, 'loadTextDomain']);
	}
}