<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Assessment\HealthProfileBuilder;
use PWB\Loader\JsonLoader;
use PWB\Reports\ReportBuilder;
use PWB\Reports\ReportDataBuilder;
use PWB\Utils\TestAssessmentFactory;

$loader = new JsonLoader();
$kb = __DIR__ . '/../knowledgebase';

$factory = new TestAssessmentFactory($kb);
$assessment = $factory->load('respiratory-inflammation');

$reportData = (new ReportDataBuilder())->build($assessment);

$html = (new ReportBuilder())->build($reportData);

$output = __DIR__ . '/report-test.html';

file_put_contents($output, $html);

echo "Report generated:\n";
echo realpath($output) . PHP_EOL;