<?php

declare(strict_types=1);

namespace PWB\Reports\Components;

/**
 * Small, reusable "What does this mean?" expandable explainers — plain
 * <details>/<summary> so they work with no JS, styled as an inline text
 * link rather than the bigger pill-style toggle used by .expand-more.
 *
 * Each method is a single, hand-written source of truth for one piece of
 * explanatory copy, shared between the questionnaire (QuestionRenderer,
 * next to where a choice is made) and the report (MastheadSection, next to
 * where its effect is visible) so the wording never drifts between the two
 * places it appears.
 */
final class ExplainerPopover
{
    /**
     * Explains how declared allergies (SAF006) and dietary preferences
     * (PF020) remove foods from recommendations, plus the reasoning/
     * caveats behind the less obvious ones (low-FODMAP's serving-size
     * simplification, halal/kosher currently being no-ops against this
     * food catalogue). Keep this in sync with FoodResolver's actual
     * behaviour and foods.json's "allergens"/"dietaryExclusions" tagging —
     * it's describing real logic, not aspirational copy.
     */
    public static function foodExclusions(string $groupName = 'food-exclusions'): string
    {
        $paragraphs = [
            'We remove any food from your recommendations that conflicts with an allergy or dietary preference you told us about — so a peanut allergy means peanuts (and anything made from them) never appear here, even if they would otherwise be a strong match for your results.',
            '<strong>Vegetarian, vegan, pescatarian and no red meat</strong> are based on each food&rsquo;s actual animal content &mdash; vegan also removes dairy and eggs, pescatarian keeps fish but not meat or poultry.',
            '<strong>Gluten-free and dairy-free</strong> remove any food already flagged as containing gluten or milk &mdash; the same check used for allergies.',
            '<strong>Low-FODMAP</strong> is more of a judgement call than the others. How much FODMAP a food contains genuinely depends on portion size, which we don&rsquo;t model yet, so we&rsquo;ve erred on the side of caution: a food is excluded if it&rsquo;s commonly a trigger at a typical serving, even where a smaller amount might be fine. Treat this as a helpful starting point rather than a clinically supervised low-FODMAP plan.',
            '<strong>Halal and kosher</strong> don&rsquo;t currently change your results &mdash; none of the foods in our database conflict with either (there&rsquo;s no pork, shellfish or alcohol-based ingredient yet). We&rsquo;ll expand this as more foods are added.',
        ];

        $body = '';

        foreach ($paragraphs as $paragraph) {
            // Content is hand-authored above, not user/request data, so the
            // inline <strong>/&mdash; markup is intentional and safe as-is.
            $body .= '<p>' . $paragraph . '</p>';
        }

        return '
        <details class="explainer-popover" name="' . htmlspecialchars($groupName) . '">
            <summary class="explainer-toggle">What does this mean? <span class="expand-chevron">&#9662;</span></summary>
            <div class="explainer-body">
                <h5>How we choose your foods</h5>
                ' . $body . '
            </div>
        </details>';
    }
}
