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

        foreach ($bioactives as $item) {

            $name  = htmlspecialchars($item['name'] ?? 'Unknown');
            $score = round($item['clinicalScore'] ?? 0);

            $summary = htmlspecialchars(
                $item['description']
                ?? $item['plainEnglish']
                ?? 'A beneficial plant compound that contributes to healthy nutritional pathways.'
            );

            $html .= '
                <div class="bioactive-card">

<div class="bioactive-header">

    <div class="bioactive-title">
        <h3>'.$name.'</h3>
    </div>

    <div class="score-badge">
        '.$score.'
        <span class="score-label">/100</span>
    </div>

</div>

                    <p class="bioactive-summary">'.$summary.'</p>';

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
        }

        $html .= '
            </div>

        </section>';

        return $html;
    }
}