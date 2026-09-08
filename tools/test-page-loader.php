<?php

$root = dirname(__DIR__);

require_once $root . '/engine/src/Loader/JsonLoader.php';
require_once $root . '/engine/src/Assessment/Assessment.php';
require_once $root . '/engine/src/Assessment/AssessmentLoader.php';
require_once $root . '/engine/src/Assessment/QuizController.php';
require_once $root . '/engine/src/Assessment/QuestionRenderer.php';

use PWB\Loader\JsonLoader;
use PWB\Assessment\AssessmentLoader;
use PWB\Assessment\QuizController;
use PWB\Assessment\QuestionRenderer;

$jsonLoader = new JsonLoader();

$loader = new AssessmentLoader(
    $jsonLoader,
    $root . '/knowledgebase'
);

$assessment = $loader->load();

$quiz = new QuizController($loader);
$renderer = new QuestionRenderer();

echo $renderer->render(
    $quiz->pageComponents('PAGE003')
);