<?php

namespace PWB\Assessment;

use PWB\Loader\JsonLoader;

final class AssessmentLoader
{
    private array $questions = [];
    private array $matrix = [];

    public function __construct(
        private JsonLoader $loader,
        private string $knowledgePath
    ) {}

    public function load(): Assessment
    {
        $this->questions = $this->loader->load(
            $this->knowledgePath . '/assessment/questions.json'
        );

        $this->matrix = $this->loader->load(
            $this->knowledgePath . '/assessment/question-body-system-matrix.json'
        );

        return new Assessment(
            assessment: [
                'version'    => '1.0.0',
                'loaded_at'  => date(DATE_ATOM),
                'questions'  => count($this->questions),
            ],
            answers: [],
            bodySystems: []
        );
    }

    public function questions(): array
    {
        return $this->questions;
    }

    public function matrix(): array
    {
        return $this->matrix;
    }
}