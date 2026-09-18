<?php

declare(strict_types=1);

namespace PWB\Reports\Components;

/**
 * "The depth behind your results" — shown between the masthead and the
 * dashboard. Reuses the same .scope-banner/.scope-stat markup and styling
 * as the questionnaire's welcome page (see tools/questionnaire.php) so the
 * two surfaces feel like one visual language, just with report-appropriate
 * copy. Every value comes from PWB\Knowledge\ScopeStats, computed live
 * from the taxonomy files rather than hand-typed.
 *
 * Replaces the earlier, more compact ScopeStrip — Simon preferred the
 * fuller banner treatment once he saw it live on the welcome page.
 */
final class ScopeBanner
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
        ];

        $cells = '';

        foreach ($items as [$value, $label]) {
            $cells .= '
                <div class="scope-stat">
                    <strong>' . number_format((int) $value) . '</strong>
                    <span>' . htmlspecialchars($label) . '</span>
                </div>';
        }

        $cells .= '
                <div class="scope-stat scope-stat-highlight">
                    <strong>' . number_format($foodPathwaysRounded) . '+</strong>
                    <span>Food pathways traced</span>
                </div>
                <div class="scope-stat scope-stat-you">
                    <strong>1</strong>
                    <span>You</span>
                </div>';

        return '
        <div class="scope-banner">
            <div class="scope-banner-label">The depth behind your results</div>
            <div class="scope-banner-grid">' . $cells . '
            </div>
        </div>';
    }
}
