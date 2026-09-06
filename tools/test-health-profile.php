<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Assessment\Assessment;
use PWB\Assessment\AssessmentLoader;
use PWB\Assessment\HealthProfileBuilder;
use PWB\Loader\JsonLoader;

$loader = new AssessmentLoader(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
);

$loader->load();
$questions = $loader->questions();

$answers = [];

foreach ($questions as $question) {

    $stored = $question['stored_values'];

    if (!is_array($stored) || empty($stored)) {
        continue;
    }

    $highestIndex = array_keys($stored, max($stored))[0];
    $answers[$question['id']] = $highestIndex;
}

$assessment = new Assessment(
    assessment: ['version' => '1.0.0'],
    answers: $answers
);

$builder = new HealthProfileBuilder(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
);

$profile = $builder->build($assessment);

echo PHP_EOL;
echo "PWB Maximum Score Validation" . PHP_EOL;
echo "============================" . PHP_EOL;

echo "Questions answered : " . count($answers) . PHP_EOL;
echo "Questions skipped  : " . (count($questions) - count($answers)) . PHP_EOL;

echo PHP_EOL;
echo "Normalised Body Systems" . PHP_EOL;
echo "-----------------------" . PHP_EOL;

foreach ($profile->bodySystems as $id => $score) {
    printf("%-6s : %6.1f%%\n", $id, $score);
}