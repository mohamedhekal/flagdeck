<?php

declare(strict_types=1);

namespace Hekal\FlagDeck;

use Hekal\FlagDeck\Console\Commands\ClearCacheCommand;
use Hekal\FlagDeck\Console\Commands\ListFlagsCommand;
use Hekal\FlagDeck\Console\Commands\SetFlagCommand;
use Hekal\FlagDeck\Contracts\AuditLoggerInterface;
use Hekal\FlagDeck\Contracts\FeatureEvaluatorInterface;
use Hekal\FlagDeck\Contracts\FlagRepositoryInterface;
use Hekal\FlagDeck\Evaluators\FeatureEvaluator;
use Hekal\FlagDeck\Evaluators\PercentageBucketer;
use Hekal\FlagDeck\Http\Middleware\EnsureFeatureIsActive;
use Hekal\FlagDeck\Services\CachedFlagRepository;
use Hekal\FlagDeck\Services\ContextResolver;
use Hekal\FlagDeck\Services\DatabaseAuditLogger;
use Hekal\FlagDeck\Services\DatabaseFlagRepository;
use Hekal\FlagDeck\Services\FeatureManager;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

final class FlagDeckServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/flagdeck.php', 'flagdeck');

        $this->app->singleton(ContextResolver::class);
        $this->app->singleton(PercentageBucketer::class, function () {
            return new PercentageBucketer((string) config('flagdeck.bucketing.salt', 'flagdeck'));
        });

        $this->app->singleton(FeatureEvaluatorInterface::class, function ($app) {
            return new FeatureEvaluator(
                $app->make(PercentageBucketer::class),
                (array) config('flagdeck.bucketing.fallback_order', ['user', 'tenant', 'anonymous']),
            );
        });

        $this->app->singleton(AuditLoggerInterface::class, function () {
            return new DatabaseAuditLogger((bool) config('flagdeck.audit.enabled', true));
        });

        $this->app->singleton(DatabaseFlagRepository::class, function ($app) {
            return new DatabaseFlagRepository($app->make(AuditLoggerInterface::class));
        });

        $this->app->singleton(FlagRepositoryInterface::class, function ($app) {
            $inner = $app->make(DatabaseFlagRepository::class);

            if (! config('flagdeck.cache.enabled', true)) {
                return $inner;
            }

            $store = config('flagdeck.cache.store');

            return new CachedFlagRepository(
                inner: $inner,
                cache: $store ? Cache::store($store) : Cache::store(),
                prefix: (string) config('flagdeck.cache.prefix', 'flagdeck'),
                ttl: (int) config('flagdeck.cache.ttl', 300),
                enabled: true,
            );
        });

        $this->app->singleton(FeatureManager::class, function ($app) {
            return new FeatureManager(
                repository: $app->make(FlagRepositoryInterface::class),
                evaluator: $app->make(FeatureEvaluatorInterface::class),
                contextResolver: $app->make(ContextResolver::class),
                missingFlagBehavior: (string) config('flagdeck.missing_flag', 'inactive'),
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/flagdeck.php' => config_path('flagdeck.php'),
        ], 'flagdeck-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'flagdeck-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('feature', EnsureFeatureIsActive::class);

        $this->registerRoutes();
        $this->registerBladeDirectives();

        if ($this->app->runningInConsole()) {
            $this->commands([
                ListFlagsCommand::class,
                SetFlagCommand::class,
                ClearCacheCommand::class,
            ]);
        }
    }

    private function registerRoutes(): void
    {
        if (! config('flagdeck.api.enabled')) {
            return;
        }

        $this->app->make(Router::class)
            ->middleware((array) config('flagdeck.api.middleware', ['api']))
            ->prefix((string) config('flagdeck.api.prefix', 'flagdeck'))
            ->group(__DIR__.'/../routes/flagdeck.php');
    }

    private function registerBladeDirectives(): void
    {
        Blade::if('feature', function (string $key): bool {
            return $this->app->make(FeatureManager::class)->active($key);
        });

        Blade::if('unlessfeature', function (string $key): bool {
            return $this->app->make(FeatureManager::class)->inactive($key);
        });
    }
}
