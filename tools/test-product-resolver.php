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

$loader = new JsonLoader();

$assessment = (new TestAssessmentFactory(
    __DIR__ . '/../knowledgebase'
))->load('respiratory-inflammation');

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

echo PHP_EOL;
echo "Top Recommended Products" . PHP_EOL;
echo "========================" . PHP_EOL . PHP_EOL;

foreach (array_slice($products, 0, 20) as $product) {
    printf(
        "#%02d %-36s %8.2f\n",
        $product['rank'],
        $product['name'],
        $product['clinicalScore']
    );
}