<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Validators\Validator;

$validator = new Validator();

$validator->run();