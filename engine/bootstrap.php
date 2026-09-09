<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Personal Wellness Blueprint Engine Bootstrap
|--------------------------------------------------------------------------
| Central loader for all engine classes.
| Tools and future web entry points should require only this file.
*/

require_once __DIR__ . '/src/Loader/JsonLoader.php';

require_once __DIR__ . '/src/Assessment/Assessment.php';
require_once __DIR__ . '/src/Assessment/AssessmentLoader.php';
require_once __DIR__ . '/src/Assessment/BodySystemNormalizer.php';
require_once __DIR__ . '/src/Assessment/HealthProfileBuilder.php';
require_once __DIR__ . '/src/Assessment/QuizController.php';
require_once __DIR__ . '/src/Assessment/QuestionRenderer.php';
require_once __DIR__ . '/src/Assessment/VisibilityEvaluator.php';