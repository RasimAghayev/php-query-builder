# Testing strategy

Written 2026-11-03 (Phase 5). This repo is a single pure, side-effect-free
class (`App\Core\QueryBuilder`) that builds SQL strings — it never opens a
connection, executes a query, or touches the filesystem/network. That shapes
the whole strategy below.

## What's covered, and how

- **Unit tests only** (`tests/QueryBuilderTest.php`, PHPUnit). Every public
  method is exercised by asserting the exact SQL string `toSql()` returns.
  There is nothing to mock: no DB, no HTTP, no I/O — a real `QueryBuilder`
  instance is used in every test, so these are also, trivially, the
  "integration" tests for this repo.
- **Fluent-chain coverage.** Most of the original 13 tests build up one
  large chain (`table → select → where → orWhere → whereIn → … → offset`),
  which is the right shape for proving the clauses compose correctly and in
  the right SQL order, but it under-tests any single clause used alone —
  a bug in one clause's default-argument branch could hide behind the
  other clauses' output.
- **Individual-clause edge cases** (added in this phase):
  `orderBy()`'s default direction (`ASC`, never exercised when every other
  test passes `'DESC'` explicitly), `groupBy()`'s variadic signature with
  genuinely separate arguments (the original test passed one pre-joined
  string `'category, status'`, which never exercised `implode()` over more
  than one element), and `limit()`/`offset()` each used alone.
- **Known-limitation test.** `escapeValue()` wraps strings in single quotes
  but does not escape an embedded quote character — see
  `docs/TECHNICAL_DEBT.md`'s "design limitation, not a bug" section. One
  test (`testEscapeValueDoesNotEscapeEmbeddedSingleQuote`) pins this exact,
  current (broken-SQL-producing) behavior so a future change to it is a
  visible, deliberate decision rather than a silent regression.

## What's deliberately not covered

- **No auth, no HTTP, no validation-boundary tests.** This library has none
  of those surfaces — there's no API, no user input boundary, nothing to
  authenticate or authorize.
- **No DB/regression/performance tests.** The class never executes the SQL
  it builds (confirmed: no PDO/mysqli anywhere in `src/`), so there is no
  real database to test against, and "performance" of building a string via
  `implode()`/string concatenation isn't a meaningful axis for a repo this
  size.
- **No SQL-injection test.** Injection-safety tests would assert that unsafe
  input is *escaped*; this library's `escapeValue()` explicitly does not do
  that (see above), so such a test would just restate the known limitation
  a second time under a different name.

## Mocking policy

None used, none needed — `QueryBuilder` has zero collaborators to mock.

## Running

- `make test` (host, requires local PHP + Composer) or `make up` /
  `docker compose run --rm test` (no local PHP needed — matches CI exactly).
- CI (`.github/workflows/ci.yml`) runs `composer lint && composer analyse &&
  composer test` on every push/PR to `main`.
