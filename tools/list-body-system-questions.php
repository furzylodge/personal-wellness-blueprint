<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Loader\JsonLoader;

$bodySystem = strtoupper($argv[1] ?? 'BS006');

$loader = new JsonLoader();

$questions = $loader->load(
    __DIR__ . '/../knowledgebase/assessment/questions.json'
);

$matrix = $loader->load(
    __DIR__ . '/../knowledgebase/assessment/question-body-system-matrix.json'
);

$bodySystems = $loader->load(
    __DIR__ . '/../knowledgebase/taxonomy/body-systems.json'
);

// -----------------------------------------------------
// Build lookups
// -----------------------------------------------------

$questionLookup = [];
foreach ($questions as $question) {
    $questionLookup[$question['id']] = $question;
}

$bodySystemName = $bodySystem;

foreach ($bodySystems['bodySystems'] as $bs) {
    if ($bs['id'] === $bodySystem) {
        $bodySystemName = $bs['name'];
        break;
    }
}

// -----------------------------------------------------
// Output
// -----------------------------------------------------

echo PHP_EOL;
echo "{$bodySystemName} ({$bodySystem})" . PHP_EOL;
echo str_repeat('=', strlen($bodySystemName) + strlen($bodySystem) + 3) . PHP_EOL;
echo PHP_EOL;

$count = 0;

foreach ($matrix as $row) {

    if (!isset($row['body_system_scores'][$bodySystem])) {
        continue;
    }

    if ($row['body_system_scores'][$bodySystem] !== 'Y') {
        continue;
    }

    $id = $row['question_id'];

    if (!isset($questionLookup[$id])) {
        continue;
    }

    $count++;

    printf(
        "%-8s %s\n",
        $id,
        $questionLookup[$id]['question']
    );
}

echo PHP_EOL;
echo "Total questions: {$count}" . PHP_EOL;
echo PHP_EOL;