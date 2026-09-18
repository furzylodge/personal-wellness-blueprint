<?php

declare(strict_types=1);

namespace PWB\Knowledge;

use PWB\Loader\JsonLoader;

/**
 * Live counts of the underlying dataset (questions, foods, body systems,
 * mechanisms, bioactives, nutrients, and the scored relationships between
 * them), used to give the questionnaire's welcome page and the report a
 * "here's the scope of what goes into this" moment — e.g. "66 questions,
 * 92 foods, 9 body systems... 1 YOU".
 *
 * Every figure here is computed directly from the taxonomy JSON files
 * rather than hand-typed, so it can never drift out of date as the
 * catalogue grows (more foods, more bioactives, etc.) — the whole point
 * of the exercise is a number Simon can point to and know it's real.
 */
final class ScopeStats
{
    public static function compute(string $knowledgePath): array
    {
        $loader = new JsonLoader();

        // questions.json's root is a flat array (not wrapped in a
        // "questions" key, unlike most other taxonomy files).
        $questions   = count($loader->load($knowledgePath . '/assessment/questions.json'));
        $bodySystems = count($loader->load($knowledgePath . '/taxonomy/body-systems-mechanisms.json')['bodySystems'] ?? []);
        $mechanisms  = count($loader->load($knowledgePath . '/taxonomy/mechanisms.json')['mechanisms'] ?? []);
        $foods       = count($loader->load($knowledgePath . '/taxonomy/foods.json')['foods'] ?? []);
        $bioactives  = count($loader->load($knowledgePath . '/taxonomy/bioactives.json')['bioactives'] ?? []);
        $vitamins    = count($loader->load($knowledgePath . '/taxonomy/vitamins.json')['vitamins'] ?? []);
        $minerals    = count($loader->load($knowledgePath . '/taxonomy/minerals.json')['minerals'] ?? []);

        $sections = self::countSections($loader, $knowledgePath);

        $foodMechanismPairs = self::countFoodMechanismPairs($loader, $knowledgePath);

        // "Relationships" = every individual scored connection in the
        // dataset — from a question flagging a body system, right through
        // to a bioactive supporting a mechanism. Counted at the pair
        // level (not the file's top-level row count, which for
        // food-mechanisms.json is one row PER FOOD containing several
        // mechanism matches each) so it reflects the real number of
        // individual clinical judgements behind the engine, not just how
        // the JSON happens to be grouped.
        $relationships =
            self::countQuestionBodySystemLinks($loader, $knowledgePath)
            + self::countBodySystemMechanismLinks($loader, $knowledgePath)
            + $foodMechanismPairs
            + count($loader->load($knowledgePath . '/taxonomy/food-bioactives.json')['relationships'] ?? [])
            + count($loader->load($knowledgePath . '/taxonomy/nutrient-mechanisms.json')['relationships'] ?? [])
            + count($loader->load($knowledgePath . '/taxonomy/bioactive-mechanisms.json')['relationships'] ?? []);

        // "Food pathways" = every distinct route from a food to a
        // nutritional pathway — either directly (food-mechanisms.json) or
        // via a bioactive it contains (food-bioactives.json joined against
        // bioactive-mechanisms.json). This is the number Simon asked
        // about specifically: a real, computed count of "how many ways a
        // food in this catalogue can influence a mechanism", as opposed
        // to a theoretical combinatorial figure (the true count of
        // possible questionnaire answer combinations runs to roughly
        // 10^48, which reads as fake rather than impressive, so it's
        // deliberately not used anywhere in these stats).
        $foodPathways = $foodMechanismPairs + self::countFoodBioactiveMechanismChains($loader, $knowledgePath);

        return [
            'questions'     => $questions,
            'sections'      => $sections,
            'bodySystems'   => $bodySystems,
            'mechanisms'    => $mechanisms,
            'foods'         => $foods,
            'bioactives'    => $bioactives,
            'vitamins'      => $vitamins,
            'minerals'      => $minerals,
            'nutrients'     => $vitamins + $minerals,
            'relationships' => $relationships,
            'foodPathways'  => $foodPathways,
        ];
    }

    /**
     * quiz-flow.json doesn't store a single "number of sections" anywhere
     * — it's implied by how many distinct page "section" numbers exist
     * across the profile + questions pages. Counted rather than hand-set
     * so it stays right if a section is ever added or removed.
     */
    private static function countSections(JsonLoader $loader, string $knowledgePath): int
    {
        $flow = $loader->load($knowledgePath . '/assessment/quiz-flow.json');

        $sections = [];

        foreach ($flow['pages'] ?? [] as $page) {
            if (isset($page['section'])) {
                $sections[$page['section']] = true;
            }
        }

        return count($sections);
    }

    private static function countQuestionBodySystemLinks(JsonLoader $loader, string $knowledgePath): int
    {
        // This file's root is also a flat array, like questions.json.
        $questions = $loader->load($knowledgePath . '/assessment/question-body-system-scoring.json');

        $count = 0;

        foreach ($questions as $question) {
            foreach ($question['body_system_scores'] ?? [] as $flag) {
                if ($flag === 'Y') {
                    $count++;
                }
            }
        }

        return $count;
    }

    private static function countBodySystemMechanismLinks(JsonLoader $loader, string $knowledgePath): int
    {
        $taxonomy = $loader->load($knowledgePath . '/taxonomy/body-systems-mechanisms.json');

        $count = 0;

        foreach ($taxonomy['bodySystems'] ?? [] as $system) {
            $count += count($system['mechanisms'] ?? []);
        }

        return $count;
    }

    private static function countFoodMechanismPairs(JsonLoader $loader, string $knowledgePath): int
    {
        $relationships = $loader->load($knowledgePath . '/taxonomy/food-mechanisms.json')['relationships'] ?? [];

        $count = 0;

        foreach ($relationships as $foodRelationship) {
            $count += count($foodRelationship['mechanisms'] ?? []);
        }

        return $count;
    }

    /**
     * Every food-bioactive pairing (food-bioactives.json) joined against
     * however many mechanisms that same bioactive supports
     * (bioactive-mechanisms.json) — i.e. the number of distinct
     * food -> bioactive -> mechanism chains, on top of the foods'
     * *direct* mechanism matches counted separately above.
     */
    private static function countFoodBioactiveMechanismChains(JsonLoader $loader, string $knowledgePath): int
    {
        $foodBioactives = $loader->load($knowledgePath . '/taxonomy/food-bioactives.json')['relationships'] ?? [];
        $bioactiveMechanisms = $loader->load($knowledgePath . '/taxonomy/bioactive-mechanisms.json')['relationships'] ?? [];

        $mechanismCountByBioactive = [];

        foreach ($bioactiveMechanisms as $row) {
            $bioactiveId = $row['bioactive'] ?? $row['bioactiveId'] ?? null;

            if ($bioactiveId === null) {
                continue;
            }

            $mechanismCountByBioactive[$bioactiveId] = ($mechanismCountByBioactive[$bioactiveId] ?? 0) + 1;
        }

        $count = 0;

        foreach ($foodBioactives as $row) {
            $bioactiveId = $row['bioactive'] ?? null;
            $count += $mechanismCountByBioactive[$bioactiveId] ?? 0;
        }

        return $count;
    }
}
