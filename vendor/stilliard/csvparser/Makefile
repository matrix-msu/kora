
unit-test:
	phpunit .

code-coverage:
	phpunit --coverage-html tmp/code-coverage-report test
	php -S localhost:8977 -t tmp/code-coverage-report
