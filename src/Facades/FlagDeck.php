<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Facades;

use Hekal\FlagDeck\DTOs\EvaluationContext;
use Hekal\FlagDeck\DTOs\FlagDefinition;
use Hekal\FlagDeck\Services\FeatureManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool active(string $key, EvaluationContext|null $context = null)
 * @method static bool inactive(string $key, EvaluationContext|null $context = null)
 * @method static array<string, bool> many(list<string> $keys, EvaluationContext|null $context = null)
 * @method static FlagDefinition|null get(string $key)
 * @method static Collection<int, FlagDefinition> all()
 * @method static FlagDefinition create(array<string, mixed> $attributes)
 * @method static FlagDefinition update(string $key, array<string, mixed> $attributes)
 * @method static void delete(string $key)
 * @method static FlagDefinition enable(string $key)
 * @method static FlagDefinition disable(string $key)
 * @method static void clearCache(string|null $key = null)
 * @method static void require(string $key, EvaluationContext|null $context = null)
 *
 * @see FeatureManager
 */
final class FlagDeck extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FeatureManager::class;
    }
}
