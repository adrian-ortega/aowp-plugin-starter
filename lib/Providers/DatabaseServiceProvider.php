<?php

namespace AOD\Plugin\Providers;

use AOD\Plugin\Database\Eloquent\Database;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class DatabaseServiceProvider extends AbstractServiceProvider
{
    protected $provides = [
        'db',
        \wpdb::class,
        Database::class,
        SchemaBuilder::class
    ];

    public function register()
    {
        global $wpdb;

        $this->container->share('db', function () use ( $wpdb ) {
            return $wpdb;
        });

        $this->container->share( \wpdb::class, function () use ( $wpdb ) {
            return $wpdb;
        });

        $this->container->share( Database::class, function () {
            return Database::getInstance();
        });

        $this->container->share( SchemaBuilder::class, function () {
            return Database::getInstance()->getSchemaBuilder();
        });
    }
}
