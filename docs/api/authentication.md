# Authentication

## Purpose
_Describe the authentication mechanisms and security protocols used in the Project Babel API._

## Scope
_This document covers API authentication methods, token management, and security protocols._

## Dependencies
- [security.md](../development/security.md)
- [security-architecture.md](../architecture/security-architecture.md)
- [api-overview.md](api-overview.md)

## See Also
- [API Overview](api-overview.md) - General API documentation
- [Endpoints](endpoints.md) - Authentication endpoints
- [Error Handling](error-handling.md) - Authentication error handling
- [Rate Limiting](rate-limiting.md) - Rate limiting for authenticated requests

## Overview

This document describes the authentication mechanisms used to secure the API.

## Authentication Methods

### 1. JWT Authentication
```mermaid
sequenceDiagram
    participant C as Client
    participant A as Auth Service
    participant R as Resource Server
    
    C->>A: Login Request
    A->>A: Validate Credentials
    A->>C: JWT Token
    C->>R: Request with JWT
    R->>R: Validate JWT
    R->>C: Protected Resource
```

### 2. OAuth2 Flow
```mermaid
sequenceDiagram
    participant C as Client
    participant A as Auth Server
    participant R as Resource Server
    
    C->>A: Authorization Request
    A->>C: Authorization Code
    C->>A: Token Request
    A->>C: Access Token
    C->>R: Request with Token
    R->>C: Protected Resource
```

## Token Management

### 1. JWT Structure
```json
{
  "header": {
    "alg": "HS256",
    "typ": "JWT"
  },
  "payload": {
    "sub": "user123",
    "name": "John Doe",
    "roles": ["user", "translator"],
    "iat": 1616789012,
    "exp": 1616792612
  },
  "signature": "HMACSHA256(base64UrlEncode(header) + '.' + base64UrlEncode(payload), secret)"
}
```

### 2. Token Lifecycle
- Token generation
- Token validation
- Token refresh
- Token revocation

## Authentication Headers

### 1. Authorization Header
```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### 2. API Key Header
```
X-API-Key: api_key_here
```

## Authentication Endpoints

### 1. Login
```yaml
POST /api/v1/auth/login:
  description: Authenticate user and get access token
  parameters:
    - name: email
      type: string
      required: true
      format: email
    - name: password
      type: string
      required: true
      format: password
  responses:
    200:
      description: Success
      content:
        application/json:
          schema:
            type: object
            properties:
              token:
                type: string
              expires_in:
                type: integer
              refresh_token:
                type: string
```

### 2. Token Refresh
```yaml
POST /api/v1/auth/refresh:
  description: Get new access token using refresh token
  parameters:
    - name: refresh_token
      type: string
      required: true
  responses:
    200:
      description: Success
      content:
        application/json:
          schema:
            type: object
            properties:
              token:
                type: string
              expires_in:
                type: integer
```

## Security Measures

### 1. Password Security
- Bcrypt hashing
- Password complexity requirements
- Password history
- Brute force protection

### 2. Token Security
- Short-lived access tokens
- Secure refresh tokens
- Token rotation
- Token revocation

## Implementation

### 1. JWT Service
```php
class JWTService
{
    public function createToken(User $user): string
    {
        $payload = [
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'iat' => time(),
            'exp' => time() + 3600
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function validateToken(string $token): ?array
    {
        try {
            return JWT::decode($token, $this->secret, ['HS256']);
        } catch (\Exception $e) {
            return null;
        }
    }
}
```