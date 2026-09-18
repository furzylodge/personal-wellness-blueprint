<?php

declare(strict_types=1);

namespace PWB\Recommendation;

use PWB\Loader\JsonLoader;

final class FoodResolver
{
    // Each successive matched pathway (ranked strongest-first) counts for
    // this fraction of the previous one's weight, so a food's score is
    // driven by how well it matches its BEST few mechanisms rather than
    // how many it matches in total. Starting value — worth tuning once
    // you've compared it against more personas/assessments.
    private const PATHWAY_DECAY = 0.6;

    public function __construct(
        private JsonLoader $loader,
        private string $knowledgePath
    ) {}

    /**
     * @param string[] $excludedAllergens Allergen slugs (matching foods.json's
     *                                     "allergens" tags, e.g. "peanuts",
     *                                     "gluten") to exclude from
     *                                     recommendations entirely — sourced
     *                                     from the person's declared food
     *                                     allergies (assessment SAF006), never
     *                                     from a soft dietary preference.
     */
    public function resolve(array $resolvedMechanisms, array $excludedAllergens = []): array
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
	
	$bodySystemMap = $this->loader->load(
        $this->knowledgePath . '/taxonomy/body-systems-mechanisms.json'
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
	// Mechanism system lookup
	// -------------------------------------------------

$mechanismSystemLookup = [];

foreach ($bodySystemMap['bodySystems'] as $bodySystem) {

    $system = $bodySystem['id'];

    foreach ($bodySystem['mechanisms'] as $mechanism) {

        $mechanismId = $mechanism['id'];

        $mechanismSystemLookup[$mechanismId][] = $system;
    }
}

        // -------------------------------------------------
	// Body system lookup
	// -------------------------------------------------


$bodySystemNames = [];

foreach ($bodySystemMap['bodySystems'] as $bodySystem) {
    $bodySystemNames[$bodySystem['id']] = $bodySystem['name'];
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

            // Hard exclusion, not a scoring penalty — a declared allergy
            // means this food must never be recommended, regardless of how
            // well it otherwise matches the person's mechanisms.
            $foodAllergens = $foodLookup[$foodId]['allergens'] ?? [];

            if ($excludedAllergens && array_intersect($foodAllergens, $excludedAllergens)) {
                continue;
            }

$total = 0;
$matched = [];
$breakdown = [];
$pathwayCount = 0;
$strengthTotal = 0;
$systemsCovered = [];

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
    
if ($contribution < 0.5) {
    continue;
}
    
    if (isset($mechanismSystemLookup[$id])) {
    foreach ($mechanismSystemLookup[$id] as $system) {
        $systemsCovered[$system] = $bodySystemNames[$system] ?? $system;
    }
}
    
    
    $total += $contribution;
    
    $pathwayCount++;

    $strengthTotal += ($strengthWeight * $confidenceWeight);

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

            $averageStrength = $pathwayCount > 0
    ? $strengthTotal / $pathwayCount
    : 0;

$systemCount = count($systemsCovered);

// Rank this food's own matched pathways strongest-first, and apply a
// diminishing weight per rank (self::PATHWAY_DECAY per step down) rather
// than adding every matched contribution up in full. A flat sum
// (the previous approach) let a food that matched many mechanisms
// decently always out-total a food that matched a handful very
// strongly and specifically — e.g. a generalist high-fibre food would
// beat beetroot for a respiratory profile purely by volume, even
// though beetroot's few matches (oxidative stress, nitric oxide,
// endothelial function) are a much sharper fit. Ranking by decayed
// contribution keeps a food's strongest, most relevant pathways
// dominant and lets weaker/incidental matches contribute only a
// little, without discarding them outright the way a hard top-N cap
// would.
usort($matched, fn($a, $b) => $b['contribution'] <=> $a['contribution']);
usort($breakdown, fn($a, $b) => $b['contribution'] <=> $a['contribution']);

$decayedTotal = 0;

foreach ($matched as $rank => $m) {
    $decayedTotal += $m['contribution'] * (self::PATHWAY_DECAY ** $rank);
}

$clinicalScore =
    ($decayedTotal * 0.80) +
    ($decayedTotal * $averageStrength * 0.20);

$results[] = [
    'foodId' => $foodId,
    'name' => $foodLookup[$foodId]['name'] ?? $foodId,
    // taxonomy/foods.json (not the monograph, which only names a food
    // group for ~11 of 86 files) is the complete source for this — every
    // food in the catalogue has a foodGroup id/name, used for the
    // food-group icon shown in the report.
    'foodGroupId'   => $foodLookup[$foodId]['foodGroup'] ?? null,
    'foodGroupName' => $foodLookup[$foodId]['foodGroupName'] ?? null,
    'clinicalScore' => round($clinicalScore, 2),
    'matchedMechanisms' => $matched,

    'scoreBreakdown' => [
        'total' => round($decayedTotal, 2),
        'mechanisms' => $breakdown,
        'summary' => [
            'relevance' => round($decayedTotal * 0.80, 2),
            'strength'  => round($decayedTotal * $averageStrength * 0.20, 2),
            'systemsCovered'   => array_values($systemsCovered),
            'systemsCoveredIds' => array_keys($systemsCovered),
            'systemCount'      => $systemCount,
        ],
    ],
];
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