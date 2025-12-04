# Traffic Case Management System (TCMS)

A comprehensive case management system for traffic violations with enhanced security, performance optimization, and modern API architecture built on Laravel 10.

## Features

- **Secure Authentication**: Bcrypt password hashing, rate limiting, and session security
- **Role-Based Access Control**: Admin, Super Admin, and User roles with proper middleware
- **RESTful API**: Laravel Sanctum-powered API with token authentication
- **Redis Integration**: High-performance caching, sessions, and queues
- **Real-time Analytics**: Dashboard with charts and statistics
- **Case Management**: Contested cases, admitted cases, and archives
- **File Attachments**: Secure file upload and management
- **Audit Logging**: Comprehensive security event logging
- **Responsive Design**: Mobile-friendly interface

## Security Features

- ✅ Bcrypt password hashing (migrated from encryption)
- ✅ Rate limiting on authentication endpoints
- ✅ CSRF protection
- ✅ Security headers (CSP, HSTS, X-Frame-Options, etc.)
- ✅ Session encryption with Redis
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ Input validation and sanitization
- ✅ Secure file uploads
- ✅ Comprehensive audit logging

## Requirements

- PHP >= 8.1
- MySQL >= 5.7 or MariaDB >= 10.3
- Redis >= 6.0
- Composer
- Node.js >= 16.x

## Installation

### 1. Clone Repository
```bash
git clone <repository-url>
cd tcms
```

### 2. Install Dependencies
```bash
composer install
npm install
npm run build
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Database and Redis
Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=TAS
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 5. Run Migrations
```bash
php artisan migrate

# IMPORTANT: Migrate encrypted passwords to hashed passwords
php artisan migrate --path=database/migrations/2024_12_01_000001_migrate_encrypted_passwords_to_hashed.php
```

### 6. Start Services
```bash
# Start development server
php artisan serve

# Start queue worker
php artisan queue:work

# Start Redis (if not running)
redis-server
```

## Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test suite
php artisan test --testsuite=Feature
```

## API Documentation

### Authentication
```bash
# Login
POST /api/v1/login
{
  "username": "admin",
  "password": "Password123!"
}

# Get user profile
GET /api/v1/user
Authorization: Bearer {token}
```

### Dashboard
```bash
# Get statistics
GET /api/v1/dashboard/stats
Authorization: Bearer {token}

# Get chart data
GET /api/v1/analytics/chart-data
Authorization: Bearer {token}
```

See `routes/api_v2.php` for complete API documentation.

## Documentation

- [Security Documentation](SECURITY.md) - Comprehensive security measures and best practices
- [Deployment Guide](DEPLOYMENT.md) - Step-by-step production deployment
- [Testing Guide](TESTING.md) - Testing strategies and guidelines

## Project Structure

```
tcms/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/          # API Controllers
│   │   │   ├── AuthController.php
│   │   │   └── DashboardController.php
│   │   └── Middleware/       # Custom Middleware
│   ├── Models/               # Eloquent Models
│   └── helpers.php           # Helper Functions
├── config/                   # Configuration Files
├── database/
│   ├── migrations/           # Database Migrations
│   └── seeders/              # Database Seeders
├── routes/
│   ├── web.php              # Web Routes
│   ├── api.php              # API Routes (Legacy)
│   └── api_v2.php           # API Routes (New)
├── tests/
│   ├── Feature/             # Integration Tests
│   └── Unit/                # Unit Tests
├── SECURITY.md              # Security Documentation
├── DEPLOYMENT.md            # Deployment Guide
└── TESTING.md               # Testing Guide
```

## Performance Optimization

### Redis Caching
- Session storage in Redis
- Query result caching
- Route and config caching

### Database Optimization
- Indexed columns for faster queries
- Eager loading to prevent N+1 queries
- Query result caching

### Asset Optimization
- Minified CSS and JavaScript
- Image optimization
- CDN integration ready

## Security Best Practices

1. **Never commit `.env` files**
2. **Keep dependencies updated**: `composer update`
3. **Regular security audits**: Review logs and access patterns
4. **Use HTTPS in production**
5. **Regular backups**: Database and file storage
6. **Monitor logs**: Check `storage/logs/laravel.log`

## Deployment

For production deployment, see [DEPLOYMENT.md](DEPLOYMENT.md)

Quick checklist:
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure Redis
- [ ] Run migrations (including password migration)
- [ ] Cache config, routes, and views
- [ ] Set up queue workers
- [ ] Configure web server (Nginx/Apache)
- [ ] Enable HTTPS
- [ ] Set up backups

## Default Credentials

After running seeders (fresh migration):
- **Super Admin**: username: `admin`, password: `Admin@123`
- **System Admin**: username: `sysadmin`, password: `SysAdmin@123`
- **Admin**: username: `mark`, password: `Mark@123`
- **User**: username: `user`, password: `User@123`
- **Test User**: username: `testuser`, password: `Test@123`

⚠️ **IMPORTANT**: Change all default passwords immediately after first login!

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## License

This project is proprietary software. All rights reserved.

## Support

For support and questions:
- Email: support@yourdomain.com
- Documentation: https://docs.yourdomain.com
- Issues: Create an issue in the repository

## Changelog

### Version 2.0.0 (2024-12-01) - Security & Performance Update
- ✅ **CRITICAL**: Migrated password storage from encryption to bcrypt hashing
- ✅ Implemented Redis for caching, sessions, and queues
- ✅ Added Laravel Sanctum API authentication
- ✅ Enhanced security with rate limiting and security headers
- ✅ Improved middleware with proper authorization checks
- ✅ Added comprehensive unit and feature tests
- ✅ Created API v2 with RESTful endpoints
- ✅ Enhanced input validation and sanitization
- ✅ Added security event logging
- ✅ Optimized database queries and caching
- ✅ Added SecurityHeaders middleware
- ✅ Added ThrottleLogin middleware
- ✅ Updated User model with helper methods
- ✅ Created comprehensive documentation

### Version 1.0.0
- Initial release with basic case management features

---

Built with ❤️ using Laravel 10
