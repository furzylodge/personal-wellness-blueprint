<?php

declare(strict_types=1);

namespace PWB\Utils;

use RuntimeException;

/**
 * -----------------------------------------------------------------------------
 * JsonWriter
 * -----------------------------------------------------------------------------
 *
 * Writes PHP arrays to formatted JSON files.
 *
 * Features
 * --------
 * • Automatically creates missing directories
 * • Pretty-prints JSON
 * • Preserves Unicode characters
 * • Preserves forward slashes
 * • Throws RuntimeException on failure
 *
 * Example
 * -------
 *
 * JsonWriter::save(
 *     'knowledgebase/taxonomy/bioactives.json',
 *     $data
 * );
 *
 * -----------------------------------------------------------------------------
 */
final class JsonWriter
{
    /**
     * Utility class.
     */
    private function __construct()
    {
    }

    /**
     * Save an array as a JSON file.
     *
     * @param string $filename Destination filename.
     * @param array  $data     Data to write.
     *
     * @throws RuntimeException
     */
    public static function save(
        string $filename,
        array $data
    ): void {

        $directory = dirname($filename);

        if (!is_dir($directory)) {

            if (!mkdir($directory, 0777, true) && !is_dir($directory)) {

                throw new RuntimeException(
                    "Unable to create directory: {$directory}"
                );

            }

        }

        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
        );

        if ($json === false) {

            throw new RuntimeException(
                'Unable to encode JSON.'
            );

        }

        if (file_put_contents($filename, $json) === false) {

            throw new RuntimeException(
                "Unable to write file: {$filename}"
            );

        }

    }
}