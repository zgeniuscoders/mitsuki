<?php

namespace Tests\Request;

use Mitsuki\Contracts\Validation\ValidatableRequestInterface;

class SuccessRequestFake implements ValidatableRequestInterface
{
    public function validateOrFail(): void {
        // Ne fait rien, donc la validation passe
    }

    public function getErrors(): array {
        return [];
    }
}