rnpm: ## Release the package and publish on npm -- DEPRECATED
	release-it --dry-run

publish: # publish on npm publish package on NPM.
	npm publish

test-front:
	yarn test


test-back:
	vendor/bin/phpunit --testdox --colors=always