<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Assessment\Assessment;
use PWB\Assessment\HealthProfileBuilder;
use PWB\Loader\JsonLoader;
use PWB\Recommendation\PriorityEngine;
use PWB\Recommendation\MechanismResolver;
use PWB\Utils\TestAssessmentFactory;
use PWB\Recommendation\BioactiveResolver;
use PWB\Recommendation\FoodResolver;


$builder = new HealthProfileBuilder(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
);

$factory = new TestAssessmentFactory(
    __DIR__ . '/../knowledgebase'
);

$assessment = $factory->load('respiratory-inflammation');
$profile = $builder->build($assessment);


$priorityEngine = new PriorityEngine(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
);

$priorities = $priorityEngine->rank($profile);

echo PHP_EOL;
echo "Body System Priorities" . PHP_EOL;
echo "======================" . PHP_EOL;

foreach ($priorities as $p) {
    printf(
        "#%d  %-20s %6.1f%%\n",
        $p['rank'],
        $p['name'],
        $p['score']
    );
}

echo PHP_EOL;

$resolver = new MechanismResolver(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
);

$mechanisms = $resolver->resolve(
    array_slice($priorities, 0, 3)
);

$bioactiveResolver = new BioactiveResolver(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
);

$bioactives = $bioactiveResolver->resolve($mechanisms);

$foodResolver = new FoodResolver(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
);

$foods = $foodResolver->resolve($mechanisms);

echo PHP_EOL;
echo "Top Clinical Mechanisms" . PHP_EOL;
echo "=======================" . PHP_EOL;

foreach (array_slice($mechanisms, 0, 15) as $m) {

    printf(
    "%-8s %-45s %6.2f\n",
    $m['mechanismId'],
    $m['mechanismName'],
    $m['clinicalScore']
);

}

echo PHP_EOL;
echo "Top Bioactives" . PHP_EOL;
echo "====================" . PHP_EOL;

foreach (array_slice($bioactives, 0, 15) as $b) {
    printf(
        "%-8s %-35s %6.2f\n",
        $b['bioactiveId'],
        $b['name'],
        $b['clinicalScore']
    );
}

echo PHP_EOL;
echo "Top Foods" . PHP_EOL;
echo "====================" . PHP_EOL;

foreach (array_slice($foods, 0, 15) as $f) {
    printf(
        "%-8s %-35s %6.2f\n",
        $f['foodId'],
        $f['name'],
        $f['clinicalScore']
    );
}
