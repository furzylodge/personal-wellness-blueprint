<?php

declare(strict_types=1);

namespace PWB\Assessment;

use PWB\Loader\JsonLoader;

final class BodySystemNormalizer
{
    public function __construct(
        private JsonLoader $loader,
        private string $knowledgePath
    ) {}

    public function normalize(array $rawScores): array
    {
        $questions = $this->loader->load(
            $this->knowledgePath . '/assessment/questions.json'
        );

        $matrix = $this->loader->load(
            $this->knowledgePath . '/assessment/question-body-system-matrix.json'
        );

        $maximums = $this->calculateMaximums($questions, $matrix);

        $normalized = [];

        foreach ($maximums as $bs => $max) {

            if ($max <= 0) {
                $normalized[$bs] = 0.0;
                continue;
            }

            $value = (($rawScores[$bs] ?? 0) / $max) * 100;

            $normalized[$bs] = round($value, 1);
        }

        return $normalized;
    }

    private function calculateMaximums(array $questions, array $matrix): array
    {
        $questionIndex = [];

        foreach ($questions as $question) {

        $scores = $question['score_values']
    ?? $question['stored_values']
    ?? [];

if (!is_array($scores) || empty($scores)) {
    $questionIndex[$question['id']] = 0.0;
    continue;
}

$questionIndex[$question['id']] = (float) max($scores);        

        }

        $maximums = [
            'BS001' => 0.0,
            'BS002' => 0.0,
            'BS003' => 0.0,
            'BS004' => 0.0,
            'BS005' => 0.0,
            'BS006' => 0.0,
            'BS007' => 0.0,
            'BS008' => 0.0,
            'BS009' => 0.0,
        ];

        foreach ($matrix as $row) {

            $maxQuestionScore = $questionIndex[$row['question_id']] ?? 0;

            foreach ($row['body_system_scores'] as $bs => $flag) {

                if (strtoupper((string)$flag) === 'Y') {
                    $maximums[$bs] += $maxQuestionScore;
                }
            }
        }

        return $maximums;
    }
}