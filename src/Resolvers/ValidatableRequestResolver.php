<?php

namespace Mitsuki\Mitsuki\Resolvers;

use Mitsuki\Contracts\Validation\ValidatableRequestInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ValidatableRequestResolver implements ValueResolverInterface
{

    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        if (!$type || !class_exists($type) || !is_subclass_of($type, ValidatableRequestInterface::class)) {
            return [];
        }

        $requestInstance = $this->container->get($type);
        $requestInstance->validateOrFail();

        return [$requestInstance];
    }
}