# FlagDeck — Project Plan

## Final name

**FlagDeck**  
Composer package: `hekal/flagdeck` (adjust vendor on publish)  
Suggested alternatives: `featuregate`, `rolloutkit`

## Product vision

Give Laravel teams LaunchDarkly-style control over feature exposure—boolean switches, percentage rollouts, and tenant/user targeting—without a hosted SaaS dependency. FlagDeck is a first-class package: cache-aware evaluation, audit of changes, HTTP middleware, Blade helpers, and an optional management API. Filament UI and experiments come after a solid core.

## Scope (v0.1)

**In:**
- Feature flag CRUD via Eloquent models / repository
- Evaluation: boolean, percentage rollout (sticky bucket by context key), environment match, user id / tenant id allow & deny lists
- `FlagDeck::active('key')` facade / container service
- `EnsureFeatureIsActive` middleware
- Blade `@feature` / `@unlessfeature` directives
- Config + cache store (default: Laravel cache)
- Audit records on flag create/update/delete
- Artisan: `flagdeck:list`, `flagdeck:set`, `flagdeck:clear-cache`
- Pest/PHPUnit tests + GitHub Actions
- Docs: README, architecture, security

**Out (later):**
- Filament resources
- Multivariate experiments
- Scheduled activation windows
- Feature dependency graph
- Remote HTTP management UI app
- React/Vue client SDK

## Functional requirements

1. Define a flag by unique `key` with `enabled` master switch.
2. Attach targeting rules: environments, percentage, include/exclude user ids, include/exclude tenant ids.
3. Evaluate against a context (user id, tenant id, environment, custom attributes later).
4. Cache compiled flag definitions; invalidate on write.
5. Record who changed what (actor id optional) in audits.
6. Protect routes when a flag is inactive (403 or abort configurable).
7. Fail closed or open based on config when flag missing (default: inactive / fail closed).

## Non-functional requirements

- PHP 8.2+, Laravel 11+
- Evaluation p50 suitable for request path (cache hit = no DB)
- Typed public API; no business logic in controllers
- Secure defaults; management routes gated by Laravel gate/middleware
- Idempotent cache clears; safe concurrent evaluation (read-only)

## Architecture

```
HTTP / Blade / Artisan / App code
        │
   FlagDeck Manager (facade)
        │
   FeatureEvaluator ──► TargetingMatcher
        │
   FlagRepository ──► Cache (definitions)
        │
   Eloquent models ──► DB
        │
   AuditLogger (on writes)
```

**Style:** Modular package with clear contracts (`FlagRepository`, `Evaluator`). Not a full hexagonal ports/adapters freeze—practical Laravel package boundaries.

## Directory structure

```
flagdeck/
├── composer.json
├── phpunit.xml / pest.php
├── PROJECT_PLAN.md
├── TASKS.md
├── README.md
├── LICENSE
├── CONTRIBUTING.md
├── SECURITY.md
├── CHANGELOG.md
├── ROADMAP.md
├── config/flagdeck.php
├── database/migrations/
├── routes/flagdeck.php
├── src/FlagDeck/
│   ├── FlagDeckServiceProvider.php
│   ├── Facades/FlagDeck.php
│   ├── Contracts/
│   ├── Models/
│   ├── Enums/
│   ├── DTOs/
│   ├── Services/
│   ├── Evaluators/
│   ├── Support/
│   ├── Http/
│   ├── Console/
│   ├── Events/
│   ├── Exceptions/
│   └── ...
├── tests/
├── docs/
└── .github/
```

## Main interfaces

- `Contracts\FlagRepositoryInterface` — load/store flags, bust cache
- `Contracts\FeatureEvaluatorInterface` — `bool active(string $key, EvaluationContext $ctx)`
- `Contracts\AuditLoggerInterface` — record mutations

## Main classes

- `FeatureManager` — public API
- `EvaluationContext` — value object
- `DatabaseFlagRepository`
- `CachedFlagRepository` (decorator)
- `FeatureEvaluator`
- `PercentageBucketer` — sticky hash
- `EnsureFeatureIsActive`
- Models: `FeatureFlag`, `FeatureFlagAudit`

## Database schema

**feature_flags**
- id, key (unique), name, description, is_enabled, percentage (0–100 nullable), environments (json), include_user_ids (json), exclude_user_ids (json), include_tenant_ids (json), exclude_tenant_ids (json), metadata (json), timestamps

**feature_flag_audits**
- id, feature_flag_id nullable, flag_key, action, before (json), after (json), actor_type, actor_id, ip, timestamps

## Configuration

See `config/flagdeck.php`: cache store/ttl, missing-flag behavior, middleware abort code, management API enable + middleware stack, environment resolver.

## Events

- `FeatureFlagUpdated`, `FeatureFlagDeleted`, `FeatureEvaluated` (optional, off by default for noise)

## Queues / jobs

v0.1: none (sync audit). Later: async audit writer.

## Commands

- `flagdeck:list`
- `flagdeck:set {key} {--enable} {--disable} {--percentage=}`
- `flagdeck:clear-cache`

## Middleware

- `EnsureFeatureIsActive:flag.key`

## Exceptions

- `FlagNotFoundException`
- `InvalidFlagConfigurationException`

## API endpoints (optional, config-gated)

- `GET /flagdeck/flags`
- `GET /flagdeck/flags/{key}`
- `PUT /flagdeck/flags/{key}`
- `POST /flagdeck/flags`
- `DELETE /flagdeck/flags/{key}`
- `GET /flagdeck/evaluate/{key}` (debug; disable in prod)

## Testing plan

- Unit: bucketer distribution, matcher edge cases, missing flag behavior
- Feature: middleware allow/deny, repository cache invalidation, audit written on update
- Concurrency: parallel evaluation with cached definitions (smoke)

## GitHub Actions

- `tests.yml` — PHP 8.2/8.3 × Laravel 11
- `static-analysis.yml` — PHPStan
- `coding-standards.yml` — Pint

## Documentation structure

- README — value, install, quick start
- docs/architecture.md — decisions
- docs/evaluation.md — algorithm
- SECURITY.md — reporting + management API advice

## Initial issue backlog

1. Filament resource for flags
2. Scheduled `active_from` / `active_until`
3. Feature dependencies
4. Vue composable `useFeature`
5. Sticky bucket salt rotation docs
6. Export/import flags JSON

## Milestones

- M1: Core evaluation + migrations + facade
- M2: Middleware, Blade, commands
- M3: Management API + audits
- M4: CI, docs, v0.1.0 tag

## Version 0.1 scope

Ship M1–M4 as described in Scope (v0.1).

## Version 1.0 roadmap

Filament admin, schedules, dependencies, experiments (A/B), metrics hooks, first-party Vue helper.

## Release checklist

- [ ] Tests green on CI
- [ ] PHPStan clean
- [ ] README accurate (no claimed unfinished features)
- [ ] CHANGELOG entry
- [ ] LICENSE MIT
- [ ] SECURITY.md present
- [ ] Example snippet verified
- [ ] Packagist-ready composer.json
- [ ] Tag `v0.1.0`

## Architectural decisions

1. **Shared rules on the flag row (JSON)** for v0.1 instead of a separate rules table — fewer joins, enough for common SaaS cases; normalize later if rule volume grows.
2. **Fail closed** when flag missing — safer for entitlements; configurable for local DX.
3. **Sticky percentage via hash(flag + bucket_key)** — stable UX across requests; bucket_key defaults to user id, then tenant id, then anonymous ip hash if configured.
4. **Cache whole flag map or per-key** — per-key with short TTL + explicit bust on write; avoids stampeding full table loads on cold start of single keys.

## Authenticity notes

Document limitations honestly. Prefer boring, correct code over decorative abstractions. Commit in small logical units when publishing to GitHub.
