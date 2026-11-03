.PHONY: run test up

run:
	php -S localhost:3000 -t src

test:
	composer lint && composer analyse && composer test

up:
	docker compose up --build app
