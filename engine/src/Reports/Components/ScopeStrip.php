<?php

declare(strict_types=1);

namespace PWB\Reports\Components;

/**
 * The compact "depth behind your results" strip shown between the
 * masthead and the dashboard — a low-profile row of live-computed stats
 * (see PWB\Knowledge\ScopeStats) rather than the fuller banner card used on the
 * questionnaire's welcome page, so it doesn't compete with the Wellness
 * Snapshot hero immediately below it.
 */
final class ScopeStrip
{
    public static function render(array $scope): string
    {
        if (empty($scope)) {
            return '';
        }

        $foodPathwaysRounded = (int) floor(($scope['foodPathways'] ?? 0) / 100) * 100;

        $items = [
            [$scope['questions'] ?? 0, 'Questions asked'],
            [$scope['foods'] ?? 0, 'Foods analysed'],
            [$scope['bodySystems'] ?? 0, 'Body systems mapped'],
            [$scope['mechanisms'] ?? 0, 'Pathways modelled'],
            [$scope['bioactives'] ?? 0, 'Bioactives tracked'],
            [$scope['nutrients'] ?? 0, 'Nutrients scored'],
            [number_format($foodPathwaysRounded) . '+', 'Food pathways traced'],
            ['1', 'You'],
        ];

        $cells = '';
        $count = count($items);

        foreach ($items as $i => [$value, $label]) {

            $isYou = $label === 'You';
            $class = $isYou ? 'scope-chip scope-chip-you' : 'scope-chip';
            $displayValue = is_int($value) ? number_format($value) : $value;

            $cells .= '
                <div class="' . $class . '">
                    <strong>' . htmlspecialchars((string) $displayValue) . '</strong>
                    <span>' . htmlspecialchars($label) . '</span>
                </div>';

            if ($i < $count - 1) {
                $cells .= '<div class="scope-chip-divider">&times;</div>';
            }
        }

        return '
        <div class="scope-strip">
            <div class="scope-strip-row">' . $cells . '</div>
            <div class="scope-strip-caption">Every answer you gave was checked against all of this to build the results below.</div>
        </div>';
    }
}
