# FlagDeck

Production-minded feature flags for Laravel: boolean switches, percentage rollouts, and first-class user/tenant targeting—without a hosted feature-flag SaaS.

[![Tests](https://github.com/mohamedmohamedhekal/flagdeck/actions/workflows/tests.yml/badge.svg)](https://github.com/mohamedmohamedhekal/flagdeck/actions/workflows/tests.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

> Replace the badge URLs with your GitHub org/user once the repository is published.

## Problem

Shipping entitlements and gradual rollouts usually means either:

- hard-coded `if` checks that rot across the codebase, or
- a third-party flag service that is expensive and adds another operational dependency.

Laravel’s built-in options are intentionally minimal. Multi-tenant SaaS apps need allow/deny lists per tenant, sticky percentage buckets, cacheable evaluation, and an audit trail of who changed what.

## Features

- Boolean flags with a master enable switch
- Percentage rollouts with sticky bucketing
- User and tenant include/exclude lists
- Environment-scoped flags
- Cache-backed definition reads with bust-on-write
- `FlagDeck` facade + `feature:` middleware + Blade `@feature`
- Optional management HTTP API (disabled by default)
- Mutation audit log
- Artisan commands for list/set/cache clear

## Requirements

- PHP 8.2+
- Laravel 11 or 12

## Installation

```bash
composer require mohamedhekal/flagdeck
```

Publish config (optional) and run migrations:

```bash
php artisan vendor:publish --tag=flagdeck-config
php artisan migrate
```

## Quick start

```php
use Hekal\FlagDeck\Facades\FlagDeck;
use Hekal\FlagDeck\DTOs\EvaluationContext;

FlagDeck::create([
    'key' => 'checkout.express',
    'name' => 'Express Checkout',
    'is_enabled' => true,
    'percentage' => 20,
    'include_tenant_ids' => ['beta-customer'],
]);

if (FlagDeck::active('checkout.express')) {
    // ...
}

// Explicit context (tests, jobs, impersonation)
FlagDeck::active('checkout.express', new EvaluationContext(
    userId: '42',
    tenantId: 'acme',
    environment: 'production',
));
```

### Middleware

```php
Route::middleware('feature:checkout.express')->group(function () {
    Route::get('/checkout/express', ExpressCheckoutController::class);
});
```

### Blade

```blade
@feature('checkout.express')
    <x-express-checkout />
@endfeature
```

### Artisan

```bash
php artisan flagdeck:set checkout.express --enable --percentage=25 --name="Express Checkout"
php artisan flagdeck:list
php artisan flagdeck:clear-cache
```

## Configuration

See `config/flagdeck.php` after publishing. Important knobs:

| Key | Meaning |
|---|---|
| `missing_flag` | `inactive` (default, fail closed) or `active` |
| `cache.*` | Store, TTL, prefix |
| `bucketing.salt` | Changing salt reshuffles percentage cohorts |
| `api.enabled` | Expose management routes (keep behind auth) |
| `context.*` | Callables to resolve user/tenant/anonymous ids |

## Architecture overview

Definitions live in `feature_flags`. Evaluation is pure given a `FlagDefinition` + `EvaluationContext`. Reads go through a cached repository decorator; writes audit and invalidate cache.

Details: [docs/architecture.md](docs/architecture.md) · [docs/evaluation.md](docs/evaluation.md)

## Management API

Disabled by default. When enabled:

| Method | Path | Purpose |
|---|---|---|
| GET | `/flagdeck/flags` | List |
| POST | `/flagdeck/flags` | Create |
| GET | `/flagdeck/flags/{key}` | Show |
| PUT/PATCH | `/flagdeck/flags/{key}` | Update |
| DELETE | `/flagdeck/flags/{key}` | Delete |
| GET | `/flagdeck/evaluate/{key}` | Debug evaluate (also gated) |

Configure middleware in `flagdeck.api.middleware`. Do not expose evaluate in production without auth.

## Testing

```bash
composer install
composer test
```

## Security

- Fail closed for missing flags when gating entitlements.
- Keep the management API authenticated and authorized.
- Treat bucket salt as sensitive configuration (rotation redistributes users).
- See [SECURITY.md](SECURITY.md).

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## Roadmap

See [ROADMAP.md](ROADMAP.md). Filament admin, schedules, and experiments are intentionally not claimed as complete in v0.1.

## FAQ

**How is this different from Laravel Pennant?**  
Pennant is a great lightweight toggle API. FlagDeck focuses on multi-tenant targeting, sticky rollouts, audit, optional HTTP management, and an evaluation model aimed at SaaS entitlements.

**Can I use it without Redis?**  
Yes. Any Laravel cache store works; `array`/`file`/`database` are fine for smaller apps.

**What happens if percentage is set but the user is anonymous?**  
Evaluation falls back through configured bucket sources. If none resolve, percentage flags evaluate inactive (fail closed).

## License

MIT — see [LICENSE](LICENSE).

## Credits

Built and maintained by Mohamed Hekal.
