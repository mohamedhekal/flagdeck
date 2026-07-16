<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Services;

use Hekal\FlagDeck\Contracts\FeatureEvaluatorInterface;
use Hekal\FlagDeck\Contracts\FlagRepositoryInterface;
use Hekal\FlagDeck\DTOs\EvaluationContext;
use Hekal\FlagDeck\DTOs\FlagDefinition;
use Hekal\FlagDeck\Exceptions\FlagNotFoundException;
use Illuminate\Support\Collection;

final class FeatureManager
{
    public function __construct(
        private readonly FlagRepositoryInterface $repository,
        private readonly FeatureEvaluatorInterface $evaluator,
        private readonly ContextResolver $contextResolver,
        private readonly string $missingFlagBehavior = 'inactive',
    ) {}

    public function active(string $key, ?EvaluationContext $context = null): bool
    {
        $flag = $this->repository->find($key);

        if ($flag === null) {
            return $this->missingFlagBehavior === 'active';
        }

        return $this->evaluator->active($flag, $this->contextResolver->resolve($context));
    }

    public function inactive(string $key, ?EvaluationContext $context = null): bool
    {
        return ! $this->active($key, $context);
    }

    /**
     * @param  list<string>  $keys
     * @return array<string, bool>
     */
    public function many(array $keys, ?EvaluationContext $context = null): array
    {
        $resolved = $this->contextResolver->resolve($context);
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->active($key, $resolved);
        }

        return $result;
    }

    public function get(string $key): ?FlagDefinition
    {
        return $this->repository->find($key);
    }

    /**
     * @return Collection<int, FlagDefinition>
     */
    public function all(): Collection
    {
        return $this->repository->all();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): FlagDefinition
    {
        return $this->repository->store($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(string $key, array $attributes): FlagDefinition
    {
        return $this->repository->update($key, $attributes);
    }

    public function delete(string $key): void
    {
        $this->repository->delete($key);
    }

    public function enable(string $key): FlagDefinition
    {
        return $this->update($key, ['is_enabled' => true]);
    }

    public function disable(string $key): FlagDefinition
    {
        return $this->update($key, ['is_enabled' => false]);
    }

    public function clearCache(?string $key = null): void
    {
        $this->repository->clearCache($key);
    }

    public function require(string $key, ?EvaluationContext $context = null): void
    {
        if ($this->inactive($key, $context)) {
            throw FlagNotFoundException::inactive($key);
        }
    }
}
