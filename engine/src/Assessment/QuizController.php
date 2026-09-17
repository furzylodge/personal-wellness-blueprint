<?php

namespace PWB\Assessment;

use PWB\Assessment\VisibilityEvaluator;

class QuizController
{
    private array $pages;
    private array $profileFields;
    private array $questions;

public function __construct(private AssessmentLoader $loader)
{
    $this->pages = $loader->quizFlow()['pages'] ?? [];
    $this->profileFields = $loader->profileFields()['fields'] ?? [];
    $this->questions = $loader->questions();
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
public function pageComponents(string $pageId, array $answers = []): array
{
    $page = $this->page($pageId);

    if (!$page) {
        return [];
    }

    $components = [];

    // Dynamic profile page
    if (($page['source'] ?? '') === 'profile-fields.json') {

        foreach ($this->profileFields as $field) {
            $components[] = [
                'type'  => 'profile',
                'field' => $field
            ];
        }

        $page['components'] = $components;
        return $page;
    }

// Dynamic questions page
if (($page['source'] ?? '') === 'questions.json') {

    $page['components'] = [];

    // Ids of every question that will appear on this page, so we can
    // tell a same-page conditional (its parent question's input is
    // present in the DOM and toggled live by JS) apart from a
    // cross-page one (the parent, e.g. a profile field or a question
    // from an earlier section, isn't rendered here at all, so it
    // must be filtered out server-side using the stored answer).
    $pageQuestionIds = [];

    foreach ($this->questions as $question) {
        if (($question['page'] ?? '') === $pageId) {
            $pageQuestionIds[] = $question['id'];
        }
    }

    foreach ($this->questions as $question) {

        if (($question['page'] ?? '') !== $pageId) {
            continue;
        }

        $visibleIf = $question['visible_if'] ?? null;

        if ($visibleIf && !in_array($visibleIf['question'] ?? null, $pageQuestionIds, true)) {

            if (!VisibilityEvaluator::isVisible($question, $answers)) {
                continue;
            }
        }

        $page['components'][] = [
            'type'     => 'question',
            'question' => $question
        ];
    }

    return $page;
}

    // Static component pages (welcome, results etc.)
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
    
/**
 * Return the next page ID in the quiz flow.
 */
public function nextPageId(string $currentPageId): ?string
{
    foreach ($this->pages as $index => $page) {

        if ($page['id'] === $currentPageId) {
            return $this->pages[$index + 1]['id'] ?? null;
        }
    }

    return null;
}

/**
 * Return the previous page ID.
 */
public function previousPageId(string $currentPageId): ?string
{
    foreach ($this->pages as $index => $page) {

        if ($page['id'] === $currentPageId) {
            return $this->pages[$index - 1]['id'] ?? null;
        }
    }

    return null;
}

/**
 * Return the complete ordered page sequence.
 */
public function pageSequence(): array
{
    return array_column($this->pages, 'id');
}

}