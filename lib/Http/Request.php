<?php

namespace AOD\Plugin\Http;

use Illuminate\Support\Collection;

class Request
{
    protected $method = 'get';

    protected $input_methods = [
        'post'   => INPUT_POST,
        'query'  => INPUT_GET,
        'cookie' => INPUT_COOKIE
    ];

    /**
     * @var Collection
     */
    protected $post_params;

    /**
     * @var Collection
     */
    protected $query_params;

    // @TODO cookieParams

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->method = strtolower( $_SERVER[ 'REQUEST_METHOD' ] );
        $this->parseParams();
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return bool
     */
    public function isMethod( $method )
    {
        return $this->getMethod() === $method;
    }

    /**
     * @return bool
     */
    public function isPost()
    {
        return $this->isMethod( 'post' );
    }

    /**
     * @return bool
     */
    public function isGet()
    {
        return $this->isMethod( 'get' );
    }

    /**
     * @return bool
     */
    public function isDelete()
    {
        return $this->isMethod( 'delete' );
    }

    /**
     * @return bool
     */
    public function isPut()
    {
        return $this->isMethod( 'put' );
    }

    /**
     * @return bool
     */
    public function isPatch()
    {
        return $this->isMethod( 'patch' );
    }

    /**
     * Returns all POST and GET inputs
     * @param array|null $only
     * @return Collection
     */
    public function all( array $only = null )
    {
        return $this->query_params->merge( $this->post_params )->only( $only );
    }

    /**
     * @param null|string $only
     * @param null|mixed $default
     * @return Collection|mixed
     */
    public function input($only = null, $default = null )
    {
        if( is_array($only) ) {
            return $this->all($only)->toArray();
        }

        return $this->all()->get($only, $default);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////
    ///

    /**
     * @param string $string
     * @return bool
     */
    private function isJson( $string )
    {
        if ( ! is_string( $string ) ) {
            return false;
        }

        json_decode( $string );

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Goes through all the method and their params to parse them into a collection for later use.
     */
    private function parseParams()
    {
        foreach ($this->input_methods as $key => $type) {
            $property = "{$type}_params";
            $this->{$property} = collect( filter_input_array( $type ) )
                ->map( function( $value ) {
                    return $this->isJson( $value ) ? json_decode( $value ) : $value;
                });
        }
    }

    /**
     * @param string $key
     * @return $this
     */
    public function forget(string $key)
    {
        if ( $this->post_params->has( $key ) ) {
            $this->post_params->forget( $key );
        }

        if ( $this->query_params->has( $key ) ) {
            $this->query_params->forget( $key );
        }

        return $this;
    }
}
