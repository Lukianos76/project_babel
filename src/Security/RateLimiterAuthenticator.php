<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Psr\Log\LoggerInterface;

class RateLimiterAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    private const API_KEY_HEADERS = ['X-API-Key', 'X-Api-Key'];

    public function __construct(
        private RateLimiterFactory $limiter,
        private LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function supports(Request $request): ?bool
    {
        $supported = $request->attributes->get('_route') !== 'api_v1_auth_login' 
            && $request->attributes->get('_route') !== 'api_v1_auth_register'
            && $request->attributes->get('_route') !== 'api_v1_auth_refresh'
            && $request->attributes->get('_route') !== 'api_v1_auth_forgot_password'
            && $request->attributes->get('_route') !== 'api_v1_auth_reset_password'
            && str_starts_with($request->getPathInfo(), '/api/v1');

        $this->logger->debug('RateLimiterAuthenticator::supports', [
            'route' => $request->attributes->get('_route'),
            'path' => $request->getPathInfo(),
            'supported' => $supported,
            'auth_header' => $request->headers->get('Authorization'),
            'has_api_key' => $this->hasApiKeyHeader($request)
        ]);

        // Si la requête a déjà un token JWT ou une API key, ne pas interférer
        if ($request->headers->has('Authorization') && str_starts_with($request->headers->get('Authorization'), 'Bearer ')) {
            $this->logger->debug('RateLimiterAuthenticator: JWT token found, skipping rate limiter');
            return false;
        }

        if ($this->hasApiKeyHeader($request)) {
            $this->logger->debug('RateLimiterAuthenticator: API key found, skipping rate limiter');
            return false;
        }

        return $supported;
    }

    public function authenticate(Request $request): Passport
    {
        $this->logger->debug('RateLimiterAuthenticator::authenticate called');

        // Get client IP for rate limiting
        $ip = $request->getClientIp();
        
        // Check rate limit
        $limiter = $this->limiter->create($ip);
        if (false === $limiter->consume(1)->isAccepted()) {
            $this->logger->warning('RateLimiterAuthenticator: Rate limit exceeded for IP ' . $ip);
            throw new TooManyRequestsHttpException();
        }

        $this->logger->debug('RateLimiterAuthenticator: Rate limit check passed for IP ' . $ip);

        // Return a dummy passport to allow the request to continue
        return new SelfValidatingPassport(
            new UserBadge('rate_limited_user')
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?JsonResponse
    {
        $this->logger->debug('RateLimiterAuthenticator::onAuthenticationSuccess called');
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?JsonResponse
    {
        $this->logger->error('RateLimiterAuthenticator::onAuthenticationFailure', [
            'error' => $exception->getMessage(),
            'class' => get_class($exception)
        ]);

        if ($exception instanceof TooManyRequestsHttpException) {
            return new JsonResponse(
                ['error' => 'Too many requests'],
                JsonResponse::HTTP_TOO_MANY_REQUESTS
            );
        }

        return new JsonResponse(
            ['error' => $exception->getMessage()],
            JsonResponse::HTTP_UNAUTHORIZED
        );
    }

    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
    {
        $this->logger->error('RateLimiterAuthenticator::start', [
            'error' => $authException?->getMessage(),
            'route' => $request->attributes->get('_route'),
            'path' => $request->getPathInfo(),
            'method' => $request->getMethod(),
            'headers' => array_keys($request->headers->all()),
        ]);

        return new JsonResponse(
            ['error' => 'Authentication required'],
            JsonResponse::HTTP_UNAUTHORIZED
        );
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
} 