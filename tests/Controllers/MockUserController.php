<?php

namespace Tests\Controllers;

use Mitsuki\Mitsuki\Attributes\Controller;
use Mitsuki\Mitsuki\Controllers\BaseController;
use Mitsuki\Mitsuki\Routes\Route;

#[Controller('/users')]
class MockUserController extends BaseController
{

    #[Route('users.index', '', 'GET')]
    public function index(): \Mitsuki\Mitsuki\Http\Response
    {
        return $this->response('<p>users.index</p>');
    }

    #[Route('users.show', '/{id}', 'GET')]
    public function show(string $id): \Mitsuki\Mitsuki\Http\Response
    {
        return $this->response('<p>show</p>');
    }

}