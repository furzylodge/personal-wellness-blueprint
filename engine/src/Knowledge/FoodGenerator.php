<?php

declare(strict_types=1);

namespace PWB\Knowledge;

use PWB\Utils\FileLocator;
use RuntimeException;

class FoodGenerator
{
    private FileLocator $locator;

    private MarkdownParser $parser;

    private JsonWriter $writer;

    public function __construct()
    {
        $this->locator = new FileLocator();

        $this->parser = new MarkdownParser();

        $this->writer = new JsonWriter();
    }

    public function generate(string $slug): void
    {
        $markdownFile = $this->getMarkdownFile($slug);

        $markdown = file_get_contents($markdownFile);

        if ($markdown === false) {
            throw new RuntimeException(
                "Unable to read {$markdownFile}"
            );
        }

	$food = $this->parser->parse($markdown);
	
	print_r($food);
	exit;
        
        $outputFile = $this->getOutputFile($food);

        $this->writer->write(
            $outputFile,
            $food
        );
    }

private function getMarkdownFile(string $slug): string
{
    $directory = $this->locator->getMonographDirectory();

    $matches = glob(
        $directory
        . DIRECTORY_SEPARATOR
        . '*-' . $slug . '.md'
    );

    if (empty($matches)) {

        throw new RuntimeException(
            "Markdown draft not found for '{$slug}'"
        );

    }

    return $matches[0];
}

    private function getOutputFile(array $food): string
    {
        if (
            !isset($food['id']) ||
            !isset($food['identity']['slug'])
        ) {

            throw new RuntimeException(
                'Food ID or slug missing.'
            );

        }

        return
            $this->locator->getFoodDirectory()
            . DIRECTORY_SEPARATOR
            . strtolower($food['id'])
            . '-'
            . $food['identity']['slug']
            . '.json';
    }
}