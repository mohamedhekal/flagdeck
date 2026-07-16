<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Console\Commands;

use Hekal\FlagDeck\Services\FeatureManager;
use Illuminate\Console\Command;

final class ClearCacheCommand extends Command
{
    protected $signature = 'flagdeck:clear-cache {key?}';

    protected $description = 'Clear FlagDeck definition cache';

    public function handle(FeatureManager $features): int
    {
        $key = $this->argument('key');
        $features->clearCache(is_string($key) ? $key : null);
        $this->info($key ? "Cleared cache for [{$key}]." : 'Cleared FlagDeck cache index.');

        return self::SUCCESS;
    }
}
