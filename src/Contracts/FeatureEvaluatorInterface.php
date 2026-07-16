<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Contracts;

use Hekal\FlagDeck\DTOs\EvaluationContext;
use Hekal\FlagDeck\DTOs\FlagDefinition;

interface FeatureEvaluatorInterface
{
    public function active(FlagDefinition $flag, EvaluationContext $context): bool;
}
