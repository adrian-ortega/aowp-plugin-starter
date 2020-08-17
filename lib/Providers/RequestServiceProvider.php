<?php

namespace AOD\Plugin\Providers;

use AOD\Plugin\Http\Request;

class RequestServiceProvider extends AbstractServiceProvider
{
    protected $provides = [
        'request'
    ];

    public function register()
    {
        $this->container->share( Request::class, function() {
            return new Request();
        });

        $this->container->share( 'request', function() {
            return $this->container->get( Request::class );
        } );
    }
}
