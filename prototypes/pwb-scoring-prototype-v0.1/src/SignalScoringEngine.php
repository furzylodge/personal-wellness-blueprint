<?php

declare(strict_types=1);

namespace PWB\Scoring;

/**
 * Calibration-stage scorer for the current PWB signal model.
 *
 * This class deliberately does NOT assign Weak/Moderate/Strong thresholds.
 * Those thresholds are still a calibration decision.
 *
 * Input format:
 * [
 *     'Q02' => 0.67,
 *     'Q13' => 0.50,
 *     ...
 * ]
 *
 * Values must already have been normalised according to
 * pwb-2026-question-definition-v0.7.
 */
final class SignalScoringEngine
{
    /**
     * @param array<string, float|null> $answers
     * @param array<string, array<string, mixed>> $config
     * @return array<string, array<string, mixed>>
     */
    public function score(
        array $answers,
        array $config
    ): array {
        $results = [];

        foreach ($config as $outcome) {
            $primary = $this->evidence($answers, $outcome['primary'] ?? []);
            $supporting = $this->evidence($answers, $outcome['supporting'] ?? []);
            $context = $this->evidence($answers, $outcome['context'] ?? []);

            $primaryIntensity = $this->mean($primary);
            $supportingIntensity = $this->mean($supporting);

            $results[$outcome['id']] = [
                'id' => $outcome['id'],
                'name' => $outcome['name'],
                'primary' => $primary,
                'supporting' => $supporting,
                'context' => $context,

                // Calibration inputs, not final signal scores.
                'primary_intensity' => $primaryIntensity,
                'supporting_intensity' => $supportingIntensity,
                'primary_count' => count($primary),
                'supporting_count' => count($supporting),
                'primary_coverage' => $this->coverage(
                    $primary,
                    $outcome['primary'] ?? []
                ),
                'supporting_coverage' => $this->coverage(
                    $supporting,
                    $outcome['supporting'] ?? []
                ),

                // Deliberately unset until thresholds/calibration are agreed.
                'signal_strength' => null,
                'signal_status' => 'calibration_pending',

                'rule' => $outcome['rule'],
            ];
        }

        return $results;
    }

    /**
     * @param array<string, float|null> $answers
     * @param array<int, string> $questionIds
     * @return array<string, float>
     */
    private function evidence(array $answers, array $questionIds): array
    {
        $result = [];

        foreach ($questionIds as $questionId) {
            if (!array_key_exists($questionId, $answers)) {
                continue;
            }

            $value = $answers[$questionId];

            if ($value === null) {
                continue;
            }

            $value = (float) $value;

            if ($value < 0.0 || $value > 1.0) {
                throw new \InvalidArgumentException(
                    "Normalised value for {$questionId} must be between 0 and 1."
                );
            }

            $result[$questionId] = $value;
        }

        return $result;
    }

    /**
     * @param array<string, float> $values
     */
    private function mean(array $values): ?float
    {
        if ($values === []) {
            return null;
        }

        return array_sum($values) / count($values);
    }

    /**
     * @param array<string, float> $values
     * @param array<int, string> $questionIds
     */
    private function coverage(array $values, array $questionIds): float
    {
        if ($questionIds === []) {
            return 0.0;
        }

        return count($values) / count($questionIds);
    }
}
