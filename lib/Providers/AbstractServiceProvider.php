<?php

namespace AOD\Plugin\Providers;

use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider as BaseAbstractServiceProvider;
use Psr\Container\ContainerInterface;

/**
 * Class AbstractServiceProvider
 * @package AOD\Plugin\Providers
 *
 * @property Container|ContainerInterface $container
 * @method Container|ContainerInterface getContainer()
 */
abstract class AbstractServiceProvider extends BaseAbstractServiceProvider
{

}
