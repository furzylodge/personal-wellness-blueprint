<?php

namespace PWB\Validation;

interface ValidatorInterface
{
    public function validate(): ValidationResult;
}