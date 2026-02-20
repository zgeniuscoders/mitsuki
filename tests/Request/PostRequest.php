<?php

namespace Tests\Request;

use Mitsuki\Contracts\Validation\ValidatableRequestInterface;
use Tests\Exceptions\FakeValidationException;

class PostRequest implements ValidatableRequestInterface
{
    public function validateOrFail(): void
    {
        throw new FakeValidationException(['title' => ['Invalid title']]);
    }

    public function getErrors(): array
    {
        return ['title' => ['Invalid title']];
    }
}