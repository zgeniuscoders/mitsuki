<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Mitsuki\Mitsuki\Routes\Router;
use Mitsuki\Mitsuki\Routes\Route;

class MockController
{
    #[Route('test', 'test', 'GET')]
    public function index(): Response
    {
        return new Response("<h1>Succès</h1>", 200);
    }

    #[Route('user.show', 'user/{id}', 'GET')]
    public function user(int $id): Response
    {
        return new Response("User ID: " . $id);
    }
}

test('le container instancie correctement le router', function () {
    expect($this->router)->toBeInstanceOf(Router::class);
});

test('the router load routes from good controller', function () {
    $this->router->load([MockController::class]);

    $request = Request::create('/test', 'GET');

    $response = $this->router->run($request);

    expect($response->getContent())->toBe("<h1>Succès</h1>");
    expect($response->getStatusCode())->toBe(200);
});

test('the router passes dynamic parameters from URI to controller arguments', function () {
    $this->router->load([MockController::class]);

    $request = Request::create('/user/42', 'GET');
    $response = $this->router->run($request);

    expect($response->getContent())->toBe("User ID: 42");
});