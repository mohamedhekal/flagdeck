<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Contracts;

use Hekal\FlagDeck\DTOs\FlagDefinition;
use Illuminate\Support\Collection;

interface FlagRepositoryInterface
{
    public function find(string $key): ?FlagDefinition;

    /**
     * @return Collection<int, FlagDefinition>
     */
    public function all(): Collection;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function store(array $attributes): FlagDefinition;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(string $key, array $attributes): FlagDefinition;

    public function delete(string $key): void;

    public function clearCache(?string $key = null): void;
}
