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

// Load the questionnaire knowledge
$loader->load();
$questions = $loader->questions();

$answers = [];

// Select the LOWEST scoring option for every scored question
foreach ($questions as $question) {

    $stored = $question['stored_values'];

    // Skip informational / conditional questions
    if (!is_array($stored) || empty($stored)) {
        continue;
    }

    $lowestIndex = array_keys($stored, min($stored))[0];
    $answers[$question['id']] = $lowestIndex;
}

$assessment = new Assessment(
    assessment: [
        'version' => '1.0.0'
    ],
    answers: $answers
);

$builder = new HealthProfileBuilder(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
);

$profile = $builder->build($assessment);

echo PHP_EOL;
echo "PWB Minimum Score Validation" . PHP_EOL;
echo "============================" . PHP_EOL;

echo "Questions answered : " . count($answers) . PHP_EOL;
echo "Questions skipped  : " . (count($questions) - count($answers)) . PHP_EOL;

echo PHP_EOL;
echo "Normalised Body Systems" . PHP_EOL;
echo "-----------------------" . PHP_EOL;

$passed = true;

foreach ($profile->bodySystems as $id => $score) {

    printf("%-6s : %5.1f%%\n", $id, $score);

    if ($score != 0.0) {
        $passed = false;
    }
}

echo PHP_EOL;

if ($passed) {
    echo "PASS: All body systems correctly normalise to 0.0%" . PHP_EOL;
} else {
    echo "FAIL: One or more body systems are above 0.0%" . PHP_EOL;
}