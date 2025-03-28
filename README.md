# Project Babel

## Overview

Project Babel is a Symfony-based API for managing and translating localization files for video game mods. It provides a robust platform for handling translations, managing games and mods, and facilitating collaboration between translators.

## Documentation

### Getting Started
- [Development Setup](docs/development/GETTING_STARTED.md)
- [Development Guidelines](docs/development/GUIDELINES.md)
- [Testing Guide](docs/development/TESTING.md)
- [Code Review Process](docs/development/CODE_REVIEW.md)
- [Contributing Guide](docs/development/CONTRIBUTING.md)
- [Release Process](docs/development/RELEASE_PROCESS.md)

### Architecture
- [System Architecture](docs/architecture/SYSTEM_ARCHITECTURE.md)
- [Component Architecture](docs/architecture/COMPONENT_ARCHITECTURE.md)
- [Security Architecture](docs/architecture/SECURITY_ARCHITECTURE.md)
- [Caching Strategy](docs/architecture/CACHING_STRATEGY.md)

### API
- [API Documentation](docs/api/README.md)
- [Authentication](docs/api/AUTHENTICATION.md)
- [Endpoints](docs/api/ENDPOINTS.md)
- [Error Handling](docs/api/ERROR_HANDLING.md)
- [Rate Limiting](docs/api/RATE_LIMITING.md)

### Features
- [Translation Management](docs/features/TRANSLATION.md)
- [Automatic Translation](docs/features/AUTOMATIC_TRANSLATION.md)

### Deployment
- [Deployment Guide](docs/deployment/DEPLOYMENT.md)
- [Environment Setup](docs/deployment/ENVIRONMENT.md)
- [Monitoring](docs/deployment/MONITORING.md)
- [Backup Strategy](docs/deployment/BACKUP.md)

## Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer 2.0 or higher
- Docker and Docker Compose
- Git
- Node.js 18 or higher (for frontend development)

### Installation

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

### Development Server

```bash
# Start Symfony development server
symfony server:start

# Or use PHP's built-in server
php -S localhost:8000 -t public/
```

## Features

### Translation Management
- Multi-language support with automatic initial translation
- Human review workflow with quality control
- Translation memory and glossary management
- Support for multiple file formats (JSON, YAML, INI, PO/MO)
- Real-time collaboration tools
- Version control for translations

### Integration
- Game and mod integration
- File parsing and validation
- Real-time updates
- Caching system
- API rate limiting
- Security features

### Translation Services
- DeepL integration for high-quality translations
- Google Translate support for wide language coverage
- Microsoft Translator for enterprise needs
- Custom translation provider support

## Contributing

We welcome contributions! Please see our [Contributing Guide](docs/development/CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## License

This project is licensed under the GNU General Public License v3.0 - see the [LICENSE](LICENSE) file for details.

## Support

- Documentation: [Project Documentation](docs/README.md)
- Issues: [GitHub Issues](https://github.com/your-org/project-babel/issues)
- Community: [Discord Server](https://discord.gg/project-babel)
- Email: support@projectbabel.org 