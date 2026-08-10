<?php

declare(strict_types=1);

namespace PWB\Recommendation\Models;

final class Priority
{
    /**
     * Unique identifier.
     *
     * e.g. MEC021, BS003
     */
    public string $id;

    /**
     * Type of priority.
     *
     * BODY_SYSTEM
     * MECHANISM
     * BIOMARKER
     * SYMPTOM
     * CONDITION
     */
    public string $type;

    /**
     * Display name.
     */
    public string $name;

    /**
     * Original score from the assessment.
     */
    public float $score;

    /**
     * Confidence in the assessment.
     *
     * 0.0 - 1.0
     */
    public float $confidence;

    /**
     * Weight applied by the recommendation engine.
     *
     * Default = 1.0
     */
    public float $weight = 1.0;

    /**
     * Final weighted score.
     */
    public float $finalScore;

    /**
     * Why this priority exists.
     */
    public array $derivedFrom = [];

    /**
     * Constructor.
     */
    public function __construct(
        string $id,
        string $type,
        string $name,
        float $score,
        float $confidence = 1.0,
        float $weight = 1.0,
        array $derivedFrom = []
    ) {
        $this->id = $id;

        $this->type = $type;

        $this->name = $name;

        $this->score = $score;

        $this->confidence = $confidence;

        $this->weight = $weight;

        $this->finalScore = $score * $weight;

        $this->derivedFrom = $derivedFrom;
    }
}