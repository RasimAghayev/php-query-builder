# Test report

Run 2026-11-03, inside the repo's own `test` Docker stage (`docker build
--target test` → `docker run`), i.e. the same environment and commands
(`composer lint && composer analyse && composer test`) as CI's `ci.yml`.

## Environment

- PHP 8.5.12 (`php:8.5-cli` image — bumped from 8.4 by Renovate PR #24,
  merged 2026-11-03)
- PHPUnit 13.4.6
- PHPStan 2.x (`phpstan.neon`)
- PHP_CodeSniffer 4.x, `PSR12` ruleset (`.phpcs.xml`)

## Results

| Gate | Result |
|---|---|
| `composer lint` (phpcs) | **Pass** — 0 errors, 0 warnings |
| `composer analyse` (phpstan) | **Pass** — 0 errors |
| `composer test` (phpunit) | **Pass** — 18 tests, 18 assertions, 0 failures, 0 errors, **0 deprecations** |

Full phpunit output:

```
PHPUnit 13.4.6 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.5.12
Configuration: /app/phpunit.xml

..................                                                18 / 18 (100%)

Time: 00:00.007, Memory: 20.00 MB

OK (18 tests, 18 assertions)
```

Before this phase (Phase 2 baseline, same Docker gate): 13 tests, 13
assertions, 1 PHPUnit deprecation (`phpunit.xml`'s pre-migration schema —
see `docs/TECHNICAL_DEBT.md` #8, now resolved).

## What changed this phase

5 tests added to `tests/QueryBuilderTest.php`, closing the gaps identified
in `docs/IMPROVEMENT_ROADMAP.md`'s Phase 5 section:

1. `testOrderByDefaultDirectionQuery` — `orderBy()`'s default `'ASC'`
   direction, never exercised elsewhere (every other test passes `'DESC'`
   explicitly).
2. `testGroupByMultipleColumnsQuery` — `groupBy()`'s variadic signature
   with two genuinely separate arguments (`groupBy('category', 'status')`);
   the original test passed one pre-joined string (`'category, status'`),
   which happened to produce the same SQL without ever exercising
   `implode()` over more than one array element.
3. `testLimitQuery` — `limit()` used alone.
4. `testOffsetQuery` — `offset()` used alone.
5. `testEscapeValueDoesNotEscapeEmbeddedSingleQuote` — pins
   `escapeValue()`'s current behavior on a string containing a single
   quote (produces syntactically broken SQL; documented, not fixed — see
   `docs/TECHNICAL_DEBT.md`'s design-limitation section). A deliberate
   regression trip-wire, not a bug fix.

## Known limitations (unchanged by this phase)

- No SQL parameterization/escaping of embedded quotes — see
  `docs/TECHNICAL_DEBT.md`. Any consumer executing `toSql()`'s output
  against a real database with unsanitized input is SQL-injectable.
  Out of scope here: fixing it is a breaking API change, tracked in
  `docs/IMPROVEMENT_ROADMAP.md`'s "Not planned" section.
- Coverage is 100% of public methods by call-site, but no line/branch
  coverage percentage is reported here — `phpunit.xml`'s `<source>` block
  scopes coverage to `src/`, but no coverage driver (Xdebug/PCOV) is
  installed in the `test` Docker stage, so `--coverage-text` cannot run.
  Not added: doing so would mean a second, larger PHP extension in the
  test image for a single-class repo where every public method already has
  at least one direct assertion.
