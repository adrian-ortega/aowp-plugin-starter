<?php

namespace AOD\Plugin\Config\Loaders;

use AOD\Plugin\Support\Contracts\LoaderInterface;

class FileArrayLoader implements LoaderInterface
{
    /**
     * @var string
     */
    protected $base_path;

    /**
     * @var array
     */
    protected $files = [];

    /**
     * FileArrayLoader constructor.
     * @param $base_path
     */
    public function __construct( $base_path )
    {
        $this->base_path = $base_path;
    }

    /**
     * @return array
     */
    public function parse()
    {
        $parsed = [];

        $this->files = array_filter(
            array_diff( scandir( $this->base_path ), [ '.', '..' ] ),
            function( $file ) {
                return is_file( $this->base_path . DIRECTORY_SEPARATOR . $file );
            }
        );

        foreach ($this->files as $file) {
            $key = str_replace('.php', '', $file);
            $value = include "{$this->base_path}/{$file}";
            $parsed[ $key ] = $value;
        }

        return $parsed;
    }
}
