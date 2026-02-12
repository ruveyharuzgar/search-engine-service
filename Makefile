.PHONY: help start stop restart logs install migrate sync test clean

help: ## Yardım menüsünü gösterir
	@echo "Kullanılabilir komutlar:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

start: ## Tüm servisleri başlatır
	@./start.sh

stop: ## Tüm servisleri durdurur
	@./stop.sh

restart: stop start ## Servisleri yeniden başlatır

logs: ## Logları gösterir
	@docker-compose logs -f

logs-php: ## PHP loglarını gösterir
	@docker-compose logs -f php

logs-nginx: ## Nginx loglarını gösterir
	@docker-compose logs -f nginx

install: ## Composer bağımlılıklarını yükler
	@docker-compose exec php composer install

migrate: ## Veritabanı migration'larını çalıştırır
	@docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction

sync: ## Provider'lardan verileri senkronize eder
	@curl -X POST http://localhost:8080/api/sync

test: ## Testleri çalıştırır
	@docker-compose exec php php bin/phpunit

cache-clear: ## Cache'i temizler
	@docker-compose exec php php bin/console cache:clear

shell: ## PHP container'a shell ile bağlanır
	@docker-compose exec php bash

db-shell: ## MySQL'e bağlanır
	@docker-compose exec mysql mysql -uroot -proot search_engine

redis-cli: ## Redis CLI'ye bağlanır
	@docker-compose exec redis redis-cli

clean: ## Tüm container'ları ve volume'ları siler
	@docker-compose down -v
	@cd mock-apis && docker-compose down -v

rebuild: clean ## Tüm container'ları yeniden build eder
	@docker-compose up -d --build
	@cd mock-apis && docker-compose up -d --build
