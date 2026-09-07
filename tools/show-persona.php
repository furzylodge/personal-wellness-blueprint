<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Loader\JsonLoader;
use PWB\Assessment\AssessmentLoader;

$personaId = $argv[1] ?? 'respiratory-inflammation';

$loader = new JsonLoader();

$persona = $loader->load(
    __DIR__ . "/../knowledgebase/personas/persona-{$personaId}.json"
);

$assessmentLoader = new AssessmentLoader(
    new JsonLoader(),
    __DIR__ . '/../knowledgebase'
);

$assessmentLoader->load();

$questionLookup = [];

foreach ($assessmentLoader->questions() as $q) {
    $questionLookup[$q['id']] = $q;
}

echo PHP_EOL;
echo "Persona: {$persona['name']}" . PHP_EOL;
echo "Extends: " . ($persona['extends'] ?? 'none') . PHP_EOL;

echo PHP_EOL;
echo "Override Details" . PHP_EOL;
echo "================" . PHP_EOL;

foreach ($persona['answers'] as $id => $targetValue) {

    if (!isset($questionLookup[$id])) {
        echo "{$id}  UNKNOWN QUESTION" . PHP_EOL;
        continue;
    }

    $question = $questionLookup[$id];
    $stored   = $question['stored_values'];

    $bestIndex = 0;
    $bestDiff  = PHP_FLOAT_MAX;

    foreach ($stored as $i => $value) {

        $diff = abs((float)$value - (float)$targetValue);

        if ($diff < $bestDiff) {
            $bestDiff  = $diff;
            $bestIndex = $i;
        }
    }

    printf(
        "%s\n  Target: %.2f   Selected index: %d   Actual value: %.2f\n  %s\n\n",
        $id,
        $targetValue,
        $bestIndex,
        $stored[$bestIndex],
        $question['question']
    );
}