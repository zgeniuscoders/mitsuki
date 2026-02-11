<?php

namespace Mitsuki\Mitsuki;

use DI\Container;
use DI\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class MitsukiApp
 *
 * The core engine of the Mitsuki Framework. This class orchestrates the Dependency Injection
 * container initialization, handles the HTTP request-response lifecycle, and manages
 * application-wide configurations.
 *
 * @author Zgeniuscoders
 * @package Mitsuki\Mitsuki
 */
class MitsukiApp
{
    /**
     * @var Container The PHP-DI dependency injection container instance.
     */
    private Container $container;

    /**
     * @var array Custom DI definitions passed during instantiation.
     */
    private array $definitions = [];

    /**
     * @var string The absolute path to the project root directory.
     */
    private string $projectRoot;

    /**
     * MitsukiApp Constructor.
     *
     * Initializes the application by setting the project root and merging custom definitions
     * before triggering the container build process.
     *
     * @param string $projectRoot The base path of the application.
     * @param array $definitions Optional array of DI definitions to override or extend defaults.
     */
    public function __construct(string $projectRoot, array $definitions = [])
    {
        $this->definitions = $definitions;
        $this->projectRoot = rtrim($projectRoot, DIRECTORY_SEPARATOR);
        $this->init();
    }

    /**
     * Initializes the DI Container.
     *
     * Loads the default providers from the configuration file, merges them with
     * manual definitions and internal paths, then builds the final PSR-11 container.
     *
     * @return void
     * @throws \RuntimeException If the mandatory providers.php configuration file is missing.
     * @throws \Exception If the container builder fails to compile or build.
     */
    private function init(): void
    {
        $containerBuilder = new ContainerBuilder();

        $providerPath = $this->projectRoot . '/config/providers.php';

        if (!file_exists($providerPath)) {
            throw new \RuntimeException("Configuration file not found: $providerPath");
        }

        $config = require $providerPath;

        $containerBuilder->addDefinitions(
            array_merge(
                $config,
                $this->definitions
            )
        );

        $this->container = $containerBuilder->build();
    }

    /**
     * Retrieves the initialized DI Container.
     *
     * @return Container The current application container.
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Executes the application request cycle.
     *
     * Resolves the HttpKernel from the container, processes the given or global request,
     * sends the response to the client (if not in a test/CLI environment),
     * and triggers the kernel termination logic.
     *
     * @param Request|null $request Optional Symfony Request object (useful for testing).
     * @return Response The generated HTTP response.
     * @throws \Exception If the Kernel fails to handle the request or resolve from the container.
     */
    public function run(?Request $request = null): Response
    {
        // 1. Resolve or create the Request
        $request = $request ?? Request::createFromGlobals();

        // 2. Resolve the Kernel from the DI Container
        $kernel = $this->container->get(HttpKernelInterface::class);

        // 3. Process the request through the middleware stack and controller
        $response = $kernel->handle($request);

        // 4. Send output to the browser, unless running in CLI/Tests to avoid polluting output
        if (PHP_SAPI !== 'cli' || !defined('PHPUNIT_COMPOSER_INSTALL')) {
            $response->send();
        }

        // 5. Trigger post-response tasks (logging, emails, cleanup)
        $kernel->terminate($request, $response);

        return $response;
    }
}