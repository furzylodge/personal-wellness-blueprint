<?php

declare(strict_types=1);

namespace PWB\Reports\Sections;

/**
 * The report's opening header: replaces the old "Personal Wellness
 * Blueprint" <h1> + "Client: X" line + an always-empty "Summary" heading
 * with a single, tight, personalised masthead — a small brand kicker, a
 * greeting by first name, a one-line orientation sentence, and (when
 * present) a compact row of dietary-preference/allergy tags.
 *
 * Deliberately doesn't repeat the person's wellness goals here — those
 * already have their own dedicated card in the dashboard immediately
 * below, and showing them twice would recreate the "too many competing
 * things at the top" problem this replaces.
 *
 * Dietary preferences (PF020) are informational only — they're shown here
 * for context but don't change which foods get recommended (see
 * FoodResolver). Allergies (SAF006) DO change recommendations — FoodResolver
 * hard-excludes any food tagged with a declared allergen — so they're
 * styled distinctly here as a safety-relevant fact, not just a preference.
 */
class MastheadSection
{
    public function render(array $reportData): string
    {
        $firstName = htmlspecialchars($reportData['client']['firstName'] ?? 'there');
        $preferences = $reportData['client']['dietaryPreferences'] ?? [];
        $allergies   = $reportData['client']['allergies'] ?? [];

        $tags = '';

        foreach ($preferences as $preference) {
            $tags .= '<span class="masthead-tag">' . htmlspecialchars($preference) . '</span>';
        }

        foreach ($allergies as $allergy) {
            $tags .= '<span class="masthead-tag masthead-tag-allergy">' . htmlspecialchars($allergy) . ' allergy</span>';
        }

        $tagsHtml = $tags !== ''
            ? '<div class="masthead-tags">' . $tags . '</div>'
            : '';

        return '
        <header class="report-masthead">
            <div class="masthead-kicker">Personal Wellness Blueprint</div>
            <h1 class="masthead-greeting">Hi ' . $firstName . '</h1>
            <p class="masthead-subtitle">Here\'s a personalised look at what your answers reveal, and where to focus first.</p>' . $tagsHtml . '
        </header>';
    }
}
