<?php

declare(strict_types=1);

namespace PWB\Recommendation;


class RecommendationEngine
{

	public function recommend(array $profile): array
	{
    	$priorities = $this->priorityEngine->calculate($profile);

	$bioactives = $this->bioactiveScorer->score($priorities);

	$foods = $this->foodScorer->score($bioactives);

	$foods = $this->ranker->rankFoods($foods);

    return [

        'priorities' => $priorities,

        'bioactives' => $bioactives,

        'foods' => $foods

    ];
}

}