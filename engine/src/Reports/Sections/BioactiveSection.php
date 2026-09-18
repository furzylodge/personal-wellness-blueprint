<?php

declare(strict_types=1);

namespace PWB\Reports\Sections;
use PWB\Reports\Components\MechanismPopover;

class BioactiveSection
{
    public function render(array $reportData): string
    {
        $bioactives = array_slice(
            $reportData['bioactives'] ?? [],
            0,
            8
        );

        if (empty($bioactives)) {
            return '';
        }

        $html = '
        <section class="report-section">

            <h2>Priority Bioactives</h2>

            <p class="section-intro">
                These naturally occurring plant compounds have been prioritised because they support several of your highest-scoring nutritional pathways.
            </p>

            <div class="bioactive-grid">';

        $cards = [];

        foreach ($bioactives as $item) {
            $cards[] = $this->renderBioactiveCard($item);
        }

        // Top 3 stay visible; the rest sit behind an expand/collapse —
        // matches the same treatment given to Priority Foods and Your
        // Wellness Priorities, so no section forces a long scroll before
        // the next one.
        $visible = array_slice($cards, 0, 3);
        $hidden  = array_slice($cards, 3);

        $html .= implode('', $visible);

        $html .= '
            </div>';

        if (!empty($hidden)) {

            $count = count($hidden);
            $plural = $count === 1 ? 'bioactive' : 'bioactives';

            $html .= '
            <details class="expand-more">
                <summary class="expand-toggle">
                    <span class="expand-label-closed">Show ' . $count . ' more ' . $plural . '</span>
                    <span class="expand-label-open">Show fewer bioactives</span>
                    <span class="expand-chevron">&#9662;</span>
                </summary>
                <div class="expand-content bioactive-grid">' . implode('', $hidden) . '</div>
            </details>';
        }

        $html .= '
        </section>';

        return $html;
    }

    private function renderBioactiveCard(array $item): string
    {
        $name = htmlspecialchars($item['name'] ?? 'Unknown');

        // reportScore is relative to this person's own top-scoring
        // bioactive (0-100) — clinicalScore itself is an open-ended decayed
        // sum that can run into the hundreds, so showing it as "X/100"
        // was misleading (e.g. "538/100").
        $score = (int) ($item['reportScore'] ?? 0);

        $summary = htmlspecialchars(
            $item['plainEnglish']
            ?: ($item['description'] ?? '')
            ?: 'A beneficial plant compound that contributes to healthy nutritional pathways.'
        );

        $category = htmlspecialchars($item['category'] ?? '');
        $subcategory = htmlspecialchars($item['subcategory'] ?? '');
        $evidence = htmlspecialchars($item['evidence'] ?? '');
        $primaryAction = htmlspecialchars($item['primaryAction'] ?? '');
        $description = htmlspecialchars($item['description'] ?? '');

        $html = '
                <div class="bioactive-card">

<div class="bioactive-header">

    <div class="bioactive-title">
        <h3>'.$name.'</h3>';

        if ($category !== '') {
            $html .= '
        <div class="bioactive-category">' . $category . ($subcategory !== '' ? ' &middot; ' . $subcategory : '') . '</div>';
        }

        $html .= '
    </div>

    <div class="score-badge">
        '.$score.'
        <span class="score-label">/100</span>
    </div>

</div>

                    <p class="bioactive-summary">'.$summary.'</p>';

        // What it actually does, day to day — the one line the old card
        // was missing beyond the name and a pathway list.
        if ($primaryAction !== '') {
            $html .= '
                    <div class="bioactive-primary-action">' . $primaryAction . '</div>';
        }

        if ($evidence !== '') {
            $html .= '
                    <div class="tag-row"><span class="tag tag-evidence">Evidence: ' . $evidence . '</span></div>';
        }

        // Full description, tucked behind a details toggle rather than
        // always shown, so the card stays scannable but the fuller
        // explanation is one click away for anyone who wants it.
        if ($description !== '' && $description !== $summary) {
            $html .= '
                    <details class="bioactive-more">
                        <summary>More about ' . $name . ' <span class="expand-chevron">&#9662;</span></summary>
                        <p>' . $description . '</p>
                    </details>';
        }

// Supporting mechanisms
if (!empty($item['mechanisms'])) {

    $html .= '
        <div class="body-label">
            KEY NUTRITIONAL PATHWAYS
        </div>

        <div class="mechanism-chip-row">';

    foreach (array_slice($item['mechanisms'], 0, 3) as $m) {
        $html .= MechanismPopover::render(
            $m,
            'bioactive-' . $name
        );
    }

    $html .= '</div>';
}

            // Food sources
            if (!empty($item['foods'])) {

                $html .= '
                    <div class="bioactive-section-title">
                        Best food sources
                    </div>

                    <div class="body-food-row">';

                foreach (array_slice($item['foods'], 0, 5) as $food) {

                    $label = htmlspecialchars(
                        is_array($food)
                            ? ($food['name'] ?? '')
                            : $food
                    );

                    $html .= '
                        <span class="body-food-chip">'.$label.'</span>';
                }

                $html .= '</div>';
            }

            $html .= '
                </div>';

        return $html;
    }
}
