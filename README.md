# Project Babel

## About the Project

Project Babel is a Symfony-based application designed as a RESTful API with a minimal administrative interface.  
It provides services for translation management, mod handling, and automated language processing.  
There is no front-end website; the system is intended to be consumed by external clients or internal tools.

![Documentation Status](https://img.shields.io/badge/docs-in%20progress-yellow)
![Test Status](https://img.shields.io/badge/tests-not%20started-red)
![Deployment Status](https://img.shields.io/badge/deployment-not%20deployed-red)
![License](https://img.shields.io/badge/license-GNU%20GPL%20v3-blue)

A powerful translation management system for game mods and content.

## 📚 Technical Documentation Overview

### 🧠 Overview
- [Project Overview](docs/overview/project-overview.md)
- [Getting Started](docs/overview/getting-started.md)
- [Contributing](docs/overview/contributing.md)

### 🧱 Architecture
- [System Architecture](docs/architecture/system-architecture.md)
- [Component Architecture](docs/architecture/component-architecture.md)
- [Security Architecture](docs/architecture/security-architecture.md)
- [Database Schema](docs/architecture/database-schema.md)

### 🔌 API
- [API Overview](docs/api/api-overview.md)
- [Endpoints](docs/api/endpoints.md)
- [Authentication](docs/api/authentication.md)
- [Error Handling](docs/api/error-handling.md)
- [Rate Limiting](docs/api/rate-limiting.md)
- [Caching](docs/api/caching.md)

### 🧑‍💻 Development
- [Development Setup](docs/development/setup.md)
- [Testing](docs/development/testing.md)
- [Performance](docs/development/performance.md)
- [Code Review](docs/development/code-review.md)
- [Deployment](docs/development/deployment.md)

### 🌍 Features
- [Translation Management](docs/features/translation.md)
- [Mod Management](docs/features/mod.md)
- [User Management](docs/features/user.md)
- [Review System](docs/features/review.md)

## 🚀 Quick Start

1. Clone the repository:
```bash
git clone https://github.com/yourusername/project-babel.git
cd project-babel
```

2. Install dependencies:
```bash
composer install
npm install
```

3. Set up environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure database:
```bash
php artisan migrate
php artisan db:seed
```

5. Start development server:
```bash
php artisan serve
npm run dev
```

## 🛠️ Technology Stack

- **Framework**: Symfony 7
- **PHP**: 8.2 or higher
- **Database**: PostgreSQL 15
- **Cache**: Redis 7
- **Search**: Elasticsearch 8
- **Queue**: RabbitMQ
- **Monitoring**: Prometheus, Grafana
- **Admin Interface**: Symfony UX with minimal JavaScript

## 📦 Requirements

- PHP 8.2 or higher
- PostgreSQL 15 or higher
- Redis 7 or higher
- Composer 2
- Symfony CLI

## 🤝 Contributing

Please read our [Contributing Guide](docs/overview/contributing.md) for details on our code of conduct and the process for submitting pull requests.

## 📄 License

This project is licensed under the GNU GPL v3 License - see the [LICENSE](LICENSE) file for details.