<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Loader\JsonLoader;
use PWB\Utils\TestAssessmentFactory;
use PWB\Assessment\HealthProfileBuilder;
use PWB\Recommendation\PriorityEngine;
use PWB\Recommendation\MechanismResolver;
use PWB\Recommendation\FoodResolver;

$factory = new TestAssessmentFactory(
    __DIR__ . '/../knowledgebase'
);

$assessment = $factory->load('respiratory-inflammation');

$profile = (new HealthProfileBuilder(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
))->build($assessment);

$priorities = (new PriorityEngine(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
))->rank($profile);

$mechanisms = (new MechanismResolver(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
))->resolve(array_slice($priorities, 0, 3));

$foods = (new FoodResolver(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
))->resolve($mechanisms);

echo PHP_EOL;
echo "Top Recommended Foods" . PHP_EOL;
echo "=====================" . PHP_EOL . PHP_EOL;

foreach (array_slice($foods, 0, 20) as $food) {

    printf(
        "#%02d %-28s %8.2f\n",
        $food['rank'],
        $food['name'],
        $food['clinicalScore']
    );
}