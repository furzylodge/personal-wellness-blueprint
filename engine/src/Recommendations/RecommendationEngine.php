<?php

declare(strict_types=1);

namespace PWB\Recommendations;


class RecommendationEngine
{
    public function recommend(array $assessment): array
    {
        return [

            'foods' => [
                'F001'
            ],

            'summary' => [
                'Placeholder recommendation.'
            ]

        ];
    }
}