# Contributing to Project Babel

## Overview

Thank you for your interest in contributing to Project Babel! This document provides guidelines and instructions for contributing to the project.

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Git
- MySQL 8.0 or higher
- Redis (optional, for caching)

### Development Setup

1. Fork the repository
2. Clone your fork:
   ```bash
   git clone https://github.com/your-username/project_babel.git
   cd project_babel
   ```
3. Install dependencies:
   ```bash
   composer install
   ```
4. Copy environment file:
   ```bash
   cp .env.example .env
   ```
5. Configure your environment:
   ```bash
   # Edit .env with your settings
   APP_ENV=dev
   DATABASE_URL=mysql://user:pass@localhost:3306/project_babel
   ```
6. Create database:
   ```bash
   php bin/console doctrine:database:create
   ```
7. Run migrations:
   ```bash
   php bin/console doctrine:migrations:migrate
   ```
8. Start development server:
   ```bash
   symfony server:start
   ```

## Development Workflow

### 1. Create a Branch

```bash
# Create and switch to a new feature branch
git checkout -b feature/your-feature-name

# Or for bug fixes
git checkout -b fix/your-bug-fix
```

### 2. Make Changes

- Follow the [Code Style Guidelines](GUIDELINES.md)
- Write tests for new features
- Update documentation
- Keep commits focused and atomic

### 3. Testing

```bash
# Run all tests
php bin/phpunit

# Run specific test suite
php bin/phpunit tests/Unit
php bin/phpunit tests/Integration

# Run with coverage report
php bin/phpunit --coverage-html coverage
```

### 4. Code Quality

```bash
# Run PHP CS Fixer
vendor/bin/php-cs-fixer fix

# Run PHPStan
vendor/bin/phpstan analyse

# Run Psalm
vendor/bin/psalm
```

### 5. Documentation

- Update relevant documentation
- Add PHPDoc blocks
- Update API documentation
- Add examples where needed

## Pull Request Process

### 1. Before Submitting

- Ensure all tests pass
- Fix any code style issues
- Update documentation
- Rebase on main branch

### 2. Creating the PR

- Use a descriptive title
- Reference related issues
- Include detailed description
- Add screenshots for UI changes

### 3. PR Review

- Address review comments
- Keep commits clean
- Update PR as needed
- Request re-review when ready

## Issue Reporting

### Bug Reports

- Use the bug report template
- Include steps to reproduce
- Add error messages
- Provide environment details

### Feature Requests

- Use the feature request template
- Explain the problem
- Propose a solution
- Consider alternatives

## Code Review Guidelines

### For Contributors

- Keep PRs focused and small
- Respond to review comments
- Make requested changes
- Test thoroughly

### For Reviewers

- Be constructive
- Focus on code quality
- Consider maintainability
- Check for security issues

## Documentation

### Code Documentation

- Document public APIs
- Include examples
- Explain complex logic
- Keep docs up to date

### API Documentation

- Document endpoints
- Include request/response examples
- Document errors
- Keep OpenAPI spec updated

## Testing

### Unit Tests

- Test business logic
- Mock dependencies
- Test edge cases
- Keep tests focused

### Integration Tests

- Test API endpoints
- Test database operations
- Test external services
- Test error scenarios

## Security

### Reporting Security Issues

- Email security@projectbabel.org
- Include detailed description
- Provide steps to reproduce
- Wait for response

### Security Best Practices

- Follow OWASP guidelines
- Validate input
- Sanitize output
- Use prepared statements

## Release Process

### Versioning

- Follow semantic versioning
- Update CHANGELOG.md
- Tag releases
- Update documentation

### Release Checklist

- Run all tests
- Check dependencies
- Update version numbers
- Generate changelog
- Create release notes

## Community Guidelines

### Communication

- Be respectful
- Use inclusive language
- Stay on topic
- Follow code of conduct

### Getting Help

- Check documentation
- Search existing issues
- Ask in discussions
- Contact maintainers

## License

By contributing, you agree that your contributions will be licensed under the project's MIT License.

## Support

For contribution questions:
- Check the [Development Guidelines](GUIDELINES.md)
- Review the [Code Structure](CODE_STRUCTURE.md)
- Contact the development team 