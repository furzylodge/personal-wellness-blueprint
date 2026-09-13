<?php

declare(strict_types=1);

namespace PWB\Recommendation;

class RecommendationEngine
{
    private PriorityEngine $priorityEngine;
    private BioactiveResolver $bioactiveScorer;
    private FoodResolver $foodScorer;

    public function __construct()
    {
        $this->priorityEngine = new PriorityEngine();
        $this->bioactiveScorer = new BioactiveResolver();
        $this->foodScorer = new FoodResolver();
    }

    public function recommend(array $profile): array
    {
        $priorities = $this->priorityEngine->calculate($profile);

        $bioactives = $this->bioactiveScorer->score($priorities);

        $foods = $this->foodScorer->score($bioactives);

        return [
            'priorities' => $priorities,
            'bioactives' => $bioactives,
            'foods' => $foods,
        ];
    }
}