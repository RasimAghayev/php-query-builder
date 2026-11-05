COMPOSE := docker compose

.PHONY: build test verify up run down logs shell lint format audit clean

## Build every image (runtime + test) without starting anything.
build:
	$(COMPOSE) build

## Run the test stage (lint -> static analysis -> phpunit -> composer audit).
## Fails (non-zero exit) if any gate fails — this is the behavior contract.
test:
	$(COMPOSE) build test
	$(COMPOSE) run --rm test

## Build + test. `make up` depends on this so nothing starts on a red build.
verify: build test

## Start the app stack in the background. Never starts without a green verify.
up: verify
	$(COMPOSE) up -d app

## Run the demo app in the foreground (Ctrl+C to stop).
run: verify
	$(COMPOSE) up app

down:
	$(COMPOSE) down

logs:
	$(COMPOSE) logs -f app

## Open a shell inside the runtime image for debugging.
shell:
	$(COMPOSE) run --rm --entrypoint sh app

## PHP_CodeSniffer (PSR12), run inside the test image.
lint:
	$(COMPOSE) build test
	$(COMPOSE) run --rm --entrypoint "composer lint" test

## phpcbf auto-fix, run inside the test image.
format:
	$(COMPOSE) build test
	$(COMPOSE) run --rm --entrypoint "vendor/bin/phpcbf src tests" test

## composer audit (known-vulnerability check), run inside the test image.
audit:
	$(COMPOSE) build test
	$(COMPOSE) run --rm --entrypoint "composer audit" test

## This repo has no DB/volumes to lose — `down` with no flag never deletes
## anything. Kept as a no-arg-confirm no-op for Makefile-contract consistency
## across repos; add CONFIRM=1 before adding any real volume removal here.
clean:
	$(COMPOSE) down --rmi local
