<?php

namespace App\Controller\Api\V1\Auth;

use App\Controller\Api\ApiController;
use App\Service\RefreshTokenService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\User\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

#[Route('/auth', name: 'api_v1_auth_')]
#[OA\Tag(
    name: 'Authentication',
    description: 'Authentication endpoints for Project Babel API v1'
)]
class LoginController extends ApiController
{
    public function __construct(
        private RefreshTokenService $refreshTokenService,
        #[Autowire(service: 'login.limiter')]
        private RateLimiterFactory $loginLimiter,
        private JWTTokenManagerInterface $jwtManager
    ) {
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/auth/login',
        operationId: 'loginUserV1',
        summary: 'Login user and get JWT token (v1)',
        description: 'Authenticate a user and return JWT access and refresh tokens. Rate limited to 5 attempts per minute.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        description: 'User email address',
                        example: 'john.doe@projectbabel.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        description: 'User password (minimum 8 characters)',
                        example: 'SecureP@ssw0rd2024'
                    ),
                ],
                required: ['email', 'password'],
                example: [
                    'email' => 'john.doe@projectbabel.com',
                    'password' => 'SecureP@ssw0rd2024'
                ]
            ),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', description: 'JWT token'),
                        new OA\Property(property: 'refresh_token', type: 'string', description: 'Refresh token for getting new access tokens'),
                        new OA\Property(property: 'expires_in', type: 'integer', description: 'Token expiration time in seconds'),
                        new OA\Property(property: 'token_type', type: 'string', description: 'Token type (Bearer)'),
                    ],
                ),
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: 'Invalid credentials',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string'),
                    ],
                ),
            ),
            new OA\Response(
                response: Response::HTTP_TOO_MANY_REQUESTS,
                description: 'Too many login attempts',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string'),
                    ],
                ),
            ),
        ],
        security: []
    )]
    public function login(): JsonResponse
    {
        // This method will be intercepted by the security system
        // The actual authentication is handled by the security system
        return $this->json(['message' => 'Login successful']);
    }

    #[Route('/refresh', name: 'refresh', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/auth/refresh',
        operationId: 'refreshTokenV1',
        summary: 'Refresh access token using refresh token (v1)',
        description: 'Get a new access token using a valid refresh token. The old refresh token will be invalidated.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'refresh_token',
                        type: 'string',
                        description: 'Valid refresh token received from login',
                        example: 'def50200...'
                    ),
                ],
                required: ['refresh_token'],
                example: [
                    'refresh_token' => 'def50200...'
                ]
            ),
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Tokens refreshed successfully',
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
                            example: 'Tokens refreshed successfully'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'access_token',
                                    type: 'string',
                                    description: 'New JWT access token',
                                    example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...'
                                ),
                                new OA\Property(
                                    property: 'refresh_token',
                                    type: 'string',
                                    description: 'New refresh token',
                                    example: 'def50200...'
                                ),
                                new OA\Property(
                                    property: 'expires_in',
                                    type: 'integer',
                                    description: 'Token expiration time in seconds',
                                    example: 3600
                                ),
                                new OA\Property(
                                    property: 'token_type',
                                    type: 'string',
                                    description: 'Token type (Bearer)',
                                    example: 'Bearer'
                                ),
                            ]
                        ),
                        new OA\Property(
                            property: 'timestamp',
                            type: 'integer',
                            example: 1711731600
                        ),
                    ],
                    example: [
                        'success' => true,
                        'message' => 'Tokens refreshed successfully',
                        'data' => [
                            'access_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...',
                            'refresh_token' => 'def50200...',
                            'expires_in' => 3600,
                            'token_type' => 'Bearer'
                        ],
                        'timestamp' => 1711731600
                    ]
                ),
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Invalid refresh token',
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
                            example: 'Invalid refresh token'
                        ),
                        new OA\Property(
                            property: 'errors',
                            type: 'array',
                            items: new OA\Items(
                                type: 'string',
                                example: 'Token is invalid or has expired'
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
                        'message' => 'Invalid refresh token',
                        'errors' => ['Token is invalid or has expired'],
                        'timestamp' => 1711731600
                    ]
                ),
            ),
        ],
    )]
    public function refresh(Request $request): JsonResponse
    {
    $data = json_decode($request->getContent(), true);
        
        if (!isset($data['refresh_token'])) {
            return $this->json(['error' => 'Refresh token is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $tokens = $this->refreshTokenService->refreshTokens($data['refresh_token'], $request);
            return $this->json($tokens);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
} 