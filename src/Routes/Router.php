<?php

namespace Mitsuki\Mitsuki\Routes;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;
use Mitsuki\Mitsuki\Exceptions\ClassOrMethodDoesNotExistException;


class Router
{

    private Filesystem $filesystem;
    private string $cacheFile;

    /**
     * Creates a new Route instance.
     *
     * @param RouteCollection $routeCollection The Symfony route collection.
     * @param RequestContext $requestContext The request context used by the router.
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
     * Adds a route to the collection.
     *
     * @param string $method HTTP method (GET, POST, PUT, PATCH, DELETE).
     * @param string $name Route name.
     * @param string $path Route path (e.g. /blog/{slug}).
     * @param array $controller Controller definition as [ControllerClass::class, 'methodName'].
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
     * @throws \ReflectionException
     */
    public function load(array $controllers): void
    {
        if ($this->filesystem->exists($this->cacheFile)) {
            $routesData = require $this->cacheFile;
            foreach ($routesData as $name => $data) {
                $this->addRoute($data['method'], $name, $data['path'], $data['controller']);
            }
            return;
        }

        $cachedData = $this->getRoutesFromControllers($controllers);

        $content = "<?php\nreturn " . var_export($cachedData, true) . ";";
        $this->filesystem->dumpFile($this->cacheFile, $content);

    }

    /**
     * @throws \ReflectionException
     */
    private function getRoutesFromControllers(array $controllers): array
    {
        $cachedData = [];
        foreach ($controllers as $controllerClass) {
            $reflection = new ReflectionClass($controllerClass);
            foreach ($reflection->getMethods() as $method) {
                $attributes = $method->getAttributes(Route::class);
                foreach ($attributes as $attribute) {
                    $routeInstance = $attribute->newInstance();

                    $this->addRoute(
                        $routeInstance->getMethods(),
                        $routeInstance->getName(),
                        $routeInstance->getPath(),
                        [$controllerClass, $method->getName()]
                    );

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


    /**
     * Runs the router for the given URI.
     *
     * Matches the URI against registered routes, instantiates the controller,
     * calls the specified method with resolved parameters, and outputs the response content.
     *
     * @param string $uri The URI to match (e.g. /blog/lorem-ipsum).
     *
     * @return Response.
     *
     * @throws ClassOrMethodDoesNotExistException If the controller class or method does not exist.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function run(Request $request): Response
    {
        $matcher = new UrlMatcher($this->routeCollection, $this->requestContext);
        $parameters = $matcher->match($request->getPathInfo());

        $controller = $parameters['_controller'];

        $controllerClass = $controller[0];
        $controllerMethod = $controller[1];

        $controllerInstance = $this->getControllerClass($controllerClass);

        if (!method_exists($controllerInstance, $controllerMethod)) {
            throw new ClassOrMethodDoesNotExistException();
        }

        unset($parameters['_controller'], $parameters['_route']);

        $response = call_user_func_array([$controllerInstance, $controllerMethod], $parameters);
        return $response;
    }

    /**
     * @param $controllerClass
     * @return mixed
     * @throws ClassOrMethodDoesNotExistException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getControllerClass($controllerClass)
    {
        if (!$this->container->has($controllerClass)) {
            if (!class_exists($controllerClass)) {
                throw new ClassOrMethodDoesNotExistException();
            }
        }

        return $this->container->get($controllerClass);
    }
}

