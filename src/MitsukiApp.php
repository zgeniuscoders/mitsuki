<?php

namespace Mitsuki\Mitsuki;

use DI\Container;
use DI\ContainerBuilder;

/**
 * Main application class using PHP-DI as the dependency injection container.
 *
 * Initializes a DI container with given definitions and exposes it as a public property
 * for use throughout the application.
 *
 * @author Zgeniuscoders
 * @package Mitsuki\Mitsuki
 */
class MitsukiApp
{
    /**
     * The DI dependency injection container.
     *
     * @var Container
     */
    public Container $container;

    /**
     * Creates a new MitsukiApp instance and initializes the DI container.
     *
     * @param array|string $definitions Path to a definitions file or an array of definitions.
     *
     * @see https://php-di.org/doc/container-configuration.html
     */
    public function __construct(array|string $definitions)
    {
        $this->init($definitions);
    }

    /**
     * Initializes the DI container with the given definitions.
     *
     * @param array $definitions An array of DI definitions.
     *
     * @return void
     */
    private function init(array $definitions): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions($definitions);
        $this->container = $containerBuilder->build();
    }
}
