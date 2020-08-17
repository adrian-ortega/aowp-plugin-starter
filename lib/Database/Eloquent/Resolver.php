<?php

namespace AOD\Plugin\Database\Eloquent;

use Illuminate\Database\Connection as IlluminateDatabaseConnection;
use Illuminate\Database\ConnectionResolverInterface;

class Resolver implements ConnectionResolverInterface {

    /**
     * Get a database connection instance.
     *
     * @param  string $name
     *
     * @return IlluminateDatabaseConnection
     */
    public function connection( $name = null )
    {
        return IlluminateDatabaseConnection::instance();
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        // TODO: Implement getDefaultConnection() method.
    }

    /**
     * Set the default connection name.
     *
     * @param  string $name
     *
     * @return void
     */
    public function setDefaultConnection( $name )
    {
        // TODO: Implement setDefaultConnection() method.
    }
}
