<?php

declare(strict_types=1);

namespace PWB\Reports;

use PWB\Loader\JsonLoader;
use PWB\Model\Repository;
use PWB\Recommendations\RecommendationEngine;
use PWB\Scanner\RepositoryScanner;
use PWB\Utils\FileLocator;
use PWB\Assessment\HealthProfileBuilder;
use PWB\Recommendation\Scorers\PriorityScorer;
use PWB\Recommendation\MechanismResolver;
use PWB\Recommendation\BioactiveResolver;

class ReportDataBuilder
{
    private Repository $repository;
    private JsonLoader $loader;
    private RecommendationEngine $recommendationEngine;
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

        $this->recommendationEngine = new RecommendationEngine();

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

    public function build(array $assessment): array
    {

$builder = new HealthProfileBuilder(
    new JsonLoader(),
    $this->locator->getKnowledgebaseDirectory()
);

$profile = $builder->build($assessment);

$priorityScorer = new PriorityScorer();
$priorities = $priorityScorer->score($profile);

$mechanismResolver = new MechanismResolver(
    new JsonLoader(),
    $this->locator->getKnowledgebaseDirectory()
);

$mechanisms = $mechanismResolver->resolve($priorities);

$bioactiveResolver = new BioactiveResolver(
    new JsonLoader(),
    $this->locator->getKnowledgebaseDirectory()
);

$bioactives = $bioactiveResolver->resolve($mechanisms);

$foods = [];

        foreach ($recommendations['foods'] as $foodId) {

            foreach ($this->repository->getFoods() as $foodFile) {

                $food = $this->loader->load($foodFile);

                if (($food['id'] ?? '') !== $foodId) {
                    continue;
                }

                $food = $this->prepareFood($food);

                $foods[] = $food;

                break;
            }

        }
		return [
		    'client' => [
		    'name' => 'Test User'],
		    'summary' => $recommendations['summary'],
		    'priorities' => $priorities,
'mechanisms' => $mechanisms,
'bioactives' => $bioactives,
		    'foods' => $foods,
		    'bodySystems' => $this->buildBodySystems($foods),
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
    
    private function buildBodySystems(array $foods): array
	{
    $bodySystems = [];

    foreach ($foods as $food) {

        $foodName =
            $food['identity']['name']
            ?? 'Unknown';

        $systems =
            $food['supports']['bodySystems']
            ?? [];

        foreach ($systems as $systemId) {

            $systemName =
                $this->bodySystems[$systemId]
                ?? $systemId;

            if (!isset($bodySystems[$systemName])) {
                $bodySystems[$systemName] = [];
            }

            $bodySystems[$systemName][] = [
                'id' => $food['id'],
                'name' => $foodName
            ];

        }

    }

    ksort($bodySystems);

    return $bodySystems;
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