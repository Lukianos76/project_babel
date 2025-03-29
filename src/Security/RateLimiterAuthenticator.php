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

class RateLimiterAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function supports(Request $request): ?bool
    {
        // Only handle routes that require authentication
        return $request->attributes->get('_route') !== 'api_v1_auth_login' 
            && $request->attributes->get('_route') !== 'api_v1_auth_register'
            && $request->attributes->get('_route') !== 'api_v1_auth_refresh'
            && $request->attributes->get('_route') !== 'api_v1_auth_forgot_password'
            && $request->attributes->get('_route') !== 'api_v1_auth_reset_password'
            && str_starts_with($request->getPathInfo(), '/api/v1');
    }

    public function authenticate(Request $request): Passport
    {
        // Get the JWT token from the Authorization header
        $token = str_replace('Bearer ', '', $request->headers->get('Authorization') ?? '');
        
        if (empty($token)) {
            throw new AuthenticationException('No token provided');
        }

        return new SelfValidatingPassport(
            new UserBadge($token)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?JsonResponse
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?JsonResponse
    {
        return new JsonResponse(
            ['error' => $exception->getMessage()],
            JsonResponse::HTTP_UNAUTHORIZED
        );
    }

    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
    {
        return new JsonResponse(
            ['error' => 'Authentication required'],
            JsonResponse::HTTP_UNAUTHORIZED
        );
    }
} 