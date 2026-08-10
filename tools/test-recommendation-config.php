<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Recommendation\RecommendationConfiguration;

$config = new RecommendationConfiguration();

echo "Mechanism weight: "
    . $config->priorityWeight('mechanisms')
    . PHP_EOL;

echo "Food limit: "
    . $config->recommendationLimit('foods')
    . PHP_EOL;

echo "Minimum score: "
    . $config->minimumRecommendationScore()
    . PHP_EOL;