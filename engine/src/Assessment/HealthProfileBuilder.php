<?php

declare(strict_types=1);

namespace PWB\Assessment;

use PWB\Recommendation\Models\HealthProfile;

final class HealthProfileBuilder
{
    /**
     * Build a HealthProfile from an Assessment.
     */
    public function build(Assessment $assessment): HealthProfile
    {
        return new HealthProfile(

            assessment: $assessment->assessment,

            bodySystems: $assessment->bodySystems,

            mechanisms: [],

            biomarkers: [],

            symptoms: [],

            conditions: [],

            preferences: $assessment->preferences,

            restrictions: $assessment->restrictions,

            goals: $assessment->goals

        );
    }
}