# API Response Format

## Purpose
_Define the standard response format for all Project Babel API endpoints._

## Overview

This document describes the standard response format used across all API endpoints. The format is designed to be consistent, predictable, and informative.

## Success Response Format

### Structure
```json
{
  "success": true,
  "data": {
    // Response data specific to the endpoint
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

### Fields
- `success`: Boolean indicating the request was successful
- `data`: Object containing the response data specific to the endpoint
- `meta`: Object containing metadata about the response
  - `timestamp`: Unix timestamp of the response
  - `pagination`: Object containing pagination information (when applicable)
    - `page`: Current page number
    - `limit`: Number of items per page
    - `total`: Total number of items

## Error Response Format

### Structure
```json
{
  "success": false,
  "error": {
    "code": "AUTH_INVALID",
    "message": "Invalid credentials",
    "details": null
  },
  "meta": {
    "timestamp": 1712001010
  }
}
```

### Fields
- `success`: Boolean indicating the request failed
- `error`: Object containing error information
  - `code`: String identifier for the error type
  - `message`: Human-readable error message
  - `details`: Additional error details (optional)
- `meta`: Object containing metadata about the response
  - `timestamp`: Unix timestamp of the response

## Common Error Codes

### Authentication Errors
- `AUTH_INVALID`: Invalid credentials
- `AUTH_EXPIRED`: Token has expired
- `AUTH_REQUIRED`: Authentication required
- `AUTH_FORBIDDEN`: Insufficient permissions

### Validation Errors
- `VALIDATION_ERROR`: Input validation failed
- `INVALID_INPUT`: Invalid input data
- `MISSING_REQUIRED`: Required field missing

### Resource Errors
- `NOT_FOUND`: Resource not found
- `ALREADY_EXISTS`: Resource already exists
- `CONFLICT`: Resource conflict

### Rate Limiting
- `RATE_LIMIT_EXCEEDED`: Too many requests

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