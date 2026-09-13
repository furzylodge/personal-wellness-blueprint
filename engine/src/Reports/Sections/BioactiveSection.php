<?php

declare(strict_types=1);

namespace PWB\Reports\Sections;

class BioactiveSection
{
    public function render(array $reportData): string
    {
        $bioactives = array_slice(
            $reportData['bioactives'] ?? [],
            0,
            10
        );

        if (empty($bioactives)) {
            return '';
        }

        $html = <<<HTML

<h2>Priority Bioactives</h2>

<p>
These bioactives have been ranked according to the clinical mechanisms
identified from your questionnaire.
</p>

<table style="width:100%; border-collapse:collapse; margin-top:15px;">
<tr style="background:#E8F3EA;">
    <th style="padding:10px; text-align:left;">Bioactive</th>
    <th style="padding:10px; text-align:center;">Clinical Score</th>
</tr>

HTML;

        foreach ($bioactives as $item) {

            $name = htmlspecialchars($item['name']);
            $score = number_format($item['clinicalScore'], 1);

            $html .= <<<HTML

<tr style="border-bottom:1px solid #dddddd;">
    <td style="padding:10px;">{$name}</td>
    <td style="padding:10px; text-align:center;">{$score}</td>
</tr>

HTML;
        }

        $html .= <<<HTML

</table>

HTML;

        return $html;
    }
}