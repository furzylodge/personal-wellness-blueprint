<?php

declare(strict_types=1);

namespace PWB\Recommendation;

use PWB\Loader\JsonLoader;

/**
 * Scores products against a person's resolved mechanisms, mirroring
 * FoodResolver's approach rather than the original bioactive-status-weight
 * algorithm this class used to run. Rebuilt for the "product strip" feature
 * (see ReportDataBuilder) because:
 *
 *  - product-mechanisms.json is a richer, DERIVED source than
 *    product-bioactives.json: it already folds in both bioactive and
 *    nutrient (vitamin/mineral) contributors, with a per-mechanism
 *    strength/confidence figure structurally identical to
 *    food-mechanisms.json — so the same decay-ranked scoring approach
 *    FoodResolver uses applies directly.
 *  - Scoring against mechanisms (not raw bioactive status weights) means a
 *    product's score, and its "why this was selected" explanation, can be
 *    expressed in the same body-system/mechanism language as the rest of
 *    the report (Priority Foods, Wellness Priorities) rather than a
 *    separate vocabulary.
 *  - Hard allergy/dietary exclusion now applies here exactly as it does in
 *    FoodResolver, using the allergens/dietaryExclusions tags added to
 *    products.json — a product must never be recommended to someone whose
 *    declared allergy or dietary preference it violates, regardless of
 *    how well it otherwise scores.
 */
final class ProductResolver
{
    // See FoodResolver::PATHWAY_DECAY for the rationale — kept identical so
    // products and foods are ranked by the same "best few pathways matter
    // most" logic rather than by how many mechanisms they happen to touch.
    private const PATHWAY_DECAY = 0.6;

    // The subscribe & save discount is a fixed site-wide policy (plus free
    // shipping, cancel anytime), not per-product data — so it's applied
    // here rather than stored as a second price in products.json, which
    // would just be a second place for the two figures to drift apart.
    private const SUBSCRIPTION_DISCOUNT = 0.10;

    // Bonuses applied to clinicalScore when a product's hand-reviewed
    // "formulatedFor" tag (products.json, factsheet-grounded, Simon-
    // approved) lines up with this person's own top body-system priority
    // or one of their selected wellness goals (PF014). Mirrors
    // PriorityScorer::PRIMARY_GOAL_BONUS / SECONDARY_GOAL_BONUS — a real
    // but bounded edge, never a hard override, and never stacked: at most
    // one body-system bonus and one outcome bonus per product, each taking
    // the strongest applicable match rather than summing several.
    // Expressed as a fraction of clinicalScore (that score's scale varies
    // per assessment) rather than a flat point value, since ProductResolver
    // — unlike PriorityScorer's 0-100 body-system scale — has no fixed
    // scale to add flat points to.
    private const PRIMARY_FLAGSHIP_BONUS = 0.15;
    private const SECONDARY_FLAGSHIP_BONUS = 0.07;
    private const OUTCOME_MATCH_BONUS = 0.10;

    // A percentage bonus can't guarantee anything — if a product's raw
    // mechanism-based score is very low (e.g. SleepWell, because
    // mechanisms.json has no real sleep-specific mechanism yet), even a
    // large multiplier leaves it buried. Simon asked for a way to force a
    // product to appear when it's directly formulated for one of the
    // person's own selected goals — not just "relevant" to it
    // (formulatedFor.outcomes) but its exact, explicit purpose.
    //
    // This deliberately reads formulatedFor.exactFor — a narrow,
    // hand-approved list, separate from primaryBodySystems — rather than
    // deriving the match from shared body systems. An earlier version did
    // derive it that way and it came out too broad: SleepWell (flagship
    // system Nervous) started forcing itself into a Better Fitness result
    // too, because More Energy also lists Nervous as a primary system.
    // exactFor has no such structural leakage: a product with no entry can
    // never be forced in, however its body systems or mechanisms overlap.
    //
    // When a match is found, this product's clinicalScore is floored to at
    // least the current #3 product's score — guaranteed a top-3 place,
    // not merely nudged. Still fully transparent: every floored product is
    // flagged in scoreBreakdown.formulatedForBonus.forcedInclusion.
    private const FORCED_INCLUSION_RANK = 3;

    public function __construct(
        private JsonLoader $loader,
        private string $knowledgePath
    ) {}

    /**
     * @param string[] $excludedTags Slugs to exclude products against,
     *                                checked against each product's combined
     *                                "allergens" + "dietaryExclusions" tags
     *                                in taxonomy/products.json — the same
     *                                merged list FoodResolver is called
     *                                with (declared allergies from SAF006
     *                                plus stated dietary preferences from
     *                                PF020). A hard exclusion, not a scoring
     *                                penalty.
     * @param string|null $topBodySystemId This person's #1 ranked body
     *                                       system (the first entry of
     *                                       PriorityScorer's already-sorted
     *                                       output) — compared against each
     *                                       product's formulatedFor tag for
     *                                       the flagship bonus.
     * @param string[] $selectedGoals This person's selected wellness goals
     *                                 (PF014 answers, up to 3 OUT-codes) —
     *                                 compared against each product's
     *                                 formulatedFor.outcomes for the outcome
     *                                 match bonus, and against
     *                                 formulatedFor.exactFor for the
     *                                 forced-inclusion floor.
     */
    public function resolve(
        array $resolvedMechanisms,
        array $excludedTags = [],
        ?string $topBodySystemId = null,
        array $selectedGoals = []
    ): array {
        $catalogue = $this->loader->load(
            $this->knowledgePath . '/taxonomy/products.json'
        );

        $productMechanisms = $this->loader->load(
            $this->knowledgePath . '/taxonomy/product-mechanisms.json'
        );

        $mechanisms = $this->loader->load(
            $this->knowledgePath . '/taxonomy/mechanisms.json'
        );

        $bodySystemMap = $this->loader->load(
            $this->knowledgePath . '/taxonomy/body-systems-mechanisms.json'
        );

        // -------------------------------------------------
        // Product catalogue lookup
        // -------------------------------------------------

        $productLookup = [];

        foreach ($catalogue['products'] as $product) {
            $productLookup[$product['id']] = $product;
        }

        // -------------------------------------------------
        // Mechanism catalogue lookup
        // -------------------------------------------------

        $mechanismNameLookup = [];

        foreach ($mechanisms['mechanisms'] as $mechanism) {
            $mechanismNameLookup[$mechanism['id']] = $mechanism['name'];
        }

        // -------------------------------------------------
        // Mechanism -> body system lookup
        // -------------------------------------------------

        $mechanismSystemLookup = [];

        foreach ($bodySystemMap['bodySystems'] as $bodySystem) {

            $system = $bodySystem['id'];

            foreach ($bodySystem['mechanisms'] as $mechanism) {
                $mechanismSystemLookup[$mechanism['id']][] = $system;
            }
        }

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
        // Score products
        // -------------------------------------------------

        $results = [];

        foreach ($productMechanisms['products'] as $productRelationship) {

            $productId = $productRelationship['productId'];

            $product = $productLookup[$productId] ?? null;

            if ($product === null || ($product['status'] ?? 'Active') !== 'Active') {
                continue;
            }

            // Hard exclusion, not a scoring penalty — same rule FoodResolver
            // applies to foods. A declared allergy or stated dietary
            // preference means this product must never be recommended,
            // regardless of how well it otherwise matches the person's
            // mechanisms.
            $productTags = array_merge(
                $product['allergens'] ?? [],
                $product['dietaryExclusions'] ?? []
            );

            if ($excludedTags && array_intersect($productTags, $excludedTags)) {
                continue;
            }

            $total = 0;
            $matched = [];
            $breakdown = [];
            $pathwayCount = 0;
            $strengthTotal = 0;
            $systemsCovered = [];

            foreach ($productRelationship['mechanisms'] as $mech) {

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

            // Rank this product's own matched pathways strongest-first and
            // decay lower-ranked contributions, exactly as FoodResolver
            // does — see that class's comment for the full rationale.
            usort($matched, fn($a, $b) => $b['contribution'] <=> $a['contribution']);
            usort($breakdown, fn($a, $b) => $b['contribution'] <=> $a['contribution']);

            $decayedTotal = 0;

            foreach ($matched as $rank => $m) {
                $decayedTotal += $m['contribution'] * (self::PATHWAY_DECAY ** $rank);
            }

            $baseClinicalScore =
                ($decayedTotal * 0.80) +
                ($decayedTotal * $averageStrength * 0.20);

            // ---------------------------------------------------------
            // formulatedFor bonus — see class-level constants for the
            // rationale. Bounded and transparent: the strongest single
            // body-system match and the strongest single outcome match
            // each apply once, never summed across several matches.
            // ---------------------------------------------------------
            $formulatedFor = $product['formulatedFor'] ?? [];

            $bodySystemBonusRate = 0.0;
            $bodySystemBonusReason = null;

            if ($topBodySystemId !== null) {
                if (in_array($topBodySystemId, $formulatedFor['primaryBodySystems'] ?? [], true)) {
                    $bodySystemBonusRate = self::PRIMARY_FLAGSHIP_BONUS;
                    $bodySystemBonusReason = 'primary';
                } elseif (in_array($topBodySystemId, $formulatedFor['secondaryBodySystems'] ?? [], true)) {
                    $bodySystemBonusRate = self::SECONDARY_FLAGSHIP_BONUS;
                    $bodySystemBonusReason = 'secondary';
                }
            }

            $matchedOutcomes = array_values(array_intersect(
                $formulatedFor['outcomes'] ?? [],
                $selectedGoals
            ));

            $outcomeBonusRate = $matchedOutcomes ? self::OUTCOME_MATCH_BONUS : 0.0;

            $totalBonusRate = $bodySystemBonusRate + $outcomeBonusRate;
            $clinicalScore = $baseClinicalScore * (1 + $totalBonusRate);

            // Direct match check for forced inclusion (see class-level
            // FORCED_INCLUSION_RANK comment) — is one of the person's
            // selected goals in this product's explicit, hand-approved
            // exactFor list?
            $directMatchGoals = array_values(array_intersect(
                $formulatedFor['exactFor'] ?? [],
                $selectedGoals
            ));

            $servingsPerContainer = $product['servingsPerContainer'] ?? null;
            $servingsPerDay       = $product['servingsPerDay'] ?? null;

            $results[] = [
                'productId'     => $productId,
                'name'          => $product['name'] ?? $productId,
                'description'   => $product['description'] ?? null,
                'claims'        => $product['claims'] ?? [],
                'category'      => $product['category'] ?? null,
                'format'        => $product['format'] ?? null,
                'servingSize'         => $product['servingSize'] ?? null,
                'servingsPerContainer' => $servingsPerContainer,
                'servingsPerDay'       => $servingsPerDay,
                'lastsDays'     => ($servingsPerContainer && $servingsPerDay)
                    ? (int) floor($servingsPerContainer / $servingsPerDay)
                    : null,
                'image'         => $product['image'] ?? null,
                // Three figures, all derived from the single stored 'cost'
                // (the one-off retail price) rather than stored separately,
                // so the subscription price and the per-day figure can
                // never disagree with the base price:
                //  - priceOneOff: the plain retail price for one container.
                //  - priceSubscription: subscribe & save price (10% off,
                //    free shipping, cancel anytime — policy, not per-item
                //    data).
                //  - pricePerDay: cost spread over how long one container
                //    lasts at the standard daily serving (lastsDays above),
                //    so it's comparable across products with very different
                //    formats/container sizes.
                'cost'              => $product['cost'] ?? null,
                'priceOneOff'       => $product['cost'] ?? null,
                'priceSubscription' => isset($product['cost'])
                    ? round($product['cost'] * (1 - self::SUBSCRIPTION_DISCOUNT), 2)
                    : null,
                'pricePerDay' => (isset($product['cost']) && !empty($servingsPerContainer) && !empty($servingsPerDay))
                    ? round($product['cost'] / (int) floor($servingsPerContainer / $servingsPerDay), 2)
                    : null,
                'shopUrl'           => $product['shopUrl'] ?? null,
                'factsheetUrl'      => $product['factsheetUrl'] ?? null,
                'allergens'         => $product['allergens'] ?? [],
                'dietaryExclusions' => $product['dietaryExclusions'] ?? [],
                'clinicalScore' => round($clinicalScore, 2),
                'matchedMechanisms' => $matched,

                'scoreBreakdown' => [
                    'total' => round($decayedTotal, 2),
                    'mechanisms' => $breakdown,
                    'summary' => [
                        'relevance' => round($decayedTotal * 0.80, 2),
                        'strength'  => round($decayedTotal * $averageStrength * 0.20, 2),
                        'systemsCovered'    => array_values($systemsCovered),
                        'systemsCoveredIds' => array_keys($systemsCovered),
                        'systemCount'       => $systemCount,
                    ],
                    'formulatedForBonus' => [
                        'baseScore'          => round($baseClinicalScore, 2),
                        'bodySystemMatch'    => $bodySystemBonusReason,
                        'bodySystemBonusRate' => $bodySystemBonusRate,
                        'matchedOutcomes'    => $matchedOutcomes,
                        'outcomeBonusRate'   => $outcomeBonusRate,
                        'totalBonusRate'     => $totalBonusRate,
                        'preFloorScore'      => round($clinicalScore, 2),
                        'directMatchGoals'   => $directMatchGoals,
                        // Filled in below, once every product's score is
                        // known and the #3 cutoff can be worked out.
                        'forcedInclusion'    => false,
                        'finalScore'         => round($clinicalScore, 2),
                    ],
                ],
            ];
        }

        usort(
            $results,
            fn($a, $b) => $b['clinicalScore'] <=> $a['clinicalScore']
        );

        // ---------------------------------------------------------------
        // Forced inclusion — see FORCED_INCLUSION_RANK. Floor any directly-
        // matched product up to the current #3 score, then re-sort once.
        // Reads the cutoff from the pre-floor ranking so a product being
        // lifted can't inflate the very cutoff it's being measured against.
        // ---------------------------------------------------------------
        $cutoffIndex = min(self::FORCED_INCLUSION_RANK, count($results)) - 1;
        $floorScore = $cutoffIndex >= 0 ? $results[$cutoffIndex]['clinicalScore'] : 0.0;

        $anyForced = false;

        foreach ($results as &$product) {
            $directMatchGoals = $product['scoreBreakdown']['formulatedForBonus']['directMatchGoals'];

            if ($directMatchGoals && $product['clinicalScore'] < $floorScore) {
                $product['clinicalScore'] = round($floorScore + 0.01, 2);
                $product['scoreBreakdown']['formulatedForBonus']['forcedInclusion'] = true;
                $product['scoreBreakdown']['formulatedForBonus']['finalScore'] = $product['clinicalScore'];
                $anyForced = true;
            }
        }
        unset($product);

        if ($anyForced) {
            usort(
                $results,
                fn($a, $b) => $b['clinicalScore'] <=> $a['clinicalScore']
            );
        }

        $maxScore = $results[0]['clinicalScore'] ?? 1;

        foreach ($results as $i => &$product) {
            $product['rank'] = $i + 1;

            $product['reportScore'] = (int) round(
                ($product['clinicalScore'] / $maxScore) * 100
            );
        }
        unset($product);

        return $results;
    }
}
