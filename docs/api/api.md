# API Documentation

## Overview

Project Babel provides a RESTful API for managing translations and game mods. This document outlines the API endpoints, authentication, and usage guidelines.

## Base URL

```
https://api.projectbabel.org/v1
```

## Authentication

### API Keys
```http
Authorization: Bearer your-api-key
```

### JWT Tokens
```http
Authorization: Bearer your-jwt-token
```

## Rate Limiting

- 100 requests per minute per API key
- 1000 requests per hour per API key
- Rate limit headers included in responses

## Endpoints

### Translations

#### List Translations
```http
GET /translations
```

Response:
```json
{
    "data": [
        {
            "id": "123",
            "key": "welcome",
            "value": "Welcome to our site",
            "locale": "en",
            "game": "game-id",
            "mod": "mod-id"
        }
    ],
    "meta": {
        "total": 100,
        "page": 1,
        "per_page": 20
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
    "id": "123",
    "key": "welcome",
    "value": "Welcome to our site",
    "locale": "en",
    "game": "game-id",
    "mod": "mod-id",
    "created_at": "2024-03-20T10:00:00Z",
    "updated_at": "2024-03-20T10:00:00Z"
}
```

#### Create Translation
```http
POST /translations
```

Request:
```json
{
    "key": "welcome",
    "value": "Welcome to our site",
    "locale": "en",
    "game": "game-id",
    "mod": "mod-id"
}
```

Response:
```json
{
    "id": "123",
    "key": "welcome",
    "value": "Welcome to our site",
    "locale": "en",
    "game": "game-id",
    "mod": "mod-id",
    "created_at": "2024-03-20T10:00:00Z"
}
```

#### Update Translation
```http
PUT /translations/{id}
```

Request:
```json
{
    "value": "Welcome to our awesome site"
}
```

Response:
```json
{
    "id": "123",
    "key": "welcome",
    "value": "Welcome to our awesome site",
    "locale": "en",
    "game": "game-id",
    "mod": "mod-id",
    "updated_at": "2024-03-20T10:30:00Z"
}
```

#### Delete Translation
```http
DELETE /translations/{id}
```

Response:
```json
{
    "message": "Translation deleted successfully"
}
```

### Games

#### List Games
```http
GET /games
```

Response:
```json
{
    "data": [
        {
            "id": "game-id",
            "name": "Game Name",
            "description": "Game description",
            "created_at": "2024-03-20T10:00:00Z"
        }
    ],
    "meta": {
        "total": 10,
        "page": 1,
        "per_page": 20
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
    "id": "game-id",
    "name": "Game Name",
    "description": "Game description",
    "created_at": "2024-03-20T10:00:00Z",
    "updated_at": "2024-03-20T10:00:00Z"
}
```

#### Create Game
```http
POST /games
```

Request:
```json
{
    "name": "Game Name",
    "description": "Game description"
}
```

Response:
```json
{
    "id": "game-id",
    "name": "Game Name",
    "description": "Game description",
    "created_at": "2024-03-20T10:00:00Z"
}
```

### Mods

#### List Mods
```http
GET /mods
```

Response:
```json
{
    "data": [
        {
            "id": "mod-id",
            "name": "Mod Name",
            "description": "Mod description",
            "game": "game-id",
            "created_at": "2024-03-20T10:00:00Z"
        }
    ],
    "meta": {
        "total": 50,
        "page": 1,
        "per_page": 20
    }
}
```

#### Get Mod
```http
GET /mods/{id}
```

Response:
```json
{
    "id": "mod-id",
    "name": "Mod Name",
    "description": "Mod description",
    "game": "game-id",
    "created_at": "2024-03-20T10:00:00Z",
    "updated_at": "2024-03-20T10:00:00Z"
}
```

#### Create Mod
```http
POST /mods
```

Request:
```json
{
    "name": "Mod Name",
    "description": "Mod description",
    "game": "game-id"
}
```

Response:
```json
{
    "id": "mod-id",
    "name": "Mod Name",
    "description": "Mod description",
    "game": "game-id",
    "created_at": "2024-03-20T10:00:00Z"
}
```

## Error Handling

### Error Response Format
```json
{
    "error": {
        "code": "ERROR_CODE",
        "message": "Error message",
        "details": {
            "field": "error details"
        }
    }
}
```

### Common Error Codes
- `400`: Bad Request
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `429`: Too Many Requests
- `500`: Internal Server Error

## Pagination

### Query Parameters
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 20, max: 100)

### Response Format
```json
{
    "data": [...],
    "meta": {
        "total": 100,
        "page": 1,
        "per_page": 20,
        "total_pages": 5
    }
}
```

## Filtering

### Query Parameters
- `filter[field]`: Filter by field value
- `filter[field][operator]`: Filter operator (eq, gt, lt, etc.)

Example:
```http
GET /translations?filter[locale]=en&filter[game]=game-id
```

## Sorting

### Query Parameters
- `sort`: Field to sort by
- `order`: Sort order (asc, desc)

Example:
```http
GET /translations?sort=created_at&order=desc
```

## Search

### Query Parameters
- `q`: Search query
- `fields`: Fields to search in

Example:
```http
GET /translations?q=welcome&fields=key,value
```

## Webhooks

### Available Events
- `translation.created`
- `translation.updated`
- `translation.deleted`
- `game.created`
- `game.updated`
- `mod.created`
- `mod.updated`

### Webhook Payload
```json
{
    "event": "translation.created",
    "timestamp": "2024-03-20T10:00:00Z",
    "data": {
        "id": "123",
        "key": "welcome",
        "value": "Welcome to our site"
    }
}
```

## SDK Support

### PHP SDK
```php
use ProjectBabel\Client;

$client = new Client('your-api-key');

// Get translations
$translations = $client->translations()->list();

// Create translation
$translation = $client->translations()->create([
    'key' => 'welcome',
    'value' => 'Welcome to our site',
    'locale' => 'en'
]);
```

### JavaScript SDK
```javascript
import { Client } from '@projectbabel/client';

const client = new Client('your-api-key');

// Get translations
const translations = await client.translations.list();

// Create translation
const translation = await client.translations.create({
    key: 'welcome',
    value: 'Welcome to our site',
    locale: 'en'
});
```

## Best Practices

### Rate Limiting
- Implement exponential backoff
- Cache responses
- Monitor rate limits
- Handle 429 responses

### Error Handling
- Handle all error codes
- Implement retry logic
- Log errors
- Show user-friendly messages

### Security
- Use HTTPS
- Secure API keys
- Validate input
- Sanitize output

### Performance
- Use caching
- Implement pagination
- Optimize requests
- Monitor response times 