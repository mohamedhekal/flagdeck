<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Console\Commands;

use Hekal\FlagDeck\Services\FeatureManager;
use Illuminate\Console\Command;

final class ListFlagsCommand extends Command
{
    protected $signature = 'flagdeck:list';

    protected $description = 'List all FlagDeck feature flags';

    public function handle(FeatureManager $features): int
    {
        $rows = $features->all()->map(fn ($flag) => [
            $flag->key,
            $flag->name,
            $flag->isEnabled ? 'yes' : 'no',
            $flag->percentage === null ? '-' : (string) $flag->percentage,
        ])->all();

        $this->table(['Key', 'Name', 'Enabled', 'Percentage'], $rows);

        return self::SUCCESS;
    }
}
