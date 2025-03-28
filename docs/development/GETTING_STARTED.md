# Getting Started with Project Babel

## Prerequisites

Before you begin developing with Project Babel, ensure you have the following software installed:

- PHP 8.2 or higher
- Composer 2.0 or higher
- Docker and Docker Compose
- Git
- Node.js 18 or higher (for frontend development)

## Initial Setup

### 1. Clone the Repository

```bash
git clone https://github.com/your-org/project-babel.git
cd project-babel
```

### 2. Environment Setup

```bash
# Copy the example environment file
cp .env.example .env

# Generate application secret
php bin/console secret:generate
```

### 3. Docker Setup

```bash
# Build and start Docker containers
docker-compose up -d

# Check container status
docker-compose ps
```

### 4. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install frontend dependencies (if applicable)
npm install
```

### 5. Database Setup

```bash
# Create database
php bin/console doctrine:database:create

# Run migrations
php bin/console doctrine:migrations:migrate

# Load fixtures (optional)
php bin/console doctrine:fixtures:load
```

### 6. Start Development Server

```bash
# Start Symfony development server
symfony server:start

# Or use PHP's built-in server
php -S localhost:8000 -t public/
```

## Development Workflow

### Code Style

Project Babel follows PSR-12 coding standards. Before committing changes:

```bash
# Run PHP CS Fixer
vendor/bin/php-cs-fixer fix

# Run PHPStan for static analysis
vendor/bin/phpstan analyse
```

### Testing

```bash
# Run PHPUnit tests
vendor/bin/phpunit

# Run with coverage report
vendor/bin/phpunit --coverage-html coverage/
```

### Database Management

```bash
# Create a new migration
php bin/console doctrine:migrations:generate

# Run migrations
php bin/console doctrine:migrations:migrate

# Rollback last migration
php bin/console doctrine:migrations:migrate prev
```

### Cache Management

```bash
# Clear cache
php bin/console cache:clear

# Warm up cache
php bin/console cache:warmup
```

## Development Tools

### IDE Setup

Recommended IDE settings for Project Babel:

1. **PHPStorm**
   - Enable Symfony plugin
   - Configure PHP 8.2 interpreter
   - Enable PHPStan integration

2. **VS Code**
   - Install PHP Intelephense
   - Install Symfony for VS Code
   - Configure PHP Debug

### Browser Tools

- Install browser extensions for API testing (e.g., Postman)
- Enable browser developer tools
- Configure CORS settings if needed

### Database Tools

- Install a database management tool (e.g., DBeaver, MySQL Workbench)
- Configure connection to local database

## Common Tasks

### Adding New Features

1. Create a new branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Implement changes following the [coding standards](CODING_STANDARDS.md)

3. Write tests:
   ```bash
   vendor/bin/phpunit tests/Feature/YourFeatureTest.php
   ```

4. Create a pull request

### Debugging

1. Enable Xdebug in your PHP configuration
2. Configure your IDE for debugging
3. Set breakpoints in your code
4. Start debugging session

### Performance Optimization

1. Enable OPcache in PHP
2. Configure Redis for caching
3. Use Symfony's profiler for performance analysis
4. Monitor database queries

## Troubleshooting

### Docker Issues

```bash
# Restart containers
docker-compose restart

# Rebuild containers
docker-compose up -d --build

# Check logs
docker-compose logs -f
```

### Permission Issues

```bash
# Fix permissions for cache and log directories
chmod -R 777 var/cache var/log

# Fix ownership
chown -R www-data:www-data var/cache var/log
```

### Cache Issues

```bash
# Clear all caches
php bin/console cache:clear
php bin/console cache:warmup

# Clear Redis cache
redis-cli FLUSHALL
```

## Getting Help

### Documentation

- Review the [project documentation](../README.md)
- Check the [API documentation](../api/README.md)
- Consult the [architecture documentation](../architecture/README.md)

### Existing Issues

- Check [GitHub Issues](https://github.com/your-org/project-babel/issues)
- Search for similar problems
- Create a new issue if needed

### Team Contact

- Join the development team chat
- Contact the project maintainers
- Schedule a code review session

### Community Support

- Join the project's Discord server
- Follow the project's Twitter account
- Subscribe to the project's newsletter

## Next Steps

1. Review the [development guidelines](GUIDELINES.md)
2. Understand the [code structure](CODE_STRUCTURE.md)
3. Learn about [design patterns](DESIGN_PATTERNS.md)
4. Start contributing to the project 