<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Assessment\Assessment;
use PWB\Assessment\HealthProfileBuilder;
use PWB\Loader\JsonLoader;
use PWB\Recommendation\Scorers\PriorityScorer;
use PWB\Recommendation\MechanismResolver;
use PWB\Recommendation\BioactiveResolver;

$assessment = new Assessment(
    assessment: [],
    answers: [
        'PHY001' => ['eczema'],
        'PHY003' => 2,
        'DIG001' => ['bad_breath'],
        'SLP001' => 1,
        'NUT001' => 3,
    ],
    bodySystems: [],
    preferences: [],
    restrictions: [],
    goals: []
);

$builder = new HealthProfileBuilder(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
);

$profile = $builder->build($assessment);

$priorityScorer = new PriorityScorer();
$priorities = $priorityScorer->score($profile);

$mechanisms = (new MechanismResolver(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
))->resolve($priorities);

$bioactives = (new BioactiveResolver(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
))->resolve($mechanisms);

echo PHP_EOL . "=== BODY SYSTEMS ===" . PHP_EOL;
foreach ($priorities as $p) {
    printf("%-15s %6.2f\n", $p['name'], $p['score']);
}

echo PHP_EOL . "=== TOP 10 MECHANISMS ===" . PHP_EOL;
foreach (array_slice($mechanisms, 0, 10) as $m) {
    printf("%-40s %5.2f\n", $m['mechanismName'], $m['clinicalScore']);
}

echo PHP_EOL . "=== TOP 10 BIOACTIVES ===" . PHP_EOL;
foreach (array_slice($bioactives, 0, 10) as $b) {
    printf("%-35s %5.2f\n", $b['name'], $b['clinicalScore']);
}