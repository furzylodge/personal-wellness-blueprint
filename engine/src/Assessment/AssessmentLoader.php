<?php

declare(strict_types=1);

namespace PWB\Assessment;

use RuntimeException;

final class AssessmentLoader
{
    /**
     * Load an Assessment from a JSON file.
     *
     * @throws RuntimeException
     */
    public static function load(string $filename): Assessment
    {
        if (!file_exists($filename)) {
            throw new RuntimeException(
                "Assessment file not found: {$filename}"
            );
        }

        $json = file_get_contents($filename);

        if ($json === false) {
            throw new RuntimeException(
                "Unable to read assessment file: {$filename}"
            );
        }

        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new RuntimeException(
                "Assessment file contains invalid JSON."
            );
        }

        return new Assessment(

            assessment: $data['assessment'] ?? [],

            person: $data['person'] ?? [],

            answers: $data['answers'] ?? [],

            bodySystems: $data['bodySystems'] ?? [],

            preferences: $data['preferences'] ?? [],

            restrictions: $data['restrictions'] ?? [],

            goals: $data['goals'] ?? []

        );
    }
}