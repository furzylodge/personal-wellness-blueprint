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
$optionBodySystemScores = $questions[$questionId]['option_body_system_scores'] ?? null;

// Normalise scalar values to arrays
$stored = is_array($stored) ? $stored : [$stored];
$scores = is_array($scores) ? $scores : [$scores];

// Submitted answers are always strings (form posts, session storage),
// while stored_values decoded from JSON are native int/float for
// numeric scales (e.g. [0, 0.33, 0.67, 1]). A *strict* array_search of
// a string against that array never matches — "1" !== 1 — so every
// numeric-scale question was silently scoring 0 regardless of the
// answer given. Compare against the stringified array instead.
$storedAsStrings = array_map('strval', $stored);

    $score = 0.0;

    if (is_array($answer) && $optionBodySystemScores) {

        // Multi-select with per-option body-system mapping:
        // each mapped body system scores 1.0 if ANY selected option
        // that maps to it has a positive score value, so unrelated
        // conditions bundled in the same question no longer bleed
        // into each other's body systems.
        $perSystemScore = [];

        foreach ($answer as $selected) {
            $index = array_search((string)$selected, $storedAsStrings, true);

            if ($index === false || (($scores[$index] ?? 0) <= 0)) {
                continue;
            }

            $optionSystems = $optionBodySystemScores[$selected] ?? [];

            foreach ($optionSystems as $bs => $flag) {
                if (strtoupper((string)$flag) === 'Y') {
                    $perSystemScore[$bs] = 1.0;
                }
            }
        }

        foreach ($perSystemScore as $bs => $value) {
            if (isset($bodySystems[$bs])) {
                $bodySystems[$bs] += $value;
            }
        }

        continue;

    }

    if (is_array($answer)) {

        // Multi-select: any positive option = 1
        foreach ($answer as $selected) {
            $index = array_search((string)$selected, $storedAsStrings, true);

            if ($index !== false && (($scores[$index] ?? 0) > 0)) {
                $score = 1.0;
                break;
            }
        }

} else {

 // Radio / single select


    $index = array_search((string)$answer, $storedAsStrings, true);

    if ($index !== false && isset($scores[$index])) {
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