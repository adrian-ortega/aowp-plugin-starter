<?php

namespace AOD\Plugin\Http\Rest;

use AOD\Plugin\Http\Controllers\AbstractController;
use AOD\Plugin\Http\Response;
use Exception;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use WP_REST_Response;

class Factory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function callback( $controller, $method = null )
    {
        return function () use ( $controller, $method ) {
            $response = $this->callMethodFromController( $controller, $method );

            if($response instanceof Response) {
                return new WP_REST_Response(
                    $response->getData(),
                    $response->getStatusCode(),
                    $response->getHeaders()
                );
            }

            return new WP_REST_Response( $response );
        };
    }

    /**
     * @param AbstractController $controller
     * @param $method
     * @return mixed
     * @throws ReflectionException
     */
    public function callMethodFromController( AbstractController $controller, $method )
    {
        $reflectionMethod = new ReflectionMethod( $controller, $method );
        $dependencies = [];

        foreach ( $reflectionMethod->getParameters() as $parameter ) {
            $dependency = $parameter->getClass();

            if ( $dependency === null ) {
                if ( $parameter->isDefaultValueAvailable() ) {
                    $dependencies[] = $parameter->getDefaultValue();
                }

                throw new Exception('Cannot resolve the values without defaults');
            } else {
                $dependencies[] = $this->resolveDependency( $dependency );
            }
        }

        return $reflectionMethod->invokeArgs( $controller, $dependencies );
    }

    /**
     * @param $controller
     * @param string $method
     * @return bool
     */
    public function hasPublicMethod($controller, $method)
    {
        if ( ! method_exists( $controller, $method ) ) {
            return false;
        }

        try {
            return ( new ReflectionMethod( $controller, $method ) )->isPublic();
        } catch ( Exception $exception ) {
            return false;
        }
    }

    /**
     * @param ReflectionClass $dependency
     * @return mixed
     */
    protected function resolveDependency(ReflectionClass $dependency)
    {
        return $this->container->get( $dependency->getName() );
    }
}
