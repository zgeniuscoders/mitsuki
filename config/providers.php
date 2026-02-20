<?php

use Mitsuki\Controller\Resolvers\ControllerResolver;
use Mitsuki\Hermite\Router;
use Mitsuki\Mitsuki\Http\Client\HttpClientInterface;
use Mitsuki\Mitsuki\Http\Client\MitsukiHttpClient;
use Mitsuki\Mitsuki\Listeners\PoweredByListener;
use Mitsuki\Mitsuki\Listeners\ValidationExceptionListener;
use Mitsuki\Mitsuki\Resolvers\ValidatableRequestResolver;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\DefaultValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestAttributeValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * PHP-DI Container Definitions
 */
return [
    /** @var string The root directory of the source code to scan for controllers. */
    'project.root' => dirname(__DIR__) . '/src',

    /** @var string Directory path for storing compiled route caches. */
    'cache.dir' => dirname(__DIR__) . '/var/caches',

    ValidatableRequestResolver::class => \DI\autowire(),

    /**
     * Controller Resolver.
     * Responsible for scanning the project to discover attributes.
     */
    ControllerResolver::class => function (ContainerInterface $c) {
        return new ControllerResolver($c->get('project.root'));
    },

    /**
     * Mitsuki HttpClient.
     * Provides an abstracted HTTP client, masking Symfony's implementation.
     */
    HttpClientInterface::class => function (ContainerInterface $c) {
        // We create the Symfony instance here, hidden from the rest of the app
        $symfonyClient = HttpClient::create();
        return new MitsukiHttpClient($symfonyClient);
    },

    /**
     * Mitsuki Router Definition.
     */
    Router::class => function (ContainerInterface $c) {
        $router = new Router(
            $c->get(RouteCollection::class),
            $c->get(RequestContext::class),
            $c,
            $c->get(ControllerResolver::class),
            $c->get('cache.dir')
        );

        $controllers = $c->has('controllers') ? $c->get('controllers') : [];
        $router->load($controllers);

        return $router;
    },

    /**
     * Symfony HttpKernel Controller Resolver.
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
     * Event Dispatcher.
     */
    EventDispatcher::class => function (ContainerInterface $c) {
        $dispatcher = new EventDispatcher();
        $listeners = $c->has('listeners') ? $c->get('listeners') : [];

        foreach ($listeners as $listener) {
            $dispatcher->addListener(KernelEvents::RESPONSE, function ($event) use ($c) {
                $c->get(PoweredByListener::class)->onKernelResponse($event);
            });
        }

        $dispatcher->addListener(KernelEvents::EXCEPTION, function (ExceptionEvent $event) use ($c) {
            (new ValidationExceptionListener())->onKernelException($event);
        });

        return $dispatcher;
    },

    /**
     * HttpKernel Main Engine.
     */
    HttpKernelInterface::class => function (ContainerInterface $c) {

        $argumentResolver = new ArgumentResolver(
            null,
            [
                $c->get(ValidatableRequestResolver::class),
                new RequestAttributeValueResolver(),
                new RequestValueResolver(),
                new DefaultValueResolver(),
            ]
        );

        return new HttpKernel(
            $c->get(EventDispatcher::class),
            $c->get(ControllerResolverInterface::class),
            new RequestStack(),
            $argumentResolver
        );
    },

    RequestContext::class => \DI\create(RequestContext::class),
    RouteCollection::class => \DI\create(RouteCollection::class),
];