<?php

namespace AOD\Plugin;

use AOD\Plugin\Config\ConfigRepository;
use AOD\Plugin\Providers\AssetsServiceProvider;
use AOD\Plugin\Providers\ConfigServiceProvider;
use AOD\Plugin\Providers\DatabaseMigrationsServiceProvider;
use AOD\Plugin\Providers\DatabaseServiceProvider;
use AOD\Plugin\Providers\RequestServiceProvider;
use AOD\Plugin\Providers\RestServiceProvider;
use AOD\Plugin\Support\Traits\SingletonTrait;
use League\Container\Container;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use League\Container\ReflectionContainer;

/**
 * Class Plugin
 * @package AOD
 *
 * @property Container $container
 */
class Plugin implements ContainerAwareInterface
{
    use SingletonTrait, ContainerAwareTrait;

    protected function __construct()
    {
        $container = new Container();
        $this->setContainer( $container );

        // The league container allows us to use Auto-Wiring, here we delegate the ReflectionContainer
        // to allow the container to resolve objects and their dependencies recursively by inspecting
        // type hints in your constructor's arguments.
        //
        // @reference https://container.thephpleague.com/3.x/auto-wiring/
        //
        $container->delegate( new ReflectionContainer );

        // The container also has the ability to set itself onto other objects if it detects that the
        // object uses the ContainerAwareInterface just like this object does. You can set your own
        // custom inflecters to detect instances of a certain class and set it for you.
        //
        $container->inflector( ContainerAwareInterface::class )->invokeMethod( 'setContainer', [ $container ]);

        // Here we start to set different dependencies for the plugin. For the most part, the following
        // defaults are the ones that are used the most. You can set each dependency by registering it
        // using a ServiceProvider.
        //
        $container->addServiceProvider( RequestServiceProvider::class );
        $container->addServiceProvider( ConfigServiceProvider::class );
        $container->addServiceProvider( DatabaseServiceProvider::class );
        $container->addServiceProvider( DatabaseMigrationsServiceProvider::class );

        //
        // Add your own service providers here
        //

        // Some services require a few things to be loaded to interact with. We use the `plugins_loaded`
        // action to make sure we push our services after it.
        //
        add_action( 'plugins_loaded', [ $this, 'addPluginDependantProviders' ]);
    }

    /**
     * Adds plugin dependant service providers to the container
     */
    public function addPluginDependantProviders()
    {
        $this->container->addServiceProvider( AssetsServiceProvider::class );
        $this->container->addServiceProvider( RestServiceProvider::class );

        //
        // Add your own plugin dependant service providers here
        //
    }

    /**
     * Wrapper for the config class
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function config($key, $default = null)
    {
        /**
         * @var ConfigRepository $config;
         */
        $config = $this->container->get( ConfigRepository::class );

        return $config->get( $key, $default );
    }
}
