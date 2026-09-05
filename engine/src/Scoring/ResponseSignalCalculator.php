<?php

declare(strict_types=1);

namespace PWB\Scoring;

use InvalidArgumentException;

/**
 * Converts a canonical ordinal response position into a 0-1 signal.
 *
 * This class deliberately knows nothing about outcomes, weights or products.
 * The response position is expected to be zero-based.
 */
final class ResponseSignalCalculator
{
    public function ordinal(int $index, int $optionCount): float
    {
        if ($optionCount < 2) {
            throw new InvalidArgumentException('An ordinal scale requires at least two options.');
        }

        if ($index < 0 || $index >= $optionCount) {
            throw new InvalidArgumentException('Response index is outside the supplied scale.');
        }

        return $index / ($optionCount - 1);
    }
}
