# API Authentication

## Overview

Project Babel supports multiple authentication methods to secure API access. This document outlines the available authentication methods and how to use them.

## Authentication Methods

### 1. JWT Authentication

JWT (JSON Web Token) is the primary authentication method for the API.

#### Obtaining a Token

```http
POST /api/v1/auth/token
Content-Type: application/json

{
    "username": "your_username",
    "password": "your_password"
}
```

Response:
```json
{
    "data": {
        "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
        "expires_in": 3600,
        "token_type": "Bearer"
    }
}
```

#### Using the Token

Include the token in the Authorization header:

```http
GET /api/v1/translations
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### 2. OAuth2 Authentication

OAuth2 is supported for third-party integrations.

#### OAuth2 Flows

1. **Authorization Code Flow**
   ```http
   GET /api/v1/oauth/authorize
   ?client_id=your_client_id
   &redirect_uri=your_redirect_uri
   &response_type=code
   &scope=read write
   ```

2. **Client Credentials Flow**
   ```http
   POST /api/v1/oauth/token
   Content-Type: application/x-www-form-urlencoded

   grant_type=client_credentials
   &client_id=your_client_id
   &client_secret=your_client_secret
   ```

### 3. API Key Authentication

API keys are available for service-to-service authentication.

#### Using API Keys

Include the API key in the X-API-Key header:

```http
GET /api/v1/translations
X-API-Key: your_api_key
```

## Security Best Practices

### Token Management

1. Store tokens securely
2. Use HTTPS for all API requests
3. Implement token refresh
4. Handle token expiration

### API Key Management

1. Rotate API keys regularly
2. Use different keys for different environments
3. Implement key revocation
4. Monitor key usage

### OAuth2 Security

1. Validate redirect URIs
2. Implement PKCE for mobile apps
3. Use secure state parameters
4. Validate scopes

## Error Responses

### Authentication Errors

```json
{
    "error": {
        "code": "AUTH_ERROR",
        "message": "Invalid credentials",
        "details": {
            "reason": "invalid_username_or_password"
        }
    }
}
```

### Token Errors

```json
{
    "error": {
        "code": "TOKEN_ERROR",
        "message": "Token expired",
        "details": {
            "expired_at": "2024-03-28T12:00:00Z"
        }
    }
}
```

## Rate Limiting

Authentication endpoints have specific rate limits:

- Token requests: 5 per minute
- OAuth2 authorization: 10 per minute
- API key requests: 100 per minute

See [Rate Limiting](RATE_LIMITING.md) for more details.

## Support

For authentication issues:
- Check the [Error Handling](ERROR_HANDLING.md) documentation
- Review the [Security Architecture](../architecture/SECURITY_ARCHITECTURE.md)
- Contact support: auth-support@projectbabel.org 