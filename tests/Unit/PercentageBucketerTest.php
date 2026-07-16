<?php

declare(strict_types=1);

use Hekal\FlagDeck\Evaluators\PercentageBucketer;

it('is deterministic for the same inputs', function () {
    $bucketer = new PercentageBucketer('salt');

    $a = $bucketer->inRollout('flag.a', 'user:1', 40);
    $b = $bucketer->inRollout('flag.a', 'user:1', 40);

    expect($a)->toBe($b);
});

it('treats 0 and 100 as hard edges', function () {
    $bucketer = new PercentageBucketer('salt');

    expect($bucketer->inRollout('flag.a', 'user:1', 0))->toBeFalse()
        ->and($bucketer->inRollout('flag.a', 'user:1', 100))->toBeTrue();
});
