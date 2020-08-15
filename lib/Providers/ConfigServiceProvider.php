<?php

namespace AOD\Plugin\Providers;

use AOD\Plugin\Config\ConfigRepository;

class ConfigServiceProvider extends AbstractServiceProvider
{
    protected $provides = [
        ConfigRepository::class,
        'config'
    ];

    public function register()
    {
        $this->container->share( ConfigRepository::class, function() {
            $plugin_file = realpath( __DIR__ . '/../../index.php' );

            return new ConfigRepository( array_merge( get_file_data( $plugin_file, [
                'plugin_name'        => 'Plugin Name',
                'plugin_version'     => 'Version',
                'plugin_text_domain' => 'Text Domain'
            ] ), compact( 'plugin_file' ) ) );
        } );

        $this->container->share('config', function() {
            return $this->container->get( ConfigRepository::class );
        } );
    }
}
