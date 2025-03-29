# Security

## Purpose
_Describe the security practices and guidelines for development in Project Babel._

## Scope
_This document covers security best practices, authentication, authorization, and vulnerability management._

## Dependencies
- [security-architecture.md](../architecture/security-architecture.md)
- [authentication.md](../api/authentication.md)
- [error-handling.md](error-handling.md)

## See Also
- [security-architecture.md](../architecture/security-architecture.md) - Security architecture
- [authentication.md](../api/authentication.md) - Authentication implementation
- [error-handling.md](error-handling.md) - Error handling

## Overview

This document outlines security best practices and guidelines for Project Babel, including authentication, authorization, data protection, and vulnerability management.

## Authentication

### 1. JWT Authentication

```php
class JwtAuthenticator extends AbstractGuardAuthenticator
{
    public function supports(Request $request): bool
    {
        return $request->headers->has('Authorization');
    }

    public function getCredentials(Request $request): array
    {
        $token = str_replace('Bearer ', '', $request->headers->get('Authorization'));
        return ['token' => $token];
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?User
    {
        try {
            $payload = $this->jwtManager->decode($credentials['token']);
            return $userProvider->loadUserByIdentifier($payload['sub']);
        } catch (\Exception $e) {
            return null;
        }
    }
}
```

### 2. OAuth2 Authentication

```php
class OAuth2Authenticator extends AbstractGuardAuthenticator
{
    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === 'oauth2_login';
    }

    public function getCredentials(Request $request): array
    {
        return [
            'code' => $request->query->get('code'),
            'state' => $request->query->get('state')
        ];
    }
}
```

### 3. API Key Authentication

```php
class ApiKeyAuthenticator extends AbstractGuardAuthenticator
{
    public function supports(Request $request): bool
    {
        return $request->headers->has('X-API-Key');
    }

    public function getCredentials(Request $request): array
    {
        return ['api_key' => $request->headers->get('X-API-Key')];
    }
}
```

## Authorization

### 1. Role-Based Access Control

```php
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin/dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }
}
```

### 2. Resource-Based Authorization

```php
class TranslationVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Translation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        return match($attribute) {
            'VIEW' => $this->canView($user, $subject),
            'EDIT' => $this->canEdit($user, $subject),
            'DELETE' => $this->canDelete($user, $subject),
            default => false,
        };
    }
}
```

## Data Protection

### 1. Input Validation

```php
class TranslationValidator
{
    public function validate(array $data): array
    {
        $errors = [];
        
        if (empty($data['key'])) {
            $errors['key'] = 'Translation key is required';
        }
        
        if (empty($data['value'])) {
            $errors['value'] = 'Translation value is required';
        }
        
        if (!in_array($data['locale'], ['en', 'fr', 'es'])) {
            $errors['locale'] = 'Invalid locale';
        }
        
        return $errors;
    }
}
```

### 2. Output Sanitization

```php
class ResponseSanitizer
{
    public function sanitize(array $data): array
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
            return $value;
        }, $data);
    }
}
```

### 3. Data Encryption

```php
class EncryptionService
{
    public function encrypt(string $data): string
    {
        $key = base64_decode($_ENV['ENCRYPTION_KEY']);
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-gcm'));
        
        $encrypted = openssl_encrypt(
            $data,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        return base64_encode($iv . $tag . $encrypted);
    }
}
```

## API Security

### 1. Rate Limiting

```php
class RateLimiter
{
    public function check(string $ip, string $endpoint): bool
    {
        $key = "rate_limit:{$ip}:{$endpoint}";
        $limit = $this->getLimit($endpoint);
        
        $current = $this->redis->incr($key);
        if ($current === 1) {
            $this->redis->expire($key, 60);
        }
        
        return $current <= $limit;
    }
}
```

### 2. CORS Configuration

```yaml
# config/packages/nelmio_cors.yaml
nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin: ['%env(CORS_ALLOW_ORIGIN)%']
        allow_methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']
        allow_headers: ['Content-Type', 'Authorization']
        expose_headers: ['Link']
        max_age: 3600
```

### 3. Request Validation

```php
class RequestValidator
{
    public function validate(Request $request): array
    {
        $errors = [];
        
        // Validate content type
        if (!$this->isValidContentType($request)) {
            $errors[] = 'Invalid content type';
        }
        
        // Validate request size
        if (!$this->isValidRequestSize($request)) {
            $errors[] = 'Request too large';
        }
        
        // Validate required headers
        if (!$this->hasRequiredHeaders($request)) {
            $errors[] = 'Missing required headers';
        }
        
        return $errors;
    }
}
```

## Security Headers

### 1. Response Headers

```php
class SecurityHeadersListener
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Content-Security-Policy', "default-src 'self'");
    }
}
```

## Vulnerability Management

### 1. Dependency Scanning

```bash
# Check for known vulnerabilities
composer audit

# Update dependencies
composer update

# Check for outdated packages
composer outdated
```

### 2. Security Monitoring

```php
class SecurityMonitor
{
    public function logSecurityEvent(string $event, array $context): void
    {
        $this->logger->warning('Security event', [
            'event' => $event,
            'context' => $context,
            'timestamp' => new \DateTime(),
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
    }
}
```

### 3. Incident Response

```php
class SecurityIncidentHandler
{
    public function handleIncident(SecurityIncident $incident): void
    {
        // Log incident
        $this->logIncident($incident);
        
        // Notify security team
        $this->notifySecurityTeam($incident);
        
        // Take corrective action
        $this->takeCorrectiveAction($incident);
        
        // Update incident status
        $this->updateIncidentStatus($incident);
    }
}
```

## Best Practices

### 1. Password Security

- Use strong password hashing
- Implement password policies
- Use secure password reset
- Implement account lockout

### 2. Session Security

- Use secure session handling
- Implement session timeout
- Use secure cookies
- Implement CSRF protection

### 3. Data Security

- Encrypt sensitive data
- Use prepared statements
- Implement input validation
- Sanitize output

## Support

For security questions:
- Check the [Development Guidelines](guidelines.md)
- Review the [Code Structure](code-structure.md)
- Contact the security team