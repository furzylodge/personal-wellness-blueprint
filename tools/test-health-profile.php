<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Assessment\Assessment;
use PWB\Assessment\HealthProfileBuilder;
use PWB\Loader\JsonLoader;

$assessment = new Assessment(
    assessment: [
        'version' => '1.0.0'
    ],
    answers: [
        // Sample answers (selected option indexes)
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

echo PHP_EOL;
echo "Health Profile Test" . PHP_EOL;
echo "===================" . PHP_EOL;



foreach ($profile->bodySystems as $id => $score) {
    printf("%s : %.2f\n", $id, $score);
}