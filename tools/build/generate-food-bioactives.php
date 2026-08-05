<?php

declare(strict_types=1);

/**
 * -----------------------------------------------------------------------------
 * Personal Wellness Blueprint
 * Food → Bioactive Relationship Generator
 * -----------------------------------------------------------------------------
 *
 * Reads the master PHP food-bioactive relationship definitions and generates
 * the production JSON taxonomy used by the recommendation engine.
 *
 * Source:
 *      tools/build/data/food-bioactives.php
 *
 * Output:
 *      knowledgebase/taxonomy/food-bioactives.json
 *
 * -----------------------------------------------------------------------------
 */

require_once __DIR__ . '/../../engine/src/Build/BuildTask.php';
require_once __DIR__ . '/../../engine/src/Utils/JsonWriter.php';

use PWB\Build\BuildTask;
use PWB\Utils\JsonWriter;

final class GenerateFoodBioactives extends BuildTask
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
        $sourceFile = __DIR__ . '/data/food-bioactives.php';

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

        $validBioavailability = [
            'Low',
            'Moderate',
            'High',
            'Variable'
        ];

        $validPreparation = [
            'Any',
            'Raw',
            'Cooked',
            'Lightly Steamed',
            'Steamed',
            'Boiled',
            'Roasted',
            'Baked',
            'Grilled',
            'Stir Fried',
            'Crushed',
            'Crushed and Rested',
            'Chopped',
            'Juiced',
            'Fermented',
            'Dried'
        ];

        foreach ($this->relationships as $relationship) {

            /*
             * ID
             */

            if (empty($relationship['id'])) {
                $this->error('Relationship missing ID.');
                continue;
            }

            if (isset($ids[$relationship['id']])) {
                $this->error("Duplicate ID: {$relationship['id']}");
            }

            $ids[$relationship['id']] = true;

            /*
             * Food
             */

            if (
                empty($relationship['food']) ||
                !preg_match('/^FD\d{3}$/', $relationship['food'])
            ) {
                $this->error("{$relationship['id']} has an invalid food ID.");
            }

            /*
             * Bioactive
             */

            if (
                empty($relationship['bioactive']) ||
                !preg_match('/^(DF|PP|OS|CA|FA|PS|TP|FC)\d{3}$/', $relationship['bioactive'])
            ) {
                $this->error("{$relationship['id']} has an invalid bioactive ID.");
            }

            /*
             * Strength
             */

            if (
                !isset($relationship['strength']) ||
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
             * Part
             */

            if (
                empty($relationship['part']) ||
                !is_string($relationship['part'])
            ) {
                $this->error("{$relationship['id']} has an invalid part.");
            }

            /*
             * Preparation
             */

            if (
                empty($relationship['preparation']) ||
                !is_array($relationship['preparation'])
            ) {
                $this->error("{$relationship['id']} has no preparation array.");
            }
            else {

                foreach ($relationship['preparation'] as $method) {

                    if (!in_array($method, $validPreparation, true)) {

                        $this->error(
                            "{$relationship['id']} has invalid preparation '{$method}'."
                        );

                    }

                }

            }

            /*
             * Bioavailability
             */

            if (
                empty($relationship['bioavailability']) ||
                !in_array(
                    $relationship['bioavailability'],
                    $validBioavailability,
                    true
                )
            ) {
                $this->error("{$relationship['id']} has an invalid bioavailability.");
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
        $this->output = [

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
            . '/../../knowledgebase/taxonomy/food-bioactives.json';
    }
}

/*
|--------------------------------------------------------------------------
| Run Generator
|--------------------------------------------------------------------------
*/

(new GenerateFoodBioactives())->run();