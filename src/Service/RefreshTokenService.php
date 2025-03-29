<?php

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class RefreshTokenService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RefreshTokenRepository $refreshTokenRepository,
        private JWTTokenManagerInterface $jwtManager
    ) {
    }

    public function createRefreshToken(User $user, Request $request): RefreshToken
    {
        $token = bin2hex(random_bytes(32));
        $refreshToken = new RefreshToken($user, $token);
        $refreshToken->setIpAddress($request->getClientIp());
        $refreshToken->setUserAgent($request->headers->get('User-Agent'));

        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();

        return $refreshToken;
    }

    public function refreshTokens(string $refreshTokenString, Request $request): array
    {
        $refreshToken = $this->refreshTokenRepository->findValidToken($refreshTokenString);
        
        if (!$refreshToken) {
            throw new \InvalidArgumentException('Invalid refresh token');
        }

        // Revoke the old refresh token
        $refreshToken->setRevoked(true);
        
        // Create new refresh token
        $newRefreshToken = $this->createRefreshToken($refreshToken->getUser(), $request);
        
        // Generate new access token
        $accessToken = $this->jwtManager->create($refreshToken->getUser());

        $this->entityManager->flush();

        return [
            'access_token' => $accessToken,
            'refresh_token' => $newRefreshToken->getToken(),
            'expires_in' => 3600, // 1 hour
            'token_type' => 'Bearer'
        ];
    }

    public function revokeAllUserTokens(User $user): void
    {
        $this->refreshTokenRepository->revokeAllUserTokens($user);
        $this->entityManager->flush();
    }
} 