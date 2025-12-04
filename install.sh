#!/bin/bash

# TCMS Installation Script
# This script automates the installation process for the Traffic Case Management System

set -e

echo "========================================="
echo "TCMS Installation Script"
echo "========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

# Check if running as root
if [ "$EUID" -eq 0 ]; then 
    print_error "Please do not run this script as root"
    exit 1
fi

# Check PHP version
echo "Checking PHP version..."
PHP_VERSION=$(php -r "echo PHP_VERSION;" | cut -d. -f1,2)
if (( $(echo "$PHP_VERSION < 8.1" | bc -l) )); then
    print_error "PHP 8.1 or higher is required. Current version: $PHP_VERSION"
    exit 1
fi
print_success "PHP version: $PHP_VERSION"

# Check if Composer is installed
echo "Checking Composer..."
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed. Please install Composer first."
    exit 1
fi
print_success "Composer is installed"

# Check if Node.js is installed
echo "Checking Node.js..."
if ! command -v node &> /dev/null; then
    print_warning "Node.js is not installed. Asset compilation will be skipped."
    NODE_INSTALLED=false
else
    print_success "Node.js is installed"
    NODE_INSTALLED=true
fi

# Check if Redis is installed
echo "Checking Redis..."
if ! command -v redis-cli &> /dev/null; then
    print_warning "Redis is not installed. Please install Redis for optimal performance."
    REDIS_INSTALLED=false
else
    if redis-cli ping &> /dev/null; then
        print_success "Redis is installed and running"
        REDIS_INSTALLED=true
    else
        print_warning "Redis is installed but not running"
        REDIS_INSTALLED=false
    fi
fi

# Install PHP dependencies
echo ""
echo "Installing PHP dependencies..."
composer install --optimize-autoloader
print_success "PHP dependencies installed"

# Install Node dependencies if Node is available
if [ "$NODE_INSTALLED" = true ]; then
    echo ""
    echo "Installing Node dependencies..."
    npm install
    print_success "Node dependencies installed"
    
    echo "Building assets..."
    npm run build
    print_success "Assets built"
fi

# Setup environment file
echo ""
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cp .env.example .env
    print_success ".env file created"
else
    print_warning ".env file already exists, skipping..."
fi

# Generate application key
echo ""
echo "Generating application key..."
php artisan key:generate
print_success "Application key generated"

# Ask for database configuration
echo ""
echo "========================================="
echo "Database Configuration"
echo "========================================="
read -p "Database host [127.0.0.1]: " DB_HOST
DB_HOST=${DB_HOST:-127.0.0.1}

read -p "Database port [3306]: " DB_PORT
DB_PORT=${DB_PORT:-3306}

read -p "Database name [TAS]: " DB_NAME
DB_NAME=${DB_NAME:-TAS}

read -p "Database username [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -sp "Database password: " DB_PASS
echo ""

# Update .env file with database configuration
sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
sed -i "s/DB_PORT=.*/DB_PORT=$DB_PORT/" .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env

print_success "Database configuration updated"

# Configure Redis if installed
if [ "$REDIS_INSTALLED" = true ]; then
    echo ""
    echo "========================================="
    echo "Redis Configuration"
    echo "========================================="
    read -p "Redis host [127.0.0.1]: " REDIS_HOST
    REDIS_HOST=${REDIS_HOST:-127.0.0.1}
    
    read -p "Redis port [6379]: " REDIS_PORT
    REDIS_PORT=${REDIS_PORT:-6379}
    
    read -sp "Redis password (leave empty if none): " REDIS_PASS
    echo ""
    
    # Update .env file with Redis configuration
    sed -i "s/REDIS_HOST=.*/REDIS_HOST=$REDIS_HOST/" .env
    sed -i "s/REDIS_PORT=.*/REDIS_PORT=$REDIS_PORT/" .env
    sed -i "s/REDIS_PASSWORD=.*/REDIS_PASSWORD=$REDIS_PASS/" .env
    sed -i "s/CACHE_DRIVER=.*/CACHE_DRIVER=redis/" .env
    sed -i "s/SESSION_DRIVER=.*/SESSION_DRIVER=redis/" .env
    sed -i "s/QUEUE_CONNECTION=.*/QUEUE_CONNECTION=redis/" .env
    
    print_success "Redis configuration updated"
fi

# Test database connection
echo ""
echo "Testing database connection..."
if php artisan db:show &> /dev/null; then
    print_success "Database connection successful"
else
    print_error "Database connection failed. Please check your credentials."
    exit 1
fi

# Run migrations
echo ""
read -p "Do you want to run database migrations? (y/n): " RUN_MIGRATIONS
if [ "$RUN_MIGRATIONS" = "y" ] || [ "$RUN_MIGRATIONS" = "Y" ]; then
    echo "Running migrations..."
    php artisan migrate --force
    print_success "Migrations completed"
    
    # Ask about password migration
    echo ""
    print_warning "IMPORTANT: If upgrading from version 1.x, you need to run the password migration."
    read -p "Run password migration? (y/n): " RUN_PASSWORD_MIGRATION
    if [ "$RUN_PASSWORD_MIGRATION" = "y" ] || [ "$RUN_PASSWORD_MIGRATION" = "Y" ]; then
        php artisan migrate --path=database/migrations/2024_12_01_000001_migrate_encrypted_passwords_to_hashed.php --force
        print_success "Password migration completed"
    fi
fi

# Ask about seeding
echo ""
read -p "Do you want to seed the database with sample data? (y/n): " RUN_SEEDERS
if [ "$RUN_SEEDERS" = "y" ] || [ "$RUN_SEEDERS" = "Y" ]; then
    echo "Seeding database..."
    php artisan db:seed
    print_success "Database seeded"
fi

# Set file permissions
echo ""
echo "Setting file permissions..."
chmod -R 755 storage bootstrap/cache
print_success "File permissions set"

# Create symbolic link for storage
echo ""
echo "Creating storage symbolic link..."
php artisan storage:link
print_success "Storage link created"

# Optimize application
echo ""
echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Application optimized"

# Run tests
echo ""
read -p "Do you want to run tests? (y/n): " RUN_TESTS
if [ "$RUN_TESTS" = "y" ] || [ "$RUN_TESTS" = "Y" ]; then
    echo "Running tests..."
    php artisan test
fi

# Final instructions
echo ""
echo "========================================="
echo "Installation Complete!"
echo "========================================="
echo ""
print_success "TCMS has been successfully installed"
echo ""
echo "Next steps:"
echo "1. Start the development server: php artisan serve"
if [ "$REDIS_INSTALLED" = true ]; then
    echo "2. Start the queue worker: php artisan queue:work"
fi
echo "3. Visit http://localhost:8000 in your browser"
echo ""
echo "Default credentials (if seeded):"
echo "  Username: admin"
echo "  Password: P@s\$w0rd123"
echo ""
print_warning "IMPORTANT: Change default passwords immediately!"
echo ""
echo "For production deployment, see DEPLOYMENT.md"
echo "For security information, see SECURITY.md"
echo ""
