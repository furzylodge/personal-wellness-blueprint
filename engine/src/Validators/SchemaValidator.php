<?php

namespace PWB\Validators;

use PWB\Model\Repository;
use PWB\Loader\JsonLoader;
use PWB\Validation\ValidationResult;
use PWB\Validation\ValidationIssue;
use PWB\Validation\ValidatorInterface;

class SchemaValidator implements ValidatorInterface
{
    public function __construct(private Repository $repository,	private JsonLoader $loader) 
    {
    
    }	

    public function validate(): ValidationResult
    {
        
        $result = new ValidationResult();

	foreach ($this->repository->getAllJsonFiles() as $file) {
	    try {
	        $this->loader->load($file);
		}
	    catch (\Throwable $e) {

        	$result->addIssue(
            	new ValidationIssue(
                validator: 'SchemaValidator',
                severity: ValidationIssue::ERROR,
                file: $file,
                message: $e->getMessage()
            	)
        	);
    	    }
	}
	return $result;
    }

}