<?php

declare(strict_types=1);

namespace PWB\Reports\Sections;

/**
 * Renders the "at a glance" dashboard shown at the very top of the report,
 * above the longer-form Priority Foods / Wellness Priorities / Bioactives
 * sections. Deliberately simple: a short personalised paragraph, a body
 * system heat grid, a key-mechanisms bar read-out, the top food matches,
 * a goal-alignment note and a handful of quick actions — six areas in one
 * screen, so the person gets the headline story before the detail.
 *
 * All figures are pre-computed by ReportDataBuilder::buildDashboard(); this
 * class only lays them out.
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
        $html .= $this->renderHeatMap($dashboard['heatMap'] ?? []);
        $html .= $this->renderGoal($dashboard['goalAlignment'] ?? null, $dashboard['topSystem'] ?? null);
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

    private function renderHeatMap(array $heatMap): string
    {
        if (empty($heatMap)) {
            return '';
        }

        $cells = '';

        foreach ($heatMap as $cell) {

            $strongClass = !empty($cell['strong']) ? ' heat-cell-strong' : '';

            $cells .= '
                        <div class="heat-cell' . $strongClass . '" style="--heat:' . (int) $cell['heat'] . '%">
                            <span class="heat-score">' . (int) $cell['score'] . '</span>
                            <span class="heat-name">' . htmlspecialchars($cell['name']) . '</span>
                        </div>';
        }

        return '
                <div class="dashboard-card dashboard-heatmap">
                    <div class="dashboard-card-label">Body Systems</div>
                    <div class="heat-grid">' . $cells . '
                    </div>
                </div>';
    }

    private function renderGoal(?array $goal, ?array $topSystem): string
    {
        if ($goal !== null) {
            $body = '
                    <p class="goal-note">
                        Your goal of <strong>' . htmlspecialchars($goal['goalName']) . '</strong>
                        lines up with your <strong>' . htmlspecialchars($goal['systemName']) . '</strong>
                        priority.
                    </p>';
        } elseif ($topSystem !== null) {
            $body = '
                    <p class="goal-note">
                        Your top priority right now is
                        <strong>' . htmlspecialchars($topSystem['name']) . '</strong>.
                    </p>';
        } else {
            $body = '
                    <p class="goal-note">
                        No standout priority found yet — the detail below still applies.
                    </p>';
        }

        return '
                <div class="dashboard-card dashboard-goal">
                    <div class="dashboard-card-label">Outcome</div>' . $body . '
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
                    <div class="dashboard-card-label">Key Mechanisms</div>' . $rows . '
                </div>';
    }

    private function renderFoods(array $foods): string
    {
        if (empty($foods)) {
            return '';
        }

        $chips = '';

        foreach ($foods as $food) {

            $chips .= '
                        <div class="dash-food-chip">
                            <span class="dash-food-name">' . htmlspecialchars($food['name']) . '</span>
                            <span class="dash-food-score">' . (int) $food['score'] . '</span>
                        </div>';
        }

        return '
                <div class="dashboard-card dashboard-foods">
                    <div class="dashboard-card-label">Top Foods</div>
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
                    <ul class="dash-action-list">' . $items . '</ul>
                </div>';
    }
}
