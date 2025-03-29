# Deployment Guidelines

## Purpose
_Describe the deployment process, environment setup, and monitoring strategies for the application._

## Scope
_This document covers deployment procedures, environment configuration, and production monitoring._

## Dependencies
- [testing.md](testing.md)
- [performance.md](performance.md)
- [code-review.md](code-review.md)

## See also
- [testing.md](testing.md) - For deployment testing requirements
- [performance.md](performance.md) - For performance monitoring
- [code-review.md](code-review.md) - For deployment review process

## Overview

This document outlines the deployment process and guidelines for Project Babel, including environment setup, deployment steps, and monitoring.

## Environment Setup

### 1. Production Environment

```yaml
# .env.prod
APP_ENV=prod
APP_DEBUG=0
DATABASE_URL=mysql://user:pass@host:3306/dbname
REDIS_URL=redis://localhost:6379
```

### 2. Staging Environment

```yaml
# .env.staging
APP_ENV=staging
APP_DEBUG=1
DATABASE_URL=mysql://user:pass@host:3306/dbname_staging
REDIS_URL=redis://localhost:6379
```

### 3. Development Environment

```yaml
# .env.dev
APP_ENV=dev
APP_DEBUG=1
DATABASE_URL=mysql://user:pass@localhost:3306/dbname_dev
REDIS_URL=redis://localhost:6379
```

## Deployment Process

### 1. Pre-Deployment Checklist

```bash
# Run tests
php bin/phpunit

# Check code style
vendor/bin/php-cs-fixer fix --dry-run

# Check dependencies
composer audit

# Generate optimized autoloader
composer install --no-dev --optimize-autoloader
```

### 2. Database Migration

```bash
# Run migrations
php bin/console doctrine:migrations:migrate --env=prod

# Verify database
php bin/console doctrine:schema:validate --env=prod
```

### 3. Cache Management

```bash
# Clear cache
php bin/console cache:clear --env=prod

# Warm up cache
php bin/console cache:warmup --env=prod
```

## Deployment Tools

### 1. Docker Deployment

```dockerfile
# Dockerfile
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-install zip pdo pdo_mysql

# Copy application files
COPY . /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html
```

### 2. Docker Compose

```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build: .
    volumes:
      - .:/var/www/html
    depends_on:
      - db
      - redis

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: project_babel

  redis:
    image: redis:6.0
```

### 3. Deployment Script

```bash
#!/bin/bash

# deploy.sh

# Pull latest changes
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php bin/console doctrine:migrations:migrate --env=prod

# Clear cache
php bin/console cache:clear --env=prod

# Warm up cache
php bin/console cache:warmup --env=prod

# Restart services
sudo systemctl restart php-fpm
sudo systemctl restart nginx
```

## Server Configuration

### 1. Nginx Configuration

```nginx
# /etc/nginx/sites-available/project_babel
server {
    listen 80;
    server_name projectbabel.org;
    root /var/www/html/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }
}
```

### 2. PHP-FPM Configuration

```ini
# /etc/php/8.2/fpm/pool.d/www.conf
[www]
user = www-data
group = www-data
listen = /var/run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
```

### 3. Supervisor Configuration

```ini
# /etc/supervisor/conf.d/project_babel.conf
[program:project_babel]
command=php /var/www/html/bin/console messenger:consume async --time-limit=3600
user=www-data
numprocs=2
autostart=true
autorestart=true
process_name=%(program_name)s_%(process_num)02d
```

## Monitoring

### 1. Health Checks

```php
class HealthChecker
{
    public function check(): array
    {
        return [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'filesystem' => $this->checkFilesystem(),
            'api' => $this->checkApi()
        ];
    }
}
```

### 2. Logging

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
```

### 3. Metrics Collection

```php
class MetricsCollector
{
    public function collect(): array
    {
        return [
            'response_time' => $this->getResponseTime(),
            'memory_usage' => $this->getMemoryUsage(),
            'database_queries' => $this->getQueryCount(),
            'cache_hit_rate' => $this->getCacheHitRate()
        ];
    }
}
```

## Backup and Recovery

### 1. Database Backup

```bash
#!/bin/bash

# backup.sh

# Set variables
BACKUP_DIR="/var/backups/project_babel"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="project_babel"

# Create backup
mysqldump -u user -p $DB_NAME > "$BACKUP_DIR/backup_$DATE.sql"

# Compress backup
gzip "$BACKUP_DIR/backup_$DATE.sql"

# Delete old backups (keep last 7 days)
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete
```

### 2. File Backup

```bash
#!/bin/bash

# backup_files.sh

# Set variables
BACKUP_DIR="/var/backups/project_babel"
DATE=$(date +%Y%m%d_%H%M%S)
APP_DIR="/var/www/html"

# Create backup
tar -czf "$BACKUP_DIR/files_$DATE.tar.gz" -C $APP_DIR .

# Delete old backups (keep last 7 days)
find $BACKUP_DIR -name "files_*.tar.gz" -mtime +7 -delete
```

### 3. Recovery Process

```bash
#!/bin/bash

# restore.sh

# Set variables
BACKUP_DIR="/var/backups/project_babel"
BACKUP_FILE="$1"
APP_DIR="/var/www/html"

# Extract backup
tar -xzf "$BACKUP_DIR/$BACKUP_FILE" -C $APP_DIR

# Restore database
gunzip -c "$BACKUP_DIR/${BACKUP_FILE%.*}.sql.gz" | mysql -u user -p project_babel
```

## Support

For deployment questions:
- Check the [Development Guidelines](guidelines.md)
- Review the [Code Structure](code-structure.md)
- Contact the development team 