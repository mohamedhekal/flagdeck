<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Services;

use Hekal\FlagDeck\Contracts\AuditLoggerInterface;
use Hekal\FlagDeck\Contracts\FlagRepositoryInterface;
use Hekal\FlagDeck\DTOs\FlagDefinition;
use Hekal\FlagDeck\Events\FeatureFlagDeleted;
use Hekal\FlagDeck\Events\FeatureFlagSaved;
use Hekal\FlagDeck\Exceptions\FlagNotFoundException;
use Hekal\FlagDeck\Exceptions\InvalidFlagConfigurationException;
use Hekal\FlagDeck\Models\FeatureFlag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class DatabaseFlagRepository implements FlagRepositoryInterface
{
    public function __construct(
        private readonly AuditLoggerInterface $auditLogger,
    ) {}

    public function find(string $key): ?FlagDefinition
    {
        $model = FeatureFlag::query()->where('key', $key)->first();

        return $model ? $this->toDefinition($model) : null;
    }

    public function all(): Collection
    {
        /** @var Collection<int, FeatureFlag> $flags */
        $flags = FeatureFlag::query()->orderBy('key')->get();

        return $flags->map(fn (FeatureFlag $flag): FlagDefinition => $this->toDefinition($flag));
    }

    public function store(array $attributes): FlagDefinition
    {
        $attributes = $this->normalize($attributes);
        $this->assertValid($attributes);

        return DB::transaction(function () use ($attributes) {
            $model = FeatureFlag::query()->create($attributes);
            $definition = $this->toDefinition($model);

            $this->auditLogger->log('created', $definition->key, $definition, null, $definition->toArray());
            event(new FeatureFlagSaved($definition, 'created'));

            return $definition;
        });
    }

    public function update(string $key, array $attributes): FlagDefinition
    {
        $attributes = $this->normalize($attributes);
        $this->assertValid($attributes, partial: true);

        return DB::transaction(function () use ($key, $attributes) {
            $model = FeatureFlag::query()->where('key', $key)->first();

            if ($model === null) {
                throw FlagNotFoundException::forKey($key);
            }

            $before = $this->toDefinition($model)->toArray();
            $model->fill($attributes);
            $model->save();

            $definition = $this->toDefinition($model->fresh());
            $this->auditLogger->log('updated', $definition->key, $definition, $before, $definition->toArray());
            event(new FeatureFlagSaved($definition, 'updated'));

            return $definition;
        });
    }

    public function delete(string $key): void
    {
        DB::transaction(function () use ($key) {
            $model = FeatureFlag::query()->where('key', $key)->first();

            if ($model === null) {
                throw FlagNotFoundException::forKey($key);
            }

            $definition = $this->toDefinition($model);
            $model->delete();

            $this->auditLogger->log('deleted', $key, null, $definition->toArray(), null);
            event(new FeatureFlagDeleted($key, $definition));
        });
    }

    public function clearCache(?string $key = null): void
    {
        // Database repository has no cache layer.
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function normalize(array $attributes): array
    {
        if (isset($attributes['key'])) {
            $attributes['key'] = strtolower(trim((string) $attributes['key']));
        }

        if (array_key_exists('percentage', $attributes) && $attributes['percentage'] !== null) {
            $attributes['percentage'] = (int) $attributes['percentage'];
        }

        foreach (['include_user_ids', 'exclude_user_ids', 'include_tenant_ids', 'exclude_tenant_ids', 'environments'] as $listKey) {
            if (array_key_exists($listKey, $attributes) && is_array($attributes[$listKey])) {
                $attributes[$listKey] = array_values(array_map(static fn ($v) => (string) $v, $attributes[$listKey]));
            }
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function assertValid(array $attributes, bool $partial = false): void
    {
        if (! $partial && empty($attributes['key'])) {
            throw InvalidFlagConfigurationException::because('Flag key is required.');
        }

        if (isset($attributes['key']) && ! preg_match('/^[a-z0-9][a-z0-9._:-]*$/', (string) $attributes['key'])) {
            throw InvalidFlagConfigurationException::because('Flag key contains invalid characters.');
        }

        if (array_key_exists('percentage', $attributes) && $attributes['percentage'] !== null) {
            $percentage = (int) $attributes['percentage'];
            if ($percentage < 0 || $percentage > 100) {
                throw InvalidFlagConfigurationException::because('Percentage must be between 0 and 100.');
            }
        }
    }

    private function toDefinition(FeatureFlag $model): FlagDefinition
    {
        return FlagDefinition::fromArray($model->toArray());
    }
}
