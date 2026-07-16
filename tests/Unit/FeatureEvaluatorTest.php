<?php

declare(strict_types=1);

use Hekal\FlagDeck\DTOs\EvaluationContext;
use Hekal\FlagDeck\DTOs\FlagDefinition;
use Hekal\FlagDeck\Evaluators\FeatureEvaluator;
use Hekal\FlagDeck\Evaluators\PercentageBucketer;

it('returns false when the master switch is off', function () {
    $evaluator = new FeatureEvaluator(new PercentageBucketer('test'));
    $flag = new FlagDefinition(key: 'x', name: 'X', isEnabled: false);

    expect($evaluator->active($flag, new EvaluationContext(userId: '1')))->toBeFalse();
});

it('respects environment restrictions', function () {
    $evaluator = new FeatureEvaluator(new PercentageBucketer('test'));
    $flag = new FlagDefinition(
        key: 'x',
        name: 'X',
        isEnabled: true,
        environments: ['production'],
    );

    expect($evaluator->active($flag, new EvaluationContext(userId: '1', environment: 'testing')))->toBeFalse()
        ->and($evaluator->active($flag, new EvaluationContext(userId: '1', environment: 'production')))->toBeTrue();
});

it('excludes users and tenants before other rules', function () {
    $evaluator = new FeatureEvaluator(new PercentageBucketer('test'));
    $flag = new FlagDefinition(
        key: 'x',
        name: 'X',
        isEnabled: true,
        excludeUserIds: ['9'],
        includeUserIds: ['9'],
    );

    expect($evaluator->active($flag, new EvaluationContext(userId: '9')))->toBeFalse();
});

it('allows explicitly included users even with percentage zero', function () {
    $evaluator = new FeatureEvaluator(new PercentageBucketer('test'));
    $flag = new FlagDefinition(
        key: 'x',
        name: 'X',
        isEnabled: true,
        percentage: 0,
        includeUserIds: ['42'],
    );

    expect($evaluator->active($flag, new EvaluationContext(userId: '42')))->toBeTrue()
        ->and($evaluator->active($flag, new EvaluationContext(userId: '7')))->toBeFalse();
});

it('applies sticky percentage buckets', function () {
    $bucketer = new PercentageBucketer('stable-salt');
    $evaluator = new FeatureEvaluator($bucketer);

    $flag = new FlagDefinition(key: 'checkout.v2', name: 'Checkout', isEnabled: true, percentage: 25);
    $context = new EvaluationContext(userId: '1001');

    $first = $evaluator->active($flag, $context);
    $second = $evaluator->active($flag, $context);

    expect($first)->toBe($second);

    $in = 0;
    for ($i = 1; $i <= 200; $i++) {
        if ($evaluator->active($flag, new EvaluationContext(userId: (string) $i))) {
            $in++;
        }
    }

    // Loose band around 25% for 200 samples
    expect($in)->toBeGreaterThan(20)->toBeLessThan(80);
});

it('fails closed when percentage is set but no bucket key exists', function () {
    $evaluator = new FeatureEvaluator(new PercentageBucketer('test'));
    $flag = new FlagDefinition(key: 'x', name: 'X', isEnabled: true, percentage: 50);

    expect($evaluator->active($flag, new EvaluationContext))->toBeFalse();
});
