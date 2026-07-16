<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\DTOs;

final readonly class EvaluationContext
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public ?string $userId = null,
        public ?string $tenantId = null,
        public ?string $anonymousId = null,
        public ?string $environment = null,
        public array $attributes = [],
    ) {}

    /**
     * @param  array{
     *     user_id?: string|int|null,
     *     tenant_id?: string|int|null,
     *     anonymous_id?: string|null,
     *     environment?: string|null,
     *     attributes?: array<string, mixed>
     * }  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: self::stringify($data['user_id'] ?? null),
            tenantId: self::stringify($data['tenant_id'] ?? null),
            anonymousId: isset($data['anonymous_id']) ? (string) $data['anonymous_id'] : null,
            environment: isset($data['environment']) ? (string) $data['environment'] : null,
            attributes: $data['attributes'] ?? [],
        );
    }

    public function withEnvironment(?string $environment): self
    {
        return new self(
            userId: $this->userId,
            tenantId: $this->tenantId,
            anonymousId: $this->anonymousId,
            environment: $environment,
            attributes: $this->attributes,
        );
    }

    /**
     * @param  list<string>  $fallbackOrder
     */
    public function bucketKey(array $fallbackOrder = ['user', 'tenant', 'anonymous']): ?string
    {
        foreach ($fallbackOrder as $source) {
            $value = match ($source) {
                'user' => $this->userId,
                'tenant' => $this->tenantId,
                'anonymous' => $this->anonymousId,
                default => null,
            };

            if ($value !== null && $value !== '') {
                return $source.':'.$value;
            }
        }

        return null;
    }

    private static function stringify(string|int|null $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
