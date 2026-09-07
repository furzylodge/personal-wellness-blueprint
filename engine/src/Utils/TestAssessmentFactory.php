<?php

declare(strict_types=1);

namespace PWB\Utils;

use RuntimeException;
use PWB\Loader\JsonLoader;
use PWB\Assessment\Assessment;
use PWB\Assessment\AssessmentLoader;

final class TestAssessmentFactory
{
    public function __construct(
        private string $knowledgePath
    ) {}

    public function load(string $personaId): Assessment
    {
        $assessmentLoader = new AssessmentLoader(
            new JsonLoader(),
            $this->knowledgePath
        );

        $assessmentLoader->load();
        $questions = $assessmentLoader->questions();

        // Build question lookup
        $questionLookup = [];
        foreach ($questions as $question) {
            $questionLookup[$question['id']] = $question;
        }

        // Load persona JSON
        $persona = (new JsonLoader())->load(
            $this->knowledgePath . "/personas/persona-{$personaId}.json"
        );

        $extends = $persona['extends'] ?? 'maximum';

        switch ($extends) {

            case 'minimum':
                $answers = $this->buildMinimumAnswers($questions);
                break;

            case 'neutral':
                $answers = $this->buildNeutralAnswers($questions);
                break;

            default:
                $answers = $this->buildMaximumAnswers($questions);
                break;
        }

        // Apply persona overrides (stored values → answer index)
        foreach ($persona['answers'] as $questionId => $storedValue) {

            if (!isset($questionLookup[$questionId])) {
                throw new RuntimeException(
                    "Unknown question ID in persona '{$personaId}': {$questionId}"
                );
            }

            $values = $questionLookup[$questionId]['stored_values'];

            $bestIndex = 0;
            $bestDiff = PHP_FLOAT_MAX;

            foreach ($values as $index => $value) {

                $diff = abs((float)$value - (float)$storedValue);

                if ($diff < $bestDiff) {
                    $bestDiff = $diff;
                    $bestIndex = $index;
                }
            }

            $answers[$questionId] = $bestIndex;
        }

        return new Assessment(
            assessment: [
                'version' => '1.0.0',
                'persona' => $personaId,
                'name'    => $persona['name'] ?? $personaId,
            ],
            answers: $answers
        );
    }

    private function buildMaximumAnswers(array $questions): array
    {
        $answers = [];

        foreach ($questions as $question) {

            $stored = $question['stored_values'];

            if (!is_array($stored) || empty($stored)) {
                continue;
            }

            $maxValue = max($stored);
            $answers[$question['id']] = array_search($maxValue, $stored, true);
        }

        return $answers;
    }

    private function buildMinimumAnswers(array $questions): array
    {
        $answers = [];

        foreach ($questions as $question) {

            $stored = $question['stored_values'];

            if (!is_array($stored) || empty($stored)) {
                continue;
            }

            $minValue = min($stored);
            $answers[$question['id']] = array_search($minValue, $stored, true);
        }

        return $answers;
    }

    private function buildNeutralAnswers(array $questions): array
    {
        $answers = [];

        foreach ($questions as $question) {

            $stored = $question['stored_values'];

            if (!is_array($stored) || empty($stored)) {
                continue;
            }

            $bestIndex = 0;
            $bestDiff = PHP_FLOAT_MAX;

            foreach ($stored as $index => $value) {

                $diff = abs((float)$value - 0.5);

                if ($diff < $bestDiff) {
                    $bestDiff = $diff;
                    $bestIndex = $index;
                }
            }

            $answers[$question['id']] = $bestIndex;
        }

        return $answers;
    }
}