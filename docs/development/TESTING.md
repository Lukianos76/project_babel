# Testing Guide

## Overview

This document outlines the testing strategy for Project Babel, including unit tests, functional tests, and integration tests.

## Test Types

### Unit Tests

Unit tests focus on testing individual components in isolation.

```php
class TranslationServiceTest extends TestCase
{
    private TranslationRepository $repository;
    private CacheInterface $cache;
    private TranslationService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TranslationRepository::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->service = new TranslationService($this->repository, $this->cache);
    }

    public function testGetTranslation(): void
    {
        $translation = new Translation();
        $this->repository->expects($this->once())
            ->method('find')
            ->with('123')
            ->willReturn($translation);

        $result = $this->service->getTranslation('123');
        $this->assertSame($translation, $result);
    }

    public function testGetTranslationNotFound(): void
    {
        $this->repository->expects($this->once())
            ->method('find')
            ->with('123')
            ->willReturn(null);

        $result = $this->service->getTranslation('123');
        $this->assertNull($result);
    }
}
```

### Functional Tests

Functional tests test the application's behavior from a user's perspective.

```php
class TranslationControllerTest extends WebTestCase
{
    public function testListTranslations(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/translations');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }

    public function testCreateTranslation(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/translations', [
            'json' => [
                'key' => 'welcome',
                'value' => 'Welcome to our site',
                'language' => 'en'
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }
}
```

### Integration Tests

Integration tests verify that different components work together correctly.

```php
class TranslationIntegrationTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private TranslationService $service;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->service = self::getContainer()->get(TranslationService::class);
    }

    public function testTranslationWorkflow(): void
    {
        // Create translation
        $translation = new Translation();
        $translation->setKey('welcome');
        $translation->setValue('Welcome');
        $translation->setLanguage('en');
        
        $this->entityManager->persist($translation);
        $this->entityManager->flush();

        // Retrieve translation
        $result = $this->service->getTranslation($translation->getId());
        $this->assertNotNull($result);
        $this->assertEquals('welcome', $result->getKey());

        // Update translation
        $result->setValue('Welcome Back');
        $this->entityManager->flush();

        // Verify update
        $updated = $this->service->getTranslation($translation->getId());
        $this->assertEquals('Welcome Back', $updated->getValue());
    }
}
```

## Test Configuration

### PHPUnit Configuration

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         colors="true"
         bootstrap="tests/bootstrap.php">
    <testsuites>
        <testsuite name="Project Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
        <testsuite name="Functional">
            <directory>tests/Functional</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory suffix=".php">src</directory>
        </include>
    </coverage>
</phpunit>
```

### Test Commands
```bash
# Run all tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite Unit

# Run with coverage report
vendor/bin/phpunit --coverage-html coverage

# Run specific test file
vendor/bin/phpunit tests/Unit/Service/TranslationServiceTest.php
```

## Best Practices

### Test Naming
- Use clear, descriptive names
- Follow pattern: test[What][ExpectedResult]
- Use camelCase
- Avoid abbreviations

### Test Organization
- One test class per source class
- Group related tests
- Use data providers for variations
- Keep tests independent

### Test Coverage
- Aim for high coverage
- Focus on critical paths
- Test edge cases
- Test error scenarios

### Performance Considerations
- Keep tests fast
- Use appropriate test types
- Clean up resources
- Use test databases

## Continuous Integration

### GitHub Actions
```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: vendor/bin/phpunit
```

### Coverage Reports
- Generate on CI
- Track coverage trends
- Set minimum thresholds
- Review uncovered code

## Debugging Tests

### Common Issues
1. **Database Issues**
   ```bash
   # Reset test database
   bin/console doctrine:database:drop --force --env=test
   bin/console doctrine:database:create --env=test
   bin/console doctrine:migrations:migrate --env=test
   ```

2. **Cache Issues**
   ```bash
   # Clear test cache
   bin/console cache:clear --env=test
   ```

3. **Fixture Issues**
   ```bash
   # Reload fixtures
   bin/console doctrine:fixtures:load --env=test
   ```

### Debugging Tools
- Xdebug
- PHPUnit debug mode
- Test database tools
- Log inspection

## Test Maintenance

### Regular Tasks
- Update tests with new features
- Remove obsolete tests
- Review test coverage
- Optimize test performance

### Code Review
- Review test quality
- Check test coverage
- Verify test independence
- Review test data

## Resources

### Documentation
- [PHPUnit Manual](https://phpunit.readthedocs.io/)
- [Symfony Testing](https://symfony.com/doc/current/testing.html)
- [Testing Best Practices](https://phpunit.readthedocs.io/en/9.5/writing-tests-for-phpunit.html)

### Tools
- PHPUnit
- Xdebug
- PHPStan
- PHP CS Fixer 