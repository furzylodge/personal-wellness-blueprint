<?php

declare(strict_types=1);

namespace PWB\Recommendation;

use PWB\Loader\JsonLoader;

final class ProductResolver
{
    public function __construct(
        private JsonLoader $loader,
        private string $knowledgePath
    ) {}

    public function resolve(array $resolvedBioactives): array
    {
        $catalogue = $this->loader->load(
            $this->knowledgePath . '/taxonomy/products.json'
        );

        $relationships = $this->loader->load(
            $this->knowledgePath . '/taxonomy/product-bioactives.json'
        );

        // Product lookup
        $productLookup = [];
        foreach ($catalogue['products'] as $product) {
            $productLookup[$product['id']] = $product;
        }

        // Bioactive score lookup
        $bioScores = [];
        foreach ($resolvedBioactives as $bio) {
            $bioScores[$bio['bioactiveId']] = $bio['clinicalScore'];
        }

        // Status weighting
        $statusWeight = [
            'DIRECT'                     => 1.00,
            'CONFIRMED_PRODUCT_SPECIFIC' => 1.00,
            'CONFIRMED_PRODUCT_PROFILE'  => 0.95,
            'CONFIRMED'                 => 0.90,
            'SOURCE_DEFINED'            => 0.75
        ];

        $results = [];

        foreach ($relationships['products'] as $product) {

            $score = 0.0;
            $matched = [];

            foreach ($product['bioactives'] as $bio) {

                $bioId = $bio['id'];

                if (!isset($bioScores[$bioId])) {
                    continue;
                }

                $weight = $statusWeight[$bio['status']] ?? 0.5;

                $contribution = $bioScores[$bioId] * $weight;
                $score += $contribution;

                $matched[] = [
                    'id' => $bioId,
                    'status' => $bio['status'],
                    'contribution' => round($contribution, 2)
                ];
            }

            if ($score <= 0) {
                continue;
            }

            $results[] = [
                'productId' => $product['id'],
                'name' => $productLookup[$product['id']]['name'] ?? $product['id'],
                'clinicalScore' => round($score, 2),
                'matchedBioactives' => $matched
            ];
        }

        usort(
            $results,
            fn($a, $b) => $b['clinicalScore'] <=> $a['clinicalScore']
        );

        foreach ($results as $i => &$row) {
            $row['rank'] = $i + 1;
        }

        return $results;
    }
}