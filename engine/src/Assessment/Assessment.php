<?php

namespace PWB\Assessment;

final class Assessment
{
    public array $assessment;
    public array $person;
    public array $answers;
    public array $bodySystems;
    public array $preferences;
    public array $restrictions;
    public array $goals;

    public function __construct(
        array $assessment = [],
        array $person = [],
        array $answers = [],
        array $bodySystems = [],
        array $preferences = [],
        array $restrictions = [],
        array $goals = []
    ) {
        $this->assessment  = $assessment;
        $this->person      = $person;
        $this->answers     = $answers;
        $this->bodySystems = $bodySystems;
        $this->preferences = $preferences;
        $this->restrictions = $restrictions;
        $this->goals       = $goals;
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