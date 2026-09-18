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
// mechanism-content.json (the source of tooltip/whyItMatters) is
// still mostly unpopulated test data — only a couple of the 100+
// mechanisms have dedicated entries. Rather than show blank hover
// text for everything else, fall back to the mechanism's own
// description, which mechanisms.json always populates with real
// clinical copy. Once mechanism-content.json is filled in properly
// these fallbacks become no-ops.
'tooltip'      => $mechanismLookup[$id]['tooltip'] ?? $mechanismLookup[$id]['description'] ?? '',
'whyItMatters' => $mechanismLookup[$id]['whyItMatters'] ?? $mechanismLookup[$id]['description'] ?? '',
    'sources'       => []
];
        }

        // Take the strongest single contributing body system rather than
        // summing across every system a mechanism happens to be linked
        // to. Most mechanisms belong to only one body system, but a
        // handful (the "gut" cluster shared by Digestive+Intestinal, and
        // the "oxidative stress" cluster shared by Immune+Respiratory)
        // belong to two — summing gave those an unearned ~2x head start
        // over every other mechanism, regardless of whether either of
        // those two systems was actually the person's real priority.
        // Taking the max means a mechanism's importance reflects its
        // single most relevant body system, not how many systems the
        // taxonomy structure happens to attach it to. All contributing
        // systems are still recorded in `sources` below for transparency.
        $resolved[$id]['clinicalScore'] = max(
            $resolved[$id]['clinicalScore'],
            $clinicalContribution
        );

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