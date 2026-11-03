# Improvement roadmap

Written 2026-11-03 (Phase 2). Reflects findings in `docs/REPOSITORY_AUDIT.md` / `docs/TECHNICAL_DEBT.md`.

## Classification

`REPOS_INDEX.md` lists this repo as `small-candidate` (fold into a related repo or an umbrella "misc" module) based on size (49KB) and lack of a description. **Confirmed, not corrected**, after reading the actual code: it is a single real class plus four marker interfaces, a single test file, zero runtime dependencies, and a repo-local demo script — a self-contained teaching/portfolio artifact, not a library with any external consumer (`composer.json`'s package name suggests Packagist distribution, but there is no evidence anyone depends on it). Nothing found in this phase changes that assessment. Whether to actually fold it into an umbrella repo is Gate #3 territory (merging repositories) and stays a separate, later decision — not resolved here.

## Phase 3 — fix the core bug (recommended first step)

The single highest-priority item is `docs/TECHNICAL_DEBT.md` #1: make `$wheres[]` entries a consistent shape and fix `buildBaseQuery()`/`aggregate()` to read it correctly, so the library's actual stated feature (WHERE/OR WHERE/IN/LIKE/BETWEEN clauses) works at all. The existing 13 tests already pin the exact expected SQL output for every case — fixing the bug means making those 13 tests pass as originally written, with no change to their assertions (the tests describe correct, intended behavior; only the implementation is wrong). This is a pure bug fix, not a behavior change requiring approval beyond the standing testing-discipline rules in `CLAUDE.md`.

Also recommended in the same pass: resolve the interface-chaining PHPStan errors (#2), either by having each interface's methods return a shared `QueryBuilderInterface` union (simplest) or by having `QueryBuilder` just not implement any of them as return types (bigger API change). The simplest fix — one shared return interface — keeps the existing split of interfaces for documentation purposes without breaking static analysis.

## Phase 4 — CI/CD and PR triage

- Consolidate the two redundant workflows (#3) into one, modeled on the pattern used elsewhere in this audit (single `ci.yml`, clear job steps, no fake "Deploy").
- Once Phase 3 lands and CI goes green for the first time, triage the 4 open Renovate PRs (#7) individually — each should now build cleanly against the fixed base, but verify, don't assume.
- Gate #4 branch rename (`master` → `main`), pending explicit approval, consistent with every other repo in this audit.
- Cosmetic cleanup, your call: trim `.gitignore` to what's actually relevant (#5), stop tracking `.phpunit.result.cache` (#6).

## Phase 5 — tests

The existing 13 tests already cover every public method combination from the README. Once Phase 3's fix makes them pass, the main gap worth adding is a few edge cases not currently exercised: `orderBy()`/`groupBy()`/`limit()`/`offset()` used individually (today they only appear bundled together in one large "kitchen sink" test), and at least one test asserting `escapeValue()`'s actual behavior on a string containing a single quote (documenting the no-escaping limitation noted in `docs/TECHNICAL_DEBT.md`, not fixing it).

**Done 2026-11-03** — added exactly those 5 tests (see `docs/TEST_REPORT.md` for the full list and verified results: 18/18 passing, 0 deprecations, lint/analyse still clean). `docs/TESTING_STRATEGY.md` added alongside, documenting why this repo needs unit tests only (no DB/HTTP/auth surface to integration-test).

## Not planned

- **Adding real SQL parameterization / execution.** This library's entire design is "build a SQL string, hand it to something else" — turning that into a real query-execution layer (PDO bindings, a `get()`/`first()` that actually runs against a database) would be a different, much larger project, not a fix to this one. Flagged as a possible future direction, not a commitment.
- **Deciding the umbrella-repo merge question.** Noted under Classification above; deferred, Gate #3.
