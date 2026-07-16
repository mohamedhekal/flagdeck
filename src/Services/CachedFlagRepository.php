<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Services;

use Hekal\FlagDeck\Contracts\FlagRepositoryInterface;
use Hekal\FlagDeck\DTOs\FlagDefinition;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;

final class CachedFlagRepository implements FlagRepositoryInterface
{
    public function __construct(
        private readonly FlagRepositoryInterface $inner,
        private readonly CacheRepository $cache,
        private readonly string $prefix,
        private readonly int $ttl,
        private readonly bool $enabled = true,
    ) {}

    public function find(string $key): ?FlagDefinition
    {
        if (! $this->enabled) {
            return $this->inner->find($key);
        }

        $payload = $this->cache->remember(
            $this->keyCacheName($key),
            $this->ttl,
            function () use ($key) {
                $flag = $this->inner->find($key);

                return $flag?->toArray();
            }
        );

        return is_array($payload) ? FlagDefinition::fromArray($payload) : null;
    }

    public function all(): Collection
    {
        if (! $this->enabled) {
            return $this->inner->all();
        }

        $payload = $this->cache->remember(
            $this->indexCacheName(),
            $this->ttl,
            fn () => $this->inner->all()->map(fn (FlagDefinition $flag) => $flag->toArray())->all()
        );

        return collect($payload)->map(fn (array $row) => FlagDefinition::fromArray($row));
    }

    public function store(array $attributes): FlagDefinition
    {
        $definition = $this->inner->store($attributes);
        $this->clearCache($definition->key);

        return $definition;
    }

    public function update(string $key, array $attributes): FlagDefinition
    {
        $definition = $this->inner->update($key, $attributes);
        $this->clearCache($definition->key);

        return $definition;
    }

    public function delete(string $key): void
    {
        $this->inner->delete($key);
        $this->clearCache($key);
    }

    public function clearCache(?string $key = null): void
    {
        if (! $this->enabled) {
            $this->inner->clearCache($key);

            return;
        }

        $this->cache->forget($this->indexCacheName());

        if ($key !== null) {
            $this->cache->forget($this->keyCacheName($key));
        }

        $this->inner->clearCache($key);
    }

    private function keyCacheName(string $key): string
    {
        return $this->prefix.':flag:'.$key;
    }

    private function indexCacheName(): string
    {
        return $this->prefix.':flags:index';
    }
}
