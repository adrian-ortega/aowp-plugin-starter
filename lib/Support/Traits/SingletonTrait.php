<?php

namespace AOD\Plugin\Support\Traits;

use Exception;

trait SingletonTrait
{
    protected function __construct()
    {
        //
    }

    /**
     * @throws Exception
     */
    final private function __clone()
    {
        throw new Exception( 'You cannot clone a singleton' );
    }

    /**
     * @throws Exception
     */
    final private function __wakeup ()
    {
        throw new Exception( 'You can not clone a singleton' );
    }

    /**
     * @return mixed
     */
    public static function getInstance()
    {
        static $instances;

        $calledClass = get_called_class();

        if(!isset($instances[$calledClass])) {
            $instances[ $calledClass ] = new $calledClass;
        }

        return $instances[ $calledClass ];
    }
}
