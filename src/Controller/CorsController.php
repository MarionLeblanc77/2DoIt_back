<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CorsController extends AbstractController
{
    #[Route('/{any}', name: 'cors_options', requirements: ['any' => '.*'], methods: ['OPTIONS'])]
    public function preflight(Request $request): JsonResponse
    {
        $allowedOrigin = 'https://2doitfront-production.up.railway.app/';
        $allowedMethods = 'GET, POST, PUT, DELETE, OPTIONS';
        $allowedHeaders = 'Content-Type, Authorization';

        return new JsonResponse([], 200, [
            'Access-Control-Allow-Origin' => $allowedOrigin,
            'Access-Control-Allow-Methods' => $allowedMethods,
            'Access-Control-Allow-Headers' => $allowedHeaders,
            'Access-Control-Max-Age' => '3600',
            'Access-Control-Allow-Credentials' => 'true',
        ]);
    }
}
