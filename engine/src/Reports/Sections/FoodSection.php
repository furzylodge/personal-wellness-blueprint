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

        $cards = [];

        foreach ($foods as $index => $food) {
            $cards[] = $this->renderFoodCard($food, $index + 1);
        }

        // Top 3 stay visible as full cards; the rest sit behind an
        // expand/collapse — 10 full recommendation cards in a row was a
        // long scroll before anyone reached the body-systems section below.
        $visible = array_slice($cards, 0, 3);
        $hidden  = array_slice($cards, 3);

        $html .= implode('', $visible);

        if (!empty($hidden)) {

            $count = count($hidden);
            $plural = $count === 1 ? 'food' : 'foods';

            $html .= '
<details class="expand-more">
    <summary class="expand-toggle">
        <span class="expand-label-closed">Show ' . $count . ' more ' . $plural . '</span>
        <span class="expand-label-open">Show fewer foods</span>
        <span class="expand-chevron">&#9662;</span>
    </summary>
    <div class="expand-content">' . implode('', $hidden) . '</div>
</details>';
        }

        return $html;
    }

    private function renderFoodCard(array $food, int $rank): string
    {
        $html = '';

$recommendation = $food['recommendation'] ?? 'Useful option';

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
    <summary>Why this was recommended <span class="expand-hint">(click to see the full breakdown)</span></summary>

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

        return $html;
    }
}