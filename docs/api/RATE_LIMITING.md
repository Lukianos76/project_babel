# API Rate Limiting

## Overview

Project Babel implements rate limiting to ensure fair usage of the API and protect against abuse. This document outlines the rate limiting policies, how to handle rate limits, and best practices.

## Rate Limit Headers

All API responses include rate limit headers:

```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1616889600
```

## Rate Limit Policies

### General API Limits

| Endpoint Type | Limit | Period |
|--------------|-------|---------|
| GET requests | 100 | 1 minute |
| POST requests | 50 | 1 minute |
| PUT requests | 50 | 1 minute |
| DELETE requests | 20 | 1 minute |

### Authentication Endpoints

| Endpoint | Limit | Period |
|----------|-------|---------|
| /auth/token | 5 | 1 minute |
| /oauth/authorize | 10 | 1 minute |
| /oauth/token | 10 | 1 minute |

### Translation Endpoints

| Endpoint | Limit | Period |
|----------|-------|---------|
| /translations (GET) | 200 | 1 minute |
| /translations (POST) | 100 | 1 minute |
| /translations/{id} (GET) | 100 | 1 minute |
| /translations/{id} (PUT) | 50 | 1 minute |
| /translations/{id} (DELETE) | 20 | 1 minute |

## Rate Limit Response

When rate limit is exceeded, the API returns a 429 status code:

```json
{
    "error": {
        "code": "RATE_LIMIT_ERROR",
        "message": "Rate limit exceeded",
        "details": {
            "limit": 100,
            "remaining": 0,
            "reset": "2024-03-28T13:00:00Z"
        }
    }
}
```

## Handling Rate Limits

### Client-Side Implementation

```php
class ApiClient
{
    private $rateLimitRemaining;
    private $rateLimitReset;

    public function request(string $method, string $endpoint, array $data = []): array
    {
        $response = $this->makeRequest($method, $endpoint, $data);
        
        // Update rate limit information from headers
        $this->rateLimitRemaining = $response->getHeader('X-RateLimit-Remaining');
        $this->rateLimitReset = $response->getHeader('X-RateLimit-Reset');
        
        return $response->getData();
    }

    public function handleRateLimit(): void
    {
        if ($this->rateLimitRemaining <= 0) {
            $resetTime = $this->rateLimitReset - time();
            sleep($resetTime);
        }
    }
}
```

### Retry Logic

```php
class RateLimitHandler
{
    public function handleRequest(callable $request): mixed
    {
        $attempts = 0;
        $maxAttempts = 3;
        
        while ($attempts < $maxAttempts) {
            try {
                return $request();
            } catch (RateLimitException $e) {
                $attempts++;
                if ($attempts === $maxAttempts) {
                    throw $e;
                }
                
                $resetTime = $e->getResetTime();
                sleep($resetTime);
            }
        }
    }
}
```

## Best Practices

### 1. Monitor Rate Limits

```php
class RateLimitMonitor
{
    public function checkRateLimit(): array
    {
        return [
            'remaining' => $this->getRemainingRequests(),
            'reset' => $this->getResetTime(),
            'limit' => $this->getRateLimit()
        ];
    }
}
```

### 2. Implement Exponential Backoff

```php
class ExponentialBackoff
{
    public function getDelay(int $attempt): int
    {
        return min(300, pow(2, $attempt) * 1000);
    }
}
```

### 3. Cache Responses

```php
class CachedApiClient
{
    public function request(string $method, string $endpoint, array $data = []): array
    {
        $cacheKey = $this->generateCacheKey($method, $endpoint, $data);
        
        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }
        
        $response = $this->api->request($method, $endpoint, $data);
        $this->cache->set($cacheKey, $response, 3600);
        
        return $response;
    }
}
```

## Rate Limit Exemptions

### 1. Internal Services

Internal services can request rate limit exemptions by:
1. Using a service account
2. Including a special API key
3. Making requests from whitelisted IPs

### 2. High-Priority Operations

Certain operations can be marked as high-priority:
1. Critical translations
2. Emergency updates
3. System maintenance

## Monitoring and Alerts

### 1. Rate Limit Monitoring

```php
class RateLimitMonitor
{
    public function monitorRateLimits(): void
    {
        $metrics = $this->collectMetrics();
        
        if ($metrics['remaining'] < $metrics['limit'] * 0.1) {
            $this->sendAlert('Rate limit warning', $metrics);
        }
    }
}
```

### 2. Usage Analytics

```php
class UsageAnalytics
{
    public function trackUsage(): array
    {
        return [
            'total_requests' => $this->getTotalRequests(),
            'rate_limit_hits' => $this->getRateLimitHits(),
            'average_response_time' => $this->getAverageResponseTime()
        ];
    }
}
```

## Support

For rate limiting issues:
- Check the [API Documentation](README.md)
- Review the [Error Handling](ERROR_HANDLING.md) documentation
- Contact support: api-support@projectbabel.org 