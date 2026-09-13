<?php

declare(strict_types=1);

namespace PWB\Reports\Sections;

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

        $rank = 1;

        foreach ($systems as $system) {

            $html .= '
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

                $html .= '
                    <span class="mechanism-chip">'
                        .htmlspecialchars($mechanism['name']).'
                    </span>';
            }

            $html .= '
                </div>

                <div class="body-section-title">
                    Foods to emphasise
                </div>

                <div class="body-food-row">';

            foreach ($system['topFoods'] ?? [] as $food) {

                $html .= '
                    <span class="body-food-chip">'
                        .htmlspecialchars($food['name']).'
                    </span>';
            }

            $html .= '
                </div>

            </div>';

            $rank++;
        }

        $html .= '
        </section>';

        return $html;
    }
}