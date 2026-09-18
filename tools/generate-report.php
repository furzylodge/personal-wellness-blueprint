<?php

declare(strict_types=1);

session_start();

$root = dirname(__DIR__);

require_once $root . '/vendor/autoload.php';

use PWB\Assessment\Assessment;
use PWB\Assessment\AssessmentLoader;
use PWB\Assessment\QuizController;
use PWB\Assessment\VisibilityEvaluator;
use PWB\Loader\JsonLoader;
use PWB\Reports\ReportBuilder;
use PWB\Reports\ReportDataBuilder;

$answers = $_SESSION['answers'] ?? [];

if (empty($answers)) {
    die('No assessment found in this session.');
}

/*
 |----------------------------------------------------------
 | Guard: the review page's "Generate" button is disabled
 | while required questions are missing, but this endpoint
 | can also be reached directly (bookmark, back button, a
 | new tab left open from an earlier run), so re-check here.
 |----------------------------------------------------------
*/

$reportLoader = new AssessmentLoader(new JsonLoader(), $root . '/knowledgebase');
$reportLoader->load();
$reportQuiz = new QuizController($reportLoader);

$reportProfile = [];

foreach ($reportLoader->profileFields()['fields'] as $field) {
    $reportProfile[$field['key']] = $answers[$field['id']] ?? null;
}

$missingCount = 0;

foreach ($reportQuiz->pages() as $quizPage) {

    $reportPage = $reportQuiz->pageComponents($quizPage['id'], $answers);

    foreach ($reportPage['components'] ?? [] as $component) {

        $componentType = $component['type'] ?? '';

        if ($componentType === 'profile') {

            $field = $component['field'] ?? [];

            if (empty($field['required'])) {
                continue;
            }

            $value = $reportProfile[$field['key'] ?? ''] ?? '';

            $isEmpty = match ($field['type'] ?? '') {
                'checkbox'     => empty($value) || (string)$value === '0',
                'multi_select' => empty($value) || (is_array($value) && count($value) === 0),
                default        => trim((string)$value) === ''
            };

            if ($isEmpty) {
                $missingCount++;
            }

        } elseif ($componentType === 'question') {

            $question = $component['question'] ?? [];

            if (empty($question['required']) || !VisibilityEvaluator::isVisible($question, $answers)) {
                continue;
            }

            $value = $answers[$question['id']] ?? null;

            $isEmpty = match ($question['answer_type'] ?? '') {
                'multi_select' => empty($value) || (is_array($value) && count($value) === 0),
                default        => trim((string)$value) === ''
            };

            if ($isEmpty) {
                $missingCount++;
            }
        }
    }
}

if ($missingCount > 0) {
    http_response_code(400);
    $plural = $missingCount === 1 ? 'question' : 'questions';
    die("Your Personal Wellness Blueprint can't be generated yet — {$missingCount} required {$plural} still need answering. Please go back and complete the assessment before generating your report.");
}

/*
 |----------------------------------------------------------
 | Build Assessment object from session
 |----------------------------------------------------------
*/

$assessment = new Assessment(
    assessment: [
        'theme' => $_SESSION['theme'] ?? 'green',
    ],
    person: $reportProfile,
    answers: $answers,
    bodySystems: [],
    // Dietary preferences (PF020) and declared food allergies (SAF006) —
    // FoodResolver hard-excludes foods against both.
    preferences: $reportProfile['dietary_preferences'] ?? [],
    restrictions: $answers['SAF006'] ?? [],
    goals: $answers['PF014'] ?? []
);

/*
 |----------------------------------------------------------
 | Generate report
 |----------------------------------------------------------
*/

$reportData = (new ReportDataBuilder())->build($assessment);

$reportData['theme'] = 'theme-' . ($_SESSION['theme'] ?? 'green');

echo (new ReportBuilder())->build($reportData);