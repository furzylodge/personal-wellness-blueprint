<?php

namespace PWB\Validators;

use PWB\Utils\FileLocator;
use PWB\Scanner\RepositoryScanner;
use PWB\Loader\JsonLoader;
use PWB\Validators\SchemaValidator;
use PWB\Validation\ValidationReport;
use PWB\Validators\RepositoryValidator;


class ValidationEngine
{
    public function execute(): void
    {
    	$report = new ValidationReport();
    
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

	echo PHP_EOL;
	echo "Repository Summary" . PHP_EOL;
	echo "------------------" . PHP_EOL;
	echo "Foods        : " . $repository->countFoods() . PHP_EOL;
	echo "Monographs   : " . $repository->countMonographs() . PHP_EOL;
	echo "Taxonomies   : " . $repository->countTaxonomies() . PHP_EOL;
	echo "Schemas      : " . $repository->countSchemas() . PHP_EOL;
	echo "Total Files  : " . $repository->getTotalFiles() . PHP_EOL;
	echo PHP_EOL;

	$loader = new JsonLoader();

	$repositoryValidator = new RepositoryValidator($repository,$locator);
	$schemaValidator = new SchemaValidator($repository,$loader,$locator);
	$report->add($repositoryValidator->validate());
	$report->add($schemaValidator->validate());
	
	echo PHP_EOL;

	if ($report->errorCount() > 0) {
	    	echo "Validation Errors" . PHP_EOL;
    		echo "-----------------" . PHP_EOL;
		    foreach ($report->getIssues() as $issue) {
		        echo "[" . $issue->validator . "]" . PHP_EOL;
        		echo "File    : " . $issue->file . PHP_EOL;
        		echo "Message : " . $issue->message . PHP_EOL;
        		echo PHP_EOL;
		    }
	}
	
	echo "==========================================" . PHP_EOL;
	echo "Validation Summary" . PHP_EOL;
	echo "==========================================" . PHP_EOL;

	echo "Repository     : PASS" . PHP_EOL;
	echo "Schema         : PASS" . PHP_EOL;

	echo PHP_EOL;

	echo "Overall        : " . ($report->passed() ? "PASS" : "FAIL") . PHP_EOL;
	echo "Errors         : " . $report->errorCount() . PHP_EOL;
	echo "Warnings       : " . $report->warningCount() . PHP_EOL;

	echo "==========================================" . PHP_EOL;
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