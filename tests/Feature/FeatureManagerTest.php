<?php

declare(strict_types=1);

use Hekal\FlagDeck\DTOs\EvaluationContext;
use Hekal\FlagDeck\Facades\FlagDeck;
use Hekal\FlagDeck\Models\FeatureFlagAudit;
use Hekal\FlagDeck\Services\FeatureManager;

it('creates and evaluates an enabled flag', function () {
    FlagDeck::create([
        'key' => 'billing.portal',
        'name' => 'Billing Portal',
        'is_enabled' => true,
    ]);

    expect(FlagDeck::active('billing.portal', new EvaluationContext(userId: '1')))->toBeTrue();
});

it('fails closed for missing flags by default', function () {
    expect(FlagDeck::active('does.not.exist'))->toBeFalse();
});

it('writes an audit row when a flag is updated', function () {
    FlagDeck::create([
        'key' => 'reports.export',
        'name' => 'Export',
        'is_enabled' => false,
    ]);

    FlagDeck::enable('reports.export');

    expect(FeatureFlagAudit::query()->where('flag_key', 'reports.export')->count())->toBeGreaterThanOrEqual(2);
});

it('invalidates cache after updates', function () {
    FlagDeck::create([
        'key' => 'cache.demo',
        'name' => 'Cache Demo',
        'is_enabled' => false,
    ]);

    expect(FlagDeck::active('cache.demo'))->toBeFalse();

    FlagDeck::enable('cache.demo');

    expect(FlagDeck::active('cache.demo'))->toBeTrue();
});

it('supports tenant allow lists', function () {
    FlagDeck::create([
        'key' => 'beta.module',
        'name' => 'Beta',
        'is_enabled' => true,
        'include_tenant_ids' => ['acme', 'globex'],
    ]);

    /** @var FeatureManager $manager */
    $manager = app(FeatureManager::class);

    expect($manager->active('beta.module', new EvaluationContext(tenantId: 'acme')))->toBeTrue()
        ->and($manager->active('beta.module', new EvaluationContext(tenantId: 'other')))->toBeFalse();
});
