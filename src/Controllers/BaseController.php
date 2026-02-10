<?php

namespace Mitsuki\Mitsuki\Controllers;

use Symfony\Component\HttpFoundation\Response;

/**
 * Base controller providing a convenient response helper.
 *
 * This controller can be extended by other controllers to easily create
 * Symfony HttpFoundation responses with a body, status code, and headers.
 *
 * @author Zgeniuscoders
 * @package Mitsuki\Mitsuki\Controllers
 */
class BaseController
{
    /**
     * Creates and returns a Symfony Response instance.
     *
     * By default, the Content-Type header is set to 'text/html',
     * which can be overridden by passing a different 'Content-Type' in $headers.
     *
     * @param mixed  $body    The response content (string, JSON, HTML, etc.).
     * @param int    $status  The HTTP status code (default: 200).
     * @param array  $headers Additional HTTP headers as key-value pairs.
     *
     * @return Response A Symfony\Component\HttpFoundation\Response instance.
     */
    public function response($body, int $status = 200, array $headers = []): Response
    {
        return new Response($body, $status, headers: array_merge(
            ['Content-Type' => 'text/html'],
            $headers
        ));
    }
}
