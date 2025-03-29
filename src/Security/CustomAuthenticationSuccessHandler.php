<?php

namespace App\Security;

use App\Entity\User;
use App\Service\RefreshTokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class CustomAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private RefreshTokenService $refreshTokenService
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $user = $token->getUser();
        
        if (!$user instanceof User) {
            throw new \RuntimeException('Expected User instance');
        }

        // Generate JWT token
        $jwt = $this->jwtManager->create($user);

        // Create refresh token
        $refreshToken = $this->refreshTokenService->createRefreshToken($user, $request);

        // Prepare response
        $response = [
            'token' => $jwt,
            'refresh_token' => $refreshToken->getToken(),
            'expires_in' => 3600, // 1 hour
            'token_type' => 'Bearer'
        ];

        return new JsonResponse($response);
    }
} 