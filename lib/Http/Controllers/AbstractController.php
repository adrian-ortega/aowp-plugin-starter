<?php

namespace AOD\Plugin\Http\Controllers;

use ReflectionClass;
use ReflectionException;

abstract class AbstractController
{
    /**
     * @var array
     */
    protected $api_routes = [

    ];

    /**
     * @return array
     */
    public function getApiRoutes(): array
    {
        return $this->api_routes;
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    public function getName()
    {
        $reflection = new ReflectionClass($this);
        $name       = str_replace(__NAMESPACE__, '', $reflection->getName());
        $name       = str_replace('Controller', '', $name);
        $name       = str_replace(DIRECTORY_SEPARATOR, '/', $name);
        $name       = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $name));
        $name       = ltrim($name, '/');

        return $name;
    }
}
