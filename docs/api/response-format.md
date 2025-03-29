# Format de réponse de l'API

## Format de succès

```json
{
  "data": { ... },
  "meta": {
    "timestamp": 1712001010
  }
}
```

### Description

- `data` : Contient les données de la réponse. La structure varie selon l'endpoint.
- `meta` : Contient les métadonnées de la réponse.
  - `timestamp` : Timestamp Unix de la génération de la réponse.

## Format d'erreur

```json
{
  "error": {
    "code": "AUTH_INVALID",
    "message": "Invalid credentials",
    "details": null
  }
}
```

### Description

- `error` : Objet contenant les informations d'erreur.
  - `code` : Code d'erreur unique pour l'identification programmatique de l'erreur.
  - `message` : Message d'erreur lisible par l'utilisateur.
  - `details` : Détails supplémentaires sur l'erreur (optionnel).

## Utilisation

Tous les endpoints de l'API retournent cette structure de réponse. Pour garantir cette cohérence, nous utilisons un listener d'exception personnalisé Symfony.

### Implémentation avec Symfony

```yaml
# config/services.yaml
services:
    App\EventListener\ApiExceptionListener:
        tags:
            - { name: kernel.event_listener, event: kernel.exception }
```

```php
<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ApiExceptionListener extends ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response = new JsonResponse();

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
        } else {
            $response->setStatusCode(500);
        }

        $response->setData([
            'error' => [
                'code' => $this->getErrorCode($exception),
                'message' => $exception->getMessage(),
                'details' => $this->getErrorDetails($exception)
            ]
        ]);

        $event->setResponse($response);
    }

    private function getErrorCode(\Throwable $exception): string
    {
        // Mapping des exceptions vers les codes d'erreur
        $codeMap = [
            'InvalidCredentialsException' => 'AUTH_INVALID',
            'TokenExpiredException' => 'AUTH_EXPIRED',
            'AccessDeniedException' => 'AUTH_FORBIDDEN',
            // ... autres mappings
        ];

        return $codeMap[get_class($exception)] ?? 'INTERNAL_ERROR';
    }

    private function getErrorDetails(\Throwable $exception): ?array
    {
        // Retourne des détails supplémentaires si disponibles
        if (method_exists($exception, 'getDetails')) {
            return $exception->getDetails();
        }

        return null;
    }
}
```

## Codes d'état HTTP

- `200` : Succès
- `201` : Création réussie
- `400` : Requête invalide
- `401` : Non authentifié
- `403` : Non autorisé
- `404` : Ressource non trouvée
- `429` : Trop de requêtes
- `500` : Erreur serveur interne

## Common Error Codes

### Erreurs d'authentification
- `AUTH_INVALID` : Identifiants invalides
- `AUTH_EXPIRED` : Token expiré
- `AUTH_REQUIRED` : Authentification requise
- `AUTH_FORBIDDEN` : Permissions insuffisantes

### Erreurs de validation
- `VALIDATION_ERROR` : Échec de la validation des données
- `INVALID_INPUT` : Données d'entrée invalides
- `MISSING_REQUIRED` : Champ requis manquant

### Erreurs de ressources
- `NOT_FOUND` : Ressource non trouvée
- `ALREADY_EXISTS` : Ressource déjà existante
- `CONFLICT` : Conflit de ressources

### Limitation de débit
- `RATE_LIMIT_EXCEEDED` : Trop de requêtes

## Examples

### Success Response
```json
{
  "success": true,
  "data": {
    "id": "123e4567-e89b-12d3-a456-426614174000",
    "email": "user@example.com",
    "roles": ["user"]
  },
  "meta": {
    "timestamp": 1712001010
  }
}
```

### Error Response
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid email format",
    "details": {
      "field": "email",
      "constraints": ["valid_email"]
    }
  },
  "meta": {
    "timestamp": 1712001010
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Item 1"
      },
      {
        "id": 2,
        "name": "Item 2"
      }
    ]
  },
  "meta": {
    "timestamp": 1712001010,
    "pagination": {
      "page": 1,
      "limit": 10,
      "total": 100
    }
  }
}
```

## Implementation Guidelines

1. Always include the `success` field
2. Use appropriate HTTP status codes
3. Include timestamps in all responses
4. Provide meaningful error codes and messages
5. Include pagination metadata when applicable
6. Keep error messages user-friendly
7. Include detailed error information in the `details` field when helpful for debugging 