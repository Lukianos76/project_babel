<?php

namespace App\Controller\Api\V1\Auth;

use App\Controller\Api\ApiController;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Route('/auth', name: 'api_v1_auth_')]
#[OA\Tag(
    name: 'Authentication',
    description: 'Authentication endpoints for Project Babel API v1'
)]
class RegisterController extends ApiController
{
    public function __construct(
        #[Autowire(service: 'register.limiter')]
        private RateLimiterFactory $registerLimiter
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/auth/register',
        operationId: 'registerUserV1',
        summary: 'Register a new user (v1)',
        description: 'Create a new user account. Rate limited to 3 attempts per hour. Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number and one special character.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        description: 'User email address (must be unique)',
                        example: 'john.doe@projectbabel.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        description: 'User password (minimum 8 characters, must contain at least one uppercase letter, one lowercase letter, one number and one special character)',
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
                response: Response::HTTP_CREATED,
                description: 'User registered successfully',
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
                            example: 'User registered successfully'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'id',
                                    type: 'string',
                                    format: 'uuid',
                                    description: 'Unique identifier of the created user',
                                    example: '123e4567-e89b-12d3-a456-426614174000'
                                ),
                                new OA\Property(
                                    property: 'email',
                                    type: 'string',
                                    format: 'email',
                                    description: 'Email address of the created user',
                                    example: 'john.doe@projectbabel.com'
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
                        'message' => 'User registered successfully',
                        'data' => [
                            'id' => '123e4567-e89b-12d3-a456-426614174000',
                            'email' => 'john.doe@projectbabel.com'
                        ],
                        'timestamp' => 1711731600
                    ]
                ),
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Invalid input',
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
                            example: 'Email and password are required'
                        ),
                        new OA\Property(
                            property: 'errors',
                            type: 'array',
                            items: new OA\Items(
                                type: 'string',
                                example: 'Password must be at least 8 characters long'
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
                        'message' => 'Email and password are required',
                        'errors' => ['Password must be at least 8 characters long'],
                        'timestamp' => 1711731600
                    ]
                ),
            ),
            new OA\Response(
                response: Response::HTTP_CONFLICT,
                description: 'Email already exists',
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
                            example: 'This email is already registered'
                        ),
                        new OA\Property(
                            property: 'errors',
                            type: 'array',
                            items: new OA\Items(
                                type: 'string',
                                example: 'Email address is already in use'
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
                        'message' => 'This email is already registered',
                        'errors' => ['Email address is already in use'],
                        'timestamp' => 1711731600
                    ]
                ),
            ),
            new OA\Response(
                response: Response::HTTP_TOO_MANY_REQUESTS,
                description: 'Too many registration attempts',
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
                            example: 'Too many registration attempts. Please try again later.'
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
                        'message' => 'Too many registration attempts. Please try again later.',
                        'errors' => ['Rate limit exceeded'],
                        'timestamp' => 1711731600
                    ]
                ),
            ),
        ],
    )]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        UserRepository $userRepository
    ): JsonResponse {
        // Apply rate limiting
        $limiter = $this->registerLimiter->create($request->getClientIp());
        if (false === $limiter->consume(1)->isAccepted()) {
            return $this->apiErrorResponse(
                'Too many registration attempts. Please try again later.',
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }

        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->apiErrorResponse('Email and password are required', Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si l'email existe déjà
        $existingUser = $userRepository->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->apiErrorResponse(
                'This email is already registered',
                Response::HTTP_CONFLICT
            );
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword(
            $passwordHasher->hashPassword($user, $data['password'])
        );

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->apiErrorResponse((string) $errors, Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return $this->apiErrorResponse(
                'This email is already registered',
                Response::HTTP_CONFLICT
            );
        }

        return $this->apiSuccessResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail()
        ], 'User registered successfully', Response::HTTP_CREATED);
    }
} 