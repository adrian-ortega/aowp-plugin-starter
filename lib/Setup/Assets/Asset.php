<?php

namespace AOD\Plugin\Setup\Assets;

class Asset
{
    /**
     * @var string
     */
    public $handle;

    /**
     * @var string
     */
    public $source;

    /**
     * @var array
     */
    public $dependencies = [];

    /**
     * @var string
     */
    public $version = '';
    /**
     * @var string
     */
    public $screen = 'screen';

    /**
     * @var bool
     */
    public $footer = true;

    /**
     * @var null|string
     */
    public $conditional = null;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var boolean
     */
    protected $isExternal = false;

    /**
     * @var null|callable
     */
    protected $blocker = null;

    public function __construct( $attributes = [], $base_url = '', $base_path = '' )
    {
        $this->baseUrl = $base_url;
        $this->basePath = $base_path;
        $this->fillAttributes($attributes);
    }

    /**
     * @return bool
     */
    public function isBlocked()
    {
        return is_callable($this->blocker)
            ? (bool) call_user_func_array($this->blocker, [$this])
            : null;
    }

    /**
     * @param array $items
     */
    public function fillAttributes( array $items )
    {
        foreach( $items as $name => $value ) {
            if( property_exists( $this, $name ) ) {
                $this->{$name} = $value;
            }
        }

        if( ! empty( $this->source ) && ( strpos( $this->source, 'http' ) !== false ) ) {
            $urlParts = parse_url( $this->source );
            $this->isExternal = $urlParts['host'] !== $_SERVER['HTTP_HOST'];
        }
    }

    /**
     * Returns a hashed modified date timestamp as the version if the file exists
     * @return string
     */
    public function version()
    {
        if ( $this->isExternal ) {
            return null;
        }

        $source = $this->source( true );

        // This is just a fallback, if the file does not exist, we will use the style.css file as
        // the baseline for all versions.
        //
        if ( ! file_exists( $source ) ) {
            $dir = get_stylesheet_directory();
            $source = "{$dir}/style.css";
        }

        return base_convert( date( 'YmdHis', filemtime( $source ) ), 10, 36 );
    }

    /**
     * @param bool $path
     * @return string
     */
    public function source( $path = false )
    {
        if( $this->isExternal ) {
            return $this->source;
        }

        return $path
            ? "{$this->basePath}/{$this->source}"
            : "{$this->baseUrl}/{$this->source}";
    }
}
