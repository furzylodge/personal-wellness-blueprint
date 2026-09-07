<?php

declare(strict_types=1);

namespace PWB\Recommendation;

use PWB\Loader\JsonLoader;

final class BioactiveResolver
{
    public function __construct(
        private JsonLoader $loader,
        private string $knowledgePath
    ) {}

    public function resolve(array $resolvedMechanisms): array
    {
        // -------------------------------------------------
        // Load taxonomy
        // -------------------------------------------------

        $bioactiveCatalogue = $this->loader->load(
            $this->knowledgePath . '/taxonomy/bioactives.json'
        );

        $relationshipMatrix = $this->loader->load(
            $this->knowledgePath . '/taxonomy/bioactive-mechanisms.json'
        );

        // -------------------------------------------------
        // Bioactive lookup
        // -------------------------------------------------

        $bioLookup = [];

        foreach ($bioactiveCatalogue['bioactives'] as $bio) {
            $bioLookup[$bio['id']] = $bio;
        }

        // -------------------------------------------------
        // Clinical mechanism scores
        // -------------------------------------------------

        $mechanismScores = [];

        foreach ($resolvedMechanisms as $mechanism) {
            $mechanismScores[$mechanism['mechanismId']] =
                (float)$mechanism['clinicalScore'];
        }

        // -------------------------------------------------
        // Confidence mapping
        // -------------------------------------------------

        $confidenceMap = [
            'High' => 1.00,
            'Moderate' => 0.75,
            'Low' => 0.50
        ];

        // -------------------------------------------------
        // Aggregate bioactive scores
        // -------------------------------------------------

        $results = [];

        foreach ($relationshipMatrix['relationships'] as $row) {

            $bioId = $row['bioactive'];
            $mechId = $row['mechanism'];

            if (!isset($mechanismScores[$mechId])) {
                continue;
            }

            $confidence = $confidenceMap[$row['confidence']] ?? 0.5;

            $contribution =
                $mechanismScores[$mechId] *
                ($row['strength'] / 5) *
                $confidence;

            if (!isset($results[$bioId])) {

                $results[$bioId] = [
                    'bioactiveId' => $bioId,
                    'name' => $bioLookup[$bioId]['name'] ?? $bioId,
                    'category' => $bioLookup[$bioId]['category'] ?? '',
                    'clinicalScore' => 0,
                    'matchedMechanisms' => []
                ];
            }

            $results[$bioId]['clinicalScore'] += $contribution;

            $results[$bioId]['matchedMechanisms'][] = [
                'id' => $mechId,
                'strength' => $row['strength'],
                'confidence' => $row['confidence'],
                'contribution' => round($contribution, 2)
            ];
        }

        // -------------------------------------------------
        // Rank
        // -------------------------------------------------

        $ranked = array_values($results);

        foreach ($ranked as &$bio) {
            $bio['clinicalScore'] = round($bio['clinicalScore'], 2);
        }

        usort(
            $ranked,
            fn($a, $b) => $b['clinicalScore'] <=> $a['clinicalScore']
        );

        foreach ($ranked as $i => &$bio) {
            $bio['rank'] = $i + 1;
        }

        return $ranked;
    }
}