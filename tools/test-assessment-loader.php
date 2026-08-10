<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Assessment\AssessmentLoader;

$assessment = AssessmentLoader::load(
    __DIR__
    . '/../knowledgebase/test-data/assessment.json'
);

echo PHP_EOL;
echo "Assessment Loaded Successfully" . PHP_EOL;
echo "==============================" . PHP_EOL;

print_r($assessment);