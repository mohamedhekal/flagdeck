<?php

declare(strict_types=1);

namespace Hekal\FlagDeck\Evaluators;

/**
 * Sticky percentage assignment: same flag + bucket key always lands in the same bucket.
 */
final class PercentageBucketer
{
    public function __construct(
        private readonly string $salt = 'flagdeck',
    ) {}

    public function inRollout(string $flagKey, string $bucketKey, int $percentage): bool
    {
        if ($percentage <= 0) {
            return false;
        }

        if ($percentage >= 100) {
            return true;
        }

        $hash = hash('sha256', $this->salt.'|'.$flagKey.'|'.$bucketKey);
        $bucket = hexdec(substr($hash, 0, 8)) % 100;

        return $bucket < $percentage;
    }
}
