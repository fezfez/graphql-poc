.PHONY: php


SUPPORTED_COMMANDS := php composer cube43 test test-and-bdd coverage cs static cbf test-module coverage-module static-baseline run-coverage checkmodule
SUPPORTS_MAKE_ARGS := $(findstring $(firstword $(MAKECMDGOALS)), $(SUPPORTED_COMMANDS))
ifneq "$(SUPPORTS_MAKE_ARGS)" ""
  COMMAND_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  COMMAND_ARGS := $(subst :,\:,$(COMMAND_ARGS))
  $(eval $(COMMAND_ARGS):;@:)
endif

DOCKER_PHP := docker-compose exec php

ifeq (, $(shell which docker-compose))
	BASE :=
else
	BASE := $(DOCKER_PHP)
endif

# Container management

dup:
    ifeq ($(BASE), $(DOCKER_PHP))
		docker-compose up -d
    endif

kill:
	docker-compose rm -f -s

login:
	docker-compose exec php sh

install: dup
	$(BASE) composer install

update: dup
	$(BASE) composer update

composer: dup
	$(BASE) composer $(COMMAND_ARGS)

composer-valid: dup
	$(BASE) composer validate

run-test:
	$(BASE) vendor/bin/phpunit

run-infection:
	$(BASE) vendor/bin/infection

phpstan:
	$(BASE)  ./vendor/bin/phpstan

psalm:
	$(BASE)  ./vendor/bin/psalm

cs:
	$(BASE) php -d memory_limit=-1 ./vendor/bin/phpcs $(COMMAND_ARGS)

cbf:
	$(BASE) php -d memory_limit=-1 ./vendor/bin/phpcbf $(COMMAND_ARGS)