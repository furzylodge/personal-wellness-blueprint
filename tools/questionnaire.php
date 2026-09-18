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

if (isset($_GET['page'])) {
    $_SESSION['current_page'] = strtoupper($_GET['page']);
}

$pageId = $_SESSION['current_page'] ?? 'WELCOME';

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

if (($page['type'] ?? '') === 'profile') {

    // Required fields from canonical JSON
    $profileFields = $loader->profileFields()['fields'] ?? [];

    foreach ($profileFields as $field) {

        if (empty($field['required'])) {
            continue;
        }

        $fieldId = $field['id'];
        $value   = $answers[$fieldId] ?? null;

        $isEmpty = match ($field['type']) {
            'checkbox'     => empty($value),
            'multi_select' => empty($value) || count($value) === 0,
            default        => trim((string)$value) === ''
        };

        if ($isEmpty) {
            $errors[$fieldId] = [
                'label'   => $field['label'],
                'message' => 'This field is required.'
            ];
        }
    }

    // Maximum 3 wellness goals
    if (count($answers['PF014'] ?? []) > 3) {
        $errors['PF014'] = [
            'label'   => 'Primary wellness goals',
            'message' => 'Select a maximum of 3 options.'
        ];
    }
}

/* ----------------------------------------------------------
   Validate required follow-up questions
---------------------------------------------------------- */

foreach ($page['components'] ?? [] as $component) {

    if (($component['type'] ?? '') !== 'question') {
        continue;
    }

    $question = $component['question'] ?? [];
    $followUp = $question['follow_up'] ?? null;

    if (!$followUp || empty($followUp['required'])) {
        continue;
    }

    $parentId = $question['id'];
    $parentAnswer = $answers[$parentId] ?? null;

    $showIf = $followUp['show_if'];
    $visible = false;

    switch ($showIf['operator']) {

        case 'equals':
            $visible = ((string)$parentAnswer === (string)$showIf['value']);
            break;

        case 'contains':
            $visible = is_array($parentAnswer)
                && in_array((string)$showIf['value'], array_map('strval', $parentAnswer), true);
            break;
    }

    if ($visible) {

        $value = trim((string)($answers[$followUp['id']] ?? ''));

        if ($value === '') {

            $errors[$followUp['id']] = [
                'label'   => $followUp['label'],
                'message' => 'Please provide this additional information.'
            ];

        }

    }

}

/* ----------------------------------------------------------
   Validate required question answers
---------------------------------------------------------- */

foreach ($page['components'] ?? [] as $component) {

    if (($component['type'] ?? '') !== 'question') {
        continue;
    }

    $question = $component['question'] ?? [];

    if (empty($question['required'])) {
        continue;
    }

    $questionId = $question['id'];

    // Respect conditional visibility so hidden questions aren't forced
    $visibleIf = $question['visible_if'] ?? null;

    if ($visibleIf) {

        $controllingAnswer = $answers[$visibleIf['question']] ?? null;
        $conditionMet = false;

        switch ($visibleIf['operator']) {

            case 'equals':
                $conditionMet = ((string)$controllingAnswer === (string)$visibleIf['value']);
                break;

            case 'not_equals':
                $conditionMet = ((string)$controllingAnswer !== (string)$visibleIf['value']);
                break;

            case 'contains':
                $conditionMet = is_array($controllingAnswer)
                    && in_array((string)$visibleIf['value'], array_map('strval', $controllingAnswer), true);
                break;

            case 'not_contains':
                $conditionMet = !is_array($controllingAnswer)
                    || !in_array((string)$visibleIf['value'], array_map('strval', $controllingAnswer), true);
                break;
        }

        if (!$conditionMet) {
            continue;
        }
    }

    $value = $answers[$questionId] ?? null;

    $isEmpty = match ($question['answer_type'] ?? '') {
        'multi_select' => empty($value) || (is_array($value) && count($value) === 0),
        default        => trim((string)$value) === ''
    };

    if ($isEmpty) {
        $errors[$questionId] = [
            'label'   => $question['question'] ?? 'This question',
            'message' => 'Please answer this question before continuing.'
        ];
    }
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

/* ----------------------------------------------------------
   Build review/nav data from the final answers, so the journey
   nav and the review page both reflect this request's submission
---------------------------------------------------------- */

$pages = [];

foreach ($quiz->pages() as $quizPage) {
    $pages[] = $quiz->pageComponents($quizPage['id'], $answers);
}

// Build profile values keyed by field key
$profile = [];

foreach ($loader->profileFields()['fields'] as $field) {
    $profile[$field['key']] = $answers[$field['id']] ?? null;
}

$reviewData = [
    'pages'     => $pages,
    'questions' => $loader->questions(),
    'profile'   => $profile,
    'answers'   => $answers
];

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
<?= $renderer->renderIcon($page['icon'] ?? 'leaf') ?>
    </div>

    <?= $renderer->render($page, $answers, $errors, $reviewData); ?>

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

<?php $scope = \PWB\Knowledge\ScopeStats::compute($root . '/knowledgebase'); ?>

<div class="scope-banner">

    <div class="scope-banner-label">The depth behind your Blueprint</div>

    <div class="scope-banner-grid">

        <div class="scope-stat">
            <strong><?= number_format($scope['questions']) ?></strong>
            <span>Questions asked</span>
        </div>

        <div class="scope-stat">
            <strong><?= number_format($scope['foods']) ?></strong>
            <span>Foods analysed</span>
        </div>

        <div class="scope-stat">
            <strong><?= number_format($scope['bodySystems']) ?></strong>
            <span>Body systems mapped</span>
        </div>

        <div class="scope-stat">
            <strong><?= number_format($scope['mechanisms']) ?></strong>
            <span>Pathways modelled</span>
        </div>

        <div class="scope-stat">
            <strong><?= number_format($scope['bioactives']) ?></strong>
            <span>Bioactives tracked</span>
        </div>

        <div class="scope-stat">
            <strong><?= number_format($scope['nutrients']) ?></strong>
            <span>Nutrients scored</span>
        </div>

        <div class="scope-stat scope-stat-highlight">
            <?php // Rounded down to the nearest hundred for the "+" claim —
                  // the exact count shifts slightly as the catalogue grows,
                  // and a precise-looking number ("3,003+") reads oddly next
                  // to a "+", whereas a round one stays true as it grows.
                  // This is every distinct food -> pathway route in the
                  // dataset (direct, or via a bioactive the food contains) —
                  // a real, computed number, not a combinatorial estimate. ?>
            <strong><?= number_format((int) floor($scope['foodPathways'] / 100) * 100) ?>+</strong>
            <span>Food pathways traced</span>
        </div>

        <div class="scope-stat scope-stat-you">
            <strong>1</strong>
            <span>You</span>
        </div>

    </div>

</div>

    <a class="next" href="?page=<?= $quiz->nextPageId($pageId) ?>">
        Begin Assessment
    </a>

                <p class="welcome-note">
    Your progress is saved automatically as you complete the assessment.
</p>

<p class="welcome-disclaimer">
    This assessment is for educational and general wellness purposes only. It does not diagnose, treat, or replace professional medical advice. If any of your answers raise a personal health concern, please speak to your GP or a qualified healthcare professional.
</p>

</div>

<?php break; ?>
<?php
    case 'profile':
?>

        <form method="post" novalidate>
        
        <input type="hidden" name="theme"
       value="<?= htmlspecialchars($_SESSION['theme'] ?? 'green') ?>">

            <?= $renderer->render($page, $answers, $errors, $reviewData); ?>

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
    case 'questions':
?>

        <form method="post" novalidate>
        
        <input type="hidden" name="theme"
       value="<?= htmlspecialchars($_SESSION['theme'] ?? 'green') ?>">

            <?= $renderer->render($page, $answers, $errors, $reviewData); ?>

            <?php if (!empty($errors)): ?>

                <div class="error">
                    <strong>Please answer all required questions before continuing.</strong>
                </div>

            <?php endif; ?>

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

        <?= $renderer->render($page, $answers, $errors, $reviewData); ?>

        <?php break; ?>

<?php endswitch; ?>

</div>

</body>
</html>