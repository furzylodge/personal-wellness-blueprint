<?php

declare(strict_types=1);

/**
 * Runs one or more personas (knowledgebase/personas/persona-*.json) all the
 * way through the real recommendation pipeline, so we can validate both the
 * *content* (does a "poor sleeper" persona actually surface sleep-relevant
 * recommendations?) and the *rendering* (does the HTML report look right)
 * without having to fill in the live questionnaire by hand each time.
 *
 * Usage:
 *   php tools/run-persona.php                     # run every persona
 *   php tools/run-persona.php respiratory-inflammation
 *   php tools/run-persona.php respiratory-inflammation poor-sleeper
 *
 * For each persona this prints a console summary (top body systems, top
 * mechanisms, top foods, and a pass/fail against any "expected" block in
 * the persona file) and writes the full rendered report to
 * tools/reports/report-<persona-id>.html for visual review.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Reports\ReportBuilder;
use PWB\Reports\ReportDataBuilder;
use PWB\Utils\TestAssessmentFactory;

$kb = __DIR__ . '/../knowledgebase';
$outputDir = __DIR__ . '/reports';

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

$requested = array_slice($argv, 1);

if (empty($requested)) {
    $requested = array_map(
        fn($file) => preg_replace('/^persona-|\.json$/', '', basename($file)),
        glob($kb . '/personas/persona-*.json')
    );
    sort($requested);
}

$factory = new TestAssessmentFactory($kb);
$overallExitCode = 0;

foreach ($requested as $personaId) {

    echo str_repeat('=', 60) . PHP_EOL;
    echo "Persona: {$personaId}" . PHP_EOL;
    echo str_repeat('=', 60) . PHP_EOL;

    $personaFile = $kb . "/personas/persona-{$personaId}.json";

    if (!file_exists($personaFile)) {
        echo "  SKIPPED - no such persona file: {$personaFile}\n\n";
        $overallExitCode = 1;
        continue;
    }

    $persona = json_decode(file_get_contents($personaFile), true);

    try {
        $assessment = $factory->load($personaId);
        $reportData = (new ReportDataBuilder())->build($assessment);
    } catch (\Throwable $e) {
        echo "  FAILED: " . $e->getMessage() . " at " . $e->getFile() . ':' . $e->getLine() . "\n\n";
        $overallExitCode = 1;
        continue;
    }

    // ---- Body systems, highest first ----
    $bodySystems = $reportData['priorities'] ?? [];
    usort($bodySystems, fn($a, $b) => $b->finalScore <=> $a->finalScore);

    echo "\nTop body systems:\n";
    foreach (array_slice($bodySystems, 0, 5) as $p) {
        printf("  %-8s %6.2f\n", $p->id, $p->finalScore);
    }

    // ---- Mechanisms, highest first ----
    $mechanisms = $reportData['mechanisms'] ?? [];
    usort($mechanisms, fn($a, $b) => $b['clinicalScore'] <=> $a['clinicalScore']);

    echo "\nTop mechanisms:\n";
    foreach (array_slice($mechanisms, 0, 5) as $m) {
        printf("  %-10s %6.2f  %s\n", $m['mechanismId'], $m['clinicalScore'], $m['mechanismName'] ?? '');
    }

    // ---- Foods, highest first ----
    $foods = $reportData['foods'] ?? [];

    echo "\nTop foods (" . count($foods) . " total):\n";
    foreach (array_slice($foods, 0, 5) as $f) {
        printf(
            "  %-4s %-25s score=%-4s %s\n",
            $f['id'] ?? '?',
            $f['identity']['name'] ?? $f['name'] ?? '?',
            $f['score'] ?? '?',
            $f['recommendation'] ?? ''
        );
    }

    echo "\nBioactives: " . count($reportData['bioactives'] ?? []) .
        "  Vitamins: " . count($reportData['vitamins'] ?? []) .
        "  Minerals: " . count($reportData['minerals'] ?? []) . "\n";

    // ---- Expectations ----
    if (!empty($persona['expected'])) {

        echo "\nExpectations:\n";

        foreach ($persona['expected'] as $key => $expectedValue) {

            $actual = match ($key) {
                'topBodySystem' => $bodySystems[0]->id ?? null,
                default         => null,
            };

            $pass = ($actual === $expectedValue);

            if (!$pass) {
                $overallExitCode = 1;
            }

            printf(
                "  %-16s expected=%-10s actual=%-10s %s\n",
                $key,
                (string) $expectedValue,
                (string) $actual,
                $pass ? 'PASS' : 'FAIL'
            );
        }
    }

    // ---- Render the full report for visual review ----
    $reportData['theme'] = 'theme-green';
    $html = (new ReportBuilder())->build($reportData);

    $outputFile = $outputDir . "/report-{$personaId}.html";
    file_put_contents($outputFile, $html);

    echo "\nFull report written to: " . realpath($outputFile) . "\n\n";
}

exit($overallExitCode);
