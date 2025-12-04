.PHONY: help build up down restart logs shell db-shell redis-shell migrate seed test clean

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

setup: ## Initial setup - copy env and install dependencies
	@echo "Setting up TCMS Docker environment..."
	@if [ ! -f .env ]; then cp .env.docker .env; echo "✓ Created .env file"; fi
	@echo "✓ Setup complete"

build: ## Build Docker containers
	@echo "Building Docker containers..."
	docker compose build
	@echo "✓ Build complete"

up: ## Start all containers
	@echo "Starting containers..."
	docker compose up -d
	@echo "✓ Containers started"
	@echo "Access application at: http://localhost:8080"

down: ## Stop all containers
	@echo "Stopping containers..."
	docker compose down
	@echo "✓ Containers stopped"

restart: ## Restart all containers
	@echo "Restarting containers..."
	docker compose restart
	@echo "✓ Containers restarted"

logs: ## View logs from all containers
	docker compose logs -f

logs-app: ## View application logs
	docker compose logs -f app

logs-web: ## View web server logs
	docker compose logs -f web

logs-db: ## View database logs
	docker compose logs -f db

shell: ## Access application container shell
	docker compose exec app sh

db-shell: ## Access MySQL shell
	docker compose exec db mysql -u tcms_user -p${DB_PASSWORD:-tcms_password} ${DB_DATABASE:-TAS}

redis-shell: ## Access Redis CLI
	docker compose exec redis redis-cli

migrate: ## Run database migrations
	@echo "Running migrations..."
	docker compose exec app php artisan migrate --force
	@echo "✓ Migrations complete"

migrate-fresh: ## Fresh migration (WARNING: deletes all data)
	@echo "Running fresh migration..."
	docker compose exec app php artisan migrate:fresh --force
	@echo "✓ Fresh migration complete"

seed: ## Seed database
	@echo "Seeding database..."
	docker compose exec app php artisan db:seed --force
	@echo "✓ Database seeded"

optimize: ## Optimize application
	@echo "Optimizing application..."
	docker compose exec app php artisan optimize
	@echo "✓ Optimization complete"

cache-clear: ## Clear all caches
	@echo "Clearing caches..."
	docker compose exec app php artisan optimize:clear
	@echo "✓ Caches cleared"

test: ## Run tests
	docker compose exec app php artisan test

install: setup build up migrate seed optimize ## Complete installation
	@echo ""
	@echo "╔════════════════════════════════════════════╗"
	@echo "║   TCMS Installation Complete! 🎉           ║"
	@echo "╚════════════════════════════════════════════╝"
	@echo ""
	@echo "Access your application:"
	@echo "  URL: http://localhost:8080"
	@echo ""
	@echo "Default credentials:"
	@echo "  Username: admin"
	@echo "  Password: Admin@123"
	@echo ""
	@echo "⚠️  Change default passwords immediately!"
	@echo ""

status: ## Show container status
	docker compose ps

clean: ## Remove containers and volumes (WARNING: deletes all data)
	@echo "⚠️  This will delete all containers and data!"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		docker compose down -v; \
		echo "✓ Cleanup complete"; \
	fi

backup-db: ## Backup database
	@echo "Backing up database..."
	@mkdir -p backups
	docker compose exec -T db mysqldump -u tcms_user -p${DB_PASSWORD:-tcms_password} ${DB_DATABASE:-TAS} > backups/backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo "✓ Database backed up to backups/"

restore-db: ## Restore database (usage: make restore-db FILE=backup.sql)
	@if [ -z "$(FILE)" ]; then \
		echo "Error: Please specify FILE=backup.sql"; \
		exit 1; \
	fi
	@echo "Restoring database from $(FILE)..."
	docker compose exec -T db mysql -u tcms_user -p${DB_PASSWORD:-tcms_password} ${DB_DATABASE:-TAS} < $(FILE)
	@echo "✓ Database restored"

health: ## Check health of all services
	@echo "Checking service health..."
	@docker compose exec app php artisan tinker --execute="echo 'App: OK\n';" 2>/dev/null || echo "App: FAILED"
	@docker compose exec db mysqladmin ping -h localhost --silent && echo "Database: OK" || echo "Database: FAILED"
	@docker compose exec redis redis-cli ping | grep -q PONG && echo "Redis: OK" || echo "Redis: FAILED"
