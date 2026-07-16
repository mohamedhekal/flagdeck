<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Tests;

use Hekal\FlagDeck\FlagDeckServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            FlagDeckServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('cache.default', 'array');
        $app['config']->set('flagdeck.cache.enabled', true);
        $app['config']->set('flagdeck.cache.store', 'array');
        $app['config']->set('flagdeck.environment', 'testing');
        $app['config']->set('flagdeck.missing_flag', 'inactive');
        $app['config']->set('flagdeck.audit.enabled', true);
        $app['config']->set('flagdeck.api.enabled', false);
    }
}
