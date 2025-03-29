<?php

namespace App\Security;

use App\Entity\ApiKey;
use App\Repository\ApiKeyRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class JwtOrApiKeyAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    private const API_KEY_HEADERS = ['X-API-Key', 'X-Api-Key'];

    public function __construct(
        private readonly ApiKeyRepository $apiKeyRepository,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly TokenExtractorInterface $tokenExtractor,
        private readonly LoggerInterface $logger
    ) {
        $this->logger->info('JwtOrApiKeyAuthenticator initialized');
    }

    public function supports(Request $request): ?bool
    {
        $this->logger->debug('JwtOrApiKeyAuthenticator::supports', [
            'route' => $request->attributes->get('_route'),
            'path' => $request->getPathInfo(),
            'method' => $request->getMethod(),
            'has_api_key' => $this->hasApiKeyHeader($request),
            'has_authorization' => $request->headers->has('Authorization'),
            'headers' => array_keys($request->headers->all()),
        ]);

        // Check if this is a public endpoint
        $publicEndpoints = [
            'api_v1_auth_login',
            'api_v1_auth_register',
            'api_v1_auth_refresh',
            'api_v1_auth_forgot_password',
            'api_v1_auth_reset_password'
        ];

        if (in_array($request->attributes->get('_route'), $publicEndpoints)) {
            $this->logger->debug('JwtOrApiKeyAuthenticator: Public endpoint detected, skipping authentication', [
                'route' => $request->attributes->get('_route')
            ]);
            return false;
        }

        $isApiRequest = str_starts_with($request->getPathInfo(), '/api/v1');
        $this->logger->debug('JwtOrApiKeyAuthenticator: Request support check', [
            'is_api_request' => $isApiRequest,
            'path' => $request->getPathInfo(),
        ]);

        return $isApiRequest;
    }

    public function authenticate(Request $request): Passport
    {
        $this->logger->debug('JwtOrApiKeyAuthenticator::authenticate called', [
            'route' => $request->attributes->get('_route'),
            'path' => $request->getPathInfo(),
            'method' => $request->getMethod(),
        ]);

        // Vérifier d'abord l'API Key
        $apiKey = $this->getApiKeyFromRequest($request);
        if ($apiKey) {
            $this->logger->debug('JwtOrApiKeyAuthenticator: API key found, trying to authenticate with it', [
                'api_key_length' => strlen($apiKey),
                'api_key_prefix' => substr($apiKey, 0, 8) . '...',
            ]);

            $apiKeyEntity = $this->apiKeyRepository->findOneBy(['token' => $apiKey]);
            
            if (!$apiKeyEntity) {
                $this->logger->error('JwtOrApiKeyAuthenticator: Invalid API key', [
                    'api_key_prefix' => substr($apiKey, 0, 8) . '...',
                ]);
                throw new CustomUserMessageAuthenticationException('Invalid API key');
            }

            if ($apiKeyEntity->isRevoked()) {
                $this->logger->error('JwtOrApiKeyAuthenticator: API key is revoked', [
                    'api_key_id' => $apiKeyEntity->getId(),
                    'user' => $apiKeyEntity->getUser()->getUserIdentifier(),
                ]);
                throw new CustomUserMessageAuthenticationException('API key is revoked');
            }

            $user = $apiKeyEntity->getUser();
            $this->logger->info('JwtOrApiKeyAuthenticator: Successfully authenticated with API key', [
                'api_key_id' => $apiKeyEntity->getId(),
                'user' => $user->getUserIdentifier(),
                'roles' => $user->getRoles(),
                'is_admin' => in_array('ROLE_ADMIN', $user->getRoles()),
                'path' => $request->getPathInfo(),
                'requires_admin' => str_starts_with($request->getPathInfo(), '/api/v1/api-keys'),
            ]);

            return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier()));
        }

        // Si pas d'API Key, vérifier le JWT
        $this->logger->debug('JwtOrApiKeyAuthenticator: No API key found, trying JWT');
        try {
            $token = $this->tokenExtractor->extract($request);
            if (!$token) {
                $this->logger->error('JwtOrApiKeyAuthenticator: No JWT token found in request');
                throw new CustomUserMessageAuthenticationException('No JWT token found');
            }

            $payload = $this->jwtManager->getPayload($token);
            $this->logger->debug('JwtOrApiKeyAuthenticator: JWT token found', [
                'email' => $payload['email'] ?? 'no_email',
                'roles' => $payload['roles'] ?? [],
                'exp' => $payload['exp'] ?? null,
                'iat' => $payload['iat'] ?? null,
                'is_admin' => in_array('ROLE_ADMIN', $payload['roles'] ?? []),
                'path' => $request->getPathInfo(),
                'requires_admin' => str_starts_with($request->getPathInfo(), '/api/v1/api-keys'),
            ]);

            $this->logger->info('JwtOrApiKeyAuthenticator: Successfully authenticated with JWT', [
                'user' => $payload['email'] ?? 'unknown',
                'roles' => $payload['roles'] ?? [],
                'is_admin' => in_array('ROLE_ADMIN', $payload['roles'] ?? []),
                'path' => $request->getPathInfo(),
                'requires_admin' => str_starts_with($request->getPathInfo(), '/api/v1/api-keys'),
            ]);

            return new SelfValidatingPassport(new UserBadge($payload['email']));
        } catch (\Exception $e) {
            $this->logger->error('JwtOrApiKeyAuthenticator: JWT authentication failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new CustomUserMessageAuthenticationException('Invalid JWT token');
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $this->logger->debug('JwtOrApiKeyAuthenticator::onAuthenticationSuccess', [
            'user' => $token->getUserIdentifier(),
            'roles' => $token->getRoleNames(),
            'is_admin' => in_array('ROLE_ADMIN', $token->getRoleNames()),
            'firewall' => $firewallName,
            'route' => $request->attributes->get('_route'),
            'path' => $request->getPathInfo(),
            'requires_admin' => str_starts_with($request->getPathInfo(), '/api/v1/api-keys'),
        ]);
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->logger->error('JwtOrApiKeyAuthenticator::onAuthenticationFailure', [
            'error' => $exception->getMessage(),
            'route' => $request->attributes->get('_route'),
            'path' => $request->getPathInfo(),
            'method' => $request->getMethod(),
            'headers' => array_keys($request->headers->all()),
        ]);

        return new JsonResponse([
            'error' => $exception->getMessage(),
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $this->logger->error('JwtOrApiKeyAuthenticator::start', [
            'error' => $authException?->getMessage(),
            'route' => $request->attributes->get('_route'),
            'path' => $request->getPathInfo(),
            'method' => $request->getMethod(),
            'headers' => array_keys($request->headers->all()),
        ]);

        return new JsonResponse([
            'error' => 'Authentication required',
        ], Response::HTTP_UNAUTHORIZED);
    }

    private function hasApiKeyHeader(Request $request): bool
    {
        foreach (self::API_KEY_HEADERS as $header) {
            if ($request->headers->has($header)) {
                return true;
            }
        }
        return false;
    }

    private function getApiKeyFromRequest(Request $request): ?string
    {
        foreach (self::API_KEY_HEADERS as $header) {
            if ($request->headers->has($header)) {
                return $request->headers->get($header);
            }
        }
        return null;
    }
} 