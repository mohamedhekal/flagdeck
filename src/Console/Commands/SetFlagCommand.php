<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Console\Commands;

use Hekal\FlagDeck\Exceptions\FlagNotFoundException;
use Hekal\FlagDeck\Services\FeatureManager;
use Illuminate\Console\Command;

final class SetFlagCommand extends Command
{
    protected $signature = 'flagdeck:set
                            {key : Flag key}
                            {--name= : Display name (used when creating)}
                            {--enable : Enable the flag}
                            {--disable : Disable the flag}
                            {--percentage= : Rollout percentage 0-100}';

    protected $description = 'Create or update a FlagDeck feature flag';

    public function handle(FeatureManager $features): int
    {
        $key = strtolower((string) $this->argument('key'));
        $attributes = [];

        if ($this->option('enable')) {
            $attributes['is_enabled'] = true;
        }

        if ($this->option('disable')) {
            $attributes['is_enabled'] = false;
        }

        if ($this->option('percentage') !== null) {
            $attributes['percentage'] = (int) $this->option('percentage');
        }

        if ($this->option('name')) {
            $attributes['name'] = (string) $this->option('name');
        }

        try {
            $existing = $features->get($key);

            if ($existing === null) {
                $attributes['key'] = $key;
                $attributes['name'] ??= $key;
                $attributes['is_enabled'] ??= false;
                $flag = $features->create($attributes);
                $this->info("Created flag [{$flag->key}].");
            } else {
                if ($attributes === []) {
                    $this->warn('Nothing to update. Pass --enable, --disable, --percentage, or --name.');

                    return self::FAILURE;
                }

                $flag = $features->update($key, $attributes);
                $this->info("Updated flag [{$flag->key}].");
            }
        } catch (FlagNotFoundException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
