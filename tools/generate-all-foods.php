<?php

declare(strict_types=1);

/**
 * -----------------------------------------------------------------------------
 * Personal Wellness Blueprint
 * Generate All Foods
 * -----------------------------------------------------------------------------
 *
 * Generates JSON food records from every markdown food monograph.
 *
 * Usage:
 *
 *     php tools/generate-all-foods.php
 *
 * -----------------------------------------------------------------------------
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Knowledge\FoodGenerator;
use PWB\Utils\FileLocator;

$locator = new FileLocator();

$generator = new FoodGenerator();

$directory = $locator->getMonographDirectory();

$files = glob(
    $directory
    . DIRECTORY_SEPARATOR
    . 'FD*-*.md'
);

if ($files === false || empty($files)) {

    echo PHP_EOL;
    echo "No food monographs found." . PHP_EOL;
    echo PHP_EOL;

    exit(1);

}

sort($files);

echo PHP_EOL;
echo "==================================================" . PHP_EOL;
echo "Personal Wellness Blueprint" . PHP_EOL;
echo "Generate All Foods" . PHP_EOL;
echo "==================================================" . PHP_EOL;
echo PHP_EOL;

$count = 0;

foreach ($files as $file) {

    $filename = basename($file);

    if (!preg_match('/^FD\d+-(.+)\.md$/i', $filename, $matches)) {

        echo "Skipping {$filename}" . PHP_EOL;

        continue;

    }

    $slug = strtolower($matches[1]);

    echo "Generating {$slug}..." . PHP_EOL;

    try {

        $generator->generate($slug);

        $count++;

    }
    catch (Throwable $exception) {

        echo "FAILED: " . $exception->getMessage() . PHP_EOL;

    }

}

echo PHP_EOL;
echo "==================================================" . PHP_EOL;
echo "Generation Complete" . PHP_EOL;
echo "==================================================" . PHP_EOL;
echo "Foods generated : {$count}" . PHP_EOL;
echo "==================================================" . PHP_EOL;
echo PHP_EOL;