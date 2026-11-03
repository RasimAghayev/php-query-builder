# Architecture

Analyzed 2026-11-03 (Phase 2).

## What this is

A single-class PHP library — `App\Core\QueryBuilder` — that builds SQL `SELECT` statements as plain strings via a fluent interface (`->table()->select()->where()->toSql()`). No database connection, no execution, no ORM: the output of every method chain is a SQL string, nothing more. `src/index.php` is a runnable demo script (`php -S localhost:3000 index.php`) that exercises every builder method and echoes the resulting SQL.

There is exactly one real class in `src/Core/QueryBuilder.inc` (PSR-4 autoloaded from a non-standard `.inc` extension — unusual but functionally fine, since Composer's `files`/`psr-4` autoloading works off the paths declared in `composer.json`, not the extension). It implements four small interfaces under `src/Core/Interface/` (also `.inc`):

- `QueryStaticBuilderInterface` — `table()`, `select()`, `toSql()`
- `QueryWhereBuilderInterface` — `where()`, `orWhere()`, `whereIn()`, `whereNotIn()`, `whereLike()`, `whereNotLike()`, `whereBetween()`, `whereNotBetween()`
- `QueryOtherBuilderInterface` — `orderBy()`, `groupBy()`, `having()`, `limit()`, `offset()`
- `AggregationInterface` — `sum()`, `avg()`, `count()`

Each interface method is typed to return only that interface, not the concrete class or a union of all four — see `docs/TECHNICAL_DEBT.md` for why this breaks static analysis on any chain that crosses interface boundaries (which is every realistic chain, including every example in the README).

## State and query construction

`QueryBuilder` holds its in-progress query as a handful of private scalar/array properties (`$table`, `$select`, `$wheres`, `$orderBy`, `$groupBy`, `$having`, `$limit`, `$offset`). Every `where*()` method appends a **pre-rendered SQL fragment string** (e.g. `"status = 1"`, `"customer IN (13, 135, 168)"`) to `$wheres[]` — there is no structured representation of a condition (no column/operator/value triple kept as data). `toSql()` calls `buildBaseQuery()`, which re-walks `$wheres` expecting each entry to be an **associative array** with `['column']`/`['operator']`/`['value']` keys, not a string. This mismatch is the core bug — see `docs/TECHNICAL_DEBT.md` #1.

`orWhere()` has a second responsibility: when passed a closure instead of a column name, it builds a fresh nested `QueryBuilder` instance, lets the closure call `where()` on it, and wraps that nested builder's `$wheres` in parentheses — this is how `->orWhere(fn($q) => $q->where(...)->where(...))` produces `(quantity > 0 AND amount > 0)`. It is the only place in the class where a `QueryBuilder` is composed with another instance of itself.

## Values are interpolated directly, not parameterized

Every value passed to `where()`, `whereIn()`, `whereBetween()`, etc. is turned into a string via `escapeValue()` (wrap in single quotes if it's a PHP string, cast to string otherwise) and concatenated straight into the SQL text. There is no placeholder/binding mechanism and no PDO/mysqli escaping — `escapeValue()` does not escape quote characters inside a string value at all. This library never executes the SQL it builds (confirmed: no PDO/mysqli usage anywhere in `src/`), so there's no SQL-injection vector *within this repo*, but any consumer that takes `toSql()`'s output and executes it against a real database with user-supplied values would be injectable. Documented as a design limitation, not fixed here — see `docs/TECHNICAL_DEBT.md`.

## Testing and quality tooling

`tests/QueryBuilderTest.php` is a single PHPUnit test class, one method per README example, asserting the exact SQL string produced by each chain. `composer.json` wires three scripts (`lint` → phpcs, `analyse` → phpstan, `test` → phpunit) and `.phpcs.xml` (PSR12 + Squiz) / `phpstan.neon` (level 6) configure the first two. Two GitHub Actions workflows (`.github/workflows/ci.yml` and `.github/workflows/php.yml`) both run on push/PR to `master` and both run some subset of these three gates — see `docs/TECHNICAL_DEBT.md` #3 for why having two is itself debt.

## Not present

No database connection, no query execution, no ORM/model layer, no HTTP layer beyond the demo script, no dependency-injection container, no runtime (`require`) dependencies at all — `composer.json`'s only entries are `require-dev` (phpunit, phpcs, phpstan). This is a self-contained teaching/demo artifact, not a library meant to be installed into another application's dependency tree in its current state.
