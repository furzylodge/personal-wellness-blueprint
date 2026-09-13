<?php

declare(strict_types=1);

namespace PWB\Reports;
use PWB\Reports\Sections\FoodSection;
use PWB\Reports\Sections\BodySystemsSection;
use PWB\Reports\Sections\ActionPlanSection;
use PWB\Reports\Sections\BioactiveSection;
use PWB\Reports\Sections\VitaminSection;
use PWB\Reports\Sections\MineralSection;

class ReportBuilder
{
    public function build(array $reportData): string
    {
        $theme = $reportData['theme'] ?? 'theme-green';
        
        $clientName = $reportData['client']['name'] ?? 'Unknown';

        $foods = $reportData['foods'] ?? [];

        $summary = $reportData['summary'] ?? [];

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Personal Wellness Blueprint</title>
    <link rel="stylesheet" href="../engine/assets/css/questionnaire.css">
</head>

<body class="<?= $theme ?>">

<h1>Personal Wellness Blueprint</h1>

<p><strong>Client:</strong> {$clientName}</p>

<h2>Summary</h2>

<ul>

HTML;

        foreach ($summary as $item) {
            $html .= "<li>{$item}</li>";
        }

        $html .= <<<HTML

</ul>

<ul>

HTML;

$foodSection = new FoodSection();
$bodySystemsSection = new BodySystemsSection();
$bioactiveSection = new BioactiveSection();
//$vitaminSection = new VitaminSection();
//$mineralSection = new MineralSection();
//$actionPlanSection = new ActionPlanSection();

$html .= $foodSection->render($reportData);
$html .= $bodySystemsSection->render(
    $reportData['bodySystems']
);
$html .= $bioactiveSection->render($reportData);
//$html .= $vitaminSection->render($reportData);
//$html .= $mineralSection->render($reportData);
//$html .= $actionPlanSection->render($reportData);
$html .= <<<HTML

</ul>

</body>

</html>
HTML;

        return $html;
    }
}