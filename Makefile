# =============================================================================
# Makefile - Commandes Utiles pour Développement
# Configuration Simplifiée: Symfony + MySQL + PhpMyAdmin
# =============================================================================

.PHONY: help build up down logs bash restart install db-migrate db-seed clean db-create

# Variables
DC = docker-compose
DC_DEV = $(DC) -f docker-compose.yml -f docker-compose.override.yml
APP = $(DC_DEV) exec app
CONSOLE = $(APP) symfony console

HELP_COLOR = \033[36m
RESET = \033[0m

help: ## Affiche cette aide
	@echo "$(HELP_COLOR)=== Commandes Disponibles ===$(RESET)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(HELP_COLOR)%-20s$(RESET) %s\n", $$1, $$2}'

# =============================================================================
# Docker
# =============================================================================

build: ## Construit les images Docker
	$(DC_DEV) build

up: ## Démarre les containers (développement)
	$(DC_DEV) up -d
	@echo "✅ Containers démarrés"
	@echo "🌐 App Symfony: http://localhost:8000"
	@echo "📊 PhpMyAdmin: http://localhost:8080"
	@echo "🗄️  MySQL: localhost:3306"

down: ## Arrête les containers
	$(DC_DEV) down

restart: ## Redémarre les containers
	$(DC_DEV) restart

logs: ## Affiche les logs de tous les services
	$(DC_DEV) logs -f

logs-app: ## Affiche les logs Symfony
	$(DC_DEV) logs -f app

logs-db: ## Affiche les logs Database
	$(DC_DEV) logs -f database

logs-phpmyadmin: ## Affiche les logs PhpMyAdmin
	$(DC_DEV) logs -f phpmyadmin

ps: ## Liste les containers en cours
	$(DC_DEV) ps

# =============================================================================
# Shell & Bash
# =============================================================================

bash: ## Entre dans le container Symfony
	$(APP) bash

db-bash: ## Entre dans le container Database
	$(DC_DEV) exec database bash

mysql: ## Entre dans MySQL CLI
	$(DC_DEV) exec database mysql -u ecole -p ecole ecole_dev

# =============================================================================
# Installation & Setup
# =============================================================================

install: build up composer-install npm-install db-migrate ## Installation complète (build + up + dépendances + DB)

composer-install: ## Installe les dépendances Composer
	$(APP) composer install

composer-update: ## Met à jour les dépendances Composer
	$(APP) composer update

npm-install: ## Installe les dépendances npm
	$(APP) npm install

npm-build: ## Compile les assets (prod)
	$(APP) npm run build

npm-dev: ## Compile les assets (dev avec watch)
	$(APP) npm run dev -- --watch

# =============================================================================
# Database
# =============================================================================

db-create: ## Crée la base de données
	$(CONSOLE) doctrine:database:create --if-not-exists

db-drop: ## Supprime la base de données (attention!)
	$(CONSOLE) doctrine:database:drop --force

db-migrate: ## Exécute les migrations
	$(CONSOLE) doctrine:migrations:migrate --no-interaction

db-migrate-dry: ## Affiche les migrations à exécuter (sans les appliquer)
	$(CONSOLE) doctrine:migrations:migrate --dry-run

db-seed: ## Charge les fixtures (données de test)
	$(CONSOLE) doctrine:fixtures:load --no-interaction

db-reset: db-drop db-create db-migrate db-seed ## Réinitialise la BD complètement

db-generate: ## Génère une migration
	@read -p "Nom de la migration: " name; \
	$(CONSOLE) doctrine:migrations:generate --name $$name

# =============================================================================
# Tests
# =============================================================================

test: ## Exécute tous les tests
	$(APP) php bin/phpunit

test-unit: ## Exécute les tests unitaires
	$(APP) php bin/phpunit tests/Unit

test-functional: ## Exécute les tests fonctionnels
	$(APP) php bin/phpunit tests/Functional

test-coverage: ## Exécute les tests avec coverage
	$(APP) php bin/phpunit --coverage-html=var/coverage

# =============================================================================
# Code Quality
# =============================================================================

lint: ## Vérifie la syntaxe PHP et YAML
	$(CONSOLE) lint:yaml config
	$(APP) php -l src

phpstan: ## Exécute PHPStan (analyse statique)
	$(APP) vendor/bin/phpstan analyse

cs-fix: ## Répare les violations de style (si PHP-CS-Fixer est installé)
	$(APP) vendor/bin/php-cs-fixer fix src --rules=@Symfony

cs-check: ## Vérifie les violations de style
	$(APP) vendor/bin/php-cs-fixer fix src --rules=@Symfony --dry-run

# =============================================================================
# Utilitaires
# =============================================================================

clean: ## Nettoie les fichiers générés (cache, logs)
	$(APP) rm -rf var/cache/* var/log/*
	@echo "✅ Cache et logs nettoyés"

clear-cache: ## Vide le cache Symfony
	$(CONSOLE) cache:clear

clear-all: ## Vide tous les caches
	$(CONSOLE) cache:clear
	$(CONSOLE) cache:warmup

# =============================================================================
# Symfony & Application
# =============================================================================

cache-clear: ## Vide le cache
	$(CONSOLE) cache:clear

cache-warmup: ## Prépare le cache
	$(CONSOLE) cache:warmup

routes: ## Liste toutes les routes disponibles
	$(CONSOLE) debug:router

services: ## Liste tous les services disponibles
	$(CONSOLE) debug:container

config-dump: ## Affiche la configuration finale
	$(CONSOLE) config:dump framework

# =============================================================================
# Nettoyage
# =============================================================================

clean: ## Nettoie les fichiers temporaires
	rm -rf var/cache/*
	rm -rf var/log/*
	rm -rf public/build/*

clean-deep: down clean ## Arrête les containers et nettoie tout

# =============================================================================
# Production
# =============================================================================

build-prod: ## Construit les images pour production
	docker-compose build

up-prod: ## Démarre les containers (production)
	docker-compose up -d

down-prod: ## Arrête les containers (production)
	docker-compose down

# =============================================================================
# Utils
# =============================================================================

health: ## Vérifie l'état des services
	@echo "Database:"
	@$(DC_DEV) exec database mysqladmin ping -h localhost || echo "❌ Database down"
	@echo "\nPHP-FPM:"
	@$(DC_DEV) exec php-fpm test -f /var/run/php-fpm.pid && echo "✅ PHP-FPM running" || echo "❌ PHP-FPM down"
	@echo "\nNginx:"
	@$(DC_DEV) exec nginx curl -f http://localhost/health >/dev/null 2>&1 && echo "✅ Nginx healthy" || echo "❌ Nginx down"

info: ## Affiche les infos du projet
	@echo "Project: Écoles - Gestion d'Agenda"
	@echo "PHP: 8.0.1"
	@echo "Symfony: 6.4"
	@echo "Database: MySQL 8.0"
	@echo ""
	@echo "URLs:"
	@echo "  App:     http://localhost"
	@echo "  Mailhog: http://localhost:8025"
	@echo "  Redis:   localhost:6379"

.DEFAULT_GOAL := help
