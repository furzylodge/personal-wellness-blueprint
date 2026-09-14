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
   
   
$recommendation = $food['recommendation'] ?? 'Useful option';        

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

           $score = (int) ($food['score'] ?? 0);

            $compounds = array_slice(
                $food['knowledge']['activeCompounds'] ?? [],
                0,
                3
            );

$html .= '
<div class="recommendation-card">

<div class="recommendation-header">

    <div class="recommendation-title-group">
        <div class="rank-badge">'.$rank.'</div>
        <h3>'.$name.'</h3>
    </div>

    <div class="food-recommendation">'.$recommendation.'</div>

</div>

<p>'.$description.'</p>

<div class="body-label">KEY NUTRITIONAL PATHWAYS</div>

<div class="mechanism-row">';


$html .= '<div class="mechanism-chip-row">';

foreach (array_slice($food['sources'] ?? [], 0, 3) as $source) {

    if (empty($source['mechanismName'])) {
        continue;
    }

 $foodGroup = htmlspecialchars($food['id'] ?? ('food-'.$rank));

$html .= '
<details class="mechanism-popover" name="food-'.$foodGroup.'">

        <summary class="mechanism-chip">
            '.htmlspecialchars($source['mechanismName']).'
            <span class="info-icon">ⓘ</span>
        </summary>

        <div class="mechanism-card">

            <h5>'.htmlspecialchars($source['mechanismName']).'</h5>

            <div class="mechanism-summary">'
                .htmlspecialchars($source['plainEnglish'] ?? '').'
            </div>

            <hr class="mechanism-divider">

            <div class="mechanism-label">
                WHY THIS PATHWAY MATTERS
            </div>

            <p>'
                .htmlspecialchars($source['whyItMatters'] ?? '').'
            </p>

        </div>

    </details>';
}

$html .= '</div>';

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