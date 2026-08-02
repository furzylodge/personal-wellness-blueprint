<?php

namespace PWB\Validators;

use PWB\Model\Repository;
use PWB\Utils\FileLocator;
use PWB\Validation\ValidationIssue;
use PWB\Validation\ValidationResult;
use PWB\Validation\ValidatorInterface;

class RepositoryValidator implements ValidatorInterface
{
    private const NAME = 'RepositoryValidator';

    public function __construct(
        private Repository $repository,
        private FileLocator $locator
    ) {
    }

    public function validate(): ValidationResult
    {
        $result = new ValidationResult();
        $requiredDirectories = [
	    $this->locator->getFoodDirectory(),
	    $this->locator->getTaxonomyDirectory(),
	    $this->locator->getSchemaDirectory(),
	    $this->locator->getMonographDirectory()];

	foreach ($requiredDirectories as $directory) {
    	if (!is_dir($directory)) {
        	$result->addIssue(
            	new ValidationIssue(
                	validator: self::NAME,
                	severity: ValidationIssue::ERROR,
                	file: $directory,
                	message: 'Directory does not exist.'
        	    	)
        		);
    		}
	}
	
	$this->validateFoodMonographs($result);
	return $result;

    }
    
	private function validateFoodMonographs(ValidationResult $result): void
	{
    	foreach ($this->repository->getFoods() as $foodFile) {
        $foodId = pathinfo(
            basename($foodFile),
            PATHINFO_FILENAME
        );
        if (!$this->repository->hasMonograph($foodId)) {
            $result->addIssue(
                new ValidationIssue(
                    validator: self::NAME,
                    severity: ValidationIssue::ERROR,
                    file: $foodFile,
                    message: "Missing monograph for '{$foodId}'"));
        		}
		}
	}
    
}