<?php

declare(strict_types=1);

/**
 * Compatibility check for the synthetic acceptance fixture.
 *
 * This intentionally reports unmatched answer labels instead of guessing their
 * position on the new response scale.
 */

$profilesFile = __DIR__ . '/../../knowledgebase/test-data/pwb-2026-acceptance-test-profiles-v0.1.json';
$questionsFile = __DIR__ . '/../../knowledgebase/interface-design/pwb-question-interaction-design-v0.1.json';

if (!is_file($profilesFile) || !is_file($questionsFile)) {
    fwrite(STDERR, "Expected fixture files were not found.\n");
    exit(1);
}

$profiles = json_decode((string) file_get_contents($profilesFile), true, 512, JSON_THROW_ON_ERROR);
$questions = json_decode((string) file_get_contents($questionsFile), true, 512, JSON_THROW_ON_ERROR);

$options = [];
foreach ($questions['existing_questions'] ?? [] as $question) {
    if (!empty($question['ID']) && !empty($question['Proposed response options'])) {
        $options[$question['ID']] = $question['Proposed response options'];
    }
}

$matched = 0;
$unmatched = [];

foreach ($profiles['profiles'] ?? [] as $profileId => $answers) {
    foreach ($answers as $questionId => $answer) {
        if (!isset($options[$questionId])) {
            continue;
        }

        if (in_array($answer, $options[$questionId], true)) {
            ++$matched;
            continue;
        }

        $unmatched[] = [
            'profile' => $profileId,
            'question' => $questionId,
            'answer' => $answer,
        ];
    }
}

printf("Matched answer labels: %d\n", $matched);
printf("Unmatched answer labels: %d\n", count($unmatched));

foreach (array_slice($unmatched, 0, 25) as $row) {
    printf("%s %s: %s\n", $row['profile'], $row['question'], $row['answer']);
}

exit($unmatched === [] ? 0 : 2);
