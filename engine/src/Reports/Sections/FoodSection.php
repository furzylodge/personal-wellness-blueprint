<?php

declare(strict_types=1);

namespace PWB\Reports\Sections;

use PWB\Reports\IconLibrary;

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

$foodGroupIcon = !empty($food['foodGroupId'])
    ? '<span class="food-group-icon">' . IconLibrary::render($food['foodGroupId']) . '</span>'
    : '';

$html .= '
<div class="recommendation-card">

<div class="recommendation-header">

    <div class="recommendation-title-group">
        <div class="rank-badge">'.$rank.'</div>
        '.$foodGroupIcon.'
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
            
   if (!empty($food['scoreBreakdown']['summary']['systemsCovered'])) {

    $html .= '<div class="body-label" style="margin-top:14px;">PHYSIOLOGICAL SYSTEMS</div>';
    $html .= '<div class="tag-row">';

    foreach ($food['scoreBreakdown']['summary']['systemsCovered'] as $system) {
        $system = htmlspecialchars($system);
        $html .= "<span class=\"tag\">{$system}</span>";
    }

    $html .= '</div>';
}         
            
            if (!empty($food['scoreBreakdown']['mechanisms'])) {

$html .= '
<details class="food-score-breakdown">
    <summary>Why this was recommended</summary>

    <div class="score-breakdown-intro">
        These values show how each nutritional pathway contributed to this foods Explainable Recommendation Score.
    </div>

    <div class="score-breakdown-table">';
    
 $displayMechanisms = array_slice(
    $food['scoreBreakdown']['mechanisms'],
    0,
    3
);

$other = 0;

foreach (array_slice($food['scoreBreakdown']['mechanisms'], 3) as $m) {
    $other += $m['contribution'];
}

foreach ($displayMechanisms as $m) {

        $html .= '
            <div class="score-row">
                <span>'.htmlspecialchars($m['mechanismName']).'</span>
                <strong>'.number_format($m['contribution'], 1).'</strong>
            </div>';
    }
    
if ($other > 0) {
    $html .= '
        <div class="score-row">
            <span>Other supporting pathways</span>
            <strong>'.number_format($other, 1).'</strong>
        </div>';
}    

    $html .= '
            <div class="score-row total">
                <span>Explainable Recommendation Score</span>
                <strong>'.number_format($food['scoreBreakdown']['total'], 1).'</strong>
            </div>
        </div>
    </details>';
}

            $html .= <<<HTML

</div>

HTML;

        }

        return $html;
    }
}