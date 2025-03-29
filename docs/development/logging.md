# Logging Guidelines

## Overview

This document outlines the logging strategy and guidelines for Project Babel, including log levels, formats, and best practices.

## Log Configuration

### 1. Monolog Configuration

```yaml
# config/packages/monolog.yaml
monolog:
    handlers:
        main:
            type: stream
            path: "%kernel.logs_dir%/%kernel.environment%.log"
            level: debug
            channels: ["!event"]
        console:
            type: console
            process_psr_3_messages: false
            channels: ["!event", "!doctrine", "!console"]
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

### 2. Log Levels

```php
class LogLevels
{
    public const EMERGENCY = 'emergency';  // System is unusable
    public const ALERT = 'alert';         // Action must be taken immediately
    public const CRITICAL = 'critical';   // Critical conditions
    public const ERROR = 'error';         // Error conditions
    public const WARNING = 'warning';     // Warning conditions
    public const NOTICE = 'notice';       // Normal but significant condition
    public const INFO = 'info';           // Informational messages
    public const DEBUG = 'debug';         // Debug-level messages
}
```

### 3. Log Channels

```yaml
# config/packages/monolog.yaml
monolog:
    channels:
        security:
            handlers: [security]
        doctrine:
            handlers: [doctrine]
        console:
            handlers: [console]
```

## Log Format

### 1. Structured Logging

```php
class StructuredLogger
{
    public function log(string $level, string $message, array $context = []): void
    {
        $this->logger->log($level, $message, [
            'timestamp' => new \DateTime(),
            'request_id' => $this->requestStack->getCurrentRequest()->getId(),
            'user' => $this->getCurrentUser(),
            'environment' => $this->kernel->getEnvironment(),
            'context' => $context
        ]);
    }
}
```

### 2. JSON Format

```php
class JsonLogger
{
    public function log(string $level, string $message, array $context = []): void
    {
        $logEntry = [
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s.u'),
            'level' => $level,
            'message' => $message,
            'context' => $context
        ];
        
        $this->logger->log($level, json_encode($logEntry));
    }
}
```

### 3. Context Format

```php
class ContextLogger
{
    public function log(string $level, string $message, array $context = []): void
    {
        $this->logger->log($level, $message, [
            'request' => [
                'method' => $this->requestStack->getCurrentRequest()->getMethod(),
                'url' => $this->requestStack->getCurrentRequest()->getUri(),
                'ip' => $this->requestStack->getCurrentRequest()->getClientIp()
            ],
            'user' => [
                'id' => $this->getCurrentUserId(),
                'roles' => $this->getCurrentUserRoles()
            ],
            'performance' => [
                'duration' => $this->getRequestDuration(),
                'memory' => memory_get_usage(true)
            ],
            'context' => $context
        ]);
    }
}
```

## Log Categories

### 1. Security Logging

```php
class SecurityLogger
{
    public function logSecurityEvent(string $event, array $context = []): void
    {
        $this->logger->info('Security event', [
            'event' => $event,
            'context' => $context,
            'timestamp' => new \DateTime(),
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
    }
}
```

### 2. Performance Logging

```php
class PerformanceLogger
{
    public function logPerformance(array $metrics): void
    {
        $this->logger->info('Performance metrics', [
            'metrics' => $metrics,
            'timestamp' => new \DateTime(),
            'request_id' => $this->requestStack->getCurrentRequest()->getId()
        ]);
    }
}
```

### 3. Business Logging

```php
class BusinessLogger
{
    public function logBusinessEvent(string $event, array $context = []): void
    {
        $this->logger->info('Business event', [
            'event' => $event,
            'context' => $context,
            'timestamp' => new \DateTime(),
            'user' => $this->getCurrentUser()
        ]);
    }
}
```

## Log Management

### 1. Log Rotation

```yaml
# config/packages/monolog.yaml
monolog:
    handlers:
        main:
            type: rotating_file
            path: "%kernel.logs_dir%/%kernel.environment%.log"
            level: debug
            channels: ["!event"]
            filename_format: "{filename}-{date}"
            date_format: "Y-m-d"
            max_files: 30
```

### 2. Log Cleanup

```php
class LogCleaner
{
    public function cleanup(): void
    {
        $logDir = $this->kernel->getLogDir();
        $files = glob($logDir . '/*.log');
        
        foreach ($files as $file) {
            if (filemtime($file) < strtotime('-30 days')) {
                unlink($file);
            }
        }
    }
}
```

### 3. Log Analysis

```php
class LogAnalyzer
{
    public function analyze(): array
    {
        return [
            'error_rate' => $this->getErrorRate(),
            'slow_requests' => $this->getSlowRequests(),
            'user_activity' => $this->getUserActivity(),
            'system_health' => $this->getSystemHealth()
        ];
    }
}
```

## Log Integration

### 1. ELK Stack Integration

```yaml
# config/packages/elasticsearch.yaml
elasticsearch:
    hosts: ['%env(ELASTICSEARCH_URL)%']
    indices:
        logs: project_babel_logs_%kernel.environment%
```

### 2. Logstash Configuration

```ruby
# logstash.conf
input {
  file {
    path => "/var/log/project_babel/*.log"
    type => "project_babel"
  }
}

filter {
  json {
    source => "message"
  }
}

output {
  elasticsearch {
    hosts => ["elasticsearch:9200"]
    index => "project_babel_logs_%{environment}"
  }
}
```

### 3. Kibana Dashboard

```json
{
  "dashboard": {
    "id": null,
    "title": "Project Babel Logs",
    "panels": [
      {
        "title": "Error Rate",
        "type": "graph",
        "datasource": "Elasticsearch",
        "targets": [
          {
            "query": "level: ERROR",
            "timeField": "timestamp"
          }
        ]
      }
    ]
  }
}
```

## Best Practices

### 1. Log Content

- Use appropriate log levels
- Include relevant context
- Avoid sensitive data
- Use structured format

### 2. Log Performance

- Use async logging
- Implement log buffering
- Monitor log size
- Regular cleanup

### 3. Log Security

- Secure log files
- Implement access control
- Monitor log access
- Regular audit

## Support

For logging questions:
- Check the [Development Guidelines](guidelines.md)
- Review the [Code Structure](code-structure.md)
- Contact the development team 