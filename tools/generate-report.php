<?php

declare(strict_types=1);

session_start();

$root = dirname(__DIR__);

require_once $root . '/vendor/autoload.php';

use PWB\Assessment\Assessment;
use PWB\Reports\ReportBuilder;
use PWB\Reports\ReportDataBuilder;

$answers = $_SESSION['answers'] ?? [];

if (empty($answers)) {
    die('No assessment found in this session.');
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
    person: [],
    answers: $answers,
    bodySystems: [],
    preferences: [],
    restrictions: [],
    goals: []
);

/*
 |----------------------------------------------------------
 | Generate report
 |----------------------------------------------------------
*/

$reportData = (new ReportDataBuilder())->build($assessment);

$reportData['theme'] = 'theme-' . ($_SESSION['theme'] ?? 'green');

echo (new ReportBuilder())->build($reportData);