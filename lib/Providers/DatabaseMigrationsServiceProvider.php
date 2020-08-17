<?php

namespace AOD\Plugin\Providers;

use AOD\Plugin\Database\Migrator\Migrator;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

class DatabaseMigrationsServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    public function boot()
    {
        if ($migrator = $this->container->get( Migrator::class ) ) {
            $migrator->run();
        }
    }

    public function register()
    {
        // Not used
    }
}
