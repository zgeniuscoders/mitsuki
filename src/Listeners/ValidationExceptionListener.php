<?php

namespace Mitsuki\Mitsuki\Listeners;

use Mitsuki\Contracts\Validation\Exceptions\ValidationFailedException;
use Mitsuki\Http\Responses\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ValidationExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ValidationFailedException) {

            $response = new JsonResponse([
                'status' => 'error',
                'message' => 'The given data was invalid.',
                'errors' => $exception->getErrors(),
            ], 422);

            $event->setResponse($response);
        }
    }
}