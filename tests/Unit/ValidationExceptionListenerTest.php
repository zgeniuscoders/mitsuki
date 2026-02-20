<?php

use Mitsuki\Http\Requests\Request;
use Mitsuki\Http\Responses\JsonResponse;

test('it captures validation failed exception from validatable request and returns 422 JSON response', function () {

    $request = Request::create('/posts', 'POST', []);

    $response = $this->app->run($request);

    expect($response->getStatusCode())->toBe(422)
        ->and($response)->toBeInstanceOf(JsonResponse::class);

});
