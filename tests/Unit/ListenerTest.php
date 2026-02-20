<?php

use Symfony\Component\HttpFoundation\Request;

test('test add listener', function () {
    $request = Request::create('posts', 'GET');

    $response = $this->app->run($request);

    expect($response->headers->get('X-Powered-By'))->toBe('Mitsuki-Framework');
});