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

    public function getKnowledgebaseDirectory(): string
    {
	return $this->projectRoot . DIRECTORY_SEPARATOR . 'knowledgebase';
    }

    public function getFoodDirectory(): string
    {
	return $this->getKnowledgebaseDirectory() . DIRECTORY_SEPARATOR . 'foods';
    }

    public function getMonographDirectory(): string
    {
	return $this->getKnowledgebaseDirectory() . DIRECTORY_SEPARATOR . 'monographs';
    }

    public function getTaxonomyDirectory(): string
    {
	return $this->getKnowledgebaseDirectory() . DIRECTORY_SEPARATOR . 'taxonomy';
    }

    public function getSchemaDirectory(): string
    {
	return $this->getKnowledgebaseDirectory() . DIRECTORY_SEPARATOR . 'schemas';
    }
    
    public function getRecommendationConfiguration(): string
    {
    	return $this->getKnowledgebaseDirectory()
        . DIRECTORY_SEPARATOR
        . 'configuration'
        . DIRECTORY_SEPARATOR
        . 'recommendation.json';
    }
}