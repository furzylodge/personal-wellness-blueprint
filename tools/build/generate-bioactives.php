<?php

declare(strict_types=1);

/**
 * -----------------------------------------------------------------------------
 * Personal Wellness Blueprint
 * Bioactive Library Generator
 * -----------------------------------------------------------------------------
 *
 * Reads the master PHP bioactive definitions and generates the production
 * JSON taxonomy used by the recommendation engine.
 *
 * Source:
 *      tools/build/data/bioactives.php
 *
 * Output:
 *      knowledgebase/taxonomy/bioactives.json
 *
 * -----------------------------------------------------------------------------
 */

require_once __DIR__ . '/../../engine/src/Build/BuildTask.php';
require_once __DIR__ . '/../../engine/src/Utils/JsonWriter.php';

use PWB\Build\BuildTask;
use PWB\Utils\JsonWriter;

final class GenerateBioactives extends BuildTask
{
    /**
     * Master source records.
     */
    private array $bioactives = [];

    /**
     * Final JSON output.
     */
    private array $output = [];

    /**
     * -------------------------------------------------------------------------
     * Load
     * -------------------------------------------------------------------------
     */
    protected function load(): void
    {
        $sourceFile = __DIR__ . '/data/bioactives.php';

        if (!file_exists($sourceFile)) {
            $this->error("Source file not found: {$sourceFile}");
            return;
        }

        $this->bioactives = require $sourceFile;

        if (!is_array($this->bioactives)) {
            $this->error('Source file did not return an array.');
        }
    }

    /**
     * -------------------------------------------------------------------------
     * Validate
     * -------------------------------------------------------------------------
     */
    protected function validate(): void
    {
        $ids = [];

        foreach ($this->bioactives as $bioactive) {

            if (empty($bioactive['id'])) {
                $this->error('Bioactive missing ID.');
                continue;
            }

            if (isset($ids[$bioactive['id']])) {
                $this->error("Duplicate ID: {$bioactive['id']}");
            }

            $ids[$bioactive['id']] = true;

            if (empty($bioactive['name'])) {
                $this->warning("{$bioactive['id']} has no display name.");
            }

            if (empty($bioactive['canonicalName'])) {
                $this->warning("{$bioactive['id']} has no canonical name.");
            }

            if (!isset($bioactive['clinicalImportance'])) {
                $this->warning("{$bioactive['id']} has no clinical importance.");
            }
        }
    }

    /**
     * -------------------------------------------------------------------------
     * Build
     * -------------------------------------------------------------------------
     */
    protected function build(): void
    {
        $this->output = [

            'version' => '2.0',

            'generated' => date('c'),

            'count' => count($this->bioactives),

            'bioactives' => $this->bioactives

        ];
    }

    /**
     * -------------------------------------------------------------------------
     * Write
     * -------------------------------------------------------------------------
     */
    protected function write(): void
    {
        JsonWriter::save(
            $this->outputFile(),
            $this->output
        );
    }

    /**
     * -------------------------------------------------------------------------
     * Statistics
     * -------------------------------------------------------------------------
     */
    protected function recordCount(): int
    {
        return count($this->bioactives);
    }

    protected function outputFile(): string
    {
        return __DIR__
            . '/../../knowledgebase/taxonomy/bioactives.json';
    }
}

/*
|--------------------------------------------------------------------------
| Run Generator
|--------------------------------------------------------------------------
*/

(new GenerateBioactives())->run();