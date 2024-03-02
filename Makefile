rnpm: ## Release the package and publish on npm
	release-it --dry-run


test-front:
	yarn test


test-back:
	vendor/bin/phpunit --testdox --colors=always