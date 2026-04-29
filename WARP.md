# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

This is **filampac** - a Laravel-based skeleton application for MPs (Ministério Público) built with Filament admin panel. It's a permission-based user management system with role-based access control using Spatie Laravel Permission.

## Technology Stack

- **Backend**: Laravel 12.x, PHP 8.2+
- **Admin Panel**: Filament 5.x
- **Database**: SQLite (default), MySQL/MariaDB/PostgreSQL support
- **Frontend**: Vite + TailwindCSS 4.x + Laravel Mix
- **Authentication**: Laravel Session-based
- **Permissions**: Spatie Laravel Permission
- **Icons**: Phosphor Icons (codeat3/blade-phosphor-icons)
- **Development Tools**: LDAP Record Laravel

## Key Commands

### Development Workflow

```bash
# Start full development environment (recommended)
composer dev    # Runs: server + queue + logs + vite concurrently

# Individual services
php artisan serve                    # Start Laravel development server
npm run dev                         # Start Vite development server
php artisan queue:listen --tries=1  # Start queue worker
php artisan pail --timeout=0       # Real-time logs

# Database operations
php artisan migrate                 # Run migrations
php artisan migrate:fresh --seed   # Fresh database with seeds
composer fresh:db                  # Alias for migrate:fresh --seed
```

### Code Quality & Testing

```bash
# Code formatting & analysis
composer style:fix                 # Format PHP code with Pint
composer style:check               # Check PHP code style
composer code:analyze              # Run PHPStan static analysis
composer dd:check                  # Check for debug statements

# Testing
composer test                      # Run PHPUnit/Pest tests
php artisan test                   # Alternative test runner
vendor/bin/pest                    # Run specific Pest tests

# Pre-commit hooks (automatic via Husky)
npm run prepare                    # Setup Husky git hooks
```

### Build & Deployment

```bash
# Frontend assets
npm run build                      # Build production assets
npm install                        # Install Node dependencies

# Laravel optimization
php artisan config:cache           # Cache configuration
php artisan route:cache           # Cache routes
php artisan view:cache            # Cache views
php artisan optimize              # Run all optimizations
```

## Application Architecture

### Core Structure

The application follows a **modular Filament architecture** with separated concerns:

```
app/
├── Enums/
│   └── NavGroups.php              # Navigation group definitions
├── Filament/
│   ├── Pages/Auth/                # Custom auth pages (Login/Register)
│   ├── Resources/                 # Main admin resources
│   │   ├── Permissions/           # Permission management
│   │   ├── Roles/                 # Role management
│   │   └── Users/                 # User management
│   └── Widgets/                   # Dashboard widgets
├── Models/                        # Eloquent models
├── Providers/Filament/            # Filament service providers
└── Traits/
    └── HasActiveScope.php         # Global active scope for models
```

### Key Models & Relationships

- **User**: Extends Authenticatable with HasRoles trait
- **Role**: Spatie permission role model
- **Permission**: Spatie permission model
- **Navigation Groups**: Centralized in `NavGroups` enum

### Permission System

Uses **Spatie Laravel Permission** with custom models:

- Panel access controlled via `canAccessPanel()` method
- Permission format: `system.panels.view.{panel_id}`
- Navigation organized by authorization groups

### Form Architecture

Filament resources use **separated Schema classes**:

- Form schemas: `Resources/{Entity}/Schemas/{Entity}Form.php`
- Table configs: `Resources/{Entity}/Tables/{Entity}Table.php`
- This separation allows for better reusability and maintainability

## Configuration Files

### Code Quality

- **pint.json**: Laravel Pint configuration with PSR-12 + custom rules
- **phpstan.neon**: Static analysis configuration (Level 9)
- **.php-cs-fixer.php**: PHP CS Fixer setup synchronized with Pint rules
- **.lintstagedrc**: Pre-commit hook configuration

### Development Environment

- **vite.config.js**: Vite configuration with Laravel plugin + TailwindCSS
- **laradumps.yaml**: Debug tool configuration
- **package.json**: Node.js dependencies and scripts

## Database Architecture

### Core Tables

- **users**: User management with username, email, active status
- **roles/permissions**: Spatie permission tables
- **sessions**: Laravel session storage
- **jobs/failed_jobs**: Queue system tables

### Key Features

- SQLite default database with MySQL/PostgreSQL support
- Foreign key constraints for audit trails (created_by/updated_by)
- Soft deletes and timestamps on core models

## Development Guidelines

### Code Standards

- **PHP**: PSR-12 compliance enforced by Pint
- **Static Analysis**: PHPStan Level 9
- **Architecture**: Follow existing Filament resource patterns
- **Forms**: Use separate Schema classes for complex forms
- **Navigation**: Utilize NavGroups enum for consistent organization

### Testing Approach

- **Pest** is the primary testing framework
- Tests organized in Feature/ and Unit/ directories
- Database testing uses in-memory SQLite
- Environment variables automatically configured for testing

### Common Patterns

1. **Filament Resources**: Follow the established pattern of separating form schemas and table configurations
2. **Navigation Groups**: Use the NavGroups enum for consistent panel organization
3. **Permissions**: Follow `{domain}.{action}.{resource}` naming convention
4. **Models**: Implement appropriate traits (HasActiveScope, HasRoles) as needed

## Admin Panel Structure

- **URL**: `/admin`
- **Authentication**: Custom Login/Register pages
- **Theme**: Custom CSS with TailwindCSS 4.x
- **Navigation**: Organized by NavGroups (Authorization, etc.)
- **Widgets**: Account + Filament Info widgets enabled

## Important Notes

- The application expects LDAP integration (`directorytree/ldaprecord-laravel`)
- Icon system uses Phosphor Icons throughout the interface
- Git hooks are managed by Husky for automatic code quality checks
- The concurrent development command runs 4 services simultaneously for optimal DX
