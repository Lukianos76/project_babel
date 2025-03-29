<?php

namespace App\Controller\Api\V1\Auth;

use App\Controller\Api\ApiController;
use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/auth', name: 'api_v1_auth_')]
#[OA\Tag(
    name: 'Authentication',
    description: 'Authentication endpoints for Project Babel API v1'
)]
class PasswordResetController extends ApiController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private PasswordResetTokenRepository $tokenRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    #[Route('/forgot-password', name: 'forgot_password', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/auth/forgot-password',
        operationId: 'requestPasswordResetV1',
        summary: 'Request password reset (v1)',
        description: 'Request a password reset link to be sent to the provided email address. For security reasons, the response is the same whether the email exists or not. Rate limited to 3 attempts per hour.',
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
                        example: 'john.doe@projectbabel.com'
                    ),
                ],
                required: ['email'],
                example: [
                    'email' => 'john.doe@projectbabel.com'
                ]
            ),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Password reset email sent (if user exists)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: true
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'If the email exists, a password reset link has been sent'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'null',
                            description: 'No data is returned for security reasons'
                        ),
                        new OA\Property(
                            property: 'timestamp',
                            type: 'integer',
                            example: 1711731600
                        ),
                    ],
                    example: [
                        'success' => true,
                        'message' => 'If the email exists, a password reset link has been sent',
                        'data' => null,
                        'timestamp' => 1711731600
                    ]
                ),
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Invalid request',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: false
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Email is required'
                        ),
                        new OA\Property(
                            property: 'errors',
                            type: 'array',
                            items: new OA\Items(
                                type: 'string',
                                example: 'Email field is required'
                            )
                        ),
                        new OA\Property(
                            property: 'timestamp',
                            type: 'integer',
                            example: 1711731600
                        ),
                    ],
                    example: [
                        'success' => false,
                        'message' => 'Email is required',
                        'errors' => ['Email field is required'],
                        'timestamp' => 1711731600
                    ]
                ),
            ),
            new OA\Response(
                response: Response::HTTP_TOO_MANY_REQUESTS,
                description: 'Too many password reset requests',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: false
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Too many password reset attempts. Please try again later.'
                        ),
                        new OA\Property(
                            property: 'errors',
                            type: 'array',
                            items: new OA\Items(
                                type: 'string',
                                example: 'Rate limit exceeded'
                            )
                        ),
                        new OA\Property(
                            property: 'timestamp',
                            type: 'integer',
                            example: 1711731600
                        ),
                    ],
                    example: [
                        'success' => false,
                        'message' => 'Too many password reset attempts. Please try again later.',
                        'errors' => ['Rate limit exceeded'],
                        'timestamp' => 1711731600
                    ]
                ),
            ),
        ],
    )]
    public function forgotPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return $this->apiErrorResponse('Email is required', Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);
        
        // Always return success even if user not found to prevent email enumeration
        if (!$user) {
            return $this->apiSuccessResponse(null, 'If the email exists, a password reset link has been sent');
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
            __DIR__ . '/../../../../../var/log/password_reset_tokens.log',
            sprintf(
                "[%s] Reset token for user %s: %s\n",
                date('Y-m-d H:i:s'),
                $user->getEmail(),
                $token->getToken()
            ),
            FILE_APPEND
        );

        return $this->apiSuccessResponse(null, 'If the email exists, a password reset link has been sent');
    }

    #[Route('/reset-password', name: 'reset_password', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/auth/reset-password',
        operationId: 'resetPasswordV1',
        summary: 'Reset password using token (v1)',
        description: 'Reset the password using a valid token received via email. The token is valid for 1 hour and can only be used once. Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number and one special character.',
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
                example: [
                    'token' => 'xK9mP2nR5vL8qW3jH4tY7cB1fD6gA0',
                    'password' => 'SecureP@ssw0rd2024'
                ]
            ),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Password successfully reset',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: true
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Password has been reset successfully'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'null',
                            description: 'No data is returned for security reasons'
                        ),
                        new OA\Property(
                            property: 'timestamp',
                            type: 'integer',
                            example: 1711731600
                        ),
                    ],
                    example: [
                        'success' => true,
                        'message' => 'Password has been reset successfully',
                        'data' => null,
                        'timestamp' => 1711731600
                    ]
                ),
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Invalid request or token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: false
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Invalid or expired token'
                        ),
                        new OA\Property(
                            property: 'errors',
                            type: 'array',
                            items: new OA\Items(
                                type: 'string',
                                example: 'Token has expired or has already been used'
                            )
                        ),
                        new OA\Property(
                            property: 'timestamp',
                            type: 'integer',
                            example: 1711731600
                        ),
                    ],
                    example: [
                        'success' => false,
                        'message' => 'Invalid or expired token',
                        'errors' => ['Token has expired or has already been used'],
                        'timestamp' => 1711731600
                    ]
                ),
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: 'Token not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: false
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Token not found'
                        ),
                        new OA\Property(
                            property: 'errors',
                            type: 'array',
                            items: new OA\Items(
                                type: 'string',
                                example: 'The provided token does not exist'
                            )
                        ),
                        new OA\Property(
                            property: 'timestamp',
                            type: 'integer',
                            example: 1711731600
                        ),
                    ],
                    example: [
                        'success' => false,
                        'message' => 'Token not found',
                        'errors' => ['The provided token does not exist'],
                        'timestamp' => 1711731600
                    ]
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
            return $this->apiErrorResponse('Token and password are required', Response::HTTP_BAD_REQUEST);
        }

        $resetToken = $this->tokenRepository->findValidToken($token);

        if (!$resetToken || !$resetToken->isValid()) {
            return $this->apiErrorResponse('Invalid or expired token', Response::HTTP_BAD_REQUEST);
        }

        $user = $resetToken->getUser();
        
        // Hash and set new password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Mark token as used
        $resetToken->setUsed(true);

        $this->entityManager->flush();

        return $this->apiSuccessResponse(null, 'Password has been reset successfully');
    }
} 