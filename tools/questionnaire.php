<?php

session_start();


if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'set_theme'
) {
    $allowed = ['green', 'ocean', 'lavender', 'amber'];

    if (in_array($_POST['theme'] ?? '', $allowed, true)) {
        $_SESSION['theme'] = $_POST['theme'];
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);

    exit;
}

if (isset($_GET['theme'])) {

    $allowed = ['green', 'ocean', 'lavender', 'amber'];

    if (in_array($_GET['theme'], $allowed, true)) {
        $_SESSION['theme'] = $_GET['theme'];
    }
}

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

$pageId = $_GET['page'] ?? 'WELCOME';

if (!isset($_SESSION['answers'])) {
    $_SESSION['answers'] = [];
}

$answers = $_SESSION['answers'];
$errors = [];
$page = $quiz->pageComponents($pageId, $answers);

/* ----------------------------------------------------------
   Handle form submission
---------------------------------------------------------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($_POST as $key => $value) {
        $answers[$key] = $value;
    }

// Profile page fields
if (($page['type'] ?? '') === 'profile') {

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


?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">

<title>Personal Wellness Blueprint</title>

<link rel="stylesheet" href="../engine/assets/css/questionnaire.css">

<script src="../engine/assets/js/questionnaire.js" defer></script>

</head>

<body class="theme-<?= htmlspecialchars($_SESSION['theme'] ?? 'green') ?>">

<div class="container">

<?php switch ($page['type'] ?? 'questions'):
    case 'welcome':
?>

<div class="welcome-card">

    <div class="welcome-icon">
<div class="welcome-icon-inner"></div>
    </div>

    <?= $renderer->render($page); ?>

<?php if (!empty($page['stats'])): ?>

<div class="welcome-stats">

    <?php foreach ($page['stats'] as $stat): ?>

        <div class="stat">
            <strong><?= htmlspecialchars($stat['value']) ?></strong>
            <span><?= htmlspecialchars($stat['label']) ?></span>
        </div>

    <?php endforeach; ?>

</div>

<?php endif; ?>

    <a class="next" href="?page=<?= $quiz->nextPageId($pageId) ?>">
        Begin Assessment
    </a>
    
                <p class="welcome-note">
    Your progress is saved automatically as you complete the assessment.
</p>


</div>

<?php break; ?>
<?php
    case 'profile':
?>

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

        <?php break; ?>


<?php
    case 'questions':
?>

        <form method="post" novalidate>

            <?= $renderer->render($page, $answers, $errors); ?>

            <div class="nav-buttons">

                <?php if ($previous = $quiz->previousPageId($pageId)): ?>
                    <a class="next previous" href="?page=<?= $previous ?>">
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

        <?php break; ?>


<?php
    case 'finish':
?>

        <?= $renderer->render($page, $answers); ?>

        <?php break; ?>

<?php endswitch; ?>

</div>

</body>
</html>