<?php

declare(strict_types=1);

namespace PWB\Recommendation\Models;

final class RecommendationPackage
{
    /**
     * Generation metadata.
     */
    public array $metadata = [];

    /**
     * Ranked biological priorities.
     *
     * @var Priority[]
     */
    public array $priorities = [];

    /**
     * Recommended foods.
     */
    public array $foods = [];

    /**
     * Recommended bioactives.
     */
    public array $bioactives = [];

    /**
     * Recommended supplements.
     */
    public array $supplements = [];

    /**
     * Lifestyle recommendations.
     */
    public array $lifestyle = [];

    /**
     * Warnings and exclusions.
     */
    public array $warnings = [];

    public function __construct(
        array $metadata = [],
        array $priorities = [],
        array $foods = [],
        array $bioactives = [],
        array $supplements = [],
        array $lifestyle = [],
        array $warnings = []
    ) {
        $this->metadata = $metadata;
        $this->priorities = $priorities;
        $this->foods = $foods;
        $this->bioactives = $bioactives;
        $this->supplements = $supplements;
        $this->lifestyle = $lifestyle;
        $this->warnings = $warnings;
    }

    public function hasFoodRecommendations(): bool
    {
        return !empty($this->foods);
    }

    public function hasBioactiveRecommendations(): bool
    {
        return !empty($this->bioactives);
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }
}