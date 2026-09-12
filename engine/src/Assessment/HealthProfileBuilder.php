<?php

declare(strict_types=1);

namespace PWB\Assessment;

use PWB\Recommendation\Models\HealthProfile;
use PWB\Loader\JsonLoader;

final class HealthProfileBuilder
{
    public function __construct(
        private JsonLoader $loader,
        private string $knowledgePath
    ) {}

    public function build(Assessment $assessment): HealthProfile
    {
$questions = $this->indexById(
    $this->loader->load(
        $this->knowledgePath . '/assessment/question-body-system-scoring.json'
    )
);

$matrix = $this->indexMatrix($questions);

        $bodySystems = [
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

foreach ($assessment->answers as $questionId => $answer) {

    if (!isset($questions[$questionId], $matrix[$questionId])) {
        continue;
    }

    $stored = $questions[$questionId]['stored_values'] ?? [];
    $scores = $questions[$questionId]['score_values'] ?? $stored;

    $score = 0.0;

    if (is_array($answer)) {

        // Multi-select: any positive option = 1
        foreach ($answer as $selected) {
            $index = array_search($selected, $stored, true);

            if ($index !== false && (($scores[$index] ?? 0) > 0)) {
                $score = 1.0;
                break;
            }
        }

    } else {

        // Slider / radio
        $index = (int) $answer;

        if (isset($scores[$index])) {
            $score = (float) $scores[$index];
        }
    }

    foreach ($matrix[$questionId] as $bs => $weight) {
        $weight = (float) $weight;

        if ($weight > 0) {
            $bodySystems[$bs] += ($score * $weight);
        }
    }
}
        
        $normalizer = new BodySystemNormalizer(
    	$this->loader,
    	$this->knowledgePath);

	$normalized = $normalizer->normalize($bodySystems);

        return new HealthProfile(
            assessment: $assessment->assessment,
            bodySystems: $normalized,
            mechanisms: [],
            biomarkers: [],
            symptoms: [],
            conditions: [],
            preferences: $assessment->preferences,
            restrictions: $assessment->restrictions,
            goals: $assessment->goals
        );
    }

private function indexById(array $questions): array
{
    $indexed = [];

    foreach ($questions as $question) {
        $id = $question['production_id'] ?? null;

        if ($id) {
            $indexed[$id] = $question;
        }
    }

    return $indexed;
}

private function indexMatrix(array $rows): array
{
    $indexed = [];

    foreach ($rows as $row) {

        $scores = [];

        foreach ($row['body_system_scores'] as $bs => $value) {
            $scores[$bs] = strtoupper((string)$value) === 'Y' ? 1.0 : 0.0;
        }

        $id = $row['production_id'] ?? null;

        if ($id) {
            $indexed[$id] = $scores;
        }
    }

    return $indexed;
}




}