app = app
exec = docker-compose exec $(execArgs)
exec-app = $(exec) $(app)

.PHONY: db-create
db-create:
	$(exec-app) vendor/bin/phinx --configuration=database/phinx.php create $(args)

.PHONY: db-migrate
db-migrate:
	$(exec-app) vendor/bin/phinx --configuration=database/phinx.php migrate

.PHONY: db-rollback
db-rollback:
	$(exec-app) vendor/bin/phinx --configuration=database/phinx.php rollback

.PHONY: db-seed
db-seed:
	$(exec-app) vendor/bin/phinx --configuration=database/phinx.php seed:run

.PHONY: phpcs
phpcs:
	$(exec-app) vendor/bin/phpcs --standard=ruleset.xml --extensions=php $(or ${args},src tests database)

.PHONY: phpcs-fix
phpcs-fix:
	$(exec-app) vendor/bin/phpcbf --standard=ruleset.xml --extensions=php $(or ${args},src tests database)

.PHONY: phpstan
phpstan:
	$(exec-app) vendor/bin/phpstan analyse --memory-limit=8G $(or ${args},src tests database)

wait-db:
	docker compose exec -T db bash -lc 'until pg_isready -U $$POSTGRES_USER -d $$POSTGRES_DB >/dev/null 2>&1; do sleep 1; done'

.PHONY: reset
reset:
	docker compose down -v
	docker compose up -d --build
	docker compose exec app composer install --no-interaction
	make wait-db
	make db-migrate
	make db-seed
