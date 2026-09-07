<?php

declare(strict_types=1);

namespace PWB\Recommendation;

use PWB\Loader\JsonLoader;

final class MechanismResolver
{
    public function __construct(
        private JsonLoader $loader,
        private string $knowledgePath
    ) {}

public function resolve(array $priorities): array
{
    $taxonomy = $this->loader->load(
        $this->knowledgePath . '/taxonomy/body-systems-mechanisms.json'
    );

    $mechanisms = $this->loader->load(
        $this->knowledgePath . '/taxonomy/mechanisms.json'
    );

    // Build body system lookup
    $bodySystems = [];

    foreach ($taxonomy['bodySystems'] as $system) {
        $bodySystems[$system['id']] = $system;
    }

    // Build mechanism lookup
    $mechanismLookup = [];

    foreach ($mechanisms['mechanisms'] as $mechanism) {
        $mechanismLookup[$mechanism['id']] = $mechanism;
    }

    $resolved = [];

    foreach ($priorities as $priority) {

        $bsId = $priority['id'];

        if (!isset($bodySystems[$bsId])) {
            continue;
        }

        foreach ($bodySystems[$bsId]['mechanisms'] as $link) {

            $weight = isset($link['weight'])
                ? ((float) $link['weight']) / 100
                : 0.0;

            $meta = $mechanismLookup[$link['id']] ?? [];

            $resolved[] = [
                'bodySystem'      => $bsId,
                'bodySystemName'  => $bodySystems[$bsId]['name'],

                'mechanismId'     => $link['id'],
                'mechanismName'   => $meta['name'] ?? $link['id'],
                'category'        => $meta['category'] ?? null,
                'description'     => $meta['description'] ?? null,

                'priority'        => $link['priority'],
                'weight'          => $weight,
                'evidence'        => $link['evidence'],

                'clinicalScore'   => round($priority['score'] * $weight, 2),
            ];
        }
    }

    usort(
        $resolved,
        fn($a, $b) => $b['clinicalScore'] <=> $a['clinicalScore']
    );

    return $resolved;
}

}