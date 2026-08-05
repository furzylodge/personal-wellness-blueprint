<?php

declare(strict_types=1);

/**
 * -----------------------------------------------------------------------------
 * Personal Wellness Blueprint
 * Bioactive → Mechanism Relationship Generator
 * -----------------------------------------------------------------------------
 *
 * Reads the master PHP bioactive-mechanism relationship definitions and
 * generates the production JSON taxonomy used by the recommendation engine.
 *
 * Source:
 *      tools/build/data/bioactive-mechanisms.php
 *
 * Output:
 *      knowledgebase/taxonomy/bioactive-mechanisms.json
 *
 * -----------------------------------------------------------------------------
 */

require_once __DIR__ . '/../../engine/src/Build/BuildTask.php';
require_once __DIR__ . '/../../engine/src/Utils/JsonWriter.php';

use PWB\Build\BuildTask;
use PWB\Utils\JsonWriter;

final class GenerateBioactiveMechanisms extends BuildTask
{
    /**
     * Master relationship records.
     */
    private array $relationships = [];

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
        $sourceFile = __DIR__ . '/data/bioactive-mechanisms.php';

        if (!file_exists($sourceFile)) {
            $this->error("Source file not found: {$sourceFile}");
            return;
        }

        $this->relationships = require $sourceFile;

        if (!is_array($this->relationships)) {
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

        $validConfidence = [
            'High',
            'Moderate',
            'Emerging'
        ];

        foreach ($this->relationships as $relationship) {

            /*
             * Relationship ID
             */

            if (empty($relationship['id'])) {
                $this->error('Relationship missing ID.');
                continue;
            }

            if (!preg_match('/^BM\d{6}$/', $relationship['id'])) {
                $this->error("Invalid relationship ID: {$relationship['id']}");
            }

            if (isset($ids[$relationship['id']])) {
                $this->error("Duplicate ID: {$relationship['id']}");
            }

            $ids[$relationship['id']] = true;

            /*
             * Bioactive ID
             */

            if (
                empty($relationship['bioactive']) ||
                !preg_match('/^(DF|PP|OS|CA|FA|PS|TP|FC)\d{3}$/', $relationship['bioactive'])
            ) {
                $this->error("{$relationship['id']} has an invalid bioactive ID.");
            }

            /*
             * Mechanism ID
             */

            if (
                empty($relationship['mechanism']) ||
                !preg_match('/^MEC\d{3}$/', $relationship['mechanism'])
            ) {
                $this->error("{$relationship['id']} has an invalid mechanism ID.");
            }

            /*
             * Strength
             */

            if (
                !isset($relationship['strength']) ||
                !is_int($relationship['strength']) ||
                $relationship['strength'] < 1 ||
                $relationship['strength'] > 5
            ) {
                $this->error("{$relationship['id']} has an invalid strength.");
            }

            /*
             * Confidence
             */

            if (
                empty($relationship['confidence']) ||
                !in_array($relationship['confidence'], $validConfidence, true)
            ) {
                $this->error("{$relationship['id']} has an invalid confidence.");
            }

            /*
             * Notes
             */

            if (
                array_key_exists('notes', $relationship) &&
                !is_null($relationship['notes']) &&
                !is_string($relationship['notes'])
            ) {
                $this->error("{$relationship['id']} has invalid notes.");
            }

            /*
             * References
             */

            if (
                isset($relationship['references']) &&
                !is_array($relationship['references'])
            ) {
                $this->error("{$relationship['id']} references must be an array.");
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
        usort(
            $this->relationships,
            fn(array $a, array $b) => strcmp($a['id'], $b['id'])
        );

        $this->output = [

            'schema' => 'bioactive-mechanisms-schema.json',

            'version' => '1.0',

            'generated' => gmdate('c'),

            'count' => count($this->relationships),

            'relationships' => $this->relationships

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
        return count($this->relationships);
    }

    protected function outputFile(): string
    {
        return __DIR__
            . '/../../knowledgebase/taxonomy/bioactive-mechanisms.json';
    }
}

/*
|--------------------------------------------------------------------------
| Run Generator
|--------------------------------------------------------------------------
*/

(new GenerateBioactiveMechanisms())->run();