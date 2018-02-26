<?php

namespace AOD\Plugin\Core;

use AOD\Plugin\Core\Abstracts\Runnable;

class Loader {
	/**
	 * Array of filters to be registered
	 * @var array
	 */
	protected $filters = [];

	/**
	 * Array of actions to be registered
	 * @var array
	 */
	protected $actions = [];

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * List of names for runners
	 * @var array
	 */
	protected $runables = [];

	public function __construct( Container &$container ) {
		$this->container = $container;
	}

	/**
	 * Adds an activation hook for the plugin
	 *
	 * @param callable $callback
	 */
	public function addActivationHook( $callback ) {
		$file = plugin_basename( $this->container->get( 'plugin_file' ) );
		$this->addAction( "activation_{$file}", $callback );
	}

	/**
	 * Adds a deactiavation hook for the plugin
	 *
	 * @param callable $callback
	 */
	public function addDeactivationHook( $callback ) {
		$file = plugin_basename( $this->container->get( 'plugin_file' ) );
		add_action( "deactivate_{$file}", $callback );
	}

	/**
	 * Adds an action
	 *
	 * @param string $hook The name of the hook we want to register to
	 * @param callable $callback The callback function, either a closure, function string or object
	 * @param integer $priority Priority of the action
	 * @param integer $accepted_args The number of accepted arguments for the callable
	 *
	 * @return $this
	 */
	public function addAction( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->add( $this->actions, $hook, $callback, $priority, $accepted_args );

		return $this;
	}

	/**
	 * Adds a filter
	 *
	 * @param string $hook The name of the hook we want to register to
	 * @param callable $callback The callback function, either a closure, function string or object
	 * @param integer $priority Priority of the action
	 * @param integer $accepted_args The number of accepted arguments for the callable
	 *
	 * @return $this
	 */
	public function addFilter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->add( $this->filters, $hook, $callback, $priority, $accepted_args );

		return $this;
	}

	/**
	 * Takes the filters or arrays by reference and adds an item to it
	 *
	 * @param array $hooks the name of the hook we want to register to
	 * @param string $hook the name of the hook we want to register to
	 * @param callable $callback The callback function, either a closure, function string or object
	 * @param integer $priority Priority of the action
	 * @param integer $accepted_args The number of accepted arguments for the callable
	 */
	private function add( &$hooks, $hook, $callback, $priority, $accepted_args ) {
		$hooks[] = [
			'hook'          => $hook,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		];
	}

	/**
	 * @param string $name
	 * @param callable $class
	 */
	public function addRunable( $name = '', callable $class ) {
		$this->runables[] = $name;
		$this->container->set($name, $class);
	}

	/**
	 * Returns all the registered classes that extend Runable
	 * @return array|Runnable[]
	 */
	public function getRunnables() {
		return array_map(function($name) {
			return $this->container->get($name);
		}, $this->runables);
	}

	/**
	 * Registers all filters and actions
	 * @return void
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				$hook['callback'],
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				$hook['callback'],
				$hook['priority'],
				$hook['accepted_args']
			);
		}
	}
}