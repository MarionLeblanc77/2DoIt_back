<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{
    #[Route('/', name: 'test', methods: ['GET'])]
    public function test(): JsonResponse {
        return $this->json(['success' => 'Test worked'], 200);
    }
    #[Route('/users', name: 'browse', methods: "GET")]
    public function browse(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->json($users, Response::HTTP_OK, [], ["groups" => ["user_read"]]);
    }
}