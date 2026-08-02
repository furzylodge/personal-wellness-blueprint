<?php

declare(strict_types=1);

namespace PWB\Knowledge;

use RuntimeException;

class JsonWriter
{
    public function write(
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