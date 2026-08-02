<?php

declare(strict_types=1);

namespace PWB\Reports\Sections;

class ActionPlanSection
{
    public function render(array $reportData): string
    {
        $actionPlan = $reportData['actionPlan'] ?? [];

        $html = <<<HTML

<h2>Your Action Plan</h2>

HTML;

        if (empty($actionPlan)) {

            $html .= "<p>No actions available.</p>";

            return $html;
        }

        foreach ($actionPlan as $section => $actions) {

            $html .= "<h3>{$section}</h3>";
            $html .= "<ul>";

            foreach ($actions as $action) {

                $html .= "<li>{$action}</li>";

            }

            $html .= "</ul>";

        }

        return $html;
    }
}