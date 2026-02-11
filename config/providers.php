<?php

use Mitsuki\Mitsuki\Listeners\PoweredByListener;
use Mitsuki\Mitsuki\Routes\Router;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * PHP-DI Container Definitions
 *
 * This file defines the core services required to run the Mitsuki Framework.
 * It wires Symfony's HttpKernel components with the internal Mitsuki Router
 * and handles event dispatching and controller resolution.
 *
 * @author Zgeniuscoders
 * @package Mitsuki\Mitsuki
 */

return [
    /** @var string Directory path for storing compiled route caches. */
    'cache.dir' => __DIR__ . '/../caches',

    /**
     * Controller Resolver Definition.
     * * Bridges the Symfony HttpKernel with the Mitsuki Router.
     * It uses an anonymous class to adapt the Router's callable resolution
     * to the ControllerResolverInterface.
     */
    ControllerResolverInterface::class => function (ContainerInterface $c) {
        return new class($c->get(Router::class)) implements ControllerResolverInterface {
            public function __construct(private Router $router)
            {
            }

            public function getController(Request $request): callable|false
            {
                return $this->router->getCallable($request);
            }
        };
    },

    /**
     * Event Dispatcher Definition.
     * * Initializes the central event system and registers listeners
     * defined in the 'listeners' configuration key using lazy-loading.
     */
    EventDispatcher::class => function (ContainerInterface $c) {
        $dispatcher = new EventDispatcher();
        $listeners = $c->get('listeners');

        foreach ($listeners as $listener) {
            // Using a closure for lazy-loading the listener instance from the container
            $dispatcher->addListener(KernelEvents::RESPONSE, function ($event) use ($c) {
                $c->get(PoweredByListener::class)->onKernelResponse($event);
            });
        }
        return $dispatcher;
    },

    /**
     * HttpKernel Definition.
     * * The main engine that handles the Request and transforms it into a Response.
     * Wires together the dispatcher, controller resolver, and argument resolver.
     */
    HttpKernelInterface::class => function (ContainerInterface $c) {
        return new HttpKernel(
            $c->get(EventDispatcher::class),
            $c->get(ControllerResolverInterface::class),
            new RequestStack(),
            new ArgumentResolver()
        );
    },

    /**
     * Routing Request Context.
     * Maintains information about the current request for URL matching.
     */
    RequestContext::class => \DI\create(RequestContext::class),

    /**
     * Route Collection.
     * A container for all registered Symfony Route objects.
     */
    RouteCollection::class => \DI\create(RouteCollection::class),

    /**
     * Mitsuki Router Definition.
     * * Initializes the custom framework router, injects dependencies,
     * and triggers the route loading process from the provided controllers.
     */
    Router::class => function (ContainerInterface $c) {
        $router = new Router(
            $c->get(RouteCollection::class),
            $c->get(RequestContext::class),
            $c,
            $c->get('cache.dir')
        );

        $controllers = $c->has('controllers') ? $c->get('controllers') : [];
        $router->load($controllers);

        return $router;
    },
];