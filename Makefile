# Makefile for php-generator-chunk

# --- Configuration ---

# Service name in docker-compose.yml
APP_SERVICE_NAME=app

# Define the base command for running tasks inside the container
DOCKER_RUN = docker compose run -T --rm $(APP_SERVICE_NAME)

# Phony targets declaration (prevents conflicts with filenames)
.PHONY: help setup install update install-hooks grumphp test cs-check cs-fix build rebuild

# --- Setup & Installation ---

# Default target when running 'make'
default: help

# Complete project setup: Installs dependencies, builds Docker image, sets up Git hooks
setup: install build install-hooks ## Run full project setup (install deps, build docker, install hooks)
	@echo "Setup complete! You're ready to go."

# Install PHP dependencies using Composer inside the container
install: ## Install project dependencies using Composer
	@echo "Installing dependencies via Composer..."
	$(DOCKER_RUN) composer install
	@echo "Dependencies installed."

# Update PHP dependencies using Composer inside the container
update: ## Update project dependencies using Composer
	@echo "Updating dependencies via Composer..."
	$(DOCKER_RUN) composer update
	@echo "Dependencies updated."

# Installs Git hooks
install-hooks: ## Install Git hooks for pre-commit checks
	@echo "Installing git hooks..."
	git config core.hooksPath .githooks
	chmod +x .githooks/pre-commit
	@echo "Git hooks installed."

# --- Code Quality & Testing ---

# Run GrumPHP checks inside the container
grumphp: ## Run GrumPHP checks
	$(DOCKER_RUN) ./vendor/bin/grumphp run

# Run PHPUnit tests inside the container
test: ## Run unit tests (PHPUnit)
	@echo "Running tests..."
	$(DOCKER_RUN) ./vendor/bin/phpunit
	@echo "Tests finished."

# Check code style issues using PHP CS Fixer inside the container
cs-check: ## Check code style without modifying files (for CI)
	@echo "Checking code style (PHP CS Fixer --dry-run)..."
	$(DOCKER_RUN) ./vendor/bin/php-cs-fixer fix --dry-run --diff
	@echo "Code style check finished."

# Fix code style issues using PHP CS Fixer inside the container
cs-fix: ## Fix code style issues (PHP CS Fixer)
	@echo "Fixing code style..."
	$(DOCKER_RUN) ./vendor/bin/php-cs-fixer fix
	@echo "Code style fixed."

# --- Docker Management ---

# Build Docker images if they don't exist (uses cache)
build: ## Build Docker image(s) using cache if available
	@echo "Building Docker image for service [$(APP_SERVICE_NAME)]..."
	docker compose build $(APP_SERVICE_NAME)

# Force rebuild Docker images without using cache (use after Dockerfile changes)
rebuild: ## Force rebuild Docker image(s) without using cache
	@echo "Rebuilding Docker image for service [$(APP_SERVICE_NAME)] without cache..."
	docker compose build --no-cache $(APP_SERVICE_NAME)

# --- Help ---

# Displays available targets with descriptions
help: ## Display this help screen
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'
