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
use PWB\Recommendation\NutrientResolver;
use PWB\Assessment\Assessment;
use PWB\Recommendation\RecommendationConfiguration;

class ReportDataBuilder
{
    private Repository $repository;
    private JsonLoader $loader;
    private FileLocator $locator;

    private array $tags = [];
    private array $foodGroups = [];
    private array $bodySystems = [];

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
    }

    public function build(Assessment $assessment): array
    {

$builder = new HealthProfileBuilder(
    new JsonLoader(),
    $this->locator->getKnowledgebaseDirectory()
);

$profile = $builder->build($assessment);

$priorityScorer = new PriorityScorer();
$config = new RecommendationConfiguration();

$priorities = $priorityScorer->score($profile, $config);

$mechanismResolver = new MechanismResolver(
    new JsonLoader(),
    $this->locator->getKnowledgebaseDirectory()
);

$mechanisms = $mechanismResolver->resolve($priorities);

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

$bioactives = $bioactiveResolver->resolve($mechanisms);
$foodResults = $foodResolver->resolve($mechanisms);

$vitamins = $nutrientResolver->resolve($mechanisms, 'vitamin');
$minerals = $nutrientResolver->resolve($mechanisms, 'mineral');

$foods = [];

foreach ($foodResults as $foodResult) {

    $foodId = $foodResult['foodId'];

            foreach ($this->repository->getFoods() as $foodFile) {

                $food = $this->loader->load($foodFile);

                if (($food['id'] ?? '') !== $foodId) {
                    continue;
                }

                $food = $this->prepareFood($food);

// Preserve recommendation metadata
$food['clinicalScore'] = $foodResult['clinicalScore'];

$food['sources'] = array_map(
    fn($m) => [
        'mechanismId'   => $m['id'],
        'mechanismName' => $m['name'],
        'contribution'  => $m['contribution'],
    ],
    $foodResult['matchedMechanisms'] ?? []
);

$foods[] = $food;

                break;
            }

        }
		return [
		'client' => [
        'name' => 'Test User'
    ],
		'summary'      => [],
'priorities'   => $priorities,
'mechanisms'   => $mechanisms,
'bioactives'   => $bioactives,
'vitamins'     => $vitamins,
'minerals'     => $minerals,
'foods'        => $foods,
'bodySystems' => $this->buildBodySystems($foods, $priorities),
'theme' => $assessment->theme ?? 'theme-green',
'actionPlan' => [

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
    ]
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

        foreach ($food['supports']['bodySystems'] ?? [] as $systemId) {

            if (!isset($systems[$systemId])) {

                $systems[$systemId] = [
                    'name' => $this->bodySystems[$systemId] ?? $systemId,
                    'score' => $priorityLookup[$systemId] ?? 0,
                    'topFoods' => [],
                    'mechanisms' => []
                ];
            }

            $systems[$systemId]['topFoods'][$food['id']] = [
                'id' => $food['id'],
                'name' => $foodName,
                'score' => $food['clinicalScore'] ?? 0
            ];

            foreach ($food['sources'] ?? [] as $source) {

                $id = $source['mechanismId'];

                if (!isset($systems[$systemId]['mechanisms'][$id])) {

                    $systems[$systemId]['mechanisms'][$id] = [
                        'name' => $source['mechanismName'],
                        'score' => 0
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
// Convert internal priority score to a 0–100
// Wellness Support Score for reporting only
// -------------------------------------------------

$rawScore = $system['score'];

$maxScore = !empty($priorityLookup)
    ? max($priorityLookup)
    : 1;

$wellnessScore = (int) round(($rawScore / $maxScore) * 100);

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