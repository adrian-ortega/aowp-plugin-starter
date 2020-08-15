<?php

namespace AOD\Plugin\Support\Abstracts;

use ArrayAccess;
use Illuminate\Support\Arr;

abstract class AbstractRepository implements ArrayAccess
{
    /**
     * @var array
     */
    protected $items;

    /**
     * AbstractRepository constructor.
     * @param array $items
     */
    public function __construct( $items = [] )
    {
        $this->items = $items;
    }

    /**
     * @param string $key
     * @param null|mixed $default
     * @return array|ArrayAccess|mixed|void
     */
    public function get( $key, $default = null )
    {
        if( is_array( $key ) ) {
            return $this->getMany( $key );
        }

        return Arr::get( $this->items, $key, $default);
    }

    /**
     * @param array $keys
     * @return array
     */
    public function getMany( array $keys)
    {
        $out = [];

        foreach( $keys as $key => $default ) {
            if( is_numeric($key) ) {
                list ($key, $default ) = [$default, null];
            }

            $out[ $key ] = Arr::get( $this->items, $key, $default );
        }

        return $out;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return Arr::has( $this->items, $key );
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * @param string|array $key
     * @param mixed|null $value
     */
    public function set( $key, $value = null )
    {
        $keys = is_array( $key ) ? $key : [ $key => $value ];
        foreach( $keys as $key => $value ) {
            Arr::set( $this->items, $key, $value );
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function push( $key, $value )
    {
        $array = $this->get( $key );
        $array[] = $value;
        $this->set( $key, $array );
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// Array Access
    ///

    /**
     * @inheritdoc
     */
    public function offsetExists( $key )
    {
        return $this->has($key);
    }

    /**
     * @param mixed $key
     *
     * @return array|mixed|null
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($key)
    {
        $this->set($key, null);
    }
}
