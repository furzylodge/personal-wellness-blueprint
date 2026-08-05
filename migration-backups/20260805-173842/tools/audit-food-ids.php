<?php

declare(strict_types=1);

/**
 * --------------------------------------------------------------------------
 * Personal Wellness Blueprint
 * Food ID Audit
 * --------------------------------------------------------------------------
 *
 * Recursively scans the project looking for Food IDs (F001, F002...)
 * before migration to FD001 format.
 *
 * This script NEVER modifies files.
 * It simply reports what it finds.
 * --------------------------------------------------------------------------
 */

$root = realpath(__DIR__ . '/..');

if ($root === false) {
    exit("Unable to locate project root.\n");
}

echo PHP_EOL;
echo "==============================================================" . PHP_EOL;
echo "Personal Wellness Blueprint - Food ID Audit" . PHP_EOL;
echo "==============================================================" . PHP_EOL;
echo "Root : {$root}" . PHP_EOL;
echo PHP_EOL;

$exclude = [
    DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR,
    DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR,
    DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR,
    DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR,
    DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR,
    DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR,
];

$extensions = [
    'php',
    'json',
    'md',
    'txt',
    'html',
    'htm',
    'xml',
    'yml',
    'yaml',
    'css',
    'js'
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(
        $root,
        FilesystemIterator::SKIP_DOTS
    )
);

$totalFiles = 0;
$matchedFiles = 0;
$totalMatches = 0;

foreach ($iterator as $file) {

    $path = $file->getPathname();

    foreach ($exclude as $skip) {
        if (strpos($path, $skip) !== false) {
            continue 2;
        }
    }

    if (!$file->isFile()) {
        continue;
    }

    $extension = strtolower($file->getExtension());

    if (!in_array($extension, $extensions, true)) {
        continue;
    }

    $totalFiles++;

    $contents = file_get_contents($path);

    if ($contents === false) {
        continue;
    }

    preg_match_all(
        '/\bF\d{3}\b/',
        $contents,
        $matches,
        PREG_OFFSET_CAPTURE
    );

    if (empty($matches[0])) {
        continue;
    }

    $matchedFiles++;

    echo "--------------------------------------------------------------" . PHP_EOL;
    echo str_replace($root . DIRECTORY_SEPARATOR, '', $path) . PHP_EOL;

    $found = [];

    foreach ($matches[0] as [$id]) {

        $found[$id] = ($found[$id] ?? 0) + 1;

        $totalMatches++;

    }

    ksort($found);

    foreach ($found as $id => $count) {

        printf(
            "    %-8s %4d occurrence%s\n",
            $id,
            $count,
            $count === 1 ? '' : 's'
        );

    }

    echo PHP_EOL;
}

echo PHP_EOL;
echo "==============================================================" . PHP_EOL;
echo "Audit Summary" . PHP_EOL;
echo "==============================================================" . PHP_EOL;
echo "Files Scanned      : {$totalFiles}" . PHP_EOL;
echo "Files With Matches : {$matchedFiles}" . PHP_EOL;
echo "Total References   : {$totalMatches}" . PHP_EOL;
echo "==============================================================" . PHP_EOL;
echo PHP_EOL;