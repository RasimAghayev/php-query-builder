# Repository audit

Performed 2026-11-03 (Phase 2). Backup: `projects/info/backups/php-query-builder.git` (`git clone --mirror`). Working clone: `projects/php-query-builder/`.

## How each finding below was verified

All verification ran inside ephemeral Docker containers (no local PHP/Composer toolchain on this host, confirmed via `php -v` / `composer --version` both returning "command not found") — `composer:2` for `composer install`, `php:8.3-cli` for running `vendor/bin/phpunit` and `vendor/bin/phpstan`, matching this repo's own CI (`shivammathur/setup-php@v2` with `php-version: '8.3'`). Nothing was installed or run on the host itself.

```bash
docker run --rm -v "<repo>:/app" -w /app composer:2 composer install --no-interaction
docker run --rm -v "<repo>:/app" -w /app php:8.3-cli php vendor/bin/phpunit --testdox
docker run --rm -v "<repo>:/app" -w /app php:8.3-cli php vendor/bin/phpcs src/ tests/
docker run --rm -v "<repo>:/app" -w /app php:8.3-cli php vendor/bin/phpstan analyse
docker run --rm -v "<repo>:/app" -w /app composer:2 composer audit
```

## Key finding — the library's core feature (WHERE clauses) is completely broken

Running the repo's own existing test suite for real (not assuming it passes because a test file exists) gives:

```
Tests: 13, Assertions: 3, Errors: 10, PHPUnit Deprecations: 1.
```

**10 of 13 tests fail** with `TypeError: Cannot access offset of type string on string` at `src/Core/QueryBuilder.inc:400`/`:399`. Root cause: every `where()`/`orWhere()`/`whereIn()`/`whereNotIn()`/`whereLike()`/`whereNotLike()`/`whereBetween()`/`whereNotBetween()` call appends a **plain string** (e.g. `"status = 1"`) to `$this->wheres[]`, but `buildBaseQuery()` (and the separate, unused-in-practice `aggregate()` method) iterate `$wheres` expecting **associative arrays** with `['column']`/`['operator']`/`['value']` keys. Reading a non-numeric string key off a PHP string value is a fatal `TypeError` on PHP 8+. Only the two tests with zero `where*()` calls, plus the aggregation-only test (which also has no `where*()` calls), pass.

**This means the library's one stated purpose — "SQL sorğularının dinamik qurulması" with WHERE/OR WHERE/IN/LIKE/BETWEEN support, per the README — does not work at all beyond a bare `SELECT ... FROM table` with no conditions.** Every README usage example that includes a `where()` call would crash if actually run.

**This is not new.** `git log --oneline -- src/Core/QueryBuilder.inc` shows exactly one commit, `5f01ec5 fix: all`, which is where the file reached its current (broken) shape — the entire history of the class as it exists today is one commit. `gh run list` confirms CI has failed on **every single run on `master` since 2025-10-19** (the PR that first merged this code), including every dependency-bot PR opened since — the 4 currently-open Renovate PRs (`#11`, `#21`, `#22`, `#23`) are all red for this same pre-existing reason, not because of their own (lockfile-only) content. **CI has never once been green on this repo.**

## Second, independent finding — static analysis has also never passed

`composer analyse` (PHPStan, level 6, one of the repo's own three CI gates) reports **22 errors**, all "Call to an undefined method `QueryStaticBuilderInterface::where()`" (or `::sum()`) — every fluent chain in both `src/index.php` and `tests/QueryBuilderTest.php` calls a method from one interface (e.g. `where()` from `QueryWhereBuilderInterface`) on a value typed as a different interface (`table()` returns `QueryStaticBuilderInterface`, which doesn't declare `where()`). The concrete class implements all four interfaces so this works at runtime, but PHPStan only sees the declared return type at each step — see `docs/ARCHITECTURE.md` and `docs/TECHNICAL_DEBT.md` #2.

## Third finding — duplicate, redundant CI workflows

`.github/workflows/ci.yml` ("CI": lint → analyse → test) and `.github/workflows/php.yml` ("PHP CI/CD Pipeline": test → a no-op "Deploy" echo step gated on `master`) both trigger on push/PR to `master`. They overlap almost entirely and both have been failing in lockstep since the same original merge. See `docs/TECHNICAL_DEBT.md` #3.

## Other observations

- `composer audit`: **0 vulnerabilities** (no runtime dependencies at all; dev deps clean).
- `.gitignore` is unmodified Laravel-framework boilerplate (`bootstrap/compiled.php`, `Homestead.yaml`, `public/storage`, etc.) — none of those paths exist in this repo. Harmless, cosmetic.
- `.phpunit.result.cache` is tracked in git despite being listed in `.gitignore` — likely added once with `git add -f` or before the ignore rule existed. Running the suite locally regenerates it as a tracked-file diff; reverted here (`git checkout -- .phpunit.result.cache`) to keep the working tree clean, not committed as part of this audit.
- Default branch is `master`, not `main` — same Gate #4 candidate as the other repos audited in this session.
- No secrets found; no `.env` or credentials file of any kind exists in this repo (it has no database connection to configure).
- Repo size and shape confirm `REPOS_INDEX.md`'s `small-candidate` classification: 49KB, one real class, one test file, no runtime dependencies. See `docs/IMPROVEMENT_ROADMAP.md` for the classification recommendation.

## Open PRs (untriaged — Phase 4 candidate)

| PR | Title | Why currently red |
|---|---|---|
| #11 | phpstan/phpstan → v2 (dev, lockfile) | Base branch's own PHPStan run already fails (22 pre-existing errors) |
| #21 | phpunit/phpunit → v12.5.43 (dev, lockfile) | Base branch's own PHPUnit run already fails (10 pre-existing errors) |
| #22 | phpunit/phpunit → v13 (dev, major) | Same |
| #23 | squizlabs/php_codesniffer → v4 (dev, major) | Likely unaffected by the two bugs above but still runs the full red pipeline |

Each will need individual verification (does the PR's own change introduce anything new, on top of the pre-existing failures) before any merge decision, per this audit's standing practice of never batch-merging without per-PR verification.
