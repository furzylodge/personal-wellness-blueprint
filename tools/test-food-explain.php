<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Loader\JsonLoader;
use PWB\Utils\TestAssessmentFactory;
use PWB\Assessment\HealthProfileBuilder;
use PWB\Recommendation\PriorityEngine;
use PWB\Recommendation\MechanismResolver;
use PWB\Recommendation\FoodResolver;

$persona = $argv[1] ?? 'respiratory-inflammation';

$loader = new JsonLoader();

// -----------------------------------------------------
// Build assessment
// -----------------------------------------------------

$factory = new TestAssessmentFactory(
    __DIR__ . '/../knowledgebase'
);

$assessment = $factory->load($persona);

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

$foods = (new FoodResolver(
    $loader,
    __DIR__ . '/../knowledgebase'
))->resolve($mechanisms);

// -----------------------------------------------------
// Output
// -----------------------------------------------------

echo PHP_EOL;
echo "Food Recommendation Explanation" . PHP_EOL;
echo "===============================" . PHP_EOL;
echo "Persona: {$persona}" . PHP_EOL;

foreach (array_slice($foods, 0, 10) as $food) {

    echo PHP_EOL;
    echo str_repeat('-', 60) . PHP_EOL;

    printf(
        "#%02d  %s   (%.2f)\n",
        $food['rank'],
        $food['name'],
        $food['clinicalScore']
    );

    echo str_repeat('-', 60) . PHP_EOL;

    foreach ($food['matchedMechanisms'] as $mech) {

        printf(
            "  +%6.2f   %-7s %-38s\n",
            $mech['contribution'],
            $mech['id'],
            $mech['name']
        );

        printf(
            "            Strength: %d/5   Confidence: %.2f\n",
            $mech['strength'],
            $mech['confidence']
        );
    }
}

echo PHP_EOL;