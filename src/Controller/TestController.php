<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{
    #[Route('/', name: 'test', methods: ['GET'])]
    public function test(): JsonResponse {
        return $this->json(['success' => 'Test worked'], 200);
    }
}