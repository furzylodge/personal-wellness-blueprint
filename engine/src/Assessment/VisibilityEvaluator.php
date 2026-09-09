<?php

namespace PWB\Assessment;

class VisibilityEvaluator
{
    public static function isVisible(array $question, array $answers): bool
    {
        if (!isset($question['visible_if'])) {
            return true;
        }

        $rule = $question['visible_if'];

        $parent = $rule['question'] ?? null;
        $operator = $rule['operator'] ?? 'equals';
        $value = $rule['value'] ?? null;

        if (!$parent || !array_key_exists($parent, $answers)) {
            return false;
        }

        $answer = $answers[$parent];

        return match ($operator) {

            'equals' =>
                (string)$answer === (string)$value,

            'contains' =>
                is_array($answer) && in_array($value, $answer, true),

            default => false,
        };
    }
}