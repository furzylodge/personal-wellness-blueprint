<?php

declare(strict_types=1);

namespace PWB\Reports\Sections;

use PWB\Reports\Components\ExplainerPopover;
use PWB\Reports\Components\ScopeBanner;

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
 * Both dietary preferences (PF020) and allergies (SAF006) are functional —
 * FoodResolver hard-excludes any food tagged against either (see
 * taxonomy/foods.json's "allergens" and "dietaryExclusions" fields) — but
 * allergies stay visually distinct (red) here because they're a safety
 * fact, not a lifestyle choice: getting one wrong has very different
 * consequences than getting a preference wrong.
 *
 * "The Depth Behind Your Results" (ScopeBanner) now renders inline in the
 * spare space to the right of the greeting, rather than as its own
 * full-width block below — the greeting text rarely fills the card's
 * width, and stacking the two pushed the actual Wellness Snapshot further
 * down the page than it needed to be. ScopeBanner's own markup/CSS is
 * untouched (the questionnaire welcome page still uses it full-width) —
 * this just nests it and overrides sizing via `.report-masthead
 * .scope-banner` in CSS, scoped to this context only.
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

        // The "what does this mean?" link only earns its place when there's
        // actually something to explain — no tags means nothing was
        // excluded on this person's report.
        $tagsHtml = $tags !== ''
            ? '<div class="masthead-tags">' . $tags . ExplainerPopover::foodExclusions('masthead') . '</div>'
            : '';

        $scopeHtml = ScopeBanner::render($reportData['scope'] ?? []);

        // Falls back to the plain single-column header when there's no
        // scope data to show (an edge case — ScopeStats failed, or an
        // empty knowledgebase) rather than leaving a lopsided empty column.
        if ($scopeHtml === '') {
            return '
        <header class="report-masthead">
            <div class="masthead-kicker">Personal Wellness Blueprint</div>
            <h1 class="masthead-greeting">Hi ' . $firstName . '</h1>
            <p class="masthead-subtitle">Here\'s a personalised look at what your answers reveal, and where to focus first.</p>' . $tagsHtml . '
        </header>';
        }

        return '
        <header class="report-masthead">
            <div class="masthead-row">
                <div class="masthead-main">
                    <div class="masthead-kicker">Personal Wellness Blueprint</div>
                    <h1 class="masthead-greeting">Hi ' . $firstName . '</h1>
                    <p class="masthead-subtitle">Here\'s a personalised look at what your answers reveal, and where to focus first.</p>' . $tagsHtml . '
                </div>
                <div class="masthead-scope">' . $scopeHtml . '</div>
            </div>
        </header>';
    }
}
