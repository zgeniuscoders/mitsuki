<?php

use Mitsuki\Mitsuki\Http\Request;

test('test resolve controller without adding this to providers', function () {
    $request = Request::create('/users/42', 'GET');
    $response = $this->app->run($request);

    expect($response->getContent())->toBe("<p>show</p>");
});