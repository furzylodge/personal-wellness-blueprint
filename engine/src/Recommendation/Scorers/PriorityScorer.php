<?php

declare(strict_types=1);

namespace PWB\Recommendation\Scorers;

use PWB\Loader\JsonLoader;
use PWB\Recommendation\Models\HealthProfile;
use PWB\Recommendation\Models\Priority;
use PWB\Recommendation\RecommendationConfiguration;

final class PriorityScorer
{
    // Bonus applied (on the same 0-100 scale as a normalised body system
    // score) to a body system that's a *primary* vs *secondary* target of
    // one of the person's own stated wellness goals (PF014, up to 3). When
    // several selected goals point at the same system, the strongest
    // applicable bonus is used rather than stacking them, so picking
    // several goals that happen to overlap doesn't run away unbounded.
    // Starting values — worth tuning once you've seen this against real
    // assessments.
    private const PRIMARY_GOAL_BONUS = 15.0;
    private const SECONDARY_GOAL_BONUS = 7.0;

    public function __construct(
        private ?JsonLoader $loader = null,
        private ?string $knowledgePath = null
    ) {}

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

        $goalBonuses = $this->goalBonusesByBodySystem($profile->goals ?? []);

        foreach ($profile->bodySystems as $id => $score) {

            $score = (float) $score;
            $derivedFrom = ['bodySystems'];

            if (isset($goalBonuses[$id])) {
                $score += $goalBonuses[$id];
                $derivedFrom[] = 'goals';
            }

            $priority = new Priority(

                id: $id,

                type: 'BODY_SYSTEM',

                name: $id,

                score: $score,

                confidence: $config->defaultConfidence(),

                weight: $weight,

                derivedFrom: $derivedFrom

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

    /**
     * Maps the person's selected wellness goals (PF014 answers — up to 3
     * outcome ids like "OUT005") to a per-body-system bonus, via
     * knowledgebase/taxonomy/outcomes.json's primary/secondaryBodySystems.
     * This taxonomy already existed (goals -> outcomes -> body systems /
     * candidate mechanisms) but wasn't wired into scoring anywhere before
     * now — goals were only ever displayed, never used as a scoring input.
     */
    private function goalBonusesByBodySystem(array $selectedGoals): array
    {
        if (empty($selectedGoals) || !$this->loader || !$this->knowledgePath) {
            return [];
        }

        $outcomesFile = $this->knowledgePath . '/taxonomy/outcomes.json';

        if (!file_exists($outcomesFile)) {
            return [];
        }

        $outcomes = $this->loader->load($outcomesFile)['outcomes'] ?? [];

        $selected = array_map('strval', $selectedGoals);
        $bonuses = [];

        foreach ($outcomes as $outcome) {

            if (!in_array((string) $outcome['id'], $selected, true)) {
                continue;
            }

            foreach ($outcome['primaryBodySystems'] ?? [] as $bs) {
                $bonuses[$bs] = max($bonuses[$bs] ?? 0.0, self::PRIMARY_GOAL_BONUS);
            }

            foreach ($outcome['secondaryBodySystems'] ?? [] as $bs) {
                $bonuses[$bs] = max($bonuses[$bs] ?? 0.0, self::SECONDARY_GOAL_BONUS);
            }
        }

        return $bonuses;
    }
}