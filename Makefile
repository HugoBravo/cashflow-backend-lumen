# Makefile
install:
	@composer install --ignore-platform-reqs
update:
	@composer update --ignore-platform-reqs
dev:
	@php -S localhost:8000 -t public/
