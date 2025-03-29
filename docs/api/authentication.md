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

### 3. Refresh Token Storage
Each refresh token is stored with:
- Associated user
- Expiration date
- IP address of the client
- User-Agent string of the client

These are used to identify misuse and allow manual revocation.

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

### 3. Register
```yaml
POST /api/v1/auth/register:
  description: Register a new user account
  parameters:
    - name: email
      type: string
      required: true
      format: email
      description: User email address (must be unique)
    - name: password
      type: string
      required: true
      format: password
      description: User password (minimum 8 characters, must contain at least one uppercase letter, one lowercase letter, one number and one special character)
  responses:
    201:
      description: User registered successfully
      content:
        application/json:
          schema:
            type: object
            properties:
              success:
                type: boolean
                example: true
              message:
                type: string
                example: "User registered successfully"
              data:
                type: object
                properties:
                  id:
                    type: string
                    format: uuid
                    description: Unique identifier of the created user
                  email:
                    type: string
                    format: email
                    description: Email address of the created user
              timestamp:
                type: integer
                description: Unix timestamp of the response
    400:
      description: Invalid input
      content:
        application/json:
          schema:
            type: object
            properties:
              success:
                type: boolean
                example: false
              message:
                type: string
                example: "Email and password are required"
              errors:
                type: array
                items:
                  type: string
              timestamp:
                type: integer
    409:
      description: Email already exists
      content:
        application/json:
          schema:
            type: object
            properties:
              success:
                type: boolean
                example: false
              message:
                type: string
                example: "This email is already registered"
              errors:
                type: array
                items:
                  type: string
              timestamp:
                type: integer
    429:
      description: Too many registration attempts
      content:
        application/json:
          schema:
            type: object
            properties:
              success:
                type: boolean
                example: false
              message:
                type: string
                example: "Too many registration attempts. Please try again later."
              errors:
                type: array
                items:
                  type: string
              timestamp:
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