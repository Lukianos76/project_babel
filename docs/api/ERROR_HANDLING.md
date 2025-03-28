# API Error Handling

## Overview

This document outlines how Project Babel handles and reports errors in its API. All error responses follow a consistent format and include helpful information for debugging.

## Error Response Format

All error responses follow this structure:

```json
{
    "error": {
        "code": "ERROR_CODE",
        "message": "Human readable error message",
        "details": {
            // Additional error details specific to the error type
        }
    }
}
```

## HTTP Status Codes

### 2xx Success
- `200 OK`: Request successful
- `201 Created`: Resource created successfully
- `204 No Content`: Request successful, no content to return

### 4xx Client Errors
- `400 Bad Request`: Invalid request parameters
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `409 Conflict`: Resource conflict
- `422 Unprocessable Entity`: Validation error
- `429 Too Many Requests`: Rate limit exceeded

### 5xx Server Errors
- `500 Internal Server Error`: Server-side error
- `503 Service Unavailable`: Service temporarily unavailable

## Error Types

### 1. Authentication Errors

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

### 2. Validation Errors

```json
{
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Invalid input data",
        "details": {
            "field": "email",
            "message": "Invalid email format"
        }
    }
}
```

### 3. Resource Errors

```json
{
    "error": {
        "code": "RESOURCE_ERROR",
        "message": "Resource not found",
        "details": {
            "resource": "translation",
            "id": "123"
        }
    }
}
```

### 4. Rate Limit Errors

```json
{
    "error": {
        "code": "RATE_LIMIT_ERROR",
        "message": "Rate limit exceeded",
        "details": {
            "limit": 100,
            "reset": "2024-03-28T13:00:00Z"
        }
    }
}
```

## Error Handling Best Practices

### Client-Side Handling

1. Check HTTP status codes
2. Parse error response format
3. Handle specific error codes
4. Implement retry logic
5. Log errors appropriately

### Server-Side Handling

1. Use appropriate status codes
2. Provide detailed error messages
3. Include relevant context
4. Log errors for debugging
5. Sanitize sensitive information

## Common Error Scenarios

### 1. Authentication Failures

```json
{
    "error": {
        "code": "AUTH_ERROR",
        "message": "Token expired",
        "details": {
            "expired_at": "2024-03-28T12:00:00Z"
        }
    }
}
```

### 2. Validation Failures

```json
{
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Invalid input data",
        "details": {
            "errors": [
                {
                    "field": "username",
                    "message": "Username must be at least 3 characters"
                },
                {
                    "field": "email",
                    "message": "Invalid email format"
                }
            ]
        }
    }
}
```

### 3. Resource Conflicts

```json
{
    "error": {
        "code": "CONFLICT_ERROR",
        "message": "Resource already exists",
        "details": {
            "resource": "translation",
            "key": "welcome_message"
        }
    }
}
```

## Error Recovery

### 1. Retry Logic

```php
class ApiClient
{
    public function request(string $method, string $endpoint, array $data = []): array
    {
        $attempts = 0;
        $maxAttempts = 3;
        
        while ($attempts < $maxAttempts) {
            try {
                return $this->makeRequest($method, $endpoint, $data);
            } catch (RateLimitException $e) {
                $attempts++;
                if ($attempts === $maxAttempts) {
                    throw $e;
                }
                sleep($e->getResetTime());
            }
        }
    }
}
```

### 2. Fallback Handling

```php
class TranslationService
{
    public function getTranslation(string $id): ?Translation
    {
        try {
            return $this->api->getTranslation($id);
        } catch (ApiException $e) {
            return $this->cache->get("translation:{$id}");
        }
    }
}
```

## Support

For error handling issues:
- Check the [API Documentation](README.md)
- Review the [Rate Limiting](RATE_LIMITING.md) documentation
- Contact support: api-support@projectbabel.org 