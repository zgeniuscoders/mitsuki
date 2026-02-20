<?php

namespace Tests\Controllers;

use Mitsuki\Attributes\Controller;
use Mitsuki\Attributes\Route;
use Mitsuki\Controller\BaseController;
use Tests\Request\PostRequest;

#[Controller('posts')]
class MockPostController extends BaseController
{
    #[Route('posts.index', '', 'GET')]
    public function index()
    {
        return $this->json([]);
    }

    #[Route('posts.store', '', 'POST')]
    public function store(PostRequest $request)
    {

    }

}