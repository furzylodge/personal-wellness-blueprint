<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Reports\ReportBuilder;
use PWB\Utils\FileLocator;

// Create the report builder
$builder = new ReportBuilder();

// Temporary test data
$reportData = [

    'client' => [
        'name' => 'Test User'
    ],

    'summary' => [
        'Your digestive system would benefit from additional fibre.',
        'Increasing antioxidant-rich foods may support overall wellbeing.',
        'Aim to include a wider variety of whole plant foods each week.'
    ],

    'foods' => [
        'Oats',
        'Blueberries',
        'Spinach',
        'Walnuts',
        'Greek Yoghurt'
    ]

];

// Build the HTML
$html = $builder->build($reportData);

// Create the output folder if required
$locator = new FileLocator();

$outputDirectory =
    $locator->getProjectRoot()
    . DIRECTORY_SEPARATOR
    . 'output';

if (!is_dir($outputDirectory)) {
    mkdir($outputDirectory, 0777, true);
}

// Save the report
$outputFile =
    $outputDirectory
    . DIRECTORY_SEPARATOR
    . 'report.html';

file_put_contents($outputFile, $html);

// Console output
echo PHP_EOL;
echo "==========================================" . PHP_EOL;
echo "Personal Wellness Blueprint" . PHP_EOL;
echo "==========================================" . PHP_EOL;
echo PHP_EOL;
echo "Report successfully generated." . PHP_EOL;
echo PHP_EOL;
echo "Output:" . PHP_EOL;
echo $outputFile . PHP_EOL;
echo PHP_EOL;