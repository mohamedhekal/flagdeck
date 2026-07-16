# Architecture

## Goals

1. Keep request-path evaluation cheap (cache hit → no DB).
2. Make targeting rules explicit and testable in isolation.
3. Prefer fail-closed defaults for entitlement-style flags.
4. Stay a Laravel package, not a mini-framework.

## Components

| Component | Responsibility |
|---|---|
| `FeatureManager` | Public application API |
| `FeatureEvaluator` | Pure rule evaluation |
| `PercentageBucketer` | Sticky cohort assignment |
| `DatabaseFlagRepository` | Persistence + validation + events |
| `CachedFlagRepository` | Read-through cache + bust on write |
| `DatabaseAuditLogger` | Mutation history |
| `ContextResolver` | Resolve user/tenant/env from the app |

## Evaluation order

1. Master `is_enabled`
2. Environment allow-list (if set)
3. Exclude user / tenant lists
4. Include user / tenant lists (short-circuit true)
5. Restrictive include lists without a match → inactive
6. Percentage rollout (sticky) or unconditional active

## Trade-offs (v0.1)

- **Rules embedded on the flag row (JSON)** — simple and fast for typical SaaS flag counts. A separate `feature_flag_rules` table can be introduced later without changing the evaluator interface.
- **Per-key cache entries** — avoids loading every flag on first miss; index cache used for `all()`.
- **Sync audits** — correct and simple; move to queued writes if audit volume becomes hot.
- **No Filament yet** — admin UI is valuable but not required to prove the evaluation core.

## Extension points

- Bind custom `FlagRepositoryInterface` or `FeatureEvaluatorInterface` in your app provider.
- Override `config('flagdeck.context.*')` callables for tenancy packages.
- Listen for `FeatureFlagSaved` / `FeatureFlagDeleted` to sync remote systems.
