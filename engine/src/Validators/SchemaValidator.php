<?php

namespace PWB\Validators;

use PWB\Model\Repository;
use PWB\Loader\JsonLoader;
use PWB\Validation\ValidationResult;
use PWB\Validation\ValidationIssue;
use PWB\Validation\ValidatorInterface;
use PWB\Utils\FileLocator;

class SchemaValidator implements ValidatorInterface
{
    private const NAME = 'SchemaValidator';
    public function __construct(private Repository $repository,	private JsonLoader $loader,  private FileLocator $locator) 
    {
    
    }	

    public function validate(): ValidationResult
    {
        
        $result = new ValidationResult();
        foreach ($this->repository->getAllJsonFiles() as $file) {
	    $schema = $this->findSchema($file);
	    if ($schema === null || !file_exists($schema)) {
		$result->addIssue(
            new ValidationIssue(
                validator: self::NAME,
                severity: ValidationIssue::ERROR,
                file: $file,
                message: 'No schema found.'
        	    )
	        );

        continue;
	    }
	    try {
		    $json = $this->loader->load($file);
		    $schemaData = $this->loader->load($schema);
		    
		    $this->validateRequiredFields( $schemaData, $json, $file, $result);
		    $this->validateTypes( $schemaData, $json, $file, $result);
		} catch (\Throwable $e) {
			$result->addIssue(
		        new ValidationIssue(
		            validator: self::NAME,
			    severity: ValidationIssue::ERROR,
		            file: $file,
		            message: $e->getMessage()
		        )
    		);
	}
	}
	return $result;
    }
    
    private function findSchema(string $jsonFile): ?string
    {
	    $directory = basename(dirname($jsonFile));
	    switch ($directory) {
	        case 'foods':
        	    return $this->locator->getSchemaDirectory() . DIRECTORY_SEPARATOR . 'food.schema.json';
	        case 'taxonomy':
	            return $this->locator->getSchemaDirectory() . DIRECTORY_SEPARATOR . 'taxonomy.schema.json';
	        default:
        	    return null;
    		}
    }

    private function validateRequiredFields( array $schema, array $json, string $file, ValidationResult $result): void
    {
    	if (!isset($schema['required'])) { return;}
	foreach ($schema['required'] as $field) {
	        if (!array_key_exists($field, $json)) {$result->addIssue(new ValidationIssue(validator: self::NAME,severity: ValidationIssue::ERROR,file: $file, message: "Missing required field '{$field}'" ) );

        }
    }
    }
    
    private function validateTypes(array $schema, array $json, string $file, ValidationResult $result): void
	{
    	if (!isset($schema['properties'])) {return; }
	foreach ($schema['properties'] as $field => $definition) {
	        if (!array_key_exists($field, $json)) { continue;}
	        if (!isset($definition['type'])) { continue;}
	        $expected = $definition['type'];
        	$value = $json[$field];
	        if (!$this->isCorrectType($value, $expected)) 
	        {$actual = gettype($value);
	            $result->addIssue(new ValidationIssue(validator: self::NAME,severity: ValidationIssue::ERROR,file: $file,message: "Field '{$field}' should be '{$expected}' but found '{$actual}'"));
	        }
    	}	
    }
    
    private function isCorrectType( mixed $value, string $expected): bool
	{return match ($expected) {
        'string'  => is_string($value),
        'integer' => is_int($value),
        'number'  => is_int($value) || is_float($value),
        'boolean' => is_bool($value),
        'array'   => is_array($value) && array_is_list($value),
        'object'  => is_array($value) && !array_is_list($value),
        default   => true};}
}
