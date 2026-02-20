<?php

namespace Tests\Request;

use Mitsuki\Contracts\Validation\ValidatableRequestInterface;
use Tests\Exceptions\FakeValidationException;

class FailureRequestFake implements ValidatableRequestInterface
{
    public function validateOrFail(): void {
        throw new FakeValidationException(['email' => ['Invalid email']]);
    }

    public function getErrors(): array {
        return ['email' => ['Invalid email']];
    }
}