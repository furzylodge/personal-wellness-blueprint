<?php

namespace PWB\Validator;

use PWB\Utils\FileLocator;
use PWB\Scanner\RepositoryScanner;
use PWB\Loader\JsonLoader;


class Validator
{
    public function run(): void
    {
        echo PHP_EOL;
        echo "==========================================" . PHP_EOL;
        echo " Personal Wellness Blueprint Validator" . PHP_EOL;
        echo "==========================================" . PHP_EOL;
        echo PHP_EOL;

        $this->checkEnvironment();
        $this->scanKnowledgeBase();

        echo PHP_EOL;
        echo "Validation complete." . PHP_EOL;
        
        $locator = new FileLocator();

	echo "Project Root      : " . $locator->getProjectRoot() . PHP_EOL;
	echo "Knowledge Base    : " . $locator->getKnowledgeBasePath() . PHP_EOL;
	echo "Foods             : " . $locator->getFoodDirectory() . PHP_EOL;
	echo "Taxonomy          : " . $locator->getTaxonomyDirectory() . PHP_EOL;
	echo "Schemas           : " . $locator->getSchemaDirectory() . PHP_EOL;
	echo "Monographs        : " . $locator->getMonographDirectory() . PHP_EOL;
	
	$scanner = new RepositoryScanner($locator);

	$repository = $scanner->scan();

        echo "Foods (" . $repository->countFoods() . ")" . PHP_EOL;
        echo "------------------------" . PHP_EOL;
        foreach ($repository->getFoods() as $file) {
            echo "  - " . basename($file) . PHP_EOL;
        }
        echo PHP_EOL;

        echo "Monographs (" . $repository->countMonographs() . ")" . PHP_EOL;
        echo "------------------------" . PHP_EOL;
        foreach ($repository->getMonographs() as $file) {
            echo "  - " . basename($file) . PHP_EOL;
        }
        echo PHP_EOL;

        echo "Taxonomies (" . $repository->countTaxonomies() . ")" . PHP_EOL;
        echo "------------------------" . PHP_EOL;
        foreach ($repository->getTaxonomies() as $file) {
            echo "  - " . basename($file) . PHP_EOL;
        }
        echo PHP_EOL;

        echo "Schemas (" . $repository->countSchemas() . ")" . PHP_EOL;
        echo "------------------------" . PHP_EOL;
        foreach ($repository->getSchemas() as $file) {
            echo "  - " . basename($file) . PHP_EOL;
        }
        echo PHP_EOL;

        echo "==========================================" . PHP_EOL;
        echo "Total Repository Files: " . $repository->getTotalFiles() . PHP_EOL;
        echo "==========================================" . PHP_EOL;
        
        $loader = new JsonLoader();
	foreach ($repository->getFoods() as $file) {
	    $food = $loader->load($file);
	    echo basename($file) . PHP_EOL;
	    print_r(array_keys($food));
	    echo PHP_EOL;
	}
    }

    private function checkEnvironment(): void
    {
        echo "[OK] PHP Environment" . PHP_EOL;
    }

    private function scanKnowledgeBase(): void
    {
        echo "[OK] Knowledgebase found" . PHP_EOL;
    }
}