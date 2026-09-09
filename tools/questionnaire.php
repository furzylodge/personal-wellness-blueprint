<?php

session_start();

$root = dirname(__DIR__);

require_once __DIR__ . '/../engine/bootstrap.php';

use PWB\Loader\JsonLoader;
use PWB\Assessment\AssessmentLoader;
use PWB\Assessment\QuizController;
use PWB\Assessment\QuestionRenderer;

$loader = new AssessmentLoader(
    new JsonLoader(),
    $root . '/knowledgebase'
);

$loader->load();

$quiz = new QuizController($loader);
$renderer = new QuestionRenderer();

$pageId = $_GET['page'] ?? 'PAGE001';

if (!isset($_SESSION['answers'])) {
    $_SESSION['answers'] = [];
}

$answers = $_SESSION['answers'];
$errors = [];

/* ----------------------------------------------------------
   Handle form submission
---------------------------------------------------------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($_POST as $key => $value) {
        $answers[$key] = $value;
    }

// Page-specific fields
if ($pageId === 'PAGE002') {

    // Unchecked checkboxes are not posted
    $answers['PF018'] = isset($_POST['PF018']) ? '1' : '0';
    $answers['PF019'] = isset($_POST['PF019']) ? '1' : '0';

    // Multi-select wellness goals
    $answers['PF014'] = $_POST['PF014'] ?? [];
}

    // Required fields from canonical JSON
    $profileFields = $loader->profileFields()['fields'] ?? [];

    foreach ($profileFields as $field) {

        if (empty($field['required'])) {
            continue;
        }

        $fieldId = $field['id'];
        $value = $answers[$fieldId] ?? null;

        $isEmpty = match ($field['type']) {

            'checkbox' =>
                empty($value),

            'multi_select' =>
                empty($value) || count($value) === 0,

            default =>
                trim((string)$value) === ''
        };

        if ($isEmpty) {
            $errors[$fieldId] = [
                'label' => $field['label'],
                'message' => 'This field is required.'
            ];
        }
    }

    // Maximum 3 wellness goals
    if (count($answers['PF014']) > 3) {

        $errors['PF014'] = [
            'label' => 'Primary wellness goals',
            'message' => 'Select a maximum of 3 options.'
        ];
    }

    // Persist answers
    $_SESSION['answers'] = $answers;

    // Advance when valid
if (empty($errors)) {

    $_SESSION['answers'] = $answers;
    
    $nextPage = $quiz->nextPageId($pageId);

    if ($nextPage) {
        header("Location: ?page={$nextPage}");
        exit;
    }
}

}

$page = $quiz->pageComponents($pageId, $answers);
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">

<title>Personal Wellness Blueprint</title>

<link rel="stylesheet" href="../engine/assets/css/questionnaire.css">

<script src="../engine/assets/js/questionnaire.js" defer></script>

</head>

<body>

<div class="container">

<?php if ($pageId === 'PAGE001'): ?>

    <?= $renderer->render($page); ?>

    <a class="next" href="?page=PAGE002">Begin Assessment</a>

<?php elseif ($pageId === 'PAGE002'): ?>

    <form method="post" novalidate>

        <?= $renderer->render($page, $answers, $errors); ?>

        <?php if (!empty($errors)): ?>

            <div class="error">
                <strong>Please correct the highlighted fields.</strong>
            </div>

            <?php foreach ($errors as $error): ?>

                <div class="error">
                    <strong><?= htmlspecialchars($error['label']) ?>:</strong>
                    <?= htmlspecialchars($error['message']) ?>
                </div>

            <?php endforeach; ?>

        <?php endif; ?>

        <button class="next" type="submit">Continue</button>

    </form>

<?php else: ?>

    <form method="post" novalidate>

        <?= $renderer->render($page, $answers, $errors); ?>

        <div style="display:flex;justify-content:space-between;margin-top:30px;">

            <?php if ($previous = $quiz->previousPageId($pageId)): ?>
                <a class="next" href="?page=<?= $previous ?>" style="background:#666;">
                    Previous
                </a>
            <?php else: ?>
                <div></div>
            <?php endif; ?>

            <button class="next" type="submit">
                Continue
            </button>

        </div>

    </form>

<?php endif; ?>

</div>

</body>
</html>