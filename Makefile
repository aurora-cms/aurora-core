.PHONY: setup qa cs stan test stan-baseline

setup:
	composer install
	php bin/console cache:clear
	mkdir -p var/phpstan
	php bin/console debug:container --format=xml > var/phpstan/container.xml

cs:
	PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix -v

stan:
	vendor/bin/phpstan analyse --memory-limit=1G

test:
	vendor/bin/phpunit --colors=always

qa: cs stan test
stan-baseline:
	vendor/bin/phpstan analyse --generate-baseline=phpstan-baseline.neon --memory-limit=1G
