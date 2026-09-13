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

// Recommendation Engine
require_once __DIR__ . '/src/Recommendation/RecommendationConfiguration.php';
require_once __DIR__ . '/src/Recommendation/PriorityEngine.php';
require_once __DIR__ . '/src/Recommendation/RecommendationEngine.php';
require_once __DIR__ . '/src/Recommendation/MechanismResolver.php';
require_once __DIR__ . '/src/Recommendation/BioactiveResolver.php';
require_once __DIR__ . '/src/Recommendation/FoodResolver.php';
require_once __DIR__ . '/src/Recommendation/NutrientResolver.php';
require_once __DIR__ . '/src/Recommendation/VitaminResolver.php';
require_once __DIR__ . '/src/Recommendation/ProductResolver.php';