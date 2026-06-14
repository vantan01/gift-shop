# Gift Shop — Developer Commands

.PHONY: help setup up down fresh seed test

help:
	@echo "Available commands:"
	@echo "  make setup    - First time setup"
	@echo "  make up       - Start all services"
	@echo "  make down     - Stop all services"
	@echo "  make fresh    - Fresh migrate + seed"
	@echo "  make seed     - Run seeders"
	@echo "  make test     - Run test suite"

setup:
	composer install
	npm install
	cp .env.example .env
	php artisan key:generate
	docker compose up -d
	php artisan migrate --seed

up:
	docker compose up -d
	php artisan serve &
	npm run dev

down:
	docker compose down

fresh:
	php artisan migrate:fresh --seed

seed:
	php artisan db:seed

test:
	vendor/bin/pest

test-verbose:
	vendor/bin/pest --verbose