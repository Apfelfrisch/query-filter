.PHONY: it
it: coding-standards tests static-analysis infection

.PHONY: coding-standards
coding-standards: vendor
	vendor/bin/php-cs-fixer fix --diff

.PHONY: static-analysis
static-analysis: vendor
	vendor/bin/phpstan

.PHONY: tests
tests: vendor
	vendor/bin/phpunit

.PHONY: infection
infection: vendor
	vendor/bin/infection
