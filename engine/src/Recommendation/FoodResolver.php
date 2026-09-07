<?php

declare(strict_types=1);

namespace PWB\Recommendation;

use PWB\Loader\JsonLoader;

final class FoodResolver
{
    public function __construct(
        private JsonLoader $loader,
        private string $knowledgePath
    ) {}

    public function resolve(array $resolvedMechanisms): array
    {
        $foods = $this->loader->load(
            $this->knowledgePath . '/taxonomy/foods.json'
        );

        $foodMechanisms = $this->loader->load(
            $this->knowledgePath . '/taxonomy/food-mechanisms.json'
        );
        
        $mechanisms = $this->loader->load(
    	$this->knowledgePath . '/taxonomy/mechanisms.json'
	);

        // -------------------------------------------------
        // Food catalogue lookup
        // -------------------------------------------------

        $foodLookup = [];

        foreach ($foods['foods'] as $food) {
            $foodLookup[$food['id']] = $food;
        }
        
        // -------------------------------------------------
	// Mechanism catalogue lookup
	// -------------------------------------------------

	$mechanismNameLookup = [];

	foreach ($mechanisms['mechanisms'] as $mechanism) {
	    $mechanismNameLookup[$mechanism['id']] = $mechanism['name'];
	}

        // -------------------------------------------------
        // Convert mechanism results into lookup
        // -------------------------------------------------

        $mechanismScores = [];

        foreach ($resolvedMechanisms as $mechanism) {
            $mechanismScores[$mechanism['mechanismId']] = $mechanism['clinicalScore'];
        }

        // -------------------------------------------------
        // Score foods
        // -------------------------------------------------

        $results = [];

        foreach ($foodMechanisms['relationships'] as $foodRelationship) {

            $foodId = $foodRelationship['foodId'];

            $total = 0;
            $matched = [];

            foreach ($foodRelationship['mechanisms'] as $mech) {

                $id = $mech['id'];

                if (!isset($mechanismScores[$id])) {
                    continue;
                }

                $contribution =
                    $mechanismScores[$id] *
                    ($mech['strength'] / 5) *
                    $mech['confidence'];

                $total += $contribution;

                $matched[] = [
                    'id' => $id,
                    'name' => $mechanismNameLookup[$id] ?? $id,
                    'strength' => $mech['strength'],
                    'confidence' => $mech['confidence'],
                    'contribution' => round($contribution, 2)
                ];
            }

            if ($total <= 0) {
                continue;
            }

            $results[] = [
                'foodId' => $foodId,
                'name' => $foodLookup[$foodId]['name'] ?? $foodId,
                'category' => $foodLookup[$foodId]['category'] ?? '',
                'clinicalScore' => round($total, 2),
                'matchedMechanisms' => $matched
            ];
        }

        usort(
            $results,
            fn($a, $b) => $b['clinicalScore'] <=> $a['clinicalScore']
        );

        foreach ($results as $i => &$food) {
            $food['rank'] = $i + 1;
        }

        return $results;
    }
}