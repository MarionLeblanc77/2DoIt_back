<?php

namespace App\Security\Handler;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CustomAuthenticationSuccessHandler extends AuthenticationSuccessHandler
{
    private NormalizerInterface $normalizer;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        EventDispatcherInterface $dispatcher,
        iterable $cookieProviders,
        bool $removeTokenFromBodyWhenCookiesUsed,
        NormalizerInterface $normalizer,
        ParameterBagInterface $parameterBag,

    ) 
    {
    $this->normalizer = $normalizer;
        $this->parameterBag = $parameterBag;
        parent::__construct($jwtManager, $dispatcher, $cookieProviders, $removeTokenFromBodyWhenCookiesUsed);   
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {    
        $parentResponse = parent::onAuthenticationSuccess($request, $token);
        $existingData = json_decode($parentResponse->getContent(), true);
        
        $user = $token->getUser();
        $userData = $this->normalizer->normalize($user, 'json', ['groups' => ['user_read']]);
        $existingData['user'] = $userData;

        $newResponse = new JsonResponse($existingData);
        $headersToAdd = $parentResponse->headers->all();
        $newResponse->headers->add($headersToAdd);
        return $newResponse;  
    }
}