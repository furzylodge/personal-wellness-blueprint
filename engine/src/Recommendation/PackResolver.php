<?php

declare(strict_types=1);

namespace PWB\Recommendation;

use PWB\Loader\JsonLoader;

/**
 * Resolves the single "optional program" pack to offer alongside the top
 * recommended product, per Simon's own framing: Synergy sells a small
 * number of targeted, multi-product bundles ("packs") that "contribute a
 * focussed outcome" - e.g. Purify Kit for digestion, Performance Power Pack
 * for fitness/performance. These are always an optional extra shown beside
 * the main product recommendation, never a replacement for it and never
 * scored/ranked against a person's mechanisms the way ProductResolver
 * scores products.
 *
 * Deliberately simple by design: a pack is offered only when its
 * formulatedFor.exactFor (packs.json - the same narrow, hand-curated
 * "this is exactly what it's for" field already used for products)
 * intersects one of the person's selected wellness goals (PF014). Many
 * packs (broad "everyday" bundles) have no exactFor at all and are simply
 * never offered by this resolver - they still exist in the catalogue for
 * the shop itself, just not surfaced here.
 *
 * Only one pack is ever returned, mirroring the "single product and an
 * optional pack alongside" framing - if more than one pack's exactFor
 * matches, the first match against the person's own goal order (PF014
 * answers, already priority-ordered) wins, same tie-break style as
 * ReportDataBuilder::buildDashboard()'s goalAlignment note.
 */
final class PackResolver
{
    // Same fixed site-wide policy ProductResolver applies to products -
    // kept identical so a pack's pricing reads consistently alongside the
    // product strip it sits next to.
    private const SUBSCRIPTION_DISCOUNT = 0.10;

    public function __construct(
        private JsonLoader $loader,
        private string $knowledgePath
    ) {}

    /**
     * @param string[] $selectedGoals This person's selected wellness goals
     *                                 (PF014 answers, up to 3 OUT-codes, in
     *                                 the order they were selected/prioritised).
     */
    public function resolve(array $selectedGoals = []): ?array
    {
        $packsFile = $this->knowledgePath . '/taxonomy/packs.json';

        if (!file_exists($packsFile)) {
            return null;
        }

        $catalogue = $this->loader->load($packsFile);

        $productCatalogue = $this->loader->load(
            $this->knowledgePath . '/taxonomy/products.json'
        );

        $productLookup = [];

        foreach ($productCatalogue['products'] as $product) {
            $productLookup[$product['id']] = $product;
        }

        if (empty($selectedGoals)) {
            return null;
        }

        // Walk the person's own goals in priority order, and within each
        // goal walk the packs in catalogue order - the first pack whose
        // exactFor contains that goal wins. This means a person's #1
        // stated goal always gets first refusal on which pack is offered,
        // rather than an arbitrary catalogue-order pick across all goals.
        foreach ($selectedGoals as $goalId) {

            foreach ($catalogue['packs'] as $pack) {

                if (($pack['status'] ?? 'Active') !== 'Active') {
                    continue;
                }

                $exactFor = $pack['formulatedFor']['exactFor'] ?? [];

                if (!in_array($goalId, $exactFor, true)) {
                    continue;
                }

                return $this->buildPackResult($pack, $productLookup, $goalId);
            }
        }

        return null;
    }

    private function buildPackResult(array $pack, array $productLookup, string $matchedGoal): array
    {
        $products = [];
        $individualTotal = 0.0;
        $hasFullPricing = true;

        foreach ($pack['products'] as $item) {

            $product = $productLookup[$item['productId']] ?? null;

            $products[] = [
                'productId' => $item['productId'],
                'name'      => $product['name'] ?? $item['productId'],
                'quantity'  => $item['quantity'],
            ];

            if (isset($product['cost'])) {
                $individualTotal += $product['cost'] * $item['quantity'];
            } else {
                $hasFullPricing = false;
            }
        }

        $cost = $pack['cost'];

        // The "saving" is only meaningful once every constituent product
        // carries its own real cost - while products.json still holds the
        // £50 placeholder across the board, this figure won't reflect a
        // genuine saving, exactly like the placeholder pricing already
        // shown on the product strip. Guarded rather than shown as a
        // misleading £0/negative saving.
        $saving = ($hasFullPricing && $individualTotal > $cost)
            ? round($individualTotal - $cost, 2)
            : null;

        return [
            'packId'          => $pack['id'],
            'name'            => $pack['name'],
            'description'     => $pack['description'] ?? null,
            'products'        => $products,
            'image'           => $pack['image'] ?? null,
            'shopUrl'         => $pack['shopUrl'] ?? null,
            'matchedGoal'     => $matchedGoal,
            'priceOneOff'     => $cost,
            'priceSubscription' => round($cost * (1 - self::SUBSCRIPTION_DISCOUNT), 2),
            'individualTotal' => $hasFullPricing ? round($individualTotal, 2) : null,
            'saving'          => $saving,
        ];
    }
}
