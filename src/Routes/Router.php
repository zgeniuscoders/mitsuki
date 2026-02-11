<?php

namespace Mitsuki\Mitsuki\Routes;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class Router
 * * Responsible for managing the application's routing system by converting
 * PHP 8 Attributes into Symfony Route objects and resolving HTTP requests
 * into executable callables.
 */
class Router
{
    /** @var Filesystem Helper for filesystem operations (cache handling). */
    private Filesystem $filesystem;

    /** @var string The absolute path to the compiled routes cache file. */
    private string $cacheFile;

    /**
     * Router Constructor.
     *
     * @param RouteCollection $routeCollection Symfony collection to store registered routes.
     * @param RequestContext $requestContext Holds information about the current request (method, host, etc.).
     * @param ContainerInterface $container PSR-11 container to resolve controller instances.
     * @param string $cacheDir Directory where the cache file should be stored.
     */
    public function __construct(
        private RouteCollection    $routeCollection,
        private RequestContext     $requestContext,
        private ContainerInterface $container,
        string                     $cacheDir
    )
    {
        $this->cacheFile = $cacheDir . '/cache_routes.php';
        $this->filesystem = new Filesystem();
    }

    /**
     * Adds a single route to the Symfony RouteCollection.
     *
     * @param string $method HTTP method(s) allowed (GET, POST, etc.).
     * @param string $name Unique identifier for the route.
     * @param string $path The URL pattern (e.g., /user/{id}).
     * @param array $controller Controller definition: [ClassName::class, 'methodName'].
     *
     * @return void
     */
    private function addRoute(string $method, string $name, string $path, array $controller): void
    {
        $this->routeCollection->add($name, new SymfonyRoute($path, [
            '_controller' => $controller,
        ], methods: $method));
    }

    /**
     * Initializes the routes by reading from cache or scanning controllers.
     *
     * @param array $controllers List of controller FQCNs (Fully Qualified Class Names) to scan.
     * @return void
     * @throws \ReflectionException If a controller class does not exist.
     */
    public function load(array $controllers): void
    {
        // 1. Try to load from cache for performance
        if ($this->filesystem->exists($this->cacheFile)) {
            $routesData = require $this->cacheFile;
            foreach ($routesData as $name => $data) {
                $this->addRoute($data['method'], $name, $data['path'], $data['controller']);
            }
            return;
        }

        // 2. Scan controllers using Reflection if cache is missing
        $cachedData = $this->getRoutesFromControllers($controllers);

        // 3. Persist the scanned routes to the cache file
        $content = "<?php\nreturn " . var_export($cachedData, true) . ";";
        $this->filesystem->dumpFile($this->cacheFile, $content);
    }

    /**
     * Matches the current request to a route and returns a controller callable.
     *
     * @param Request $request The current Symfony Request object.
     * @return callable The resolved [ControllerInstance, MethodName].
     * * @throws NotFoundHttpException If no route matches the request or the collection is empty.
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface If the controller cannot be resolved from the container.
     */
    public function getCallable(Request $request): callable
    {
        try {
            $matcher = new UrlMatcher($this->routeCollection, $this->requestContext);

            // Attempt to find a match for the request path
            $parameters = $matcher->match($request->getPathInfo());

            // Merge route parameters (id, slug, etc.) into request attributes
            $request->attributes->add($parameters);

            [$controllerClass, $method] = $parameters['_controller'];

            // Verify if the controller class exists
            if (!$this->container->has($controllerClass)) {
                if (!class_exists($controllerClass)) {
                    throw new \RuntimeException("Class $controllerClass not found.");
                }
            }

            // Retrieve the controller instance from the Dependency Injection container
            $instance = $this->container->get($controllerClass);

            return [$instance, $method];

        } catch (ResourceNotFoundException|NoConfigurationException $e) {
            // Convert Symfony Routing exceptions into a standard HTTP 404
            throw new NotFoundHttpException("No route found for " . $request->getPathInfo(), $e);
        }
    }

    /**
     * Scans controller classes for the #[Route] attribute using PHP Reflection API.
     *
     * @param array $controllers Array of controller class names.
     * @return array Prepared route data for the cache file.
     * @throws \ReflectionException If a class is invalid.
     */
    private function getRoutesFromControllers(array $controllers): array
    {
        $cachedData = [];
        foreach ($controllers as $controllerClass) {
            $reflection = new ReflectionClass($controllerClass);

            foreach ($reflection->getMethods() as $method) {
                // Look for #[Route] attributes on each method
                $attributes = $method->getAttributes(Route::class);

                foreach ($attributes as $attribute) {
                    /** @var Route $routeInstance */
                    $routeInstance = $attribute->newInstance();

                    // Register the route in the current session
                    $this->addRoute(
                        $routeInstance->getMethods(),
                        $routeInstance->getName(),
                        $routeInstance->getPath(),
                        [$controllerClass, $method->getName()]
                    );

                    // Prepare data for persistent cache
                    $cachedData[$routeInstance->getName()] = [
                        'method' => $routeInstance->getMethods(),
                        'path' => $routeInstance->getPath(),
                        'controller' => [$controllerClass, $method->getName()]
                    ];
                }
            }
        }
        return $cachedData;
    }
}