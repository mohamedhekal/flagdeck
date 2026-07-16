<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Evaluators;

use Hekal\FlagDeck\Contracts\FeatureEvaluatorInterface;
use Hekal\FlagDeck\DTOs\EvaluationContext;
use Hekal\FlagDeck\DTOs\FlagDefinition;

final class FeatureEvaluator implements FeatureEvaluatorInterface
{
    /**
     * @param  list<string>  $fallbackOrder
     */
    public function __construct(
        private readonly PercentageBucketer $bucketer,
        private readonly array $fallbackOrder = ['user', 'tenant', 'anonymous'],
    ) {}

    public function active(FlagDefinition $flag, EvaluationContext $context): bool
    {
        if (! $flag->isEnabled) {
            return false;
        }

        if (! $this->environmentMatches($flag, $context)) {
            return false;
        }

        if ($this->isExcluded($flag, $context)) {
            return false;
        }

        if ($this->isExplicitlyIncluded($flag, $context)) {
            return true;
        }

        // Non-empty include lists act as allow-lists when the subject is known.
        if ($this->hasRestrictiveIncludeList($flag)) {
            return false;
        }

        if ($flag->percentage === null) {
            return true;
        }

        $bucketKey = $context->bucketKey($this->fallbackOrder);

        if ($bucketKey === null) {
            // No stable identity: treat percentage as unavailable → inactive.
            return false;
        }

        return $this->bucketer->inRollout($flag->key, $bucketKey, $flag->percentage);
    }

    private function environmentMatches(FlagDefinition $flag, EvaluationContext $context): bool
    {
        if ($flag->environments === null || $flag->environments === []) {
            return true;
        }

        $environment = $context->environment;

        if ($environment === null || $environment === '') {
            return false;
        }

        return in_array($environment, $flag->environments, true);
    }

    private function isExcluded(FlagDefinition $flag, EvaluationContext $context): bool
    {
        if ($context->userId !== null && $this->inList($context->userId, $flag->excludeUserIds)) {
            return true;
        }

        if ($context->tenantId !== null && $this->inList($context->tenantId, $flag->excludeTenantIds)) {
            return true;
        }

        return false;
    }

    private function isExplicitlyIncluded(FlagDefinition $flag, EvaluationContext $context): bool
    {
        if ($context->userId !== null && $this->inList($context->userId, $flag->includeUserIds)) {
            return true;
        }

        if ($context->tenantId !== null && $this->inList($context->tenantId, $flag->includeTenantIds)) {
            return true;
        }

        return false;
    }

    private function hasRestrictiveIncludeList(FlagDefinition $flag): bool
    {
        $userList = $flag->includeUserIds ?? [];
        $tenantList = $flag->includeTenantIds ?? [];

        return $userList !== [] || $tenantList !== [];
    }

    /**
     * @param  list<string>|null  $list
     */
    private function inList(string $value, ?array $list): bool
    {
        if ($list === null || $list === []) {
            return false;
        }

        return in_array($value, $list, true);
    }
}
