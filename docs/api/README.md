# API Documentation

## Overview

Project Babel provides a comprehensive REST API for managing translations, games, and mods. This documentation covers all aspects of the API, including authentication, endpoints, error handling, and rate limiting.

## Table of Contents

1. [Authentication](authentication.md)
   - JWT Authentication
   - OAuth2 Integration
   - API Keys
   - Session Management

2. [Endpoints](endpoints.md)
   - Translation Management
   - Game Management
   - Mod Management
   - User Management
   - File Management

3. [Error Handling](error-handling.md)
   - Error Codes
   - Error Responses
   - Validation Errors
   - Authentication Errors

4. [Rate Limiting](rate-limiting.md)
   - Rate Limit Policies
   - Quota Management
   - Rate Limit Headers

## Base URL

```
https://api.projectbabel.org/v1
```

## Authentication

All API requests require authentication. See the [Authentication](authentication.md) documentation for details.

## Response Format

All responses are in JSON format:

```json
{
    "data": {
        // Response data
    },
    "meta": {
        "timestamp": "2024-03-28T12:00:00Z",
        "version": "1.0.0"
    }
}
```

## Error Format

Error responses follow this format:

```json
{
    "error": {
        "code": "ERROR_CODE",
        "message": "Human readable error message",
        "details": {
            // Additional error details
        }
    }
}
```

## Versioning

The API is versioned through the URL path. Current version: v1

## Rate Limiting

API requests are subject to rate limiting. See the [Rate Limiting](rate-limiting.md) documentation for details.

## Support

For API support:
- Documentation: [Project Documentation](../README.md)
- Issues: [GitHub Issues](https://github.com/your-org/project-babel/issues)
- Email: api-support@projectbabel.org 