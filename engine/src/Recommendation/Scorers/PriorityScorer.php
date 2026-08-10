<?php

declare(strict_types=1);

namespace PWB\Recommendation\Scorers;

use PWB\Recommendation\Models\HealthProfile;
use PWB\Recommendation\Models\Priority;
use PWB\Recommendation\RecommendationConfiguration;

final class PriorityScorer
{
    /**
     * Calculate ranked priorities from a HealthProfile.
     *
     * @return Priority[]
     */
    public function score(
        HealthProfile $profile,
        RecommendationConfiguration $config
    ): array {

        $priorities = [];

        $weight = $config->priorityWeight('bodySystems');

        foreach ($profile->bodySystems as $id => $score) {

            $priority = new Priority(

                id: $id,

                type: 'BODY_SYSTEM',

                name: $id,

                score: (float)$score,

                confidence: $config->defaultConfidence(),

                weight: $weight,

                derivedFrom: [
                    'bodySystems'
                ]

            );

            $priorities[] = $priority;

        }

        usort(
            $priorities,
            function (Priority $a, Priority $b): int {

                return $b->finalScore <=> $a->finalScore;

            }
        );

        return $priorities;
    }
}