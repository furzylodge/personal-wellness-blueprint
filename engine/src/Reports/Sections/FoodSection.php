<?php

declare(strict_types=1);

namespace PWB\Reports\Sections;

class FoodSection
{
    public function render(array $reportData): string
    {
        $foods = array_slice(
            $reportData['foods'] ?? [],
            0,
            10
        );

        if (empty($foods)) {
            return '';
        }

        $html = <<<HTML

<h2>Priority Foods</h2>

<p class="section-intro">
These foods have been prioritised because they best support the highest-scoring clinical mechanisms identified in your assessment.
</p>

HTML;


        foreach ($foods as $index => $food) {

    $rank = $index + 1;

            $name = htmlspecialchars(
                $food['identity']['name'] ?? 'Unknown'
            );

            $description = strip_tags(
    $food['knowledge']['description'] ?? ''
);

if (strlen($description) > 220) {
    $description = substr($description, 0, 217) . '...';
}

$description = htmlspecialchars($description);

            $score = number_format(
                $food['clinicalScore'] ?? 0,
                1
            );

            $compounds = array_slice(
                $food['knowledge']['activeCompounds'] ?? [],
                0,
                3
            );

 $mechanisms = [];

foreach (array_slice($food['sources'] ?? [], 0, 3) as $source) {

    if (!empty($source['mechanismName'])) {
        $mechanisms[] = $source['mechanismName'];
    }

}

$why = implode(', ', $mechanisms);

            $html .= <<<HTML

<div class="recommendation-card">

<div class="recommendation-header">

    <div class="recommendation-title-group">
        <div class="rank-badge">{$rank}</div>
        <h3>{$name}</h3>
    </div>

    <div class="score-badge">{$score}</div>

</div>

    <p>{$description}</p>

<p><strong>Supports:</strong></p>

<div class="mechanism-row">

HTML;

foreach ($mechanisms as $mechanism) {

    $mechanism = htmlspecialchars($mechanism);

    $html .= "<span class=\"mechanism-chip\">{$mechanism}</span>";

}

$html .= <<<HTML

</div>

HTML;

            if (!empty($compounds)) {

                $html .= '<div class="tag-row">';

                foreach ($compounds as $compound) {
                    $compound = htmlspecialchars($compound);
                    $html .= "<span class=\"tag\">{$compound}</span>";
                }

                $html .= '</div>';
            }

            $html .= <<<HTML

</div>

HTML;

        }

        return $html;
    }
}