# Error Handling Guidelines

## Overview

This document outlines the error handling strategy and guidelines for Project Babel, including exception handling, logging, and error reporting.

## Exception Handling

### 1. Custom Exceptions

```php
class TranslationNotFoundException extends \Exception
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('Translation with ID "%s" not found', $id));
    }
}

class ValidationException extends \Exception
{
    private array $errors;

    public function __construct(array $errors)
    {
        parent::__construct('Validation failed');
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
```

### 2. Exception Listener

```php
class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        
        if ($exception instanceof ValidationException) {
            $response = new JsonResponse([
                'error' => 'Validation failed',
                'details' => $exception->getErrors()
            ], Response::HTTP_BAD_REQUEST);
        } elseif ($exception instanceof TranslationNotFoundException) {
            $response = new JsonResponse([
                'error' => $exception->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } else {
            $response = new JsonResponse([
                'error' => 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        $event->setResponse($response);
    }
}
```

### 3. Exception Hierarchy

```php
abstract class ProjectBabelException extends \Exception
{
    protected array $context = [];

    public function getContext(): array
    {
        return $this->context;
    }
}

class BusinessException extends ProjectBabelException
{
    public function __construct(string $message, array $context = [])
    {
        parent::__construct($message);
        $this->context = $context;
    }
}

class TechnicalException extends ProjectBabelException
{
    public function __construct(string $message, array $context = [])
    {
        parent::__construct($message);
        $this->context = $context;
    }
}
```

## Error Logging

### 1. Error Logger

```php
class ErrorLogger
{
    public function logError(\Throwable $error): void
    {
        $this->logger->error('Application error', [
            'message' => $error->getMessage(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
            'context' => $error instanceof ProjectBabelException ? $error->getContext() : []
        ]);
    }
}
```

### 2. Log Configuration

```yaml
# config/packages/monolog.yaml
monolog:
    handlers:
        error:
            type: stream
            path: "%kernel.logs_dir%/%kernel.environment%.error.log"
            level: error
            channels: ["!event"]
        critical:
            type: stream
            path: "%kernel.logs_dir%/%kernel.environment%.critical.log"
            level: critical
            channels: ["!event"]
```

### 3. Error Context

```php
class ErrorContext
{
    public function getContext(\Throwable $error): array
    {
        return [
            'timestamp' => new \DateTime(),
            'request_id' => $this->requestStack->getCurrentRequest()->getId(),
            'user' => $this->getCurrentUser(),
            'environment' => $this->kernel->getEnvironment(),
            'url' => $this->requestStack->getCurrentRequest()->getUri(),
            'method' => $this->requestStack->getCurrentRequest()->getMethod()
        ];
    }
}
```

## Error Response Format

### 1. API Error Response

```php
class ErrorResponse
{
    public function create(\Throwable $error): array
    {
        return [
            'error' => [
                'code' => $this->getErrorCode($error),
                'message' => $error->getMessage(),
                'details' => $this->getErrorDetails($error),
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s.u')
            ]
        ];
    }
}
```

### 2. HTTP Status Codes

```php
class HttpStatusCodes
{
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const VALIDATION_ERROR = 422;
    public const TOO_MANY_REQUESTS = 429;
    public const INTERNAL_SERVER_ERROR = 500;
    public const SERVICE_UNAVAILABLE = 503;
}
```

### 3. Error Response Handler

```php
class ErrorResponseHandler
{
    public function handle(\Throwable $error): Response
    {
        $statusCode = $this->getStatusCode($error);
        $response = $this->createErrorResponse($error);
        
        return new JsonResponse($response, $statusCode);
    }
}
```

## Error Recovery

### 1. Retry Mechanism

```php
class RetryHandler
{
    public function retry(callable $operation, int $maxAttempts = 3): mixed
    {
        $attempts = 0;
        $lastError = null;
        
        while ($attempts < $maxAttempts) {
            try {
                return $operation();
            } catch (\Exception $e) {
                $lastError = $e;
                $attempts++;
                
                if ($attempts < $maxAttempts) {
                    sleep(pow(2, $attempts)); // Exponential backoff
                }
            }
        }
        
        throw $lastError;
    }
}
```

### 2. Fallback Mechanism

```php
class FallbackHandler
{
    public function withFallback(callable $operation, callable $fallback): mixed
    {
        try {
            return $operation();
        } catch (\Exception $e) {
            return $fallback($e);
        }
    }
}
```

### 3. Circuit Breaker

```php
class CircuitBreaker
{
    private int $failureCount = 0;
    private bool $isOpen = false;
    
    public function execute(callable $operation): mixed
    {
        if ($this->isOpen) {
            throw new CircuitBreakerOpenException();
        }
        
        try {
            $result = $operation();
            $this->reset();
            return $result;
        } catch (\Exception $e) {
            $this->recordFailure();
            throw $e;
        }
    }
}
```

## Error Monitoring

### 1. Error Tracking

```php
class ErrorTracker
{
    public function track(\Throwable $error): void
    {
        $this->metrics->increment('error_count', [
            'type' => get_class($error),
            'environment' => $this->kernel->getEnvironment()
        ]);
        
        $this->logger->error('Error tracked', [
            'error' => $error->getMessage(),
            'context' => $this->getErrorContext($error)
        ]);
    }
}
```

### 2. Error Reporting

```php
class ErrorReporter
{
    public function report(\Throwable $error): void
    {
        if ($this->shouldReport($error)) {
            $this->sendToErrorReportingService([
                'error' => $error->getMessage(),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
                'trace' => $error->getTraceAsString(),
                'context' => $this->getErrorContext($error)
            ]);
        }
    }
}
```

### 3. Error Analytics

```php
class ErrorAnalytics
{
    public function analyze(): array
    {
        return [
            'error_rate' => $this->getErrorRate(),
            'error_types' => $this->getErrorTypes(),
            'error_trends' => $this->getErrorTrends(),
            'affected_users' => $this->getAffectedUsers()
        ];
    }
}
```

## Support

For error handling questions:
- Check the [Development Guidelines](GUIDELINES.md)
- Review the [Code Structure](CODE_STRUCTURE.md)
- Contact the development team 