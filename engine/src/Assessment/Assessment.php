<?php

declare(strict_types=1);

namespace PWB\Assessment;

final class Assessment
{
    /**
     * Assessment metadata.
     */
    public array $assessment = [];

    /**
     * Person details.
     */
    public array $person = [];

    /**
     * Questionnaire answers.
     */
    public array $answers = [];

    /**
     * Body System percentages.
     */
    public array $bodySystems = [];
    
     /**
     * User preferences.
     */
    public array $preferences = [];

    /**
     * User restrictions.
     */
    public array $restrictions = [];

    /**
     * User goals.
     */
    public array $goals = [];

    public function __construct(
        array $assessment = [],
        array $person = [],
        array $answers = [],
        array $bodySystems = [],
        array $preferences = [],
        array $restrictions = [],
        array $goals = []
    ) {
        $this->assessment = $assessment;
        $this->person = $person;
        $this->answers = $answers;
        $this->bodySystems = $bodySystems;
        $this->preferences = $preferences;
        $this->restrictions = $restrictions;
        $this->goals = $goals;
    }

    public function hasAnswers(): bool
    {
        return !empty($this->answers);
    }

    public function hasBodySystems(): bool
    {
        return !empty($this->bodySystems);
    }
    
    public function hasPreferences(): bool
    {
        return !empty($this->preferences);
    }

    public function hasRestrictions(): bool
    {
        return !empty($this->restrictions);
    }

    public function hasGoals(): bool
    {
        return !empty($this->goals);
    }
}