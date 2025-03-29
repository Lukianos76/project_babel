# Error Handling

## Purpose
_Describe the internal error handling practices and development guidelines for Project Babel._

## Scope
_This document covers internal error handling, logging, and developer-specific concerns._

## Dependencies
- [code-structure.md](code-structure.md)
- [logging.md](logging.md)
- [error-handling.md](../api/error-handling.md)

## See Also
- [code-structure.md](code-structure.md) - Code organization
- [logging.md](logging.md) - Logging guidelines
- [error-handling.md](../api/error-handling.md) - API error handling

## Overview

This document describes the internal error handling practices and development guidelines for Project Babel. It focuses on backend concerns such as exception handling, logging strategies, monitoring, and developer-specific error management. This document complements the API error handling documentation in [error-handling.md](../api/error-handling.md), which covers client-facing error formats and HTTP status codes.

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