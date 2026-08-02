<?php

namespace PWB\Validation;

class ValidationIssue
{
    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const INFO = 'info';

    public function __construct(
        public string $severity,
        public string $file,
        public string $message,
        public string $path = ''
    ) {
    }
}