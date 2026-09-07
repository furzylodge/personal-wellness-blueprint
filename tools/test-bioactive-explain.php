<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Loader\JsonLoader;
use PWB\Utils\TestAssessmentFactory;
use PWB\Assessment\HealthProfileBuilder;
use PWB\Recommendation\PriorityEngine;
use PWB\Recommendation\MechanismResolver;
use PWB\Recommendation\BioactiveResolver;

$persona = $argv[1] ?? 'respiratory-inflammation';

$loader = new JsonLoader();

// -----------------------------------------------------
// Build assessment
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

// -----------------------------------------------------
// Output
// -----------------------------------------------------

echo PHP_EOL;
echo "Bioactive Recommendation Explanation" . PHP_EOL;
echo "==================================" . PHP_EOL;
echo "Persona: {$persona}" . PHP_EOL;

foreach (array_slice($bioactives, 0, 10) as $bio) {

    echo PHP_EOL;
    echo str_repeat('=', 70) . PHP_EOL;

    printf(
        "#%02d  %s   (%.2f)\n",
        $bio['rank'],
        $bio['name'],
        $bio['clinicalScore']
    );

    if ($bio['category'] !== '') {
        echo "Category: {$bio['category']}" . PHP_EOL;
    }

    echo str_repeat('-', 70) . PHP_EOL;

    foreach ($bio['matchedMechanisms'] as $mech) {

        // Find mechanism name
        $name = '';
        foreach ($mechanisms as $m) {
            if ($m['mechanismId'] === $mech['id']) {
                $name = $m['mechanismName'];
                break;
            }
        }

        printf(
            " +%6.2f   %-7s %s\n",
            $mech['contribution'],
            $mech['id'],
            $name
        );

        printf(
            "          Strength: %d/5   Confidence: %s\n",
            $mech['strength'],
            $mech['confidence']
        );
    }
}

echo PHP_EOL;