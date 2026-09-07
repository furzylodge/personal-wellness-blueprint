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
                $this->knowledgePath . '/assessment/questions.json'
            )
        );

        $matrix = $this->indexMatrix(
            $this->loader->load(
                $this->knowledgePath . '/assessment/question-body-system-matrix.json'
            )
        );

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
        
        echo PHP_EOL;
	echo "Questions indexed: " . count($questions) . PHP_EOL;
	echo "Matrix indexed: " . count($matrix) . PHP_EOL;
	echo "Answer IDs:" . PHP_EOL;

	foreach ($assessment->answers as $id => $value) {
	    echo "  {$id}";
	    echo isset($questions[$id]) ? " T question" : " X question";
	    echo isset($matrix[$id]) ? " T matrix" : " X matrix";
	    echo PHP_EOL;
	}

        foreach ($assessment->answers as $questionId => $selectedIndex) {

            if (!isset($questions[$questionId])) {
                continue;
            }

            $stored = $questions[$questionId]['stored_values'];

            if (!isset($stored[$selectedIndex])) {
                continue;
            }

            $score = (float) $stored[$selectedIndex];

            if (!isset($matrix[$questionId])) {
                continue;
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
            $indexed[$question['id']] = $question;
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

        $indexed[$row['question_id']] = $scores;
    }

    return $indexed;
}

}