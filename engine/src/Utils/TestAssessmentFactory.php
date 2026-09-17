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

        // A base persona (persona-neutral.json, persona-maximum.json) has no
        // parent to extend, and stores that as "extends": null rather than
        // omitting the key — but `?? 'maximum'` treats an explicit null the
        // same as "missing" and silently falls through to 'maximum', which
        // made persona-neutral.json build a MAXIMUM-severity baseline
        // instead of a neutral one. Fall back to the persona's own id first,
        // since these base personas are named after the mode they mean.
        $extends = $persona['extends'] ?? $persona['id'] ?? 'maximum';

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

        // Apply persona overrides. Two forms are supported:
        //
        //  - a specific stored value (string, or array of strings for a
        //    multi_select), applied directly — the only sensible option
        //    for enum-style questions (e.g. SAF001's named conditions)
        //    that have no inherent severity ordering and no score_values
        //    to rank against; and
        //  - a numeric target severity (0-1), matched to the nearest
        //    score_values entry (falling back to stored_values itself
        //    when a question has no separate scoring scale) — for
        //    frequency/quality-style questions where "how bad" is what
        //    matters, not which literal option was picked.
        foreach ($persona['answers'] as $questionId => $override) {

            if (!isset($questionLookup[$questionId])) {
                throw new RuntimeException(
                    "Unknown question ID in persona '{$personaId}': {$questionId}"
                );
            }

            $question = $questionLookup[$questionId];
            $stored   = $question['stored_values'];
            $isMultiSelect = ($question['answer_type'] ?? '') === 'multi_select';

            if (is_string($override) || (is_array($override) && $isMultiSelect)) {

                // Explicit stored value(s) — validate they actually exist
                // on this question, so a typo fails loudly rather than
                // silently producing an unanswered/mismatched question.
                foreach ((array) $override as $explicit) {
                    if (!in_array((string) $explicit, array_map('strval', $stored), true)) {
                        throw new RuntimeException(
                            "Persona '{$personaId}': '{$explicit}' is not a valid stored value for {$questionId}"
                        );
                    }
                }

                $answers[$questionId] = $isMultiSelect
                    ? array_map('strval', (array) $override)
                    : (string) $override;

                continue;
            }

            // Numeric target severity → nearest score_values/stored_values entry
            $scores = $question['score_values'] ?? $stored;

            $bestIndex = 0;
            $bestDiff = PHP_FLOAT_MAX;

            foreach ($scores as $index => $value) {

                $diff = abs((float)$value - (float)$override);

                if ($diff < $bestDiff) {
                    $bestDiff = $diff;
                    $bestIndex = $index;
                }
            }

            // Store the actual stored_values entry (as submitted by the
            // real questionnaire form), not its numeric score or index.
            $value = (string) $stored[$bestIndex];

            $answers[$questionId] = $isMultiSelect ? [$value] : $value;
        }

        // A persona can also declare stated wellness goals (PF014's
        // options, e.g. "OUT005" for Digestive & Gut Health), separately
        // from "answers" since PF014 is a profile field rather than a
        // scored question.
        $goals = $persona['goals'] ?? [];
        $answers['PF014'] = $goals;

        return new Assessment(
            assessment: [
                'version' => '1.0.0',
                'persona' => $personaId,
                'name'    => $persona['name'] ?? $personaId,
            ],
            answers: $answers,
            goals: $goals
        );
    }

    private function buildMaximumAnswers(array $questions): array
    {
        return $this->buildAnswersByScore($questions, fn(array $scores) => array_search(max($scores), $scores, true));
    }

    private function buildMinimumAnswers(array $questions): array
    {
        return $this->buildAnswersByScore($questions, fn(array $scores) => array_search(min($scores), $scores, true));
    }

    private function buildNeutralAnswers(array $questions): array
    {
        return $this->buildAnswersByScore($questions, function (array $scores) {

            $bestIndex = 0;
            $bestDiff = PHP_FLOAT_MAX;

            foreach ($scores as $index => $value) {

                $diff = abs((float)$value - 0.5);

                if ($diff < $bestDiff) {
                    $bestDiff = $diff;
                    $bestIndex = $index;
                }
            }

            return $bestIndex;
        });
    }

    /**
     * Picks an index using $pickIndex against each question's score_values
     * (falling back to stored_values for questions with no separate scoring
     * scale), then records the actual stored_values entry at that index —
     * matching the format real submitted answers are stored in.
     */
    private function buildAnswersByScore(array $questions, callable $pickIndex): array
    {
        $answers = [];

        foreach ($questions as $question) {

            $stored = $question['stored_values'] ?? null;

            if (!is_array($stored) || empty($stored)) {
                continue;
            }

            $scores = $question['score_values'] ?? $stored;

            if (!is_array($scores) || count($scores) !== count($stored)) {
                $scores = $stored;
            }

            $index = $pickIndex(array_values($scores));

            if ($index === false) {
                continue;
            }

            $value = (string) $stored[$index];

            // multi_select answers are submitted (and expected downstream)
            // as arrays of selected values, not a single scalar.
            $answers[$question['id']] = ($question['answer_type'] ?? '') === 'multi_select'
                ? [$value]
                : $value;
        }

        return $answers;
    }
}