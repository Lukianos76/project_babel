# Testing Guidelines

## Overview

This document outlines the testing strategy and guidelines for Project Babel, including different types of tests, testing tools, and best practices.

## Test Types

### 1. Unit Tests

Unit tests test individual components in isolation.

```php
class TranslationServiceTest extends TestCase
{
    private TranslationRepository $repository;
    private TranslationService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TranslationRepository::class);
        $this->service = new TranslationService($this->repository);
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
}
```

### 2. Integration Tests

Integration tests test component interactions.

```php
class TranslationIntegrationTest extends WebTestCase
{
    public function testTranslationWorkflow(): void
    {
        $client = static::createClient();
        
        // Create translation
        $client->request('POST', '/api/translations', [
            'key' => 'test.key',
            'value' => 'Test Value',
            'locale' => 'en'
        ]);
        
        $this->assertResponseIsSuccessful();
        
        // Get translation
        $client->request('GET', '/api/translations/test.key');
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }
}
```

### 3. Functional Tests

Functional tests test complete features.

```php
class TranslationFunctionalTest extends WebTestCase
{
    public function testTranslationManagement(): void
    {
        $client = static::createClient();
        
        // Login
        $client->request('POST', '/api/login', [
            'username' => 'admin',
            'password' => 'password'
        ]);
        
        // Create translation
        $client->request('POST', '/api/translations', [
            'key' => 'welcome',
            'value' => 'Welcome to our site',
            'locale' => 'en'
        ]);
        
        // Verify translation
        $client->request('GET', '/api/translations/welcome');
        $this->assertResponseIsSuccessful();
    }
}
```

## Test Organization

### Directory Structure

```
tests/
├── Unit/              # Unit tests
├── Integration/       # Integration tests
├── Functional/        # Functional tests
├── Api/              # API tests
└── Fixtures/         # Test fixtures
```

### Naming Conventions

- Test classes: `*Test.php`
- Test methods: `test*()`
- Data providers: `provide*()`

## Testing Tools

### 1. PHPUnit

```bash
# Run all tests
php bin/phpunit

# Run specific test suite
php bin/phpunit tests/Unit
php bin/phpunit tests/Integration

# Run with coverage
php bin/phpunit --coverage-html coverage
```

### 2. PHP CS Fixer

```bash
# Check code style
vendor/bin/php-cs-fixer fix --dry-run

# Fix code style
vendor/bin/php-cs-fixer fix
```

### 3. PHPStan

```bash
# Run static analysis
vendor/bin/phpstan analyse
```

### 4. Psalm

```bash
# Run type checking
vendor/bin/psalm
```

## Test Coverage

### Coverage Requirements

- Minimum 80% code coverage
- 100% coverage for critical paths
- Test all public methods
- Test error conditions

### Coverage Report

```bash
# Generate coverage report
php bin/phpunit --coverage-html coverage

# View coverage report
open coverage/index.html
```

## Testing Best Practices

### 1. Test Isolation

- Use fresh database for each test
- Clean up after tests
- Mock external dependencies
- Use test fixtures

### 2. Test Data

```php
class TranslationFixtures
{
    public function load(ObjectManager $manager): void
    {
        $translation = new Translation();
        $translation->setKey('welcome');
        $translation->setValue('Welcome');
        $translation->setLocale('en');
        
        $manager->persist($translation);
        $manager->flush();
    }
}
```

### 3. Assertions

- Use specific assertions
- Test edge cases
- Test error conditions
- Test boundary conditions

### 4. Performance

- Keep tests fast
- Use appropriate test types
- Avoid unnecessary setup
- Use data providers

## API Testing

### 1. Request Testing

```php
class ApiTest extends WebTestCase
{
    public function testCreateTranslation(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/translations', [
            'key' => 'test.key',
            'value' => 'Test Value',
            'locale' => 'en'
        ]);
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }
}
```

### 2. Response Testing

```php
class ResponseTest extends WebTestCase
{
    public function testTranslationResponse(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/translations/test.key');
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('key', $data);
        $this->assertArrayHasKey('value', $data);
        $this->assertArrayHasKey('locale', $data);
    }
}
```

## Database Testing

### 1. Database Fixtures

```php
class DatabaseTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->loadFixtures([
            TranslationFixtures::class
        ]);
    }
    
    public function testDatabaseOperations(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/translations');
        
        $this->assertResponseIsSuccessful();
    }
}
```

### 2. Transaction Management

```php
class TransactionTest extends WebTestCase
{
    use TransactionalTestCase;
    
    public function testTransactionRollback(): void
    {
        // Test database operations
        // Changes will be rolled back automatically
    }
}
```

## Continuous Integration

### 1. GitHub Actions

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
        run: php bin/phpunit
```

### 2. Test Reports

- Generate test reports
- Track coverage trends
- Monitor test results
- Set up notifications

## Support

For testing questions:
- Check the [Development Guidelines](GUIDELINES.md)
- Review the [Code Structure](CODE_STRUCTURE.md)
- Contact the development team 