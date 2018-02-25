<?php

namespace AOD\Plugin\Core;

/**
 * Class Container
 * @package AOD\Core
 * @property Loader $loader
 */
class Container implements \ArrayAccess
{
	/**
	 * Saved items
	 * @var array
	 */
	private $items = [];

	/**
	 * All items are cached to return a single instance
	 * @var array
	 */
	private $cache = [];

	/**
	 * Container constructor.
	 *
	 * @param array $items
	 */
	public function __construct( $items = [] ) {
		if(is_array($items))
			foreach($items as $key => $value)
				$this->offsetSet($key, $value);
	}

	/**
	 * @inheritdoc
	 */
	public function offsetExists( $offset ) {
		return isset($this->items[$offset]);
	}

	/**
	 * @inheritdoc
	 */
	public function offsetGet( $offset ) {
		// Return null if the item key does not exist
		if ( !$this->offsetExists( $offset ) ) {
			return null;
		}

		// Check to see if the item has been cached before
		// trying to set and/or instantiate it
		if ( isset( $this->cache[$offset] ) ) {
			return $this->cache[$offset];
		}

		// Since the item exists, we will save it in a temporary
		// variable to check if its a callable
		$item = $this->items[$offset];

		// Since this allows to store callables, like a closure
		// or an object's method, we check to see if its callable
		// before we try to set it into the cache items
		if($item instanceof \Closure || is_callable($item) ||
			( is_string($item) && class_exists($item) ) )  {

			// Instantiate the callable and pass this container into it.
			$item = call_user_func_array( $this->items[$offset], [ &$this ] );
		}

		// Finally, its saved into cache for future calls
		$this->cache[$offset] = $item;

		// Item is returned for the first time
		return $item;
	}

	/**
	 * @inheritdoc
	 */
	public function offsetSet( $offset, $value ) {
		$this->items[$offset] = $value;
	}

	/**
	 * @inheritdoc
	 */
	public function offsetUnset($offset) {
		unset($this->items[$offset]);
	}

	////////////////////////////////////////////////////////////////////////
	//  Magic Methods
	////////////////////////////////////////////////////////////////////////

	public function __get( $item ) {
		return $this->offsetGet($item);
	}

	public function __set( $key, $value ) {
		$this->offsetSet($key, $value);
	}

	public function __isset( $item ) {
		return $this->offsetExists($item);
	}

	////////////////////////////////////////////////////////////////////////
	//  API Wrappers
	////////////////////////////////////////////////////////////////////////

	/**
	 * Wrapper for offsetSet
	 * @param $key
	 * @param $item
	 */
	public function set($key, $item)
	{
		$this->offsetSet($key, $item);
	}

	/**
	 * Wrapper for offsetExists, Check to see if an item exists
	 *
	 * @param string $item
	 *
	 * @return bool
	 */
	public function has( $item )
	{
		return $this->offsetExists( $item );
	}

	/**
	 * Wrapper for offsetGet
	 * @param $item
	 * @return mixed|null
	 */
	public function get( $item )
	{
		return $this->offsetGet( $item );
	}
}