<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Events;

use Hekal\FlagDeck\DTOs\FlagDefinition;
use Illuminate\Foundation\Events\Dispatchable;

final class FeatureFlagSaved
{
    use Dispatchable;

    public function __construct(
        public readonly FlagDefinition $flag,
        public readonly string $action,
    ) {}
}
