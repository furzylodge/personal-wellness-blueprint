<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Assessment\Assessment;
use PWB\Assessment\HealthProfileBuilder;
use PWB\Loader\JsonLoader;
use PWB\Recommendation\PriorityEngine;

$assessment = new Assessment(
    answers: [
        'CTX001' => 0,
        'IMM001' => 2,
        'DIG001' => 1,
        'DIG002' => 3,
        'PHY004' => 1,
    ]
);

$builder = new HealthProfileBuilder(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
);

$profile = $builder->build($assessment);


$engine = new PriorityEngine(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
);

$priorities = $engine->rank($profile);

echo PHP_EOL;
echo "Priority Engine Test" . PHP_EOL;
echo "====================" . PHP_EOL;

foreach ($priorities as $item) {
    printf(
        "#%d  %-15s %6.1f%%\n",
        $item['rank'],
        $item['name'],
        $item['score']
    );
}