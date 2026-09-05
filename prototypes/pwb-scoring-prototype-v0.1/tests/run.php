<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/SignalScoringEngine.php';

use PWB\Scoring\SignalScoringEngine;

$config = json_decode(
    file_get_contents(__DIR__ . '/../config/signal-scoring-model-v0.1.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);

$engine = new SignalScoringEngine();

$answers = [
    // Synthetic convergence example only.
    'Q02' => 0.67,
    'Q13' => 0.50,
    'Q28' => 0.67,
    'Q04' => 0.25,
    'Q14' => 0.50,
    'Q34' => 0.50,
    'Q12' => 0.25,
    'Q26' => 0.50,
    'Q35' => 0.50,
    'Q45' => 0.00,
];

$results = $engine->score($answers, $config['outcomes']);

foreach ($results as $result) {
    printf(
        "%-42s primary=%s supporting=%s coverage=%s/%s\n",
        $result['name'],
        $result['primary_intensity'] === null
            ? 'n/a'
            : number_format($result['primary_intensity'], 3),
        $result['supporting_intensity'] === null
            ? 'n/a'
            : number_format($result['supporting_intensity'], 3),
        $result['primary_count'],
        count($result['primary']) + count($result['supporting'])
    );
}
