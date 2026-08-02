<?php

declare(strict_types=1);

namespace PWB\Reports\Sections;

class BodySystemsSection
{
    public function render(array $reportData): string
    {
        $bodySystems = $reportData['bodySystems'] ?? [];

        $html = <<<HTML

<h2>Primary Body Systems Supported</h2>

HTML;

        if (empty($bodySystems)) {

            $html .= "<p>No body system recommendations available.</p>";

            return $html;
        }

        foreach ($bodySystems as $systemName => $foods) {

            $html .= <<<HTML

<div class="body-system">

<h3>{$systemName}</h3>

<p>The following recommended foods may help support this body system:</p>

<ul>

HTML;

            foreach ($foods as $food) {

                $foodName = $food['name'] ?? 'Unknown';

                $html .= "<li>{$foodName}</li>";

            }

            $html .= <<<HTML

</ul>

</div>

<hr>

HTML;

        }

        return $html;
    }
}