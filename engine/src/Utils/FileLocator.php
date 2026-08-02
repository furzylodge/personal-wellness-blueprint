<?php

declare(strict_types=1);

namespace PWB\Utils;

class FileLocator
{
    private string $projectRoot;

    public function __construct()
    {
        // engine/src/Utils -> project root
        $this->projectRoot = dirname(__DIR__, 3);
    }

    public function getProjectRoot(): string
    {
        return $this->projectRoot;
    }

    public function getKnowledgeBasePath(): string
    {
	return $this->projectRoot . DIRECTORY_SEPARATOR . 'knowledgebase';
    }

    public function getFoodDirectory(): string
    {
	return $this->getKnowledgeBasePath() . DIRECTORY_SEPARATOR . 'foods';
    }

    public function getMonographDirectory(): string
    {
	return $this->getKnowledgeBasePath() . DIRECTORY_SEPARATOR . 'monographs';
    }

    public function getTaxonomyDirectory(): string
    {
	return $this->getKnowledgeBasePath() . DIRECTORY_SEPARATOR . 'taxonomy';
    }

    public function getSchemaDirectory(): string
    {
	return $this->getKnowledgeBasePath() . DIRECTORY_SEPARATOR . 'schemas';
    }
}