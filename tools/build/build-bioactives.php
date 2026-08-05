<?php

declare(strict_types=1);

require_once __DIR__ . '/../../engine/src/Utils/JsonWriter.php';

use PersonalWellnessBlueprint\Utils\JsonWriter;

//----------------------------------------------------------
// Configuration
//----------------------------------------------------------

$sourceFile = __DIR__ . '/data/bioactives.php';

$outputFile = __DIR__ . '/../../knowledgebase/taxonomy/bioactives.json';

//----------------------------------------------------------
// Load Source Data
//----------------------------------------------------------

if (!file_exists($sourceFile)) {
    exit("Source file not found: {$sourceFile}\n");
}

$bioactives = require $sourceFile;

if (!is_array($bioactives)) {
    exit("Source file did not return an array.\n");
}

//----------------------------------------------------------
// Build Output
//----------------------------------------------------------

$output = [

    'version' => '2.0',

    'generated' => date('c'),

    'count' => count($bioactives),

    'bioactives' => $bioactives

];

//----------------------------------------------------------
// Save
//----------------------------------------------------------

JsonWriter::save(

    $outputFile,

    $output

);

echo PHP_EOL;
echo "Bioactives generated successfully." . PHP_EOL;
echo "Output : {$outputFile}" . PHP_EOL;
echo "Records: " . count($bioactives) . PHP_EOL;
echo PHP_EOL;