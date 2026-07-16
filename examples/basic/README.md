# Basic usage sketch (pseudo-app)

```php
use Hekal\FlagDeck\Facades\FlagDeck;

FlagDeck::create([
    'key' => 'inventory.fifo',
    'name' => 'FIFO Costing',
    'is_enabled' => true,
    'include_tenant_ids' => ['enterprise-1'],
]);

// Gate a domain capability
if (FlagDeck::active('inventory.fifo')) {
    $costing = app(FifoCostingService::class);
} else {
    $costing = app(AverageCostingService::class);
}
```

Wire tenant resolution in a service provider:

```php
config([
    'flagdeck.context.tenant_id' => fn () => auth()->user()?->tenant_id,
]);
```
