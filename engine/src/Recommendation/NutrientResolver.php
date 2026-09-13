<?php

declare(strict_types=1);

namespace PWB\Recommendation;

use PWB\Loader\JsonLoader;

final class NutrientResolver
{
    private array $lookup = [];
    private array $relationships = [];

    public function __construct(
        private JsonLoader $loader,
        string $knowledgebasePath
    ) {
        $vitamins = $loader->load($knowledgebasePath . '/taxonomy/vitamins.json');
        $minerals = $loader->load($knowledgebasePath . '/taxonomy/minerals.json');
        $taxonomy = $loader->load($knowledgebasePath . '/taxonomy/nutrient-mechanisms.json');

        foreach ($vitamins['vitamins'] as $v) {
            $this->lookup[$v['id']] = [
                'id'   => $v['id'],
                'name' => $v['name'],
                'type' => 'vitamin'
            ];
        }

        foreach ($minerals['minerals'] as $m) {
            $this->lookup[$m['id']] = [
                'id'   => $m['id'],
                'name' => $m['name'],
                'type' => 'mineral'
            ];
        }

        $this->relationships = $taxonomy['relationships'];
    }

    public function resolve(array $mechanisms, string $type = 'all'): array
    {
        $scores = [];

        foreach ($mechanisms as $mechanism) {

            foreach ($this->relationships as $row) {

                if ($row['mechanismId'] !== $mechanism['mechanismId']) {
                    continue;
                }

                $id = $row['nutrientId'];

                if (!isset($this->lookup[$id])) {
                    continue;
                }

                if ($type !== 'all' && $this->lookup[$id]['type'] !== $type) {
                    continue;
                }

                if (!isset($scores[$id])) {
                    $scores[$id] = [
                        'nutrientId'    => $id,
                        'name'          => $this->lookup[$id]['name'],
                        'type'          => $this->lookup[$id]['type'],
                        'clinicalScore' => 0,
                        'sources'       => []
                    ];
                }

                $contribution =
                    $mechanism['clinicalScore'] * ($row['strength'] / 5);

                $scores[$id]['clinicalScore'] += $contribution;

                $scores[$id]['sources'][] = [
                    'mechanismId'   => $mechanism['mechanismId'],
                    'mechanismName' => $mechanism['mechanismName'],
                    'strength'      => $row['strength'],
                    'contribution'  => round($contribution, 2)
                ];
            }
        }

        foreach ($scores as &$row) {
            $row['clinicalScore'] = round($row['clinicalScore'], 2);
        }

        usort($scores, fn($a, $b) =>
            $b['clinicalScore'] <=> $a['clinicalScore']
        );

        return $scores;
    }
}