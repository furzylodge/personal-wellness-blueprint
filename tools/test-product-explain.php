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

$bioactives = (new BioactiveResolver(
    $loader,
    __DIR__ . '/../knowledgebase'
))->resolve($mechanisms);

$products = (new ProductResolver(
    $loader,
    __DIR__ . '/../knowledgebase'
))->resolve($bioactives);

// -----------------------------------------------------
// Bioactive lookup
// -----------------------------------------------------

$bioLookup = [];
foreach ($bioactives as $bio) {
    $bioLookup[$bio['bioactiveId']] = $bio;
}

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

    foreach ($product['matchedBioactives'] as $match) {

        $bio = $bioLookup[$match['id']] ?? null;

        $bioName = $bio['name'] ?? $match['id'];

        printf(
            " +%7.2f   %-8s %s\n",
            $match['contribution'],
            $match['id'],
            $bioName
        );

        printf(
            "           Status: %s\n",
            $match['status']
        );
    }
}

echo PHP_EOL;

// -----------------------------------------------------
// Audit products with zero score
// -----------------------------------------------------

$relationships = $loader->load(
    __DIR__ . '/../knowledgebase/taxonomy/product-bioactives.json'
);

$scored = [];
foreach ($products as $p) {
    $scored[$p['productId']] = true;
}

echo PHP_EOL;
echo "Products With No Matching Clinical Bioactives" . PHP_EOL;
echo "============================================" . PHP_EOL;

foreach ($relationships['products'] as $product) {

    if (isset($scored[$product['id']])) {
        continue;
    }

    echo PHP_EOL;
    echo $product['name'] . PHP_EOL;

    foreach ($product['bioactives'] as $bio) {
        $name = $bioLookup[$bio['id']]['name'] ?? $bio['id'];

        printf(
            "   %-8s %-35s %s\n",
            $bio['id'],
            $name,
            $bio['status']
        );
    }
}