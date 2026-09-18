<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Loader\JsonLoader;
use PWB\Utils\TestAssessmentFactory;
use PWB\Assessment\HealthProfileBuilder;
use PWB\Recommendation\PriorityEngine;
use PWB\Recommendation\MechanismResolver;
use PWB\Recommendation\BioactiveResolver;
use PWB\Recommendation\ProductResolver;

$persona = $argv[1] ?? 'respiratory-inflammation';

$loader = new JsonLoader();

// -----------------------------------------------------
// Build recommendation pipeline
// -----------------------------------------------------

$assessment = (new TestAssessmentFactory(
    __DIR__ . '/../knowledgebase'
))->load($persona);

$profile = (new HealthProfileBuilder(
    $loader,
    __DIR__ . '/../knowledgebase'
))->build($assessment);

$priorities = (new PriorityEngine(
    $loader,
    __DIR__ . '/../knowledgebase'
))->rank($profile);

$mechanisms = (new MechanismResolver(
    $loader,
    __DIR__ . '/../knowledgebase'
))->resolve(array_slice($priorities, 0, 3));

$excludedTags = array_merge($assessment->restrictions ?? [], $assessment->preferences ?? []);

$products = (new ProductResolver(
    $loader,
    __DIR__ . '/../knowledgebase'
))->resolve($mechanisms, $excludedTags);

// -----------------------------------------------------
// Output
// -----------------------------------------------------

echo PHP_EOL;
echo "Product Recommendation Explanation" . PHP_EOL;
echo "=================================" . PHP_EOL;
echo "Persona: {$persona}" . PHP_EOL;

foreach (array_slice($products, 0, 10) as $product) {

    echo PHP_EOL;
    echo str_repeat('=', 72) . PHP_EOL;

    printf(
        "#%02d  %s  (%.2f)\n",
        $product['rank'],
        $product['name'],
        $product['clinicalScore']
    );

    echo str_repeat('-', 72) . PHP_EOL;

    foreach ($product['matchedMechanisms'] as $match) {

        printf(
            " +%7.2f   %-8s %s\n",
            $match['contribution'],
            $match['id'],
            $match['name']
        );

        printf(
            "           Strength: %d  Confidence: %.2f\n",
            $match['strength'],
            $match['confidence']
        );
    }

    echo PHP_EOL;
    echo "  Why selected: " . ($product['whySelected'] ?? '(generated in ReportDataBuilder, not here)') . PHP_EOL;
}

echo PHP_EOL;

// -----------------------------------------------------
// Audit products with zero score
// -----------------------------------------------------

$productMechanisms = $loader->load(
    __DIR__ . '/../knowledgebase/taxonomy/product-mechanisms.json'
);

$scored = [];
foreach ($products as $p) {
    $scored[$p['productId']] = true;
}

echo PHP_EOL;
echo "Products With No Matching Clinical Mechanisms" . PHP_EOL;
echo "============================================" . PHP_EOL;

foreach ($productMechanisms['products'] as $product) {

    if (isset($scored[$product['productId']])) {
        continue;
    }

    echo PHP_EOL;
    echo $product['productName'] . PHP_EOL;

    foreach (array_slice($product['mechanisms'], 0, 5) as $mech) {
        printf(
            "   %-8s %-35s strength=%d confidence=%.2f\n",
            $mech['id'],
            $mech['name'],
            $mech['strength'],
            $mech['confidence']
        );
    }
}