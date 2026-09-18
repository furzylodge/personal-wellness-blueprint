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
                    'subcategory' => $bioLookup[$bioId]['subcategory'] ?? '',
                    // plainEnglish/description/primaryAction give the report
                    // something real to say about a bioactive beyond its name
                    // and pathway list — description is the fuller version,
                    // plainEnglish the one-line gist, primaryAction what it
                    // actually does day to day.
                    'plainEnglish' => $bioLookup[$bioId]['plainEnglish'] ?? '',
                    'description' => $bioLookup[$bioId]['description'] ?? '',
                    'primaryAction' => $bioLookup[$bioId]['primaryAction'] ?? '',
                    'evidence' => $bioLookup[$bioId]['evidence'] ?? '',
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

        // clinicalScore is an open-ended decayed sum (can run into the
        // hundreds), not a figure calibrated to 0-100 — showing it as
        // "538/100" is misleading. reportScore expresses it relative to
        // this person's own top-scoring bioactive instead, the same
        // treatment FoodResolver already gives its own reportScore.
        $topScore = $ranked[0]['clinicalScore'] ?? 0;
        $topScore = $topScore > 0 ? $topScore : 1;

        foreach ($ranked as $i => &$bio) {
            $bio['rank'] = $i + 1;
            $bio['reportScore'] = (int) round(($bio['clinicalScore'] / $topScore) * 100);
        }

        return $ranked;
    }
}