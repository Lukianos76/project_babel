# Error Handling

## Purpose
_Describe the error handling strategies and response formats used in the Project Babel API._

## Scope
_This document covers API error handling, status codes, and error response formats._

## Dependencies
- [api-overview.md](api-overview.md)
- [authentication.md](authentication.md)
- [endpoints.md](endpoints.md)
- [error-handling.md](../development/error-handling.md)

## See Also
- [api-overview.md](api-overview.md) - General API documentation
- [error-handling.md](../development/error-handling.md) - Development error handling guidelines
- [authentication.md](authentication.md) - Authentication error handling
- [endpoints.md](endpoints.md) - Endpoint-specific error responses
- [rate-limiting.md](rate-limiting.md) - Rate limit error handling
- [API Overview](api-overview.md) - General API documentation

## Overview

This document describes how errors are handled and reported at the API layer of Project Babel. It focuses on the client-facing aspects of error handling, including HTTP status codes, error response formats, and client-side error handling strategies. This document complements the development error handling guidelines in [error-handling.md](../development/error-handling.md), which covers internal logging, exception handling, and developer-specific concerns.

## Note on Scope
This document specifically covers error handling at the API layer, including HTTP status codes, error response formats, and client-side error handling. For backend development error handling practices, including logging, monitoring, and internal error management, please refer to [error-handling.md](../development/error-handling.md).

## Error Response Format

### 1. Standard Format
```json
{
  "error": {
    "code": "ERROR_CODE",
    "message": "Human readable error message",
    "details": {
      "field": "field_name",
      "message": "Specific error message"
    }
  }
}
```

### 2. Example Responses
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

## Error Codes

### 1. Authentication Errors
- `AUTH_INVALID_CREDENTIALS`: Invalid username or password
- `AUTH_TOKEN_EXPIRED`: Access token has expired
- `AUTH_INVALID_TOKEN`: Invalid or malformed token
- `AUTH_MISSING_TOKEN`: No authentication token provided

### 2. Authorization Errors
- `AUTHZ_INSUFFICIENT_PERMISSIONS`: User lacks required permissions
- `AUTHZ_INVALID_SCOPE`: Invalid OAuth scope
- `AUTHZ_RESOURCE_ACCESS_DENIED`: Access to resource denied

### 3. Validation Errors
- `VALIDATION_ERROR`: General validation error
- `VALIDATION_REQUIRED_FIELD`: Required field missing
- `VALIDATION_INVALID_FORMAT`: Invalid data format
- `VALIDATION_INVALID_VALUE`: Invalid value provided

### 4. Resource Errors
- `RESOURCE_NOT_FOUND`: Requested resource not found
- `RESOURCE_CONFLICT`: Resource conflict
- `RESOURCE_LOCKED`: Resource is locked
- `RESOURCE_DELETED`: Resource has been deleted

### 5. Rate Limiting Errors
- `RATE_LIMIT_EXCEEDED`: Rate limit exceeded
- `RATE_LIMIT_RESET`: Rate limit reset time

### 6. System Errors
- `INTERNAL_SERVER_ERROR`: Internal server error
- `SERVICE_UNAVAILABLE`: Service temporarily unavailable
- `MAINTENANCE_MODE`: System in maintenance mode

## HTTP Status Codes

### 1. Client Errors (4xx)
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 409: Conflict
- 422: Unprocessable Entity
- 429: Too Many Requests

### 2. Server Errors (5xx)
- 500: Internal Server Error
- 503: Service Unavailable
- 504: Gateway Timeout

## Error Handling Implementation

### 1. Global Error Handler
```php
class GlobalErrorHandler
{
    public function handleError(Throwable $error): Response
    {
        $statusCode = $this->getStatusCode($error);
        $errorCode = $this->getErrorCode($error);
        
        return new Response(
            json_encode([
                'error' => [
                    'code' => $errorCode,
                    'message' => $error->getMessage(),
                    'details' => $this->getErrorDetails($error)
                ]
            ]),
            $statusCode,
            ['Content-Type' => 'application/json']
        );
    }
}
```

### 2. Validation Error Handler
```php
class ValidationErrorHandler
{
    public function handleValidationError(ValidationException $error): Response
    {
        return new Response(
            json_encode([
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid input data',
                    'details' => $error->getErrors()
                ]
            ]),
            422,
            ['Content-Type' => 'application/json']
        );
    }
}
```

## Error Logging

### 1. Log Format
```json
{
  "timestamp": "2024-03-28T10:00:00Z",
  "level": "ERROR",
  "code": "ERROR_CODE",
  "message": "Error message",
  "context": {
    "user_id": "user123",
    "request_id": "req456",
    "ip": "192.168.1.1",
    "path": "/api/v1/translations",
    "method": "POST"
  },
  "trace": "Stack trace..."
}
```

### 2. Log Levels
- ERROR: Critical errors requiring immediate attention
- WARNING: Non-critical issues that should be monitored
- INFO: General information about system operation
- DEBUG: Detailed information for debugging

## Error Recovery

### 1. Retry Strategy
```php
class RetryStrategy
{
    public function retry(Callable $operation, int $maxAttempts = 3): mixed
    {
        $attempts = 0;
        $lastError = null;
        
        while ($attempts < $maxAttempts) {
            try {
                return $operation();
            } catch (Throwable $error) {
                $lastError = $error;
                $attempts++;
                
                if ($this->shouldRetry($error)) {
                    sleep(pow(2, $attempts)); // Exponential backoff
                    continue;
                }
                
                break;
            }
        }
        
        throw $lastError;
    }
}
```

### 2. Circuit Breaker
```php
class CircuitBreaker
{
    private int $failureThreshold = 5;
    private int $resetTimeout = 60;
    private int $failures = 0;
    private ?int $lastFailureTime = null;
    
    public function execute(Callable $operation): mixed
    {
        if ($this->isOpen()) {
            throw new CircuitBreakerOpenException();
        }
        
        try {
            $result = $operation();
            $this->reset();
            return $result;
        } catch (Throwable $error) {
            $this->recordFailure();
            throw $error;
        }
    }
}
```

## Client Error Handling

### 1. Error Interceptor
```typescript
class ApiErrorInterceptor {
    intercept(error: any): Observable<never> {
        if (error.status === 401) {
            return this.handleUnauthorized(error);
        }
        
        if (error.status === 429) {
            return this.handleRateLimit(error);
        }
        
        return throwError(() => this.formatError(error));
    }
}
```

### 2. Error Formatting
```typescript
interface ApiError {
    code: string;
    message: string;
    details?: Record<string, any>;
}

function formatError(error: any): ApiError {
    return {
        code: error.error?.code || 'UNKNOWN_ERROR',
        message: error.error?.message || 'An unknown error occurred',
        details: error.error?.details
    };
}
```

## Best Practices

### 1. Error Handling Guidelines
- Use consistent error codes
- Provide meaningful error messages
- Include relevant error details
- Log errors appropriately
- Handle errors at appropriate levels

### 2. Security Considerations
- Don't expose sensitive information
- Sanitize error messages
- Log security-related errors
- Monitor error patterns
- Implement rate limiting 