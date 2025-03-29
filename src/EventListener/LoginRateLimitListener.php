<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class LoginRateLimitListener
{
    public function __construct(
        #[Autowire(service: 'login.limiter')]
        private RateLimiterFactory $loginLimiter
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Only handle login requests
        if ($request->getPathInfo() !== '/api/v1/auth/login' || $request->getMethod() !== 'POST') {
            return;
        }

        // Apply rate limiting
        $limiter = $this->loginLimiter->create($request->getClientIp());
        if (false === $limiter->consume(1)->isAccepted()) {
            $response = new JsonResponse(
                ['error' => 'Too many login attempts. Please try again later.'],
                Response::HTTP_TOO_MANY_REQUESTS
            );
            $event->setResponse($response);
        }
    }
} 