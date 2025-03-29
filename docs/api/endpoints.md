# Endpoints

## Purpose
_Describe the available API endpoints, their functionality, and usage guidelines._

## Scope
_This document covers all API endpoints, request/response formats, and endpoint-specific behaviors._

## Dependencies
- [api-overview.md](api-overview.md)
- [authentication.md](authentication.md)
- [error-handling.md](error-handling.md)
- [rate-limiting.md](rate-limiting.md)

## See Also
- [api-overview.md](api-overview.md) - General API documentation
- [authentication.md](authentication.md) - Authentication implementation
- [error-handling.md](error-handling.md) - Error handling
- [rate-limiting.md](rate-limiting.md) - Rate limiting
- [API Overview](api-overview.md) - General API documentation
- [caching.md](caching.md) - Response caching

## Overview

This document details all available API endpoints, their parameters, and expected responses.

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
    401:
      description: Invalid credentials
```

### 2. Refresh Token
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
    401:
      description: Invalid refresh token
```

## Translation Endpoints

### 1. List Translations
```yaml
GET /api/v1/translations:
  description: List all translations with optional filtering
  parameters:
    - name: page
      type: integer
      required: false
      default: 1
    - name: limit
      type: integer
      required: false
      default: 20
    - name: status
      type: string
      required: false
      enum: [draft, pending, approved, rejected]
    - name: language
      type: string
      required: false
      pattern: ^[a-z]{2}$
  responses:
    200:
      description: Success
      content:
        application/json:
          schema:
            type: object
            properties:
              data:
                type: array
                items:
                  $ref: '#/components/schemas/Translation'
              meta:
                type: object
                properties:
                  total:
                    type: integer
                  page:
                    type: integer
                  limit:
                    type: integer
```

### 2. Get Translation
```yaml
GET /api/v1/translations/{id}:
  description: Get a specific translation by ID
  parameters:
    - name: id
      type: string
      required: true
      in: path
  responses:
    200:
      description: Success
      content:
        application/json:
          schema:
            $ref: '#/components/schemas/Translation'
    404:
      description: Translation not found
```

### 3. Create Translation
```yaml
POST /api/v1/translations:
  description: Create a new translation
  parameters:
    - name: source_text
      type: string
      required: true
    - name: target_language
      type: string
      required: true
      pattern: ^[a-z]{2}$
    - name: context
      type: object
      required: false
      properties:
        game_id:
          type: string
        mod_id:
          type: string
  responses:
    201:
      description: Created
      content:
        application/json:
          schema:
            $ref: '#/components/schemas/Translation'
    400:
      description: Invalid input
```

## Mod Endpoints

### 1. List Mods
```yaml
GET /api/v1/mods:
  description: List all mods with optional filtering
  parameters:
    - name: page
      type: integer
      required: false
      default: 1
    - name: limit
      type: integer
      required: false
      default: 20
    - name: game_id
      type: string
      required: false
    - name: status
      type: string
      required: false
      enum: [active, inactive, archived]
  responses:
    200:
      description: Success
      content:
        application/json:
          schema:
            type: object
            properties:
              data:
                type: array
                items:
                  $ref: '#/components/schemas/Mod'
              meta:
                type: object
                properties:
                  total:
                    type: integer
                  page:
                    type: integer
                  limit:
                    type: integer
```

### 2. Get Mod
```yaml
GET /api/v1/mods/{id}:
  description: Get a specific mod by ID
  parameters:
    - name: id
      type: string
      required: true
      in: path
  responses:
    200:
      description: Success
      content:
        application/json:
          schema:
            $ref: '#/components/schemas/Mod'
    404:
      description: Mod not found
```

### 3. Create Mod
```yaml
POST /api/v1/mods:
  description: Create a new mod
  parameters:
    - name: name
      type: string
      required: true
    - name: description
      type: string
      required: true
    - name: game_id
      type: string
      required: true
    - name: version
      type: string
      required: true
  responses:
    201:
      description: Created
      content:
        application/json:
          schema:
            $ref: '#/components/schemas/Mod'
    400:
      description: Invalid input
```

## Review Endpoints

### 1. List Reviews
```yaml
GET /api/v1/reviews:
  description: List all reviews with optional filtering
  parameters:
    - name: page
      type: integer
      required: false
      default: 1
    - name: limit
      type: integer
      required: false
      default: 20
    - name: translation_id
      type: string
      required: false
    - name: rating
      type: integer
      required: false
      minimum: 1
      maximum: 5
  responses:
    200:
      description: Success
      content:
        application/json:
          schema:
            type: object
            properties:
              data:
                type: array
                items:
                  $ref: '#/components/schemas/Review'
              meta:
                type: object
                properties:
                  total:
                    type: integer
                  page:
                    type: integer
                  limit:
                    type: integer
```

### 2. Create Review
```yaml
POST /api/v1/reviews:
  description: Create a new review
  parameters:
    - name: translation_id
      type: string
      required: true
    - name: rating
      type: integer
      required: true
      minimum: 1
      maximum: 5
    - name: comment
      type: string
      required: false
  responses:
    201:
      description: Created
      content:
        application/json:
          schema:
            $ref: '#/components/schemas/Review'
    400:
      description: Invalid input
```

## Schemas

### Translation Schema
```yaml
Translation:
  type: object
  properties:
    id:
      type: string
    source_text:
      type: string
    target_text:
      type: string
    source_language:
      type: string
      pattern: ^[a-z]{2}$
    target_language:
      type: string
      pattern: ^[a-z]{2}$
    status:
      type: string
      enum: [draft, pending, approved, rejected]
    created_at:
      type: string
      format: date-time
    updated_at:
      type: string
      format: date-time
```

### Mod Schema
```yaml
Mod:
  type: object
  properties:
    id:
      type: string
    name:
      type: string
    description:
      type: string
    game_id:
      type: string
    version:
      type: string
    status:
      type: string
      enum: [active, inactive, archived]
    created_at:
      type: string
      format: date-time
    updated_at:
      type: string
      format: date-time
```

### Review Schema
```yaml
Review:
  type: object
  properties:
    id:
      type: string
    translation_id:
      type: string
    user_id:
      type: string
    rating:
      type: integer
      minimum: 1
      maximum: 5
    comment:
      type: string
    created_at:
      type: string
      format: date-time
```

## Base URL

```
https://api.projectbabel.org/v1
```

## Authentication

All endpoints require authentication. See [authentication.md](authentication.md) for details.

## Endpoints

### Translation Management

#### List Translations

```http
GET /translations
```

Query Parameters:
- `locale` (string, required): Target language code
- `game_id` (string, optional): Filter by game ID
- `mod_id` (string, optional): Filter by mod ID
- `page` (integer, optional): Page number (default: 1)
- `limit` (integer, optional): Items per page (default: 20)

Response:
```json
{
    "data": {
        "translations": [
            {
                "id": "123",
                "key": "welcome_message",
                "value": "Welcome to our game!",
                "locale": "en",
                "game_id": "456",
                "mod_id": "789",
                "created_at": "2024-03-28T12:00:00Z",
                "updated_at": "2024-03-28T12:00:00Z"
            }
        ],
        "pagination": {
            "total": 100,
            "page": 1,
            "limit": 20,
            "pages": 5
        }
    }
}
```

#### Get Translation

```http
GET /translations/{id}
```

Response:
```json
{
    "data": {
        "id": "123",
        "key": "welcome_message",
        "value": "Welcome to our game!",
        "locale": "en",
        "game_id": "456",
        "mod_id": "789",
        "created_at": "2024-03-28T12:00:00Z",
        "updated_at": "2024-03-28T12:00:00Z"
    }
}
```

#### Create Translation

```http
POST /translations
Content-Type: application/json

{
    "key": "welcome_message",
    "value": "Welcome to our game!",
    "locale": "en",
    "game_id": "456",
    "mod_id": "789"
}
```

Response:
```json
{
    "data": {
        "id": "123",
        "key": "welcome_message",
        "value": "Welcome to our game!",
        "locale": "en",
        "game_id": "456",
        "mod_id": "789",
        "created_at": "2024-03-28T12:00:00Z",
        "updated_at": "2024-03-28T12:00:00Z"
    }
}
```

#### Update Translation

```http
PUT /translations/{id}
Content-Type: application/json

{
    "value": "Welcome to our awesome game!",
    "locale": "en"
}
```

Response:
```json
{
    "data": {
        "id": "123",
        "key": "welcome_message",
        "value": "Welcome to our awesome game!",
        "locale": "en",
        "game_id": "456",
        "mod_id": "789",
        "created_at": "2024-03-28T12:00:00Z",
        "updated_at": "2024-03-28T12:30:00Z"
    }
}
```

#### Delete Translation

```http
DELETE /translations/{id}
```

Response:
```json
{
    "data": {
        "message": "Translation deleted successfully"
    }
}
```

### Game Management

#### List Games

```http
GET /games
```

Query Parameters:
- `page` (integer, optional): Page number (default: 1)
- `limit` (integer, optional): Items per page (default: 20)

Response:
```json
{
    "data": {
        "games": [
            {
                "id": "456",
                "name": "Awesome Game",
                "description": "An awesome game",
                "created_at": "2024-03-28T12:00:00Z",
                "updated_at": "2024-03-28T12:00:00Z"
            }
        ],
        "pagination": {
            "total": 50,
            "page": 1,
            "limit": 20,
            "pages": 3
        }
    }
}
```

#### Get Game

```http
GET /games/{id}
```

Response:
```json
{
    "data": {
        "id": "456",
        "name": "Awesome Game",
        "description": "An awesome game",
        "created_at": "2024-03-28T12:00:00Z",
        "updated_at": "2024-03-28T12:00:00Z"
    }
}
```

#### Create Game

```http
POST /games
Content-Type: application/json

{
    "name": "Awesome Game",
    "description": "An awesome game"
}
```

Response:
```json
{
    "data": {
        "id": "456",
        "name": "Awesome Game",
        "description": "An awesome game",
        "created_at": "2024-03-28T12:00:00Z",
        "updated_at": "2024-03-28T12:00:00Z"
    }
}
```

### Mod Management

#### List Mods

```http
GET /games/{game_id}/mods
```

Query Parameters:
- `page` (integer, optional): Page number (default: 1)
- `limit` (integer, optional): Items per page (default: 20)

Response:
```json
{
    "data": {
        "mods": [
            {
                "id": "789",
                "name": "Awesome Mod",
                "description": "An awesome mod",
                "game_id": "456",
                "created_at": "2024-03-28T12:00:00Z",
                "updated_at": "2024-03-28T12:00:00Z"
            }
        ],
        "pagination": {
            "total": 30,
            "page": 1,
            "limit": 20,
            "pages": 2
        }
    }
}
```

#### Get Mod

```http
GET /games/{game_id}/mods/{id}
```

Response:
```json
{
    "data": {
        "id": "789",
        "name": "Awesome Mod",
        "description": "An awesome mod",
        "game_id": "456",
        "created_at": "2024-03-28T12:00:00Z",
        "updated_at": "2024-03-28T12:00:00Z"
    }
}
```

#### Create Mod

```http
POST /games/{game_id}/mods
Content-Type: application/json

{
    "name": "Awesome Mod",
    "description": "An awesome mod"
}
```

Response:
```json
{
    "data": {
        "id": "789",
        "name": "Awesome Mod",
        "description": "An awesome mod",
        "game_id": "456",
        "created_at": "2024-03-28T12:00:00Z",
        "updated_at": "2024-03-28T12:00:00Z"
    }
}
```

## Rate Limiting

All endpoints are subject to rate limiting. See [rate-limiting.md](rate-limiting.md) for details.

## Error Handling

For information about error responses, see [error-handling.md](error-handling.md).

## Support

For API endpoint issues:
- Check the [API Documentation](README.md)
- Review the [Authentication](authentication.md) documentation
- Contact support: api-support@projectbabel.org 