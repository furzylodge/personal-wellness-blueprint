<?php

namespace PWB\Validation;

class ValidationResult
{
    private array $issues = [];

    public function addIssue(ValidationIssue $issue): void
    {
        $this->issues[] = $issue;
    }

    public function passed(): bool
    {
        foreach ($this->issues as $issue) {
            if ($issue->severity === ValidationIssue::ERROR) {
                return false;
            }
        }

        return true;
    }

    public function getIssues(): array
    {
        return $this->issues;
    }

    public function errorCount(): int
    {
        return count(array_filter(
            $this->issues,
            fn ($i) => $i->severity === ValidationIssue::ERROR
        ));
    }

    public function warningCount(): int
    {
        return count(array_filter(
            $this->issues,
            fn ($i) => $i->severity === ValidationIssue::WARNING
        ));
    }
}