<?php

namespace PWB\Assessment;

class QuizController
{
    private array $pages;
    private array $profileFields;
    private array $questions;

public function __construct(private AssessmentLoader $loader)
{
    $this->pages = $loader->quizFlow()['pages'] ?? [];
    $this->profileFields = $loader->profileFields()['fields'] ?? [];
    $this->questions = $loader->questions()['questions'] ?? [];
}
    /**
     * Return all quiz pages.
     */
    public function pages(): array
    {
        return $this->pages;
    }

    /**
     * Return the first page in the quiz flow.
     */
    public function firstPage(): ?array
    {
        return $this->pages[0] ?? null;
    }

    /**
     * Find a page by its ID.
     */
    public function page(string $pageId): ?array
    {
        foreach ($this->pages as $page) {
            if (($page['id'] ?? '') === $pageId) {
                return $page;
            }
        }

        return null;
    }

    /**
     * Return a page with all profile/question references expanded.
     */
public function pageComponents(string $pageId): array
{
    $page = $this->page($pageId);

    if (!$page) {
        return [];
    }

    // Dynamic profile page
    if (($page['source'] ?? null) === 'profile-fields.json') {

        $components = [];

        foreach ($this->profileFields as $field) {
            $components[] = [
                'type'  => 'profile',
                'field' => $field
            ];
        }

        $page['components'] = $components;

        return $page;
    }

    // Standard page with explicit components
    $components = [];

    foreach ($page['components'] ?? [] as $component) {

        switch ($component['type']) {

            case 'profile':
                $components[] = [
                    'type'  => 'profile',
                    'field' => $this->findProfileField($component['field'])
                ];
                break;

            case 'question':
                $components[] = [
                    'type'     => 'question',
                    'question' => $this->findQuestion($component['question'])
                ];
                break;

            default:
                $components[] = $component;
        }
    }

    $page['components'] = $components;

    return $page;
}

    /**
     * Locate a profile field by ID.
     */
    private function findProfileField(string $id): ?array
    {
        foreach ($this->profileFields as $field) {
            if (($field['id'] ?? '') === $id) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Locate a question by ID.
     */
    private function findQuestion(string $id): ?array
    {
        foreach ($this->questions as $question) {
            if (($question['id'] ?? '') === $id) {
                return $question;
            }
        }

        return null;
    }
}