# Rate Limiting

## Purpose
_Define rate limiting policies and implementation for the Project Babel API._

## Scope
_This document covers API rate limiting rules, quotas, and throttling mechanisms._

## Dependencies
- [api-overview.md](api-overview.md)
- [authentication.md](authentication.md)
- [error-handling.md](error-handling.md)
- [caching.md](caching.md)
- [monitoring.md](../development/monitoring.md)

## See Also
- [API Overview](api-overview.md) - General API documentation
- [Authentication](authentication.md) - Authentication
- [Error Handling](error-handling.md) - Error handling
- [Caching](caching.md) - Caching strategies
- [Monitoring Guide](../development/monitoring.md) - Rate limit monitoring
- [Performance Guide](../development/performance.md) - Performance optimization

## Overview

Rate limiting is implemented to protect the API from abuse and ensure fair usage. The system uses Symfony's RateLimiter component with a sliding window approach.

## Authentication Endpoints

### Login Rate Limiting
- Max 5 attempts per minute per IP
- Enforced via Symfony RateLimiter with sliding window
- Returns 429 Too Many Requests when limit exceeded

### Register Rate Limiting
- Max 3 attempts per hour per IP
- Enforced via Symfony RateLimiter with sliding window
- Returns 429 Too Many Requests when limit exceeded

## Rate Limiting Strategy

### 1. Rate Limiting Types
```mermaid
graph TD
    subgraph Rate Limiting
        IP[IP-based]
        User[User-based]
        Endpoint[Endpoint-based]
        Service[Service-based]
    end

    subgraph Implementation
        Fixed[Fixed Window]
        Sliding[Sliding Window]
        Token[Token Bucket]
        Leaky[Leaky Bucket]
    end

    IP --> Fixed
    User --> Sliding
    Endpoint --> Token
    Service --> Leaky
```

### 2. Rate Limit Headers
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1616789012
Retry-After: 60
```

## Rate Limits

### 1. Default Limits
```yaml
rate_limits:
  default:
    requests: 100
    period: 60  # seconds
  authenticated:
    requests: 1000
    period: 60
  admin:
    requests: 5000
    period: 60
```

### 2. Endpoint-Specific Limits
```yaml
endpoints:
  /api/v1/translations:
    get:
      limit: 100
      period: 60
    post:
      limit: 50
      period: 60
  /api/v1/auth/login:
    post:
      limit: 5
      period: 60
```

## Implementation

Rate limiting is implemented using Symfony's RateLimiter component:

```php
#[Route('/auth/login')]
public function login(Request $request): Response
{
    $limiter = $this->loginLimiter->create($request->getClientIp());
    if (false === $limiter->consume(1)->isAccepted()) {
        return $this->json(['error' => 'Too many login attempts'], Response::HTTP_TOO_MANY_REQUESTS);
    }
    // ... rest of login logic
}
```

## Response Format

When rate limit is exceeded, the API returns:

```json
{
  "success": false,
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "Too many attempts. Please try again later.",
    "details": {
      "retry_after": 60
    }
  },
  "meta": {
    "timestamp": 1712001010
  }
}
```

## Headers

Rate limit information is included in response headers:

```
X-RateLimit-Limit: 5
X-RateLimit-Remaining: 3
X-RateLimit-Reset: 1712001070
```

## Rate Limit Keys

### 1. Key Generation
```php
class RateLimitKeyGenerator
{
    public function generateKey(Request $request): string
    {
        $parts = [
            'rate_limit',
            $request->ip(),
            $request->user()?->getId() ?? 'anonymous',
            $request->path()
        ];
        
        return implode(':', $parts);
    }
}
```

### 2. Key Types
- IP-based keys
- User-based keys
- Endpoint-based keys
- Service-based keys

## Rate Limit Policies

### 1. Fixed Window
```php
class FixedWindowRateLimiter
{
    public function isAllowed(string $key, int $limit, int $period): bool
    {
        $current = $this->redis->incr($key);
        
        if ($current === 1) {
            $this->redis->expire($key, $period);
        }
        
        return $current <= $limit;
    }
}
```

### 2. Sliding Window
```php
class SlidingWindowRateLimiter
{
    public function isAllowed(string $key, int $limit, int $period): bool
    {
        $now = time();
        $window = $this->redis->zrangebyscore($key, $now - $period, $now);
        
        if (count($window) >= $limit) {
            return false;
        }
        
        $this->redis->zadd($key, $now, uniqid());
        $this->redis->expire($key, $period);
        
        return true;
    }
}
```

## Error Handling

### 1. Rate Limit Errors
```json
{
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "Rate limit exceeded",
    "details": {
      "limit": 100,
      "remaining": 0,
      "reset": "2024-03-28T11:00:00Z"
    }
  }
}
```

### 2. Retry-After Header
```
HTTP/1.1 429 Too Many Requests
Retry-After: 60
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1616789012
```

## Monitoring

### 1. Rate Limit Metrics
- Request counts
- Rate limit hits
- Rate limit bypasses
- Rate limit resets

### 2. Rate Limit Logging
```json
{
  "timestamp": "2024-03-28T10:00:00Z",
  "event": "RATE_LIMIT_HIT",
  "key": "rate_limit:192.168.1.1:user123:/api/v1/translations",
  "limit": 100,
  "current": 101,
  "ip": "192.168.1.1",
  "user_id": "user123"
}
```

## Best Practices

1. Implement exponential backoff in clients
2. Cache rate limit headers
3. Monitor rate limit hits
4. Log suspicious activity
5. Consider implementing IP whitelisting for trusted clients

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
- Review the [Error Handling](error-handling.md) documentation
