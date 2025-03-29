<?php

namespace App\Controller\Auth;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/auth')]
#[OA\Tag(
    name: 'Authentication',
    description: 'Authentication endpoints for Project Babel API v1'
)]
class PasswordResetController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private PasswordResetTokenRepository $tokenRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    #[Route('/forgot-password', name: 'api_v1_auth_forgot_password', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/auth/forgot-password',
        operationId: 'requestPasswordResetV1',
        summary: 'Request password reset (v1)',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        description: 'Email address associated with the account',
                        example: 'john.doe@example.com'
                    ),
                ],
                required: ['email'],
            ),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Password reset email sent (if user exists)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'If the email exists, a password reset link has been sent'
                        ),
                    ],
                ),
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Invalid request',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Email is required'
                        ),
                    ],
                ),
            ),
            new OA\Response(
                response: Response::HTTP_TOO_MANY_REQUESTS,
                description: 'Too many password reset requests',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Too many password reset attempts. Please try again later.'
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function forgotPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return $this->json(['error' => 'Email is required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);
        
        // Always return success even if user not found to prevent email enumeration
        if (!$user) {
            return $this->json(['message' => 'If the email exists, a password reset link has been sent']);
        }

        // Create new token
        $token = new PasswordResetToken();
        $token->setUser($user);
        $token->setExpiresAt(new \DateTimeImmutable('+1 hour'));
        $token->setUsed(false);

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        // For development, log the token
        file_put_contents(
            __DIR__ . '/../../../var/log/password_reset_tokens.log',
            sprintf(
                "[%s] Reset token for user %s: %s\n",
                date('Y-m-d H:i:s'),
                $user->getEmail(),
                $token->getToken()
            ),
            FILE_APPEND
        );

        return $this->json(['message' => 'If the email exists, a password reset link has been sent']);
    }

    #[Route('/reset-password', name: 'api_v1_auth_reset_password', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/auth/reset-password',
        operationId: 'resetPasswordV1',
        summary: 'Reset password using token (v1)',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'token',
                        type: 'string',
                        description: 'Password reset token received via email',
                        example: 'xK9mP2nR5vL8qW3jH4tY7cB1fD6gA0'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        description: 'New password (minimum 8 characters, must contain at least one uppercase letter, one lowercase letter, one number and one special character)',
                        example: 'SecureP@ssw0rd2024'
                    ),
                ],
                required: ['token', 'password'],
            ),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Password successfully reset',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Password has been reset successfully'
                        ),
                    ],
                ),
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Invalid request or token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Invalid or expired token'
                        ),
                    ],
                ),
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: 'Token not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Token not found'
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function resetPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;
        $password = $data['password'] ?? null;

        if (!$token || !$password) {
            return $this->json(['error' => 'Token and password are required'], Response::HTTP_BAD_REQUEST);
        }

        $resetToken = $this->tokenRepository->findValidToken($token);

        if (!$resetToken || !$resetToken->isValid()) {
            return $this->json(['error' => 'Invalid or expired token'], Response::HTTP_BAD_REQUEST);
        }

        $user = $resetToken->getUser();
        
        // Hash and set new password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Mark token as used
        $resetToken->setUsed(true);

        $this->entityManager->flush();

        return $this->json(['message' => 'Password has been reset successfully']);
    }
} 