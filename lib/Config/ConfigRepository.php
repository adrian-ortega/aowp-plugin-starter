<?php

namespace AOD\Plugin\Config;

use AOD\Plugin\Config\Loaders\FileArrayLoader;
use AOD\Plugin\Support\Abstracts\AbstractRepository;

class ConfigRepository extends AbstractRepository
{
    public function __construct( $items = [] )
    {
        parent::__construct( $items );

        $loader = new FileArrayLoader( realpath( __DIR__ . '/../../config' ) );
        $this->set( $loader->parse() );
    }
}
