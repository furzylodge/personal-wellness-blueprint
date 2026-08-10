<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Assessment\AssessmentLoader;
use PWB\Assessment\HealthProfileBuilder;
use PWB\Recommendation\RecommendationConfiguration;
use PWB\Recommendation\Scorers\PriorityScorer;

echo PHP_EOL;
echo "Priority Scorer Test" . PHP_EOL;
echo "====================" . PHP_EOL;
echo PHP_EOL;

// Load assessment
$assessment = AssessmentLoader::load(
    __DIR__ . '/../knowledgebase/test-data/assessment.json'
);

// Build HealthProfile
$builder = new HealthProfileBuilder();

$profile = $builder->build($assessment);

// Load recommendation configuration
$config = new RecommendationConfiguration();

// Score priorities
$scorer = new PriorityScorer();

$priorities = $scorer->score(
    $profile,
    $config
);

// Display results
echo "Generated " . count($priorities) . " priorities." . PHP_EOL;
echo PHP_EOL;

foreach ($priorities as $priority) {

    printf(
        "%-8s %-15s Score:%6.2f  Weight:%4.2f  Final:%6.2f\n",
        $priority->id,
        $priority->type,
        $priority->score,
        $priority->weight,
        $priority->finalScore
    );

}

echo PHP_EOL;
echo "Test complete." . PHP_EOL;