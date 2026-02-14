<?php

namespace Mitsuki\Mitsuki\Http\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClient;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MitsukiHttpClient implements HttpClientInterface
{
    public function __construct(
        private SymfonyHttpClient $symfonyClient
    ) {}

    public function get(string $url, array $options = []): ResponseInterface
    {
        return $this->request('GET', $url, $options);
    }

    public function post(string $url, array $data = [], array $options = []): ResponseInterface
    {
        return $this->request('POST', $url, ['json' => $data] + $options);
    }

    public function put(string $url, array $data = [], array $options = []): ResponseInterface
    {
        return $this->request('PUT', $url, ['json' => $data] + $options);
    }

    public function patch(string $url, array $data = [], array $options = []): ResponseInterface
    {
        return $this->request('PATCH', $url, ['json' => $data] + $options);
    }

    public function delete(string $url, array $options = []): ResponseInterface
    {
        return $this->request('DELETE', $url, $options);
    }

    /**
     * Internal method to centralize Symfony execution and response parsing.
     */
    private function request(string $method, string $url, array $options): ResponseInterface
    {
        return $this->symfonyClient->request($method, $url, $options);
    }
}