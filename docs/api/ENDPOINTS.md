# API Endpoints

## Overview

This document provides detailed information about all available API endpoints in Project Babel. Each endpoint is documented with its HTTP method, path, parameters, and example responses.

## Base URL

```
https://api.projectbabel.org/v1
```

## Authentication

All endpoints require authentication. See [Authentication](AUTHENTICATION.md) for details.

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

All endpoints are subject to rate limiting. See [Rate Limiting](RATE_LIMITING.md) for details.

## Error Handling

For information about error responses, see [Error Handling](ERROR_HANDLING.md).

## Support

For API endpoint issues:
- Check the [API Documentation](README.md)
- Review the [Authentication](AUTHENTICATION.md) documentation
- Contact support: api-support@projectbabel.org 