<?php

declare(strict_types=1);

namespace PWB\Loader;

use JsonException;
use RuntimeException;

class JsonLoader
{
    /**
     * Load and decode a JSON file.
     *
     * @param string $filename
     * @return array
     * @throws RuntimeException
     */
    public function load(string $filename): array
    {
        if (!file_exists($filename)) {
            throw new RuntimeException(
                "JSON file not found: {$filename}"
            );
        }

        if (!is_readable($filename)) {
            throw new RuntimeException(
                "JSON file is not readable: {$filename}"
            );
        }

        $json = file_get_contents($filename);

        if ($json === false) {
            throw new RuntimeException(
                "Unable to read JSON file: {$filename}"
            );
        }

	try {
	    $data = json_decode(
	       	$json,
        	true,
	        512,
        	JSON_THROW_ON_ERROR
	    );
	} catch (JsonException $e) {
	    throw new RuntimeException(
	        "Invalid JSON in '" . basename($filename) . "': " . $e->getMessage(),
	        0,
	        $e
	    );
	}

        if (!is_array($data)) {
            throw new RuntimeException(
                "JSON root element must be an object or array: {$filename}"
            );
        }

        return $data;
    }
}