test: phpunit phpcs

.PHONY: test phpunit phpcs

pretest:
		if [ ! -d vendor ] || [ ! -f composer.lock ]; then composer install; else echo "Already have dependencies"; fi

phpunit: pretest
		mkdir -p build
		/usr/bin/php vendor/bin/phpunit --coverage-text --coverage-clover=build/coverage.clover --coverage-html=build

phpunit-ci: pretest
		mkdir -p build
		vendor/bin/phpunit --coverage-text --coverage-clover=build/coverage.clover

ifndef STRICT
STRICT = 0
endif

phpcs: pretest
		vendor/bin/phpcs --standard=PSR1,PSR2 -n src tests/ examples/

phpcbf: pretest
		vendor/bin/phpcbf --standard=PSR1,PSR2 -n src tests/ examples/

ocular:
		wget https://scrutinizer-ci.com/ocular.phar

ifdef OCULAR_TOKEN
scrutinizer: ocular
		@php ocular.phar code-coverage:upload --format=php-clover build/coverage.clover --access-token=$(OCULAR_TOKEN);
else
scrutinizer: ocular
		php ocular.phar code-coverage:upload --format=php-clover build/coverage.clover;
endif

clean: clean-env clean-deps

clean-env:
		rm -rf ocular.phar
		rm -rf build

clean-deps:
		rm -rf vendor/

