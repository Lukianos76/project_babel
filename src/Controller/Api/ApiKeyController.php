<?php

namespace App\Controller\Api;

use App\Entity\ApiKey;
use App\Repository\ApiKeyRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/api-keys', name: 'api_api_keys_')]
#[OA\Tag(name: 'API Keys')]
class ApiKeyController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ApiKeyRepository $apiKeyRepository
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Post(
        path: '/api/v1/api-keys',
        summary: 'Create a new API key (Admin only)',
        security: [
            ['bearerAuth' => []],
            ['apiKeyAuth' => []]
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: 'API key created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'string'),
                        new OA\Property(property: 'token', type: 'string'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_FORBIDDEN,
                description: 'Access denied. Admin role required.'
            ),
        ]
    )]
    public function create(): JsonResponse
    {
        $apiKey = new ApiKey($this->getUser());
        $this->entityManager->persist($apiKey);
        $this->entityManager->flush();

        return $this->json([
            'id' => $apiKey->getId(),
            'token' => $apiKey->getToken(),
            'createdAt' => $apiKey->getCreatedAt(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'revoke', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Post(
        path: '/api/v1/api-keys/{id}',
        summary: 'Revoke an API key (Admin only)',
        security: [
            ['bearerAuth' => []],
            ['apiKeyAuth' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_NO_CONTENT,
                description: 'API key revoked successfully'
            ),
            new OA\Response(
                response: Response::HTTP_FORBIDDEN,
                description: 'Access denied. Admin role required.'
            ),
        ]
    )]
    public function revoke(ApiKey $apiKey): JsonResponse
    {
        $apiKey->revoke();
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Get(
        path: '/api/v1/api-keys',
        summary: 'List all API keys (Admin only)',
        security: [
            ['bearerAuth' => []],
            ['apiKeyAuth' => []]
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'List of API keys',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'string'),
                            new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'revoked', type: 'boolean'),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: Response::HTTP_FORBIDDEN,
                description: 'Access denied. Admin role required.'
            ),
        ]
    )]
    public function list(): JsonResponse
    {
        $apiKeys = $this->apiKeyRepository->findAll();
        
        return $this->json(array_map(fn(ApiKey $key) => [
            'id' => $key->getId(),
            'createdAt' => $key->getCreatedAt(),
            'revoked' => $key->isRevoked(),
        ], $apiKeys));
    }
} 