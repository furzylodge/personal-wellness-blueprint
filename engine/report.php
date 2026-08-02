<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Reports\ReportBuilder;
use PWB\Reports\ReportDataBuilder;

// Build the report data
$dataBuilder = new ReportDataBuilder();

$reportData = $dataBuilder->build();

// Generate the HTML report
$reportBuilder = new ReportBuilder();

$html = $reportBuilder->build($reportData);

// Create output directory
$outputDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'output';

if (!is_dir($outputDirectory)) {
    mkdir($outputDirectory, 0777, true);
}

// Save report
$outputFile = $outputDirectory . DIRECTORY_SEPARATOR . 'report.html';

file_put_contents($outputFile, $html);

// Console output
echo PHP_EOL;
echo "==========================================" . PHP_EOL;
echo "Personal Wellness Blueprint" . PHP_EOL;
echo "==========================================" . PHP_EOL;
echo PHP_EOL;
echo "Report successfully generated." . PHP_EOL;
echo "Output: " . $outputFile . PHP_EOL;
echo PHP_EOL;