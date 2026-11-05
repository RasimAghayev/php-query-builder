# Container baseline — behavior contract (Phase 5)

Established 2026-11-05 by the `container-engineer` agent (pilot run for the
PHP/Composer stack — no prior Phase-5 skill existed yet). Everything below
was produced by actually building and running the images in Docker on this
host; no command result is assumed. This file is the regression baseline
Phase 6+ must not break without a documented, justified reason.

## What this repo is

A standalone PHP library (zero runtime dependencies) that builds SQL query
strings fluently; it never executes SQL (no PDO/mysqli anywhere in `src/`).
`src/index.php` is a demo script that prints example `toSql()` output. No
database, cache, queue, or other external service — a single `app` + `test`
Docker Compose stack covers the whole repo.

## Build

```
$ make build
docker compose build
 Image php-query-builder-p5-app Built
 Image php-query-builder-p5-test Built
```
Exit code: 0.

## Test stage (the behavior contract)

`make test` runs `docker compose build test && docker compose run --rm test`.
The `test` image's `CMD` is `composer lint && composer analyse && composer
test && composer audit` — any gate failing fails the whole stage (and
`make verify`) with a non-zero exit code.

Result, this run (full command: `make verify`, exit code **0**):

| Gate | Tool | Result |
|---|---|---|
| Lint | `phpcs` (`.phpcs.xml`, PSR12) | **Pass** — 0 errors, 0 warnings (no output = no violations) |
| Static analysis | `phpstan` (`phpstan.neon`, level 6) | **Pass** — 0 errors (`[OK] No errors`) |
| Unit tests | `phpunit` (`phpunit.xml`) | **Pass** — 18 tests, 18 assertions, 0 failures, 0 errors, 0 deprecations |
| Dependency audit | `composer audit` | **Pass** — "No security vulnerability advisories found." |

Full `phpunit` output:
```
PHPUnit 13.4.6 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.5.12
Configuration: /app/phpunit.xml

..................                                                18 / 18 (100%)

Time: 00:00.008, Memory: 20.00 MB

OK (18 tests, 18 assertions)
```

**Pitfall hit and fixed (local verification only, no repo content changed):**
the first `make verify` run failed `composer lint` with "End of line character
is invalid; expected \n but found \r\n" on 7 files (`src/Core/QueryBuilder.inc`,
the 4 `src/Core/Interface/*.inc` files, `src/index.php`,
`tests/QueryBuilderTest.php`). Root cause: this Windows host's
`core.autocrlf=true` rewrites LF blobs to CRLF on checkout; `git show
HEAD:<file>` confirmed every one of these files is stored as LF in git (the
repo itself is correct), so the CRLF only existed in this local working
copy, not in the committed content or in what a Linux CI runner would ever
see. Fixed by normalizing the 7 files back to LF in the worktree
(`sed -i 's/\r$//'`); `git diff --stat` afterward shows **zero** byte
difference on any of the 7 — confirming no real content changed, only the
local checkout artifact was removed. Re-ran `make verify`: green (see above).
No commit was needed for `src/`/`tests/` since their checked-in content was
already correct.

### Coverage

No coverage percentage is reported — `phpunit.xml` scopes `<source>` to
`src/`, but no coverage driver (Xdebug/PCOV) is installed in the `test`
stage (same state as before this phase; adding one for a single-class repo
where every public method already has a direct `phpunit` assertion was
judged not worth the extra PHP extension in the test image — this is a
restatement of the existing note in `docs/TEST_REPORT.md`, not a new
decision made here).

## API/CLI output snapshot (runtime demo)

`make up` (→ `make verify` then `docker compose up -d app`), then
`curl http://localhost:20030/`:

```
SELECT * FROM product;
SELECT id, name, price FROM product;
SELECT id, name, price FROM product WHERE status = 1;
SELECT id, name, price FROM product WHERE status = 1 OR quantity > 0;
SELECT id, name, price FROM product WHERE status = 1 OR (quantity > 0 AND amount > 0);
SELECT id, name, price FROM product WHERE status = 1 OR (quantity > 0 AND amount > 0) AND customer IN (13, 135, 168);
SELECT id, name, price FROM product WHERE status = 1 OR (quantity > 0 AND amount > 0) AND customer IN (13, 135, 168) AND payer NOT IN (13, 135, 168);
SELECT id, name, price FROM product WHERE status = 1 OR (quantity > 0 AND amount > 0) AND customer IN (13, 135, 168) AND payer NOT IN (13, 135, 168) AND name LIKE '%Ras%';
SELECT id, name, price FROM product WHERE status = 1 OR (quantity > 0 AND amount > 0) AND customer IN (13, 135, 168) AND payer NOT IN (13, 135, 168) AND name LIKE '%Ras%' AND name NOT LIKE '%es%';
SELECT id, name, price FROM product WHERE status = 1 OR (quantity > 0 AND amount > 0) AND customer IN (13, 135, 168) AND payer NOT IN (13, 135, 168) AND name LIKE '%Ras%' AND name NOT LIKE '%es%' AND date BETWEEN '2023-01-01' AND '2023-12-31';
SELECT id, name, price FROM product WHERE status = 1 OR (quantity > 0 AND amount > 0) AND customer IN (13, 135, 168) AND payer NOT IN (13, 135, 168) AND name LIKE '%Ras%' AND name NOT LIKE '%es%' AND date BETWEEN '2023-01-01' AND '2023-12-31' AND created_at NOT BETWEEN '2023-01-01' AND '2023-12-31';
SELECT id, name, price FROM product WHERE status = 1 OR (quantity > 0 AND amount > 0) AND customer IN (13, 135, 168) AND payer NOT IN (13, 135, 168) AND name LIKE '%Ras%' AND name NOT LIKE '%es%' AND date BETWEEN '2023-01-01' AND '2023-12-31' AND created_at NOT BETWEEN '2023-01-01' AND '2023-12-31' GROUP BY category, status HAVING amount > 100 ORDER BY amount DESC LIMIT 10 OFFSET 5;
SELECT *, SUM(quantity * amount) AS total_quantity, AVG(amount) AS avg_amount, COUNT(id) AS total_products FROM product;
```
(each line was followed by `<br/>` in the raw HTTP response; stripped here
for readability). Matches `README.md`'s documented example exactly. This is
the behavior contract's user-visible surface — any future phase that
changes this output without updating this file and the README is a
regression.

`docker compose ps` while running:
```
NAME                         STATUS                            PORTS
php-query-builder-p5-app-1   Up (health: starting → healthy)   0.0.0.0:20030->3000/tcp
```
Container user verified non-root: `docker exec ... whoami` → `app`.
Healthcheck verified: `docker inspect ... .State.Health` → `{"Status":"healthy", ...}`
within the configured `start-period`.

## Image

| Image | Tag | Size | Base |
|---|---|---|---|
| Runtime (`app`) | `php-query-builder-p5-app:latest` | **187 MB** | `php:8.5-cli-alpine@sha256:e899a75c775804e5466dbd9c96b31f8b6c6ae2ddb053a8466223a0c32992ff8e` (PHP 8.5.12, Alpine 3.24) |
| Test (`test`, never shipped) | `php-query-builder-p5-test:latest` | 937 MB | `php:8.5-cli@sha256:9236fd373d3bcda0659cf94838a744bd79791419292958963f8c6b34a0071554` + `apt`-installed `libzip-dev`/`unzip`/`zip` ext + dev Composer deps |

Base images are pinned by exact tag **and digest** (not `latest`, not just a
floating minor tag) in the Dockerfile, so a rebuild months from now uses the
exact same upstream bytes until this phase (or Phase 6) deliberately bumps
them.

## Image scan — `docker scout cves php-query-builder-p5-app:latest`

Run 2026-11-05. **41 vulnerabilities across 6 OS-level Alpine packages** —
all come from the Alpine 3.24 base layer, none from PHP application code or
Composer dependencies (those are covered separately by `composer audit`
inside the `test` stage, which reported zero advisories, see above).

| Package | Critical | High | Medium | Low | Unspecified |
|---|---|---|---|---|---|
| curl 8.22.0-r0 | 7 | 17 | 0 | 0 | 0 |
| brotli 1.2.0-r1 | 1 | 0 | 0 | 0 | 0 |
| libxml2 2.13.9-r2 | 0 | 1 | 5 | 5 | 0 |
| sqlite 3.53.4-r0 | 0 | 1 | 0 | 0 | 0 |
| tar 1.35-r5 | 0 | 0 | 3 | 0 | 0 |
| musl 1.2.6-r3 | 0 | 0 | 0 | 0 | 1 |
| **Total** | **8** | **19** | **8** | **5** | **1** |

`docker scout recommendations php-query-builder-p5-app:latest` confirms the
base image is **already the most current available tag** for this PHP/Alpine
combination ("This image version is up to date" / "There are no tag
recommendations at this time") — this is not a case of us picking a stale
tag; Alpine 3.24's own `curl`/`libxml2`/`sqlite`/`tar`/`musl` packages
mostly show `Fixed version: not fixed` upstream as of this scan date, i.e.
no newer Alpine package build exists yet to pull in.

**Decision (standing decision 1's documented-blocker path, highest
compatible version already in use):** not fixed in this phase — fixing OS
package CVEs with no upstream fix available is not something a Dockerfile
change can do, and re-scanning/base-image-swap evaluation (e.g. distroless,
a different distro) is a Phase 9 (hardening) / Phase 6 (upgrade) decision,
not Phase 5 (container baseline) scope. **Out-of-scope finding for Phase 9:**
re-run `docker scout cves` closer to Phase 9/10 and re-evaluate (Alpine
ships fixes on its own schedule; a newer `php:8.5-cli-alpine` tag may close
some of these by then). None of the 6 flagged packages (`curl`, `brotli`,
`libxml2`, `sqlite`, `tar`, `musl`) are invoked by this repo's own code path
(no outbound HTTP calls, no XML parsing, no SQLite, no tar extraction at
runtime) — the demo server's attack surface for these specific CVEs is low,
but the finding is still recorded in full, not downplayed.

## Secrets

None exist in this repo. Grepped `src/` for `getenv`/`$_ENV`/`putenv`/
`$_SERVER[` — zero matches; the library reads no environment configuration
at all. `.env.example` was added anyway (standing decision requires one per
repo) with a single placeholder, `APP_HTTP_PORT=20030`, consumed only by
`docker-compose.yml` (host port override) — never read by application code,
never a secret. No `--mount=type=secret` is needed in the Dockerfile because
nothing in any build stage requires a credential (public Packagist only).
`.dockerignore` excludes `.env`/`.env.*` (with a `!.env.example` exception)
alongside `.git`/`.github`/`vendor`/`docs`/`*.md`. `.gitignore` was widened
from the literal `.env` to `.env*` (with the same `.env.example` exception)
so no future `.env.local`-style file is ever accidentally committed.

## Ports

Reserved block for this repo: **20030-20039** (`projects/info/state/ports.md`).
`app` publishes `${APP_HTTP_PORT:-20030}:3000` — only one port from the
block is in use; the rest stay free for this repo's later phases.
`COMPOSE_PROJECT_NAME` equivalent: `docker-compose.yml` sets top-level
`name: php-query-builder-p5`, so parallel Phase 5 runs on other repos never
collide on container/network names (R4).

## Dependent services

None. No `depends_on: condition: service_healthy` was added because there is
nothing for `app` to depend on — documented here rather than silently
omitted.

## Phase 6 (Upgrade to latest) — re-verification, 2026-11-05

Ran as the Phase 6 **pilot** for the PHP/Composer stack (no matching skill
existed yet). Per standing decision 1, every version below was read live
from its registry on this date, not assumed from the Phase 2 snapshot
(`projects/info/deps/php-query-builder.md`, dated 2026-11-04):

| Item | Current | Latest stable (checked live, 2026-11-05) | Action |
|---|---|---|---|
| PHP runtime (`php:8.5-cli-alpine`, `php:8.5-cli`) | 8.5.12 | 8.5.12 (php.net `releases/index.php?json`: `supported_versions` tops out at `8.5`; 8.6 is still pre-release, not eligible under decision 1's no-RC rule) | No change |
| `composer:2` base image | pinned digest `sha256:5190d9b4...` | same digest — confirmed via `hub.docker.com/v2/.../tags/2` live lookup | No change |
| `php:8.5-cli-alpine` digest | `sha256:e899a75c...` | same digest — confirmed live | No change |
| `php:8.5-cli` digest | `sha256:9236fd37...` | same digest — confirmed live | No change |
| `phpunit/phpunit` | 13.4.6 | 13.4.6 (Packagist `p2` metadata) | No change |
| `phpstan/phpstan` | 2.3.5 | 2.3.5 (Packagist `p2` metadata) | No change |
| `squizlabs/php_codesniffer` | 4.0.4 | 4.0.4 (Packagist `p2` metadata) | No change |
| All transitive Composer packages | — | `composer outdated` (run inside `composer:2` container): "Everything up to date" for both direct and transitive | No change |
| `actions/checkout@v7` | floating major tag | `v7.0.1` — same commit (`3d3c42e...`) the `v7` tag already pointed to | SHA-pinned (no functional bump; see below) |
| `shivammathur/setup-php@v2` | floating major tag | `2.40.0` — same commit (`eb7c497e...`) the `v2` tag already pointed to | SHA-pinned (no functional bump; see below) |

**Conclusion: this repo had zero outdated dependencies going into Phase 6.**
Renovate (see below) had already landed every available bump as of PR #24
(2026-11-03, two days before this phase ran) — this is exactly how the
automation is supposed to work. Nothing here contradicts the Phase 2
inventory; it independently re-confirms it two days later, live, as decision
1 requires rather than assumes.

**Changes actually made this phase:**
1. `.github/workflows/ci.yml` — pinned `actions/checkout` and
   `shivammathur/setup-php` to their current release commit SHA (with the
   version as a trailing comment), closing the SHA-pinning gap
   `deps/php-query-builder.md` flagged. Purely mechanical: both tags already
   resolved to these exact commits, so CI behavior is unchanged.
2. Added `renovate.json` (repo root) to make the already-active Renovate
   automation's policy explicit and committed, instead of relying entirely
   on account-level defaults invisible to anyone reading this repo: weekly
   schedule, `prConcurrentLimit`/`prHourlyLimit` (runner-time budget),
   grouping (PHP dev deps, GitHub Actions, Docker base images), major
   Composer bumps kept ungrouped and labeled for individual review, Renovate
   digest-pinning enabled for GitHub Actions going forward, and
   vulnerability alerts on. No `dependabot.yml` added — the repo already has
   a working bot (confirmed via `gh pr list`: `app/renovate` is the real
   author of 17 historical PRs, most recently #24 merged 2026-11-05); running
   both would just create duplicate/conflicting PRs.
3. Local-checkout-only fix: this Windows host's `core.autocrlf=true` again
   rewrote the same 7 LF-stored files to CRLF in the fresh Phase 6 worktree
   (identical pitfall to the one already documented above under Phase 5 —
   expected, since a new `git worktree add` repeats the same checkout).
   Re-normalized to LF in the working copy only (`git diff --stat` showed
   zero byte difference beforehand); no repo content changed, no commit
   needed for `src/`/`tests/`.

**Re-verification after the above** (`COMPOSE_PROJECT_NAME=php-query-builder-p6`,
to stay isolated from any parallel Phase 5/6 run on another repo per R4):

```
$ make verify   →  exit 0
```

| Gate | Result | vs. Phase 5 baseline |
|---|---|---|
| `phpcs` (lint) | 0 errors, 0 warnings | unchanged |
| `phpstan` (analyse) | 0 errors | unchanged |
| `phpunit` (test) | 18 tests, 18 assertions, 0 failures | unchanged |
| `composer audit` | "No security vulnerability advisories found." | unchanged |
| `make up` + `curl localhost:20031/` (temporary port override) | byte-identical to the API/CLI snapshot recorded above | unchanged |

No regression. No `docs/TECHNICAL_DEBT.md` entry was needed — there was no
blocker, no unresolved gap, and no breaking change to this repo's own public
contract (Gate 5 did not apply).
