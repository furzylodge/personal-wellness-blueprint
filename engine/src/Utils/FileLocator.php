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
	$path = $this->projectRoot . DIRECTORY_SEPARATOR . 'knowledgebase';
   	if (!is_dir($path)) 
    	{
        	throw new \RuntimeException(
            	"Knowledge base directory not found: {$path}"
        	);
    	}
	return $path;
    }

    public function getFoodDirectory(): string
    {
	$path = $this->getKnowledgeBasePath() . DIRECTORY_SEPARATOR . 'foods';
   	if (!is_dir($path)) 
    	{
        	throw new \RuntimeException(
            	"Foods directory not found: {$path}"
        	);
    	}
    	return $path;
    }

    public function getMonographDirectory(): string
    {
	$path = $this->getKnowledgeBasePath() . DIRECTORY_SEPARATOR . 'monographs';
   	if (!is_dir($path)) 
    	{
        	throw new \RuntimeException(
            	"Monographs directory not found: {$path}"
        	);
    	}
    	return $path;
    }

    public function getTaxonomyDirectory(): string
    {
	$path = $this->getKnowledgeBasePath() . DIRECTORY_SEPARATOR . 'taxonomy';
   	if (!is_dir($path)) 
    	{
        	throw new \RuntimeException(
            	"Taxonomy directory not found: {$path}"
        	);
    	}
    	return $path;
    }

    public function getSchemaDirectory(): string
    {
	$path = $this->getKnowledgeBasePath() . DIRECTORY_SEPARATOR . 'schemas';
   	if (!is_dir($path)) 
    	{
        	throw new \RuntimeException(
            	"Schemas directory not found: {$path}"
        	);
    	}
    	return $path;
    }
}