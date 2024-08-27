include .env

DOCKER_COMPOSE ?= $(DOCKER_COMPOSE)
DOCKER_CMD ?= exec

## default        : Run `make` without parameters to create the dist build from scratch.
default: .env up-dist
	$(DOCKER_COMPOSE) $(DOCKER_CMD) dist bash -c "drush uli"

## help		: Print commands help.
.PHONY: help
help : Makefile
	@sed -n 's/^##//p' $<

# Files to make
.env:
	cp .env.dist .env

docker-compose.override.yml:
	cp docker-compose.yml docker-compose.override.yml

# Build tasks for development.
## build		: Build the development environment.
.PHONY: build build/composer
build: .env build/composer
build/composer:
	@echo "Building $(PROJECT_NAME) project development environment..."
	$(DOCKER_COMPOSE) $(DOCKER_CMD) dev bash -c "composer install"

# Install design system assets
## install-design-system	: Copy design system assets in the designated directory.
.PHONY: install-design-system
install-design-system:
	rm -rf ./modules/ilo_base_theme_companion/dist
	mkdir -p ./modules/ilo_base_theme_companion/dist
	mkdir -p ./modules/ilo_base_theme_companion/dist/fonts
	cp -r ./node_modules/@ilo-org/twig/dist/components ./modules/ilo_base_theme_companion/dist
	cp ./node_modules/@ilo-org/styles/css/index.css ./modules/ilo_base_theme_companion/dist
	cp ./node_modules/@ilo-org/styles/css/global.css ./modules/ilo_base_theme_companion/dist
	cp -r ./node_modules/@ilo-org/fonts/assets ./modules/ilo_base_theme_companion/dist/fonts
	cp -r ./node_modules/@ilo-org/fonts/font-css ./modules/ilo_base_theme_companion/dist/fonts

# Install test site.
## build		: Build the development environment.
.PHONY: install
install: build
	@echo "Installing $(PROJECT_NAME)..."
	$(DOCKER_COMPOSE) $(DOCKER_CMD) dev bash -c "./vendor/bin/run drupal:site-install"
	@$(DOCKER_COMPOSE) up -d --remove-orphans node
	@$(MAKE) --no-print-directory theme-install
	$(DOCKER_COMPOSE) $(DOCKER_CMD) dev bash -c "drush uli"

# Build tasks for development.
## build-dev	: Build the Docker image.
.PHONY: build-dev
build-dev:
	@echo "Building $(PROJECT_NAME) dev Docker image..."
	$(DOCKER_COMPOSE) build --no-cache dev
	$(DOCKER_COMPOSE) up -d

# Build tasks for development.
## build-dist	: Build the Docker image.
.PHONY: build-dist
build-dist:
	@echo "Building $(PROJECT_NAME) dist Docker image..."
	$(DOCKER_COMPOSE) build --no-cache dist
	$(DOCKER_COMPOSE) up -d

.PHONY: release
release: docker-compose.override.yml up-dev build install-design-system
	@echo Building release artifact...
	$(DOCKER_COMPOSE) exec -T dev ./vendor/bin/run release:ca --tag=$(RELEASE_TAG) --zip

## up		: Start up containers.
.PHONY: up
up: .env
	@echo "Starting up containers for $(PROJECT_NAME)..."
	@$(DOCKER_COMPOSE) up -d --remove-orphans

## up-dev		: Start dev container.
.PHONY: up-dev
up-dev: .env
	@echo "Starting dev container for $(PROJECT_NAME)..."
	@$(DOCKER_COMPOSE) up -d --remove-orphans dev

## up-dist	: Start dist container.
.PHONY: up-dist
up-dist: .env
	@echo "Starting dist container for $(PROJECT_NAME)..."
	@$(DOCKER_COMPOSE) up -d --remove-orphans dist

## down		: Stop containers.
.PHONY: down
down: stop

## start		: Start containers without updating.
.PHONY: start
start:
	@echo "Starting containers for $(PROJECT_NAME) from where you left off..."
	@$(DOCKER_COMPOSE) start

.PHONY: stop
stop:
	@echo "Stopping containers for $(PROJECT_NAME)..."
	@$(DOCKER_COMPOSE) stop

## pull		: Pull containers.
.PHONY: pull
pull:
	@echo "Pulling containers..."
	@$(DOCKER_COMPOSE) pull

## shell		: Access a container via shell.
##		  The container to be accessed must be passed as argument (without argument the 'dev' container will be accessed by default, if active)
.PHONY: shell
shell:
	docker exec -ti -e COLUMNS=$(shell tput cols) -e LINES=$(shell tput lines) $(shell docker ps --filter name='$(PROJECT_NAME)_$(or $(filter-out $@,$(MAKECMDGOALS)), 'dev')' --format "{{ .ID }}") bash

## shell-dev	: Access `dev` container via shell.
.PHONY: shell-dev
shell-dev:
	@$(MAKE) --no-print-directory shell dev

## shell-dist	: Access `dist` container via shell.
.PHONY: shell-dist
shell-dist:
	@$(MAKE) --no-print-directory shell dist

## shell-node	: Access `node` container via shell.
.PHONY: shell-node
shell-node:
	@$(MAKE) --no-print-directory shell node

## logs		: View containers logs.
##		  You can optionally pass an argument with the service name to limit logs
##		  logs dev	: View `dev` container logs.
##		  logs nginx dev	: View `nginx` and `dev` containers logs.
.PHONY: logs
logs:
	@$(DOCKER_COMPOSE) logs -f $(filter-out $@,$(MAKECMDGOALS))

# Shortcuts for developers not using docker-relay. See https://github.com/bircher/docker-relay

## composer	: Executes `composer` command.
##		  To use "--flag" arguments include them in quotation marks.
##		  For example: make composer "update drupal/core --with-dependencies"
.PHONY: composer
composer:
	$(DOCKER_COMPOSE) $(DOCKER_CMD) dev composer $(filter-out $@,$(MAKECMDGOALS))

## drush		: Executes `drush` command.
##		  To use "--flag" arguments include them in quotation marks.
##		  For example: make drush "watchdog:show --type=cron"
.PHONY: drush
drush:
	$(DOCKER_COMPOSE) $(DOCKER_CMD) dev vendor/bin/drush $(filter-out $@,$(MAKECMDGOALS))

## fix-perms	: Fix files permissions
.PHONY: fix-perms
fix-perms:
	$(DOCKER_COMPOSE) $(DOCKER_CMD) dev vendor/bin/run drupal:fix-perms

## cr		: Clears the Drupal cache.
.PHONY: cr
cr:
	@$(MAKE) --no-print-directory drush cache-rebuild

## phpunit	: Runs phpunit tests.
.PHONY: phpunit
phpunit:
	$(DOCKER_COMPOSE) $(DOCKER_CMD) dev bash -c "vendor/bin/phpunit" $(filter-out $@,$(MAKECMDGOALS))

## twig-debug-on	: Enable Twig debug.
.PHONY: twig-debug-on
twig-debug-on:
	@echo "Enabling Twig debug for $(PROJECT_NAME)..."
	$(DOCKER_COMPOSE) $(DOCKER_CMD) dev drush twig:debug on
	$(DOCKER_COMPOSE) $(DOCKER_CMD) dev drush state:set disable_rendered_output_cache_bins 1 --input-format=integer
	@$(MAKE) --no-print-directory cr

## twig-debug-off	: Disable Twig debug.
.PHONY: twig-debug-off
twig-debug-off:
	@echo "Disabling Twig debug for $(PROJECT_NAME)..."
	$(DOCKER_COMPOSE) $(DOCKER_CMD) dev drush twig:debug off
	$(DOCKER_COMPOSE) $(DOCKER_CMD) dev drush state:set disable_rendered_output_cache_bins 0 --input-format=integer
	@$(MAKE) --no-print-directory cr

## theme-install	: Install all tools for theme development.
.PHONY: theme-install
theme-install:
	@echo "Install all tools for $(PROJECT_NAME) theme development..."
	$(DOCKER_COMPOSE) $(DOCKER_CMD) node npm install
	@$(MAKE) --no-print-directory cr

# https://stackoverflow.com/a/6273809/1826109
%:
	@:
