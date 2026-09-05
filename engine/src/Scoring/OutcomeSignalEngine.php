<?php

declare(strict_types=1);

namespace PWB\Scoring;

/**
 * Provisional 2026 outcome signal engine.
 *
 * This is intentionally a structural engine, not the final calibrated model.
 * It implements the agreed separation between response signals, constructs,
 * correlated clusters and outcomes without introducing unapproved numerical
 * weights.
 */
final class OutcomeSignalEngine
{
    public const VERSION = '2026-scoring-provisional-v0.1';

    /** @var array<string, array<string, mixed>> */
    private const OUTCOMES = [
        'Sleep & Recovery' => [
            'primary' => ['Q20', 'Q35'],
            'mode' => 'independent_primary_mean',
            'supporting' => ['Q21', 'Q37'],
        ],
        'Immune Resilience' => [
            'primary' => ['Q02', 'Q13', 'Q28'],
            'mode' => 'correlated_primary_cluster',
            'supporting' => ['Q04', 'Q14', 'Q34'],
        ],
        'Mood, Stress & Emotional Balance' => [
            'primary' => ['Q15', 'Q30', 'Q42', 'Q43'],
            'mode' => 'balanced_primary_facets',
            'supporting' => ['Q08', 'Q19', 'Q38'],
        ],
        'Digestive & Gut Health' => [
            'primary' => ['Q04', 'Q14', 'Q29'],
            'mode' => 'convergent_primary',
            'supporting' => ['Q03', 'Q24a', 'Q27', 'Q31'],
        ],
        'Healthy Weight & Metabolic Balance' => [
            'primary' => ['Q25'],
            'mode' => 'direct_plus_convergence',
            'supporting' => ['Q17', 'Q24a', 'Q27', 'Q44'],
        ],
        'Hormonal & Life-Stage Balance' => [
            'primary' => ['Q47'],
            'mode' => 'conditional_direct',
            'supporting' => ['Q23', 'Q33'],
        ],
        'Mobility, Joints & Physical Function' => [
            'primary' => ['Q32', 'Q41'],
            'mode' => 'independent_primary_mean',
            'supporting' => ['Q44'],
        ],
        'Respiratory Health' => [
            'primary' => ['Q45'],
            'mode' => 'direct_with_support',
            'supporting' => ['Q11', 'Q26', 'Q34'],
        ],
        'Circulation & Cardiovascular Fitness' => [
            'primary' => [],
            'mode' => 'convergence_only',
            'supporting' => ['Q05', 'Q11', 'Q24a', 'Q26', 'Q44'],
        ],
        'Energy & Vitality' => [
            'primary' => ['Q12', 'Q26'],
            'mode' => 'convergent_primary',
            'supporting' => ['Q35'],
        ],
    ];

    public function __construct(
        private readonly ResponseSignalCalculator $responses,
        private readonly ConstructAggregator $aggregator,
    ) {
    }

    /**
     * @param array<string, float|null> $signals
     * @return array<string, array<string, mixed>>
     */
    public function calculate(array $signals): array
    {
        $results = [];

        foreach (self::OUTCOMES as $outcome => $definition) {
            $primary = $this->present($signals, $definition['primary']);
            $supporting = $this->present($signals, $definition['supporting']);

            if ($primary === [] && $supporting === []) {
                continue;
            }

            $score = $this->aggregatePrimary($primary, $definition['mode']);

            $results[$outcome] = [
                'score' => $score,
                'mode' => $definition['mode'],
                'primary' => $primary,
                'supporting' => $supporting,
                'ruleVersion' => self::VERSION,
            ];
        }

        return $results;
    }

    /** @param array<string, float> $signals */
    private function aggregatePrimary(array $signals, string $mode): ?float
    {
        if ($signals === []) {
            return null;
        }

        // The current architecture has not approved differential numerical
        // weights. Correlated clusters therefore use a normalised cluster mean,
        // while independent constructs use the same neutral mean at this stage.
        return $this->aggregator->mean(array_values($signals));
    }

    /**
     * @param array<string, float|null> $signals
     * @param string[] $questionIds
     * @return array<string, float>
     */
    private function present(array $signals, array $questionIds): array
    {
        $result = [];

        foreach ($questionIds as $questionId) {
            if (isset($signals[$questionId]) && $signals[$questionId] !== null) {
                $result[$questionId] = (float) $signals[$questionId];
            }
        }

        return $result;
    }
}
