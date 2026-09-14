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
    
    // Optional educational content
$contentPath = $this->knowledgePath . '/taxonomy/mechanism-content.json';
$mechanismContent = [];

if (file_exists($contentPath)) {
    $content = $this->loader->load($contentPath);
    $mechanismContent = $content['mechanisms'] ?? [];
}

    // Build body system lookup
    $bodySystems = [];

    foreach ($taxonomy['bodySystems'] as $system) {
        $bodySystems[$system['id']] = $system;
    }

    // Build mechanism lookup and merge educational content
$mechanismLookup = [];

foreach ($mechanisms['mechanisms'] as $mechanism) {

    $id = $mechanism['id'];

    if (isset($mechanismContent[$id])) {
        $mechanism = array_merge(
            $mechanism,
            $mechanismContent[$id]
        );
    }

    $mechanismLookup[$id] = $mechanism;
}

$resolved = [];

foreach ($priorities as $priority) {

    $bodySystemId = $priority->id;

    if (!isset($bodySystems[$bodySystemId])) {
        continue;
    }

    foreach ($bodySystems[$bodySystemId]['mechanisms'] as $mechanism) {

        $id = $mechanism['id'];

        $clinicalContribution =
    $priority->score * (($mechanism['weight'] ?? 100) / 100);

        if (!isset($resolved[$id])) {

            $resolved[$id] = [
    'mechanismId'   => $id,
    'mechanismName' => $mechanismLookup[$id]['name'] ?? $id,
    'clinicalScore' => 0,
    'evidence'      => $mechanismLookup[$id]['evidence'] ?? '',
    'plainEnglish' => $mechanismLookup[$id]['plainEnglish'] ?? '',
'description'  => $mechanismLookup[$id]['description'] ?? '',
'tooltip'      => $mechanismLookup[$id]['tooltip'] ?? '',
'whyItMatters' => $mechanismLookup[$id]['whyItMatters'] ?? '',
    'sources'       => []
];
        }

        // Add the contribution from this body system
        $resolved[$id]['clinicalScore'] += $clinicalContribution;

        $resolved[$id]['sources'][] = [
            'bodySystem' => $bodySystemId,
            'contribution' => round($clinicalContribution, 2),
            'weight' => $mechanism['weight'] ?? 100
        ];
    }
}

// Convert to indexed array
$resolved = array_values($resolved);

// Round scores
foreach ($resolved as &$row) {
    $row['clinicalScore'] = round($row['clinicalScore'], 2);
}

// Rank highest first
usort(
    $resolved,
    fn($a, $b) => $b['clinicalScore'] <=> $a['clinicalScore']
);



return $resolved;

}

}