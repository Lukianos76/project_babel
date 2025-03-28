# Code Structure

## Overview

This document outlines the code structure and organization of Project Babel. The project follows a modular architecture with clear separation of concerns.

## Directory Structure

```
project_babel/
├── bin/                    # Console commands
├── config/                 # Configuration files
├── docs/                   # Documentation
├── public/                 # Public web directory
├── src/                    # Source code
│   ├── Api/               # API components
│   │   ├── Controller/    # API controllers
│   │   ├── DTO/          # Data Transfer Objects
│   │   ├── Resource/     # API resources
│   │   └── Security/     # Security components
│   ├── Command/          # Console commands
│   ├── Entity/           # Database entities
│   ├── Event/            # Event system
│   ├── Exception/        # Custom exceptions
│   ├── Form/             # Form types
│   ├── Repository/       # Database repositories
│   ├── Service/          # Business logic
│   ├── Translation/      # Translation components
│   └── Validator/        # Custom validators
├── templates/             # Twig templates
├── tests/                 # Test files
│   ├── Api/              # API tests
│   ├── Command/          # Command tests
│   ├── Entity/           # Entity tests
│   ├── Functional/       # Functional tests
│   ├── Integration/      # Integration tests
│   ├── Service/          # Service tests
│   └── Unit/             # Unit tests
├── translations/          # Translation files
├── var/                   # Runtime files
└── vendor/               # Dependencies
```

## Component Organization

### 1. API Layer

```php
namespace App\Api\Controller;

class TranslationController extends AbstractController
{
    #[Route('/api/translations', name: 'api_translations_list', methods: ['GET'])]
    public function list(TranslationService $service): JsonResponse
    {
        return $this->json($service->getAllTranslations());
    }
}
```

### 2. Service Layer

```php
namespace App\Service;

class TranslationService
{
    public function __construct(
        private readonly TranslationRepository $repository,
        private readonly CacheInterface $cache
    ) {}

    public function getTranslation(string $id): ?Translation
    {
        return $this->cache->get(
            "translation:{$id}",
            fn() => $this->repository->find($id)
        );
    }
}
```

### 3. Repository Layer

```php
namespace App\Repository;

class TranslationRepository extends ServiceEntityRepository
{
    public function findByGame(string $gameId): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.game = :gameId')
            ->setParameter('gameId', $gameId)
            ->getQuery()
            ->getResult();
    }
}
```

### 4. Entity Layer

```php
namespace App\Entity;

#[ORM\Entity]
class Translation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $key = null;

    #[ORM\Column(type: 'text')]
    private ?string $value = null;

    #[ORM\Column(length: 10)]
    private ?string $locale = null;

    // Getters and setters
}
```

## Design Patterns

### 1. Repository Pattern

```php
interface TranslationRepositoryInterface
{
    public function find(string $id): ?Translation;
    public function save(Translation $translation): void;
    public function delete(Translation $translation): void;
}
```

### 2. Service Layer Pattern

```php
interface TranslationServiceInterface
{
    public function getTranslation(string $id): ?Translation;
    public function createTranslation(array $data): Translation;
    public function updateTranslation(Translation $translation): void;
}
```

### 3. Factory Pattern

```php
class TranslationFactory
{
    public function createFromArray(array $data): Translation
    {
        $translation = new Translation();
        $translation->setKey($data['key']);
        $translation->setValue($data['value']);
        $translation->setLocale($data['locale']);
        return $translation;
    }
}
```

## Code Organization Guidelines

### 1. Namespace Structure

- Use PSR-4 autoloading
- Follow Symfony's namespace conventions
- Group related components in sub-namespaces

### 2. File Organization

- One class per file
- Clear file naming conventions
- Logical directory structure
- Consistent file ordering

### 3. Code Style

- Follow PSR-12 standards
- Use type hints
- Document classes and methods
- Keep methods focused and small

## Testing Organization

### 1. Test Types

```php
// Unit Test
class TranslationServiceTest extends TestCase
{
    public function testGetTranslation(): void
    {
        // Test implementation
    }
}

// Integration Test
class TranslationIntegrationTest extends WebTestCase
{
    public function testTranslationWorkflow(): void
    {
        // Test implementation
    }
}
```

### 2. Test Structure

- Mirror source directory structure
- Clear test naming
- Proper test isolation
- Comprehensive test coverage

## Documentation

### 1. Code Documentation

```php
/**
 * Translation service for managing translations.
 */
class TranslationService
{
    /**
     * Creates a new translation.
     *
     * @param array<string, mixed> $data Translation data
     *
     * @throws ValidationException If data is invalid
     */
    public function createTranslation(array $data): Translation
    {
        // Implementation
    }
}
```

### 2. API Documentation

```php
/**
 * @OA\Get(
 *     path="/api/translations",
 *     summary="List all translations",
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref=@Model(type=Translation::class))
 *         )
 *     )
 * )
 */
```

## Best Practices

### 1. Code Organization

- Keep related code together
- Use appropriate abstractions
- Follow SOLID principles
- Maintain clear boundaries

### 2. Testing

- Write tests for new features
- Maintain test coverage
- Use appropriate test types
- Keep tests maintainable

### 3. Documentation

- Document public APIs
- Keep documentation up to date
- Use clear and concise language
- Include examples

## Support

For code structure questions:
- Check the [Development Guidelines](GUIDELINES.md)
- Review the [Architecture Documentation](../architecture/SYSTEM_ARCHITECTURE.md)
- Contact the development team 