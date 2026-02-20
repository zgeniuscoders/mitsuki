<?php

namespace Mitsuki\Tests\Fakes;

use Mitsuki\Contracts\Validation\Exceptions\ValidationException;
use Mitsuki\Contracts\Validation\Exceptions\ValidationFailedException;
use Tests\Request\FailureRequestFake;
use Tests\Request\SuccessRequestFake;

test('validateOrFail does not throw exception when data is valid', function () {
    $request = new SuccessRequestFake();

    expect(/**
     * @throws ValidationException
     */ fn() => $request->validateOrFail())->not->toThrow(\Exception::class);
});

test('validateOrFail throws ValidationFailedException when data is invalid', function () {
    $request = new FailureRequestFake();

    expect(/**
     * @throws ValidationException
     */ fn() => $request->validateOrFail())
        ->toThrow(ValidationFailedException::class);
});

test('thrown exception contains validation errors', function () {
    $request = new FailureRequestFake();

    try {
        $request->validateOrFail();
    } catch (ValidationException $e) {
        expect($e->getErrors())->toBe(['email' => ['Invalid email']]);
        return;
    }

    $this->fail("L'exception n'a pas été interceptée.");
});