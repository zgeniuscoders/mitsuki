<?php

namespace Mitsuki\Mitsuki\Listeners;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class PoweredByListener
{

    /**
     * Adds the framework signature to the response headers.
     *
     * @param ResponseEvent $event The event object containing the request and response.
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->headers->set('X-Powered-By', 'Mitsuki-Framework');
    }

}