<?php

declare(strict_types=1);

namespace PWB\Recommendation;

use PWB\Loader\JsonLoader;

final class VitaminResolver
{
    private array $lookup = [];

    public function __construct(
        private JsonLoader $loader,
        string $knowledgebasePath
    ) {
        $vitamins = $loader->load($knowledgebasePath . '/taxonomy/vitamins.json');
        $matrix   = $loader->load($knowledgebasePath . '/taxonomy/vitamin-mechanisms.json');

        foreach ($vitamins['vitamins'] as $v) {
            $this->lookup[$v['id']] = [
                'id'   => $v['id'],
                'name' => $v['name']
            ];
        }

        $this->matrix = $matrix;
    }

    private array $matrix;

    public function resolve(array $mechanisms): array
    {
        $scores = [];

        foreach ($mechanisms as $mechanism) {

            foreach ($this->matrix as $row) {

                if ($row['mechanism_id'] !== $mechanism['mechanismId']) {
                    continue;
                }

                $id = $row['vitamin_id'];

                if (!isset($scores[$id])) {
                    $scores[$id] = [
                        'vitaminId'    => $id,
                        'name'         => $this->lookup[$id]['name'],
                        'clinicalScore'=> 0
                    ];
                }

                $scores[$id]['clinicalScore'] +=
                    $mechanism['clinicalScore'] * ($row['strength'] / 100);
            }
        }

        foreach ($scores as &$v) {
            $v['clinicalScore'] = round($v['clinicalScore'], 2);
        }

        usort($scores, fn($a,$b) =>
            $b['clinicalScore'] <=> $a['clinicalScore']
        );

        return $scores;
    }
}