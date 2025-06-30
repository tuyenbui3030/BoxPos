# BoxPos - Multi-Tenant Point of Sale System

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red.svg" alt="Laravel Version">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue.svg" alt="PHP Version">
  <img src="https://img.shields.io/badge/Livewire-3.6+-green.svg" alt="Livewire Version">
  <img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="License">
</p>

BoxPos is a modern, multi-tenant Point of Sale (POS) system built with Laravel 12, designed for scalability and multi-store management. The application features a modular package-based architecture with comprehensive logging, multi-language support, and advanced tenant isolation.

## 🚀 Key Features

### Multi-Tenancy & Store Management
- **Multi-Tenant Architecture**: Each customer gets their own domain with complete data isolation
- **Project-Based Stores**: Support for multiple business types (retail, coffee shops, etc.)
- **Tenant Context Switching**: Seamless switching between stores with proper access control
- **Role-Based Permissions**: Granular permission system for different user roles

### Core Business Features
- **Customer Management**: Comprehensive customer database with advanced search and filtering
- **User Management**: Authentication, device management, and user profiles
- **Dashboard System**: Multiple dashboard types based on business requirements
- **Localization**: Full multi-language support (Vietnamese/English) with URL-based locale switching

### Technical Excellence
- **Package-Based Architecture**: Modular design with self-contained packages
- **Repository + Builder Pattern**: Clean architecture with proper separation of concerns
- **Comprehensive Logging**: Advanced logging with SQL query tracking, performance monitoring, and Sentry integration
- **Laravel Sail Development**: Consistent Docker-based development environment

## 🏗️ Architecture Overview

BoxPos follows a clean, modular architecture with the following layers:

```
Controller → Service → Repository → Builder → Model → Database
```

### Package Structure
```
packages/
├── appearance/          # Theme management and UI preferences
├── customer/           # Customer management functionality
├── localization/       # Multi-language support
├── log/               # Comprehensive logging system
├── session-manager/   # Session management utilities
├── store/             # Store/tenant management
├── tenant/            # Multi-tenancy functionality
└── user/              # User authentication and management
```

## 🛠️ Technology Stack

### Backend
- **Laravel 12.x** - PHP framework
- **PHP 8.2+** - Programming language
- **MySQL 8.0** - Primary database
- **Redis** - Caching and sessions
- **Laravel Livewire 3.6** - Dynamic frontend components

### Frontend
- **Tailwind CSS 4.0** - Utility-first CSS framework
- **Alpine.js 3.14** - Lightweight JavaScript framework
- **Tabler Core 1.3** - Admin dashboard template
- **Bootstrap 5.3** - UI components

### Development & Deployment
- **Laravel Sail** - Docker-based development environment
- **Vite 6.2** - Frontend build tool
- **Sentry** - Error tracking and performance monitoring
- **Nginx** - Web server and reverse proxy

## 📦 Installation & Setup

### Prerequisites
- Docker and Docker Compose
- Git

### Quick Start

1. **Clone the repository:**
```bash
git clone <repository-url>
cd BoxPos
```

2. **Install dependencies:**
```bash
composer install
```

3. **Environment setup:**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Start development environment:**
```bash
# Without alias
./vendor/bin/sail up -d

# Setup alias (recommended)
alias sail='./vendor/bin/sail'
sail up -d
```

5. **Database setup:**
```bash
sail artisan migrate
sail artisan db:seed
```

6. **Frontend assets:**
```bash
sail npm install
sail npm run dev
```

### Service Access
- **Application**: http://localhost
- **MySQL**: localhost:3306
- **Redis**: localhost:6379
- **Mailpit**: http://localhost:8025
- **Vite Dev Server**: http://localhost:5173

## 🔧 Development Workflow

### Daily Development Commands

**Always use Laravel Sail for consistency:**

```bash
# Artisan commands
sail artisan migrate
sail artisan make:model Customer
sail artisan queue:work

# Composer commands
sail composer install
sail composer require package/name

# Node/NPM commands
sail npm install
sail npm run dev
sail npm run build

# Testing
sail test
sail test --filter CustomerTest

# Code quality
sail composer pint      # Code formatting
sail composer phpstan   # Static analysis
```

### Code Quality Standards

The project follows strict coding standards:
- **Repository + Builder Pattern** for data access
- **Service Layer** for business logic
- **Mandatory Logging** for all business operations
- **Type Hints** and **PHPDoc** for all methods
- **Comprehensive Testing** with >80% coverage

## 📚 Package Documentation

Each package includes comprehensive documentation:

- **[Customer Package](packages/customer/README.md)** - Customer management functionality
- **[User Package](packages/user/README.md)** - Authentication and user management
- **[Store Package](packages/store/README.md)** - Multi-tenant store management
- **[Log Package](packages/log/README.md)** - Advanced logging system
- **[Localization Package](packages/localization/README.md)** - Multi-language support

## 🧪 Testing

Run tests using Laravel Sail:

```bash
# Run all tests
sail test

# Run specific test suite
sail test tests/Unit/
sail test tests/Feature/

# Run with coverage
sail test --coverage

# Run specific test
sail test --filter CustomerServiceTest
```

## 📊 Monitoring & Logging

BoxPos includes comprehensive monitoring:

### Log Files
- **laravel.log** - General application logs
- **error.log** - Error and critical logs
- **sql.log** - Database query performance

### Sentry Integration
- Error tracking with context
- Performance monitoring
- SQL query monitoring
- User activity tracking

### Performance Monitoring
- Request duration tracking
- Memory usage monitoring
- N+1 query detection
- Slow query identification

## 🌐 Multi-Language Support

BoxPos supports multiple languages with:
- URL-based locale switching (`/en/dashboard`, `/vi/dashboard`)
- Dynamic language switching without page reload
- Comprehensive translation files
- RTL language support ready

## 🔒 Security Features

- **Multi-tenant data isolation**
- **Role-based access control**
- **Rate limiting** on API endpoints
- **Input validation** and sanitization
- **Activity logging** for audit trails
- **Secure session management**

## 🚀 Deployment

### Production Deployment
The application is designed for VPS/dedicated server deployment with:
- Docker containerization
- Nginx reverse proxy for multi-tenancy
- Automated log rotation and cleanup
- Environment-specific configurations

### Scaling Considerations
- **Horizontal scaling** with load balancers
- **Database replication** for read/write separation
- **Redis clustering** for session management
- **CDN integration** for static assets

## 🤝 Contributing

1. Follow the coding standards in [CODING_RULES.md](CODING_RULES.md)
2. Write comprehensive tests for new features
3. Update documentation for any changes
4. Use Laravel Sail for development consistency
5. Ensure all business operations include proper logging

### Code Review Checklist
- [ ] All Services use `Loggable` trait
- [ ] All Controllers use `Loggable` trait
- [ ] Business operations are properly logged
- [ ] Tests are written and passing
- [ ] Documentation is updated

## 📄 License

BoxPos is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🆘 Support & Documentation

- **[Coding Rules](CODING_RULES.md)** - Development guidelines and architecture
- **[Package Documentation](packages/)** - Individual package documentation
- **[Log Package Quick Reference](packages/log/QUICK_REFERENCE.md)** - Logging usage guide
