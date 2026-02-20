<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use Mitsuki\Controller\Resolvers\ControllerResolver;
use Mitsuki\Hermite\Router;
use Mitsuki\Mitsuki\MitsukiApp;

pest()->extend(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * @throws Exception
 */
function createApp(): MitsukiApp
{
    $projectRoot = realpath(__DIR__ . '/..');
    return new MitsukiApp($projectRoot, [
        'cache.dir' => __DIR__ . '/temp_cache',
        ControllerResolver::class => function () {
            return new ControllerResolver(  __DIR__ . '/');
        },
        'listeners' => [
            \Mitsuki\Mitsuki\Listeners\PoweredByListener::class
        ],
    ]);
}

uses()->beforeEach(function () {
    $this->app = createApp();
    $this->router = $this->app->getContainer()->get(Router::class);
})->in(__DIR__);