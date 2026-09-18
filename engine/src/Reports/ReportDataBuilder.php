<?php

declare(strict_types=1);

namespace PWB\Reports;

use PWB\Loader\JsonLoader;
use PWB\Model\Repository;
use PWB\Scanner\RepositoryScanner;
use PWB\Utils\FileLocator;
use PWB\Assessment\HealthProfileBuilder;
use PWB\Recommendation\Scorers\PriorityScorer;
use PWB\Recommendation\MechanismResolver;
use PWB\Recommendation\BioactiveResolver;
use PWB\Recommendation\FoodResolver;
use PWB\Recommendation\ProductResolver;
use PWB\Recommendation\NutrientResolver;
use PWB\Assessment\Assessment;
use PWB\Recommendation\RecommendationConfiguration;
use PWB\Loader\MonographParser;

class ReportDataBuilder
{
    private Repository $repository;
    private JsonLoader $loader;
    private FileLocator $locator;

    private array $tags = [];
    private array $foodGroups = [];
    private array $bodySystems = [];
    private array $mechanismLookup = [];
    private array $bioactiveLookup = [];
    private array $foodLookup = [];
    private array $monographLookup = [];
    private array $outcomeLookup = [];
    private array $dietaryPreferenceLabels = [];
    private array $allergyLabels = [];
    private array $foodBioactiveLookup = [];

    public function __construct()
    {
        $this->locator = new FileLocator();

        $scanner = new RepositoryScanner($this->locator);

        $this->repository = $scanner->scan();

        $this->loader = new JsonLoader();


        $this->tags = $this->loadLookup(
            'tags.json',
            'tags'
        );

        $this->foodGroups = $this->loadLookup(
            'food-groups.json',
            'foodGroups'
        );
        
        $this->bodySystems = $this->loadLookup(
  	  	'body-systems.json',
    		'bodySystems');

        // Full outcome records (not just id => name) so the dashboard can
        // work out which stated wellness goal (PF014) lines up with which
        // body system, the same way PriorityScorer's goal bonus does.
        $outcomesFile = $this->locator->getTaxonomyDirectory() . DIRECTORY_SEPARATOR . 'outcomes.json';

        if (file_exists($outcomesFile)) {

            $outcomesData = $this->loader->load($outcomesFile);

            foreach ($outcomesData['outcomes'] ?? [] as $outcome) {
                $this->outcomeLookup[$outcome['id']] = $outcome;
            }
        }

        // Full food detail records now live as monograph markdown files
        // (knowledgebase/monographs/FDxxx-*.md) rather than the archived
        // per-food JSON files knowledgebase/foods/*.json used to hold —
        // FoodResolver still scores/matches foods via taxonomy/foods.json
        // and taxonomy/food-mechanisms.json, but the descriptive detail
        // (description, active compounds, tags, food group, etc.) for the
        // report comes from parsing the monograph.
        $parser = new MonographParser();

        foreach ($this->repository->getMonographs() as $monographFile) {

            $food = $parser->parse($monographFile);

            if ($food !== null) {
                $this->monographLookup[$food['id']] = $food;
            }
        }

        // Stored-value → display-label lookups for the masthead, so it can
        // show "Vegetarian" / "Peanuts" rather than raw slugs like
        // "vegetarian" / "peanuts". Both PF020 (dietary preferences) and
        // SAF006 (allergies) are functional exclusions in FoodResolver.
        $profileFieldsFile = $this->locator->getKnowledgebaseDirectory() . '/assessment/profile-fields.json';

        if (file_exists($profileFieldsFile)) {

            $profileFields = $this->loader->load($profileFieldsFile);

            foreach ($profileFields['fields'] ?? [] as $field) {

                if (($field['key'] ?? '') === 'dietary_preferences') {

                    foreach ($field['options'] ?? [] as $option) {
                        $this->dietaryPreferenceLabels[$option['value']] = $option['label'];
                    }
                }
            }
        }

        // Bioactive -> food reverse lookup for "Best food sources", sourced
        // from the complete taxonomy relationship file rather than the
        // monograph's own "Contains" tagging, which — like food group and
        // food description before it — is only sparsely populated there.
        $foodBioactivesFile = $this->locator->getTaxonomyDirectory() . '/food-bioactives.json';

        if (file_exists($foodBioactivesFile)) {

            $foodBioactivesData = $this->loader->load($foodBioactivesFile);

            foreach ($foodBioactivesData['relationships'] ?? [] as $relationship) {
                $this->foodBioactiveLookup[$relationship['bioactive']][] = $relationship['food'];
            }
        }

        $questionsFile = $this->locator->getKnowledgebaseDirectory() . '/assessment/questions.json';

        if (file_exists($questionsFile)) {

            $questionsData = $this->loader->load($questionsFile);
            $questionList  = $questionsData['questions'] ?? $questionsData;

            foreach ($questionList as $question) {

                if (($question['id'] ?? '') === 'SAF006') {

                    $labels = $question['answer_options'] ?? [];
                    $values = $question['stored_values'] ?? [];

                    foreach ($values as $index => $value) {
                        $this->allergyLabels[(string) $value] = $labels[$index] ?? (string) $value;
                    }
                }
            }
        }
    }

    public function build(Assessment $assessment): array
    {

$builder = new HealthProfileBuilder(
    new JsonLoader(),
    $this->locator->getKnowledgebaseDirectory()
);

$profile = $builder->build($assessment);

$priorityScorer = new PriorityScorer(
    new JsonLoader(),
    $this->locator->getKnowledgebaseDirectory()
);
$config = new RecommendationConfiguration();

$priorities = $priorityScorer->score($profile, $config);

$mechanismResolver = new MechanismResolver(
    new JsonLoader(),
    $this->locator->getKnowledgebaseDirectory()
);

$mechanisms = $mechanismResolver->resolve($priorities);
foreach ($mechanisms as $m) {
    $this->mechanismLookup[$m['mechanismId']] = $m;
}

$bioactiveResolver = new BioactiveResolver(
    new JsonLoader(),
    $this->locator->getKnowledgebaseDirectory()
);

$foodResolver = new FoodResolver(
    new JsonLoader(),
    $this->locator->getKnowledgebaseDirectory()
);

$nutrientResolver = new NutrientResolver(
    new JsonLoader(),
    $this->locator->getKnowledgebaseDirectory()
);

$productResolver = new ProductResolver(
    new JsonLoader(),
    $this->locator->getKnowledgebaseDirectory()
);

$bioactives = $bioactiveResolver->resolve($mechanisms);
// Allergies (restrictions) and dietary preferences are now BOTH functional
// exclusions — foods.json tags every food against both vocabularies
// (allergens + dietaryExclusions), so FoodResolver just needs one combined
// list of slugs to check a food's tags against. products.json now carries
// the same two vocabularies, so ProductResolver reuses the identical
// excluded-tags list.
$excludedFoodTags = array_merge($assessment->restrictions ?? [], $assessment->preferences ?? []);
$foodResults = $foodResolver->resolve($mechanisms, $excludedFoodTags);
$productResults = $productResolver->resolve($mechanisms, $excludedFoodTags);

foreach ($productResults as &$product) {
    $product['whySelected'] = $this->buildProductWhySelected($product);
}
unset($product);

$vitamins = $nutrientResolver->resolve($mechanisms, 'vitamin');
$minerals = $nutrientResolver->resolve($mechanisms, 'mineral');

$foods = [];

foreach ($foodResults as $foodResult) {

    $foodId = $foodResult['foodId'];

    $food = $this->monographLookup[$foodId] ?? null;

    if ($food !== null) {

                $food = $this->prepareFood($food);

// Preserve recommendation metadata
$food['clinicalScore'] = $foodResult['reportScore'];
$food['rawScore']      = $foodResult['clinicalScore'];
$food['scoreBreakdown']  = $foodResult['scoreBreakdown'] ?? null;

// taxonomy/foods.json's foodGroup is far more complete than the
// monograph's own (often-missing) "Food group:" line, so it's the
// source used for the food-group icon shown in the report.
$food['foodGroupId'] = $foodResult['foodGroupId'] ?? null;

if (!empty($foodResult['foodGroupName'])) {
    $food['classification']['foodGroup'] = $foodResult['foodGroupName'];
}

$food['sources'] = array_map(
    function ($m) {

        $resolved = $this->mechanismLookup[$m['id']] ?? [];

        return [
            'id'   => $m['id'],
            'mechanismName' => $resolved['mechanismName'] ?? $m['name'],
            'plainEnglish'  => $resolved['plainEnglish'] ?? '',
            'whyItMatters'  => $resolved['whyItMatters'] ?? '',
            'tooltip'       => $resolved['tooltip'] ?? '',
            'contribution'  => $m['contribution'],
        ];
    },
    $foodResult['matchedMechanisms'] ?? []
);

$foods[] = $food;
    }
}

// Normalise food scores (0–100)
//
// $foods can legitimately be empty (e.g. a low-scoring assessment
// where no food/mechanism pairing clears FoodResolver's contribution
// threshold), and every rawScore can legitimately be 0 — max() throws
// on an empty array in PHP 8, and dividing by a zero $maxScore would
// throw a DivisionByZeroError, so both are guarded here.

$maxScore = !empty($foods) ? max(array_column($foods, 'rawScore')) : 0;

foreach ($foods as &$food) {

    $normalised = $maxScore > 0 ? $food['rawScore'] / $maxScore : 0;

    // Create a wider spread (40–100)
    $food['score'] = (int) round(
        40 + (pow($normalised, 2.8) * 60)
    );

    // Recommendation text
    if ($food['score'] >= 92) {
        $food['recommendation'] = 'Highly Recommended';
    } elseif ($food['score'] >= 82) {
        $food['recommendation'] = 'Recommended';
    } elseif ($food['score'] >= 70) {
        $food['recommendation'] = 'Good Choice';
    } else {
        $food['recommendation'] = 'Useful Option';
    }
}
unset($food);

// ---------------------------------------------
// Build lookup tables for report enrichment
// ---------------------------------------------

foreach ($bioactives as $b) {
    $this->bioactiveLookup[$b['bioactiveId']] = $b;
}

foreach ($foods as $food) {
    $this->foodLookup[$food['id']] = $food;
}

$bioactives = $this->enrichBioactives($bioactives);

$bodySystemsData = $this->buildBodySystems($foods, $priorities);

$dashboard = $this->buildDashboard(
    $priorities,
    $mechanisms,
    $foods,
    $bodySystemsData,
    $assessment->goals ?? [],
    $bioactives,
    array_merge($vitamins, $minerals),
    $productResults[0] ?? null
);

		return [
		'client' => [
        'firstName' => trim((string) ($assessment->person['first_name'] ?? '')) ?: 'there',
        'lastName'  => trim((string) ($assessment->person['last_name'] ?? '')),
        // Both of these are functional exclusions in FoodResolver now —
        // see taxonomy/foods.json's "allergens"/"dietaryExclusions" fields.
        'dietaryPreferences' => array_map(
            fn($slug) => $this->dietaryPreferenceLabels[$slug] ?? $slug,
            $assessment->preferences ?? []
        ),
        'allergies' => array_map(
            fn($slug) => $this->allergyLabels[$slug] ?? $slug,
            $assessment->restrictions ?? []
        ),
    ],
		'summary'      => [],
'priorities'   => $priorities,
'mechanisms'   => $mechanisms,
'bioactives'   => $bioactives,
'vitamins'     => $vitamins,
'minerals'     => $minerals,
'foods'        => $foods,
'bodySystems' => $bodySystemsData,
'dashboard'   => $dashboard,
'products'    => $productResults,
'topProduct'  => $productResults[0] ?? null,
'theme' => $assessment->theme ?? 'theme-green',
'actionPlan' => $this->buildActionPlan(),
// Live counts of the underlying dataset (questions, foods, body
// systems, etc.) for the report's "scope" strip — see ScopeStats for
// why these are computed rather than hand-typed.
'scope' => \PWB\Knowledge\ScopeStats::compute($this->locator->getKnowledgebaseDirectory()),
];
    }

    private function prepareFood(array $food): array
    {
        if (isset($food['tags'])) {

            $resolved = [];

            foreach ($food['tags'] as $id) {

                $resolved[] =
                    $this->tags[$id]
                    ?? $id;

            }

            $food['tags'] = $resolved;
        }

        if (isset($food['classification']['foodGroup'])) {

            $id = $food['classification']['foodGroup'];

            $food['classification']['foodGroup'] =
                $this->foodGroups[$id]
                ?? $id;
        }

        return $food;
    }
    
private function buildBodySystems(array $foods, array $priorities): array
{
    $priorityLookup = [];

    foreach ($priorities as $priority) {
        $priorityLookup[$priority->id] = $priority->finalScore;
    }

    $systems = [];

    foreach ($foods as $food) {

        $foodName = $food['identity']['name'] ?? 'Unknown';

        // Prefer the body systems this food actually matched for THIS
        // person (computed dynamically by FoodResolver from their
        // mechanism scores) over the food's static monograph tag, which
        // is often missing (e.g. beetroot has no "JSON Mapping" footer)
        // and, even when present, doesn't vary per person. Falling back
        // to the static tag keeps foods visible for any legacy/partial
        // scoreBreakdown data that predates this field.
        $systemIds = $food['scoreBreakdown']['summary']['systemsCoveredIds']
            ?? $food['supports']['bodySystems']
            ?? [];

        foreach ($systemIds as $systemId) {

            if (!isset($systems[$systemId])) {

                $systems[$systemId] = [
                    'id' => $systemId,
                    'name' => $this->bodySystems[$systemId] ?? $systemId,
                    'score' => $priorityLookup[$systemId] ?? 0,
                    'topFoods' => [],
                    'mechanisms' => []
                ];
            }

            $systems[$systemId]['topFoods'][$food['id']] = [
                'id' => $food['id'],
                'name' => $foodName,
                'score' => $food['score'] ?? 0,
                'foodGroupId' => $food['foodGroupId'] ?? null,
            ];

            foreach ($food['sources'] ?? [] as $source) {

               $id = $source['id'] ?? null;

if (!$id) {
    continue;
}

if (!isset($systems[$systemId]['mechanisms'][$id])) {

    $resolved = $this->mechanismLookup[$id] ?? [];

    $systems[$systemId]['mechanisms'][$id] = [
        'name'          => $source['mechanismName'],
        'score'         => 0,
        'evidence'      => $resolved['evidence'] ?? '',
        'plainEnglish'  => $resolved['plainEnglish'] ?? '',
        'tooltip'       => $resolved['tooltip'] ?? '',
        'whyItMatters'  => $resolved['whyItMatters'] ?? '',
    ];
}

$systems[$systemId]['mechanisms'][$id]['score'] += $source['contribution'];

            }
        }
    }

    foreach ($systems as &$system) {

        usort(
            $system['topFoods'],
            fn($a, $b) => $b['score'] <=> $a['score']
        );

        $system['topFoods'] = array_slice($system['topFoods'], 0, 5);

        usort(
            $system['mechanisms'],
            fn($a, $b) => $b['score'] <=> $a['score']
        );

        $system['topMechanisms'] = array_slice($system['mechanisms'], 0, 3);

        unset($system['mechanisms']);

// -------------------------------------------------
// -------------------------------------------------
// The 0–100 Wellness Support Score shown in the report
// -------------------------------------------------
//
// $system['score'] here is a Priority::finalScore, and for body systems
// that's already an absolute 0–100 figure: BodySystemNormalizer (in
// HealthProfileBuilder) divides each system's raw questionnaire score by
// that system's own theoretical maximum (given which questions map to
// it and their score ranges), so it's directly comparable across people
// — a 60 always means the same amount of flagged severity, however many
// or few systems someone's answers happened to touch.
//
// This used to be re-normalised AGAIN here, dividing by max($priorityLookup)
// — i.e. by this one person's own top-scoring system — which forces the
// #1 system to display as 100/100 for literally everyone, including a
// "best possible answers" persona whose real top score is ~17. That's
// the numbers-look-too-high issue: confirmed by running the scoring
// pipeline with every question answered at its healthiest value
// (persona-minimum) and comparing to worst-case (persona-maximum):
// raw top-system scores of ~17 vs ~62 (neutral) vs ~100+ (worst), which
// the old code flattened to 100/100/100. Clamped to 100 because a goal
// bonus (PriorityScorer::PRIMARY_GOAL_BONUS) can push a couple of points
// past it.
$wellnessScore = (int) round(min(100, max(0, $system['score'])));

$system['score'] = $wellnessScore;

// Interpretation shown in the report
if ($wellnessScore >= 85) {
    $system['interpretation'] = 'Strong opportunity for support';
} elseif ($wellnessScore >= 70) {
    $system['interpretation'] = 'Good opportunity for support';
} elseif ($wellnessScore >= 55) {
    $system['interpretation'] = 'Worth paying attention to';
} elseif ($wellnessScore >= 40) {
    $system['interpretation'] = 'Supportive habits may help';
} else {
    $system['interpretation'] = 'Lower current focus';
}

$system['summary'] =
    'Your answers suggest this is one of the areas most likely to benefit from consistent dietary support. The pathways below explain why these foods were prioritised.';
    
    }

    unset($system);

    $systems = array_filter(
        $systems,
        fn($system) => $system['score'] > 0
    );

    usort($systems, fn($a, $b) => $b['score'] <=> $a['score']);

    return array_values($systems);
}

    /**
     * Assembles the top-of-report "at a glance" dashboard: a simple heat
     * grid of body systems, a short bar-style read-out of the strongest
     * mechanisms, the top food matches, a goal-alignment note and a
     * personalised summary paragraph, plus a handful of quick actions.
     * Everything here is derived from data already computed elsewhere in
     * this class (priorities/mechanisms/foods/bodySystems) — nothing new
     * is scored here, it's purely a presentation layer over it.
     */
    private function buildDashboard(
        array $priorities,
        array $mechanisms,
        array $foods,
        array $bodySystemsData,
        array $goalIds,
        array $bioactives,
        array $nutrients,
        ?array $topProduct = null
    ): array {

        // ---- Body system heat grid ----
        // $bodySystemsData is already sorted strongest-first and filtered
        // to score > 0 by buildBodySystems(). Cap the grid at 8 so it
        // stays scannable even for a maximal/severe persona that flags
        // most systems. Every cell keeps the same fixed dark text — only
        // the meter bar length varies with score, so there's no
        // light-text/dark-text switching to reason about.
        $heatMap = array_values(array_map(
            fn($system, $rank) => [
                'id'    => $system['id'] ?? null,
                'name'  => $system['name'],
                'score' => (int) $system['score'],
                'top'   => $rank === 0,
            ],
            array_slice($bodySystemsData, 0, 8),
            array_keys(array_slice($bodySystemsData, 0, 8))
        ));

        // ---- Body system radar (all flagged systems, not just the top 8,
        // so it reads as the fuller picture alongside the heat grid) ----
        $radarSystems = array_map(
            fn($system) => [
                'id'    => $system['id'] ?? null,
                'name'  => $system['name'],
                'score' => (int) $system['score'],
            ],
            $bodySystemsData
        );

        // ---- Stated wellness goals (PF014), tying the dashboard back to
        // the questionnaire's own outcome choices ----
        $goals = [];

        foreach ($goalIds as $goalId) {
            $outcome = $this->outcomeLookup[$goalId] ?? null;

            if ($outcome !== null) {
                $goals[] = [
                    'id'   => $goalId,
                    'name' => $outcome['name'],
                ];
            }
        }

        // ---- Key mechanisms (bar read-out) ----
        $topMechanismsRaw = array_slice($mechanisms, 0, 5);
        $maxMechanismScore = $topMechanismsRaw[0]['clinicalScore'] ?? 1;
        $maxMechanismScore = $maxMechanismScore > 0 ? $maxMechanismScore : 1;

        $topMechanisms = array_map(
            fn($m) => [
                'name'    => $m['mechanismName'] ?? $m['mechanismId'],
                'score'   => $m['clinicalScore'],
                'percent' => (int) round(($m['clinicalScore'] / $maxMechanismScore) * 100),
            ],
            $topMechanismsRaw
        );

        // ---- Key bioactives (same relative bar treatment as mechanisms —
        // clinicalScore here is an open-ended decayed sum, not a
        // calibrated 0-100 figure, so it's shown relative to the person's
        // own top bioactive rather than as a misleading absolute number) ----
        $topBioactivesRaw = array_slice($bioactives, 0, 4);
        $maxBioactiveScore = $topBioactivesRaw[0]['clinicalScore'] ?? 1;
        $maxBioactiveScore = $maxBioactiveScore > 0 ? $maxBioactiveScore : 1;

        $topBioactives = array_map(
            fn($b) => [
                'name'    => $b['name'] ?? $b['bioactiveId'],
                'percent' => (int) round(($b['clinicalScore'] / $maxBioactiveScore) * 100),
            ],
            $topBioactivesRaw
        );

        // ---- Key nutrients (vitamins & minerals, same relative bar
        // treatment as mechanisms/bioactives) — combines both lists and
        // re-ranks by score so the widget shows whichever nutrients this
        // person's own answers point to most strongly, not a fixed split
        // between vitamins and minerals ----
        $nutrientsRanked = $nutrients;
        usort($nutrientsRanked, fn($a, $b) => ($b['clinicalScore'] ?? 0) <=> ($a['clinicalScore'] ?? 0));

        $topNutrientsRaw = array_slice($nutrientsRanked, 0, 4);
        $maxNutrientScore = $topNutrientsRaw[0]['clinicalScore'] ?? 1;
        $maxNutrientScore = $maxNutrientScore > 0 ? $maxNutrientScore : 1;

        $topNutrients = array_map(
            fn($n) => [
                'name'    => $n['name'] ?? $n['nutrientId'],
                'type'    => $n['type'] ?? '',
                'percent' => (int) round(($n['clinicalScore'] / $maxNutrientScore) * 100),
            ],
            $topNutrientsRaw
        );

        // ---- Top food matches ----
        $topFoods = array_map(
            fn($f) => [
                'name'           => $f['identity']['name'] ?? $f['name'] ?? 'Unknown',
                'score'          => $f['score'] ?? 0,
                'recommendation' => $f['recommendation'] ?? '',
                'foodGroupId'    => $f['foodGroupId'] ?? null,
            ],
            array_slice($foods, 0, 5)
        );

        // ---- Quick actions ----
        $actions = array_slice($this->buildActionPlan()['This Week'], 0, 3);

        // ---- Goal alignment ----
        $topSystem = $bodySystemsData[0] ?? null;
        $goalAlignment = null;

        foreach ($goalIds as $goalId) {

            $outcome = $this->outcomeLookup[$goalId] ?? null;

            if ($outcome === null) {
                continue;
            }

            // Prefer a primary match, fall back to secondary, and pick
            // whichever of the person's own flagged systems it hits with
            // the highest score, so the note points at something that's
            // actually prominent in their results rather than merely
            // technically linked.
            $candidateIds = array_merge(
                $outcome['primaryBodySystems'] ?? [],
                $outcome['secondaryBodySystems'] ?? []
            );

            $best = null;

            foreach ($bodySystemsData as $system) {
                if (in_array($system['id'] ?? null, $candidateIds, true)) {
                    if ($best === null || $system['score'] > $best['score']) {
                        $best = $system;
                    }
                }
            }

            if ($best !== null) {
                $goalAlignment = [
                    'goalName'   => $outcome['name'],
                    'systemName' => $best['name'],
                    'score'      => $best['score'],
                ];
                break;
            }
        }

        // ---- Personalised summary paragraph ----
        $topMechanismName = $topMechanisms[0]['name'] ?? null;
        $topFoodName = $topFoods[0]['name'] ?? null;

        $paragraph = 'Your questionnaire highlights ' .
            ($topSystem['name'] ?? 'a few key areas') .
            ' as your strongest current priority';

        $paragraph .= '. ';

        if ($topMechanismName) {
            // Mechanism names are short verb phrases (e.g. "Reduces
            // Oxidative Stress"), written for use as their own clause
            // rather than lower-cased mid-sentence.
            $paragraph .= 'The pathway behind this: ' . $topMechanismName . '. ';
        }

        if ($topFoodName) {
            $paragraph .= $topFoodName . ' is your top-matching food for this profile. ';
        }

        if ($goalAlignment) {
            $paragraph .= 'This also lines up well with your stated goal of ' .
                $goalAlignment['goalName'] . ', which is closely tied to your ' .
                $goalAlignment['systemName'] . ' priority.';
        } else {
            $paragraph .= 'The sections below break down exactly why, and which foods can help.';
        }

        return [
            'paragraph'     => $paragraph,
            'heatMap'       => $heatMap,
            'radarSystems'  => $radarSystems,
            'goals'         => $goals,
            'topMechanisms' => $topMechanisms,
            'topFoods'      => $topFoods,
            'topBioactives' => $topBioactives,
            'topNutrients'  => $topNutrients,
            'actions'       => $actions,
            'goalAlignment' => $goalAlignment,
            'topSystem'     => $topSystem,
            // The supplementation-framing intro shown inside the product
            // strip, above the recommended product — approved copy, static
            // rather than generated, since it must never drift into
            // language that tells someone to replace food with a product.
            // Exposed here (ahead of the strip UI itself being built) so
            // the text and the data it introduces ship from the same
            // place.
            'supplementationIntro' => 'Many people use nutritional supplementation to help close everyday gaps in their diet, particularly where getting enough of a specific nutrient through food alone can be difficult. Supplementation isn\'t a substitute for a varied, balanced diet — it\'s simply one more tool that research suggests can help support the areas your results have highlighted. Based on your answers, here\'s the product our engine identified as the closest match for you:',
            // The single top-scoring product (ProductResolver's own ranked
            // output for this person, already allergy/dietary excluded) —
            // handed straight through so the dashboard's product strip has
            // everything it needs (image, claims, servings, whySelected)
            // without DashboardSection reaching outside the $dashboard array.
            'topProduct' => $topProduct,
        ];
    }

    /**
     * Auto-generates the "why this was selected" explanation for a scored
     * product, built entirely from that product's own matchedMechanisms
     * (ProductResolver's real scoring output for this person) — never
     * hand-written or generic copy. Mirrors the same "explain the score"
     * approach already used for Priority Foods (FoodResolver's
     * scoreBreakdown, surfaced via $food['sources']), so a product
     * recommendation is auditable back to the person's own answers in
     * exactly the same way a food recommendation is.
     */
    private function buildProductWhySelected(array $product): string
    {
        $matched = $product['matchedMechanisms'] ?? [];

        if (empty($matched)) {
            return '';
        }

        // scoreBreakdown.summary.systemsCovered lists EVERY body system any
        // matched mechanism touches, collected in raw iteration order
        // before the strongest-first sort — it does not line up with which
        // 1-2 mechanisms are actually named below. So the systems named
        // here are derived directly from those same top mechanisms' own
        // body-system links (via mechanismLookup, populated from
        // MechanismResolver's per-mechanism "sources"), keeping the
        // sentence internally consistent.
        $topMatched = array_slice($matched, 0, 2);
        $topMechanismNames = array_map(fn($m) => $m['name'], $topMatched);

        $systemNames = [];

        foreach ($topMatched as $m) {
            foreach ($this->mechanismLookup[$m['id']]['sources'] ?? [] as $source) {
                $systemId = $source['bodySystem'] ?? null;
                if ($systemId && isset($this->bodySystems[$systemId])) {
                    $systemNames[$this->bodySystems[$systemId]] = true;
                }
            }
        }

        $systemNames = array_slice(array_keys($systemNames), 0, 2);

        $sentence = 'Recommended based on your own results';

        if (!empty($systemNames)) {
            $sentence .= ' — it targets ' . $this->joinWithAnd($systemNames) .
                ', flagged as ' . (count($systemNames) > 1 ? 'priority areas' : 'a priority area') . ' in your results';
        }

        $sentence .= '. ';

        if (!empty($topMechanismNames)) {
            $sentence .= 'Its strongest matched pathway' . (count($topMechanismNames) > 1 ? 's are ' : ' is ') .
                $this->joinWithAnd($topMechanismNames) . ', identified from your questionnaire answers.';
        }

        return trim($sentence);
    }

    private function joinWithAnd(array $items): string
    {
        $items = array_values($items);

        if (count($items) <= 1) {
            return $items[0] ?? '';
        }

        $last = array_pop($items);

        return implode(', ', $items) . ' and ' . $last;
    }

    /**
     * The generic (not yet personalised) action-plan content, factored
     * out so both the dashboard's "quick actions" widget and the full
     * Action Plan section can draw on the same list without drifting out
     * of sync.
     */
    private function buildActionPlan(): array
    {
        return [

            'This Week' => [

                'Add at least one serving of oats to your breakfast.',
                'Aim to eat one additional wholegrain food each day.',
                'Include at least five different plant foods this week.'

            ],

            'Over the Next Month' => [

                'Gradually increase your fibre intake.',
                'Drink plenty of water as fibre intake increases.',
                'Introduce a wider variety of minimally processed foods.'

            ],

            'Long-Term Goals' => [

                'Build meals around whole foods.',
                'Maintain a diverse range of plant-based foods.',
                'Review your progress every four weeks.'

            ]
        ];
    }

private function enrichBioactives(array $bioactives): array
{
    foreach ($bioactives as &$bioactive) {

        $id = $bioactive['bioactiveId'];

        // description/plainEnglish/primaryAction/evidence already come
        // straight from BioactiveResolver (which reads the full taxonomy
        // record) — nothing to enrich here, just guard the empty case.
        $bioactive['description'] = $bioactive['description'] ?? '';

$bioactive['mechanisms'] = [];

foreach (array_slice($bioactive['matchedMechanisms'] ?? [], 0, 3) as $m) {

    $mechanismId = $m['mechanismId'] ?? $m['id'] ?? null;

    if (!$mechanismId || !isset($this->mechanismLookup[$mechanismId])) {
        continue;
    }

    $resolved = $this->mechanismLookup[$mechanismId];

    $bioactive['mechanisms'][] = [
        'id'            => $mechanismId,
        'name'          => $resolved['mechanismName'],
        'plainEnglish'  => $resolved['plainEnglish'] ?? '',
        'whyItMatters'  => $resolved['whyItMatters'] ?? '',
        'tooltip'       => $resolved['tooltip'] ?? '',
        'evidence'      => $resolved['evidence'] ?? '',
    ];
}
        // Best food sources — via the taxonomy reverse lookup built in the
        // constructor, cross-referenced against THIS person's own scored
        // food list (not a generic ranking), so a food only shows here if
        // it's both a real source of this bioactive AND actually relevant
        // to them.
        $foods = [];

        foreach ($this->foodBioactiveLookup[$id] ?? [] as $foodId) {

            if (!isset($this->foodLookup[$foodId])) {
                continue;
            }

            $food = $this->foodLookup[$foodId];

            $foods[] = [
                'name'        => $food['identity']['name'] ?? $foodId,
                'score'       => $food['clinicalScore'] ?? 0,
                'foodGroupId' => $food['foodGroupId'] ?? null,
            ];
        }

        usort($foods, fn($a, $b) => $b['score'] <=> $a['score']);

        $bioactive['foods'] = array_slice($foods, 0, 5);
    }

    return $bioactives;
}

    private function loadLookup(
        string $filename,
        string $arrayName
    ): array {

        $lookup = [];

        $file =
            $this->locator->getTaxonomyDirectory()
            . DIRECTORY_SEPARATOR
            . $filename;

        if (!file_exists($file)) {
            return $lookup;
        }

        $json = $this->loader->load($file);

        if (
            !isset($json[$arrayName]) ||
            !is_array($json[$arrayName])
        ) {
            return $lookup;
        }

        foreach ($json[$arrayName] as $item) {

            if (
                isset($item['id']) &&
                isset($item['name'])
            ) {

                $lookup[$item['id']] = $item['name'];

            }

        }

        return $lookup;
    }
}