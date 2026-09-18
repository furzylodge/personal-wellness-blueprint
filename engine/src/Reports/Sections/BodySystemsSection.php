<?php

declare(strict_types=1);

namespace PWB\Reports\Sections;
use PWB\Reports\Components\MechanismPopover;
use PWB\Reports\IconLibrary;

class BodySystemsSection
{
    public function render(array $systems): string
    {
        $html = '
        <section class="report-section">

            <h2>Your Wellness Priorities</h2>

            <p class="section-intro">
                These are the areas where your questionnaire suggests nutrition is likely to have the greatest overall impact. They are not diagnoses, but practical priorities to help guide your food choices.
            </p>';

        $cards = [];
        $rank = 1;

        foreach ($systems as $system) {
            $cards[] = $this->renderSystemCard($system, $rank);
            $rank++;
        }

        // Top 3 stay visible; the rest sit behind an expand/collapse — the
        // same treatment as Priority Foods and Priority Bioactives, so no
        // section forces a long scroll before the next one.
        $visible = array_slice($cards, 0, 3);
        $hidden  = array_slice($cards, 3);

        $html .= implode('', $visible);

        if (!empty($hidden)) {

            $count = count($hidden);
            $plural = $count === 1 ? 'priority' : 'priorities';

            $html .= '
            <details class="expand-more">
                <summary class="expand-toggle">
                    <span class="expand-label-closed">Show ' . $count . ' more ' . $plural . '</span>
                    <span class="expand-label-open">Show fewer priorities</span>
                    <span class="expand-chevron">&#9662;</span>
                </summary>
                <div class="expand-content">' . implode('', $hidden) . '</div>
            </details>';
        }

        $html .= '
        </section>';

        return $html;
    }

    private function renderSystemCard(array $system, int $rank): string
    {
        $html = '
            <div class="body-system-card">

                <div class="body-system-header">

                    <div class="body-system-title">

                        <div class="rank-badge">'.$rank.'</div>

                        <div>
                            <h3>'.htmlspecialchars($system['name']).'</h3>
                            <div class="priority-label">'
                                .htmlspecialchars($system['interpretation'] ?? '').'
                            </div>
                        </div>

                    </div>

                    <div class="score-badge">
    '.round($system['score']).'
    <span class="score-label">/100</span>
</div>

                </div>

                <p class="body-summary">'
                    .htmlspecialchars($system['summary'] ?? '').'
                </p>

                <div class="body-section-title">
                    Key nutritional pathways
                </div>

                <div class="mechanism-row">';

foreach ($system['topMechanisms'] ?? [] as $mechanism) {
    $html .= MechanismPopover::render(
        $mechanism,
        'mechanisms-'.$rank
    );
}

            $html .= '
                </div>

                <div class="body-section-title">
                    Foods to emphasise
                </div>

                <div class="body-food-row">';

            foreach ($system['topFoods'] ?? [] as $food) {

                $icon = !empty($food['foodGroupId'])
                    ? '<span class="food-group-icon">' . IconLibrary::render($food['foodGroupId']) . '</span>'
                    : '';

                $html .= '
                    <span class="body-food-chip">' . $icon
                        .htmlspecialchars($food['name']).'
                    </span>';
            }

            $html .= '
                </div>

            </div>';

        return $html;
    }
}
