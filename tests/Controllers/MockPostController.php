<?php

namespace Tests\Controllers;

use Mitsuki\Mitsuki\Attributes\Controller;
use Mitsuki\Mitsuki\Routes\Route;
use Tests\Request\PostRequest;

#[Controller('posts')]
class MockPostController
{
    #[Route('posts.store', '', 'POST')]
    public function store(PostRequest $request)
    {

    }

}