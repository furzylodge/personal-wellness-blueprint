<?php

declare(strict_types=1);

namespace PWB\Reports\Sections;

class BodySystemsSection
{
public function render(array $systems): string
{
    $html = '
    <section class="report-section">

        <h2>Body System Priorities</h2>

        <p class="section-intro">
            Your assessment identified these as the highest priority physiological systems. The foods shown are the five strongest whole-food recommendations supporting each system.
        </p>';

    $rank = 1;

    foreach ($systems as $system) {

        $foods = array_slice($system['topFoods'] ?? [], 0, 5);

        $html .= '
        <div class="body-system-card">

            <div class="body-system-header">

                <div class="body-system-title">
                    <div class="rank-badge">'.$rank.'</div>
                    <h3>'.htmlspecialchars($system['name']).'</h3>
                </div>

                <div class="score-badge">
                    '.number_format($system['score'], 1).'%
                </div>

            </div>

            <div class="body-food-row">';

        foreach ($foods as $food) {

            $html .= '
                <span class="body-food-chip">
                    '.htmlspecialchars($food['name']).'
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