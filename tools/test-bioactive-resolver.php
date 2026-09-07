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

echo PHP_EOL;
echo "Top Bioactives" . PHP_EOL;
echo "==============" . PHP_EOL . PHP_EOL;

foreach (array_slice($bioactives, 0, 20) as $bio) {

    printf(
        "#%02d %-30s %8.2f\n",
        $bio['rank'],
        $bio['name'],
        $bio['clinicalScore']
    );
}