<?php

declare(strict_types=1);

namespace PWB\Reports\Sections;

use PWB\Reports\IconLibrary;

/**
 * Renders the "at a glance" dashboard shown at the very top of the report,
 * above the longer-form Priority Foods / Wellness Priorities / Bioactives
 * sections. A personalised paragraph, the goals they picked in the
 * questionnaire, a body system heat grid + radar chart, a key-mechanisms
 * bar read-out, the top food matches and a handful of quick actions.
 *
 * All figures are pre-computed by ReportDataBuilder::buildDashboard(); this
 * class only lays them out (and looks up icons by id via IconLibrary).
 */
class DashboardSection
{
    public function render(array $dashboard): string
    {
        if (empty($dashboard)) {
            return '';
        }

        $html = '
        <section class="report-section dashboard-section">

            <h2>Your Wellness Snapshot</h2>

            <p class="section-intro">
                A quick read of where you stand today. The full detail — every
                pathway, food and body system — follows below.
            </p>

            <div class="dashboard-grid">';

        $html .= $this->renderSummary($dashboard);
        $html .= $this->renderGoals($dashboard['goals'] ?? [], $dashboard['goalAlignment'] ?? null, $dashboard['topSystem'] ?? null);
        $html .= $this->renderHeatMap($dashboard['heatMap'] ?? []);
        $html .= $this->renderRadar($dashboard['radarSystems'] ?? []);
        $html .= $this->renderMechanisms($dashboard['topMechanisms'] ?? []);
        $html .= $this->renderFoods($dashboard['topFoods'] ?? []);
        $html .= $this->renderActions($dashboard['actions'] ?? []);

        $html .= '
            </div>

        </section>';

        return $html;
    }

    private function renderSummary(array $dashboard): string
    {
        $paragraph = $dashboard['paragraph'] ?? '';

        return '
                <div class="dashboard-card dashboard-summary">
                    <p>' . htmlspecialchars($paragraph) . '</p>
                </div>';
    }

    private function renderGoals(array $goals, ?array $goalAlignment, ?array $topSystem): string
    {
        // Nothing selected — skip the strip but keep a one-line note about
        // the top priority so the card isn't just missing.
        if (empty($goals)) {
            $note = $topSystem !== null
                ? 'Your top priority right now is <strong>' . htmlspecialchars($topSystem['name']) . '</strong>.'
                : 'No standout priority found yet — the detail below still applies.';

            return '
                <div class="dashboard-card dashboard-goals">
                    <div class="dashboard-card-label">Your Goals</div>
                    <p class="dashboard-card-caption">You didn\'t select any wellness goals in the questionnaire.</p>
                    <p class="goal-note">' . $note . '</p>
                </div>';
        }

        $chips = '';

        foreach ($goals as $goal) {
            $icon = IconLibrary::render($goal['id']);

            $chips .= '
                        <div class="goal-chip">
                            <div class="goal-chip-icon">' . $icon . '</div>
                            <div class="goal-chip-name">' . htmlspecialchars($goal['name']) . '</div>
                        </div>';
        }

        $noteText = $goalAlignment
            ? 'Your goal of <strong>' . htmlspecialchars($goalAlignment['goalName']) . '</strong> lines up with your <strong>' . htmlspecialchars($goalAlignment['systemName']) . '</strong> priority.'
            : ($topSystem !== null
                ? 'Your top priority right now is <strong>' . htmlspecialchars($topSystem['name']) . '</strong>.'
                : '');

        $note = $noteText !== '' ? '<p class="goal-note">' . $noteText . '</p>' : '';

        return '
                <div class="dashboard-card dashboard-goals">
                    <div class="dashboard-card-label">Your Goals</div>
                    <p class="dashboard-card-caption">The wellness goals you chose at the start of the questionnaire.</p>
                    <div class="goal-chip-row">' . $chips . '
                    </div>' . $note . '
                </div>';
    }

    private function renderHeatMap(array $heatMap): string
    {
        if (empty($heatMap)) {
            return '';
        }

        $cells = '';

        foreach ($heatMap as $cell) {

            $topClass = !empty($cell['top']) ? ' heat-cell-top' : '';
            $topTag = !empty($cell['top']) ? '<span class="heat-cell-tag">Top</span>' : '';
            $icon = $cell['id'] ? IconLibrary::render($cell['id']) : '';
            $score = (int) $cell['score'];

            $cells .= '
                        <div class="heat-cell' . $topClass . '">
                            <div class="heat-cell-head">
                                <div class="heat-cell-icon">' . $icon . '</div>
                                <div class="heat-cell-name">' . htmlspecialchars($cell['name']) . '</div>
                            </div>
                            <div class="heat-cell-score-row">
                                <span class="heat-cell-score">' . $score . '</span>
                                ' . $topTag . '
                            </div>
                            <div class="heat-cell-meter">
                                <div class="heat-cell-meter-fill" style="width:' . $score . '%"></div>
                            </div>
                        </div>';
        }

        return '
                <div class="dashboard-card dashboard-heatmap">
                    <div class="dashboard-card-label">Body Systems</div>
                    <p class="dashboard-card-caption">Each score is out of 100 — the higher it is, the more your answers suggest that system could benefit from dietary support.</p>
                    <div class="heat-grid">' . $cells . '
                    </div>
                </div>';
    }

    /**
     * A pure-SVG radar/spider chart (no JS) plotting every flagged body
     * system as an axis, purely as a visual companion to the heat grid
     * above — the heat grid remains the precise, readable reference, this
     * is the "shape of your results at a glance" picture.
     */
    private function renderRadar(array $systems): string
    {
        $count = count($systems);

        if ($count < 3) {
            // A radar needs at least a handful of axes to read as a shape
            // rather than a random polygon — fall back quietly.
            return '';
        }

        $size = 300;
        $center = $size / 2;
        $maxRadius = $center - 55; // leave generous room for edge labels
        $angleStep = (2 * M_PI) / $count;
        $startAngle = -M_PI / 2; // first axis points straight up

        $ringsSvg = '';

        foreach ([0.25, 0.5, 0.75, 1.0] as $ringFraction) {

            $points = [];

            for ($i = 0; $i < $count; $i++) {
                $angle = $startAngle + $i * $angleStep;
                $r = $maxRadius * $ringFraction;
                $points[] = round($center + $r * cos($angle), 1) . ',' . round($center + $r * sin($angle), 1);
            }

            $ringsSvg .= '<polygon class="radar-grid" points="' . implode(' ', $points) . '"/>';
        }

        $spokesSvg = '';
        $shapePoints = [];
        $labelsSvg = '';

        foreach ($systems as $i => $system) {

            $angle = $startAngle + $i * $angleStep;

            $ex = $center + $maxRadius * cos($angle);
            $ey = $center + $maxRadius * sin($angle);

            $spokesSvg .= '<line class="radar-spoke" x1="' . $center . '" y1="' . $center . '" x2="' . round($ex, 1) . '" y2="' . round($ey, 1) . '"/>';

            $scoreFraction = max(0, min(100, $system['score'])) / 100;
            $sx = $center + ($maxRadius * $scoreFraction) * cos($angle);
            $sy = $center + ($maxRadius * $scoreFraction) * sin($angle);

            $shapePoints[] = round($sx, 1) . ',' . round($sy, 1);

            // Labels sit just outside the outer ring, nudged so they don't
            // overlap the plot; short body-system names keep this simple.
            $lx = $center + ($maxRadius + 14) * cos($angle);
            $ly = $center + ($maxRadius + 14) * sin($angle) + 3;

            $anchor = 'middle';
            if (cos($angle) > 0.3) {
                $anchor = 'start';
            } elseif (cos($angle) < -0.3) {
                $anchor = 'end';
            }

            $shortName = str_replace(' System', '', $system['name']);

            $labelsSvg .= '<text class="radar-label" x="' . round($lx, 1) . '" y="' . round($ly, 1) . '" text-anchor="' . $anchor . '">' . htmlspecialchars($shortName) . '</text>';
        }

        $shapeSvg = '<polygon class="radar-shape" points="' . implode(' ', $shapePoints) . '"/>';

        $pointsSvg = '';
        foreach ($systems as $i => $system) {
            $angle = $startAngle + $i * $angleStep;
            $scoreFraction = max(0, min(100, $system['score'])) / 100;
            $sx = $center + ($maxRadius * $scoreFraction) * cos($angle);
            $sy = $center + ($maxRadius * $scoreFraction) * sin($angle);
            $pointsSvg .= '<circle class="radar-point" cx="' . round($sx, 1) . '" cy="' . round($sy, 1) . '" r="2.5"/>';
        }

        $svg = '<svg class="radar-svg" viewBox="0 0 ' . $size . ' ' . $size . '" xmlns="http://www.w3.org/2000/svg">'
            . $ringsSvg . $spokesSvg . $shapeSvg . $pointsSvg . $labelsSvg
            . '</svg>';

        return '
                <div class="dashboard-card dashboard-radar">
                    <div class="dashboard-card-label">Shape of Your Results</div>
                    <p class="dashboard-card-caption">All of your flagged body systems in one picture — bigger reach, more areas in play.</p>
                    ' . $svg . '
                </div>';
    }

    private function renderMechanisms(array $mechanisms): string
    {
        if (empty($mechanisms)) {
            return '';
        }

        $rows = '';

        foreach ($mechanisms as $mechanism) {

            $rows .= '
                        <div class="bar-row">
                            <div class="bar-row-label">' . htmlspecialchars($mechanism['name']) . '</div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width:' . (int) $mechanism['percent'] . '%"></div>
                            </div>
                        </div>';
        }

        return '
                <div class="dashboard-card dashboard-mechanisms">
                    <div class="dashboard-card-label">Key Mechanisms</div>
                    <p class="dashboard-card-caption">The nutritional pathways doing the most work for your top priorities, ranked strongest first.</p>' . $rows . '
                </div>';
    }

    private function renderFoods(array $foods): string
    {
        if (empty($foods)) {
            return '';
        }

        $chips = '';

        foreach ($foods as $food) {

            $icon = !empty($food['foodGroupId']) ? IconLibrary::render($food['foodGroupId']) : '';

            $chips .= '
                        <div class="dash-food-chip">
                            <span class="dash-food-name"><span class="dash-food-icon">' . $icon . '</span>' . htmlspecialchars($food['name']) . '</span>
                            <span class="dash-food-score">' . (int) $food['score'] . '</span>
                        </div>';
        }

        return '
                <div class="dashboard-card dashboard-foods">
                    <div class="dashboard-card-label">Top Foods</div>
                    <p class="dashboard-card-caption">Scored out of 100 on how well each food matches your top pathways.</p>
                    <div class="dash-food-list">' . $chips . '
                    </div>
                </div>';
    }

    private function renderActions(array $actions): string
    {
        if (empty($actions)) {
            return '';
        }

        $items = '';

        foreach ($actions as $action) {
            $items .= '<li>' . htmlspecialchars($action) . '</li>';
        }

        return '
                <div class="dashboard-card dashboard-actions">
                    <div class="dashboard-card-label">This Week</div>
                    <p class="dashboard-card-caption">Simple starting points based on your results.</p>
                    <ul class="dash-action-list">' . $items . '</ul>
                </div>';
    }
}
