<?php

namespace App\Security\Handler;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class CustomAuthenticationFailureHandler extends AuthenticationFailureHandler
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        // Create custom error response format to match your frontend expectations
        $errorMessage = 'Email and password do not match.';
        
        // You could customize the message based on exception type if needed
        // if ($exception instanceof BadCredentialsException) {
        //     $errorMessage = 'Email and password do not match.';
        // }
        
        return new JsonResponse([
            'errors' => $errorMessage
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }
}