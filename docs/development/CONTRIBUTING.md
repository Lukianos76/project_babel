# Contributing to Project Babel

## Overview

This document provides guidelines for contributing to Project Babel, including setup instructions, coding standards, and the contribution process.

## Getting Started

### Prerequisites

1. PHP 8.2 or higher
2. Composer 2.0 or higher
3. Git
4. Docker and Docker Compose
5. Node.js 18 or higher (for frontend development)

### Initial Setup

```bash
# Clone the repository
git clone https://github.com/your-org/project-babel.git
cd project-babel

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Start Docker containers
docker-compose up -d

# Run migrations
php bin/console doctrine:migrations:migrate
```

## Development Workflow

### Branch Strategy

1. Create a new branch for your feature:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make your changes following our [coding standards](GUIDELINES.md)

3. Write tests for new features

4. Update documentation

5. Create a pull request

### Commit Messages

Follow conventional commits:

```
type(scope): description

[optional body]

[optional footer]
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes
- `refactor`: Code refactoring
- `test`: Test changes
- `chore`: Maintenance tasks

Example:
```
feat(auth): implement JWT authentication

- Add JWT token generation
- Implement token validation
- Add refresh token support

Closes #123
```

## Code Standards

### PHP Standards

1. Follow PSR-12 coding standards
2. Use strict typing
3. Use type hints
4. Use constructor property promotion
5. Use attributes where applicable

Example:
```php
declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

class UserService
{
    public function __construct(
        private readonly UserRepository $repository
    ) {
    }

    public function getUser(string $id): ?User
    {
        return $this->repository->find($id);
    }
}
```

### Documentation Standards

1. PHPDoc blocks for classes and methods
2. Clear and concise comments
3. Update README when needed
4. Document API changes

Example:
```php
/**
 * User service for managing user-related operations.
 */
class UserService
{
    /**
     * Creates a new user.
     *
     * @param array<string, mixed> $data User data
     *
     * @throws ValidationException If data is invalid
     */
    public function createUser(array $data): User
    {
        // Implementation
    }
}
```

## Testing

### Unit Tests

```php
class UserServiceTest extends TestCase
{
    private UserRepository $repository;
    private UserService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserRepository::class);
        $this->service = new UserService($this->repository);
    }

    public function testGetUser(): void
    {
        $user = new User();
        $this->repository->expects($this->once())
            ->method('find')
            ->with('123')
            ->willReturn($user);

        $result = $this->service->getUser('123');
        $this->assertSame($user, $result);
    }
}
```

### Functional Tests

```php
class UserControllerTest extends WebTestCase
{
    public function testListUsers(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/users');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }
}
```

## Pull Request Process

### Before Submitting

1. Run tests:
   ```bash
   vendor/bin/phpunit
   ```

2. Check code style:
   ```bash
   vendor/bin/php-cs-fixer fix --dry-run
   ```

3. Run static analysis:
   ```bash
   vendor/bin/phpstan analyse
   ```

### Pull Request Template

```markdown
## Description
[Describe your changes]

## Type of change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Unit tests added/updated
- [ ] Functional tests added/updated
- [ ] Manual testing completed

## Checklist
- [ ] Code follows PSR-12
- [ ] Tests pass
- [ ] Documentation updated
- [ ] Security reviewed
```

## Code Review Process

### For Authors

1. Address all review comments
2. Update documentation if needed
3. Add tests if requested
4. Keep commits clean and focused

### For Reviewers

1. Review promptly
2. Be constructive
3. Focus on important issues
4. Provide clear explanations

## Security Guidelines

### Input Validation

```php
class UserValidator
{
    public function validate(array $data): array
    {
        $errors = [];
        
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        return $errors;
    }
}
```

### Output Sanitization

```php
class ResponseSanitizer
{
    public function sanitize(array $data): array
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
            return $value;
        }, $data);
    }
}
```

## Performance Guidelines

### Database Optimization

1. Use indexes appropriately
2. Optimize queries
3. Use caching
4. Implement pagination

### Cache Usage

```php
class TranslationService
{
    public function getTranslation(string $id): ?Translation
    {
        $cacheKey = "translation:{$id}";
        return $this->cache->get($cacheKey, function () use ($id) {
            return $this->repository->find($id);
        });
    }
}
```

## Getting Help

### Resources

1. [Project Documentation](../README.md)
2. [API Documentation](../api/README.md)
3. [Architecture Documentation](../architecture/README.md)
4. [Development Guidelines](GUIDELINES.md)

### Communication

1. GitHub Issues
2. Pull Request discussions
3. Team chat
4. Email

## Release Process

### Versioning

Follow semantic versioning:
- MAJOR version for incompatible API changes
- MINOR version for backwards-compatible functionality
- PATCH version for backwards-compatible bug fixes

### Release Checklist

1. Update version numbers
2. Update changelog
3. Run all tests
4. Update documentation
5. Create release tag
6. Deploy to staging
7. Deploy to production

## License

By contributing to Project Babel, you agree that your contributions will be licensed under the GNU General Public License v3.0. 