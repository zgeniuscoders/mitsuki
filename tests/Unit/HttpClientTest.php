<?php

use Mitsuki\Mitsuki\Http\Client\HttpClientInterface;

test('test http client', function () {
    $app = $this->app->getContainer();
    $httpClient = $app->get(HttpClientInterface::class);
    $res = $httpClient->get('https://dummyjson.com/products');

    $this->assertEquals(200, $res->getStatusCode());
});