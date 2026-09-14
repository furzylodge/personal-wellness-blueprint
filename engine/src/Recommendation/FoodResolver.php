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
$breakdown = [];

foreach ($foodRelationship['mechanisms'] as $mech) {

    $id = $mech['id'];

    if (!isset($mechanismScores[$id])) {
        continue;
    }

    $mechanismScore   = $mechanismScores[$id];
    $strengthWeight   = $mech['strength'] / 5;
    $confidenceWeight = $mech['confidence'];

    $contribution = round(
        $mechanismScore * $strengthWeight * $confidenceWeight,
        2
    );

    $total += $contribution;

    $matched[] = [
        'id'           => $id,
        'name'         => $mechanismNameLookup[$id] ?? $id,
        'strength'     => $mech['strength'],
        'confidence'   => $mech['confidence'],
        'contribution' => $contribution,
    ];

    $breakdown[] = [
        'mechanismId'      => $id,
        'mechanismName'    => $mechanismNameLookup[$id] ?? $id,
        'mechanismScore'   => $mechanismScore,
        'strengthWeight'   => $strengthWeight,
        'confidenceWeight' => $confidenceWeight,
        'contribution'     => $contribution,
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
		'matchedMechanisms' => $matched,
		'scoreBreakdown' => [
		'total' => round($total, 2),
		'mechanisms' => $breakdown,
		]];
        }

        usort(
            $results,
            fn($a, $b) => $b['clinicalScore'] <=> $a['clinicalScore']
        );
        
        $maxScore = $results[0]['clinicalScore'] ?? 1;

foreach ($results as $i => &$food) {
    $food['rank'] = $i + 1;

    $food['reportScore'] = (int) round(
        ($food['clinicalScore'] / $maxScore) * 100
    );
}
unset($food);


        foreach ($results as $i => &$food) {
            $food['rank'] = $i + 1;
        }
        return $results;
    }
}