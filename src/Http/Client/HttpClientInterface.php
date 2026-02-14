<?php

namespace Mitsuki\Mitsuki\Http\Client;

use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Interface HttpClientInterface
 *
 * Defines the contract for external HTTP requests within the Mitsuki framework,
 * ensuring total decoupling from underlying libraries.
 */
interface HttpClientInterface
{
    public function get(string $url, array $options = []): ResponseInterface;

    public function post(string $url, array $data = [], array $options = []): ResponseInterface;

    public function put(string $url, array $data = [], array $options = []): ResponseInterface;

    public function patch(string $url, array $data = [], array $options = []): ResponseInterface;

    public function delete(string $url, array $options = []): ResponseInterface;
}