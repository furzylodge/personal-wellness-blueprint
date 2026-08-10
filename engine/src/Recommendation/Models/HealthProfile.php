<?php

declare(strict_types=1);

namespace PWB\Recommendation\Models;

final class HealthProfile
{
    /**
     * Assessment metadata.
     */
    public array $assessment = [];

    /**
     * Body System scores.
     *
     * Example:
     * [
     *     'BS001' => 82,
     *     'BS002' => 45
     * ]
     */
    public array $bodySystems = [];

    /**
     * Mechanism scores.
     *
     * Example:
     * [
     *     'MEC001' => 91,
     *     'MEC015' => 72
     * ]
     */
    public array $mechanisms = [];

    /**
     * Biomarker scores.
     */
    public array $biomarkers = [];

    /**
     * Symptom scores.
     */
    public array $symptoms = [];

    /**
     * Condition scores.
     */
    public array $conditions = [];

    /**
     * User preferences.
     */
    public array $preferences = [];

    /**
     * Dietary restrictions and exclusions.
     */
    public array $restrictions = [];

    /**
     * User goals.
     */
    public array $goals = [];

    public function __construct(
        array $assessment = [],
        array $bodySystems = [],
        array $mechanisms = [],
        array $biomarkers = [],
        array $symptoms = [],
        array $conditions = [],
        array $preferences = [],
        array $restrictions = [],
        array $goals = []
    ) {
        $this->assessment = $assessment;
        $this->bodySystems = $bodySystems;
        $this->mechanisms = $mechanisms;
        $this->biomarkers = $biomarkers;
        $this->symptoms = $symptoms;
        $this->conditions = $conditions;
        $this->preferences = $preferences;
        $this->restrictions = $restrictions;
        $this->goals = $goals;
    }
     public function hasRestrictions(): bool
    {
        return !empty($this->restrictions);
    }

    public function hasGoals(): bool
    {
        return !empty($this->goals);
    }
    public function hasMechanisms(): bool
    {
        return !empty($this->mechanisms);
    }
}