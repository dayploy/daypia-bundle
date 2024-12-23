#!make
.PHONY: help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

composer-install: ## composer install
	docker run --rm -it  -w="/srv/app" --volume $$(pwd):/srv/app prooph/composer:8.2 install

composer-update: ## update composer
	docker run --rm -it  -w="/srv/app" --volume $$(pwd):/srv/app prooph/composer:8.2 update

phpcs: ## Run phpcs
	${DOCKER_CMD} ./vendor/bin/php-cs-fixer fix $(target)
