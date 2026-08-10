<?php

declare(strict_types=1);

namespace PWB\Recommendation;

use PWB\Utils\FileLocator;
use RuntimeException;

final class RecommendationConfiguration
{
    /**
     * Configuration data.
     */
    private array $config;

    public function __construct()
    {
        $locator = new FileLocator();

        $file = $locator->getRecommendationConfiguration();

        if (!file_exists($file)) {
            throw new RuntimeException(
                "Recommendation configuration not found:\n{$file}"
            );
        }

        $config = json_decode(
            file_get_contents($file),
            true
        );

        if (!is_array($config)) {
            throw new RuntimeException(
                'Unable to load recommendation configuration.'
            );
        }

        $this->config = $config;
    }

    /**
     * Return the entire configuration.
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Return a priority weight.
     */
    public function priorityWeight(string $type): float
    {
        return (float)(
            $this->config['priorityWeights'][$type]
            ?? 1.0
        );
    }

    /**
     * Minimum recommendation score.
     */
    public function minimumRecommendationScore(): float
    {
        return (float)
            $this->config['minimumRecommendationScore'];
    }

    /**
     * Recommendation limit.
     */
    public function recommendationLimit(
        string $type
    ): int {

        return (int)(
            $this->config['recommendationLimits'][$type]
            ?? 0
        );

    }

    /**
     * Default confidence.
     */
    public function defaultConfidence(): float
    {
        return (float)
            $this->config['confidence']['default'];
    }

    /**
     * Minimum confidence.
     */
    public function minimumConfidence(): float
    {
        return (float)
            $this->config['confidence']['minimum'];
    }

    /**
     * Minimum score.
     */
    public function minimumScore(): float
    {
        return (float)
            $this->config['scoreRanges']['minimum'];
    }

    /**
     * Maximum score.
     */
    public function maximumScore(): float
    {
        return (float)
            $this->config['scoreRanges']['maximum'];
    }
}