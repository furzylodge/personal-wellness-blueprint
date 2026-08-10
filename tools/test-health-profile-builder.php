<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Assessment\AssessmentLoader;
use PWB\Assessment\HealthProfileBuilder;

$assessment = AssessmentLoader::load(
    __DIR__ . '/../knowledgebase/test-data/assessment.json'
);

$builder = new HealthProfileBuilder();

$profile = $builder->build($assessment);

echo PHP_EOL;
echo "Health Profile Created Successfully" . PHP_EOL;
echo "===================================" . PHP_EOL;

print_r($profile);