<?php

declare(strict_types=1);

namespace PWB\Recommendation;

use PWB\Loader\JsonLoader;
use PWB\Recommendation\Models\HealthProfile;

final class PriorityEngine
{
    public function __construct(
        private JsonLoader $loader,
        private string $knowledgePath
    ) {}

    public function rank(HealthProfile $profile): array
    {
        $bodySystemData = $this->loader->load(
            $this->knowledgePath . '/taxonomy/body-systems.json'
        );

        $lookup = [];

foreach ($bodySystemData['bodySystems'] as $system) {
    $lookup[$system['id']] = $system;
}

        $priorities = [];

        foreach ($profile->bodySystems as $id => $score) {

            $priorities[] = [
                'id'    => $id,
                'name'  => $lookup[$id]['name'] ?? $id,
                'score' => (float) $score,
            ];
        }

        usort(
            $priorities,
            fn ($a, $b) => $b['score'] <=> $a['score']
        );

        foreach ($priorities as $index => &$system) {
            $system['rank'] = $index + 1;
        }

        return $priorities;
    }
}