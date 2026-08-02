<?php

namespace PWB\Validation;

class ValidationReport
{
    /**
     * @var ValidationResult[]
     */
    private array $results = [];

    public function add(ValidationResult $result): void
    {
        $this->results[] = $result;
    }

    /**
     * @return ValidationResult[]
     */
    public function getResults(): array
    {
        return $this->results;
    }

    public function passed(): bool
    {
        foreach ($this->results as $result) {
            if (!$result->passed()) {
                return false;
            }
        }

        return true;
    }

    public function errorCount(): int
    {
        $count = 0;

        foreach ($this->results as $result) {
            $count += $result->errorCount();
        }

        return $count;
    }

    public function warningCount(): int
    {
        $count = 0;

        foreach ($this->results as $result) {
            $count += $result->warningCount();
        }

        return $count;
    }
    
    public function getIssues(): array
    {
    	$issues = [];
	foreach ($this->results as $result) {
	        foreach ($result->getIssues() as $issue) {
            $issues[] = $issue;
        	}
    	}
    	return $issues;
    }
}