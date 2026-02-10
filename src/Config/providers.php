<?php

use Mitsuki\Mitsuki\Routes\Router;
use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * PHP‑DI container definitions for routing components.
 *
 * This file configures the dependency injection container to:
 *
 * - Create a singleton instance of Symfony\Component\Routing\RequestContext.
 * - Create a singleton instance of Symfony\Component\Routing\RouteCollection.
 * - Create a Mitsuki\Mitsuki\Routes\Router instance
 *   by injecting the RouteCollection and RequestContext dependencies.
 *
 * @author Zgeniuscoders
 * @package Mitsuki\Mitsuki;
 */

return [
    'cache.dir' => __DIR__ . '/../caches',
    /**
     * Definition for the routing request context.
     *
     * @var class-string<RequestContext>
     */
    RequestContext::class => \DI\create(RequestContext::class),

    /**
     * Definition for the route collection.
     *
     * @var class-string<RouteCollection>
     */
    RouteCollection::class => \DI\create(RouteCollection::class),

    Router::class => function (ContainerInterface $c) {
        $router = new Router(
            $c->get(RouteCollection::class),
            $c->get(RequestContext::class),
            $c,
            $c->get('cache.dir')
        );

        $router->load($c->get('controllers'));
        return $router;
    },
];
