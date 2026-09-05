<?php

declare(strict_types=1);

namespace PWB\Scoring;

use InvalidArgumentException;

/**
 * Aggregates already-normalised construct signals.
 *
 * No evidence weights are embedded here. Weighting remains a calibration
 * concern until the numerical scoring specification is frozen.
 */
final class ConstructAggregator
{
    /**
     * @param float[] $signals
     */
    public function mean(array $signals): float
    {
        $signals = array_values(array_filter(
            $signals,
            static fn ($value): bool => $value !== null
        ));

        if ($signals === []) {
            throw new InvalidArgumentException('Cannot aggregate an empty signal set.');
        }

        foreach ($signals as $signal) {
            if (!is_numeric($signal) || $signal < 0 || $signal > 1) {
                throw new InvalidArgumentException('Signals must be numeric values between 0 and 1.');
            }
        }

        return array_sum($signals) / count($signals);
    }
}
