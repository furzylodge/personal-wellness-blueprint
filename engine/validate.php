<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Validators\ValidationEngine;

$engine = new ValidationEngine();

$engine->execute();