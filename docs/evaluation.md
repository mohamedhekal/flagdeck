# Evaluation algorithm

Given flag `F` and context `C`:

```
if F.is_enabled is false → inactive

if F.environments is non-empty and C.environment not in list → inactive

if C.user_id in F.exclude_user_ids → inactive
if C.tenant_id in F.exclude_tenant_ids → inactive

if C.user_id in F.include_user_ids → active
if C.tenant_id in F.include_tenant_ids → active

if include lists are non-empty and subject did not match → inactive

if F.percentage is null → active

bucket_key = first available of (user, tenant, anonymous) per config
if bucket_key is null → inactive

active if hash(salt|flag_key|bucket_key) % 100 < percentage
```

## Sticky buckets

The same `salt + flag key + bucket key` always maps to the same bucket `0..99`. Changing the salt reshuffles cohorts—document that as a breaking operational change.

## Missing flags

Default `flagdeck.missing_flag = inactive`. Use `active` only for local experimentation; never for billing entitlements.
