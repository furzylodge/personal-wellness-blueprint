<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Assessment\AssessmentLoader;
use PWB\Loader\JsonLoader;

$loader = new AssessmentLoader(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
);

$assessment = $loader->load();

echo PHP_EOL;
echo "Assessment Loader Test" . PHP_EOL;
echo "======================" . PHP_EOL;

echo "Version: " . $assessment->assessment['version'] . PHP_EOL;
echo "Questions: " . count($loader->questions()) . PHP_EOL;
echo "Matrix Rows: " . count($loader->matrix()) . PHP_EOL;

$questions = $loader->questions();

echo PHP_EOL;
echo "First Question" . PHP_EOL;
echo "--------------" . PHP_EOL;
echo "ID: " . $questions[0]['id'] . PHP_EOL;
echo "Question: " . $questions[0]['question'] . PHP_EOL;

echo PHP_EOL;
echo "Stored Values:" . PHP_EOL;
print_r($questions[0]['stored_values']);

$matrix = $loader->matrix();

echo PHP_EOL;
echo "Body System Mapping:" . PHP_EOL;
print_r($matrix[0]['body_system_scores']);