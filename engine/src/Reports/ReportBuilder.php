<?php

declare(strict_types=1);

namespace PWB\Reports;
use PWB\Reports\Sections\FoodSection;
use PWB\Reports\Sections\BodySystemsSection;
use PWB\Reports\Sections\ActionPlanSection;

class ReportBuilder
{
    public function build(array $reportData): string
    {
        $clientName = $reportData['client']['name'] ?? 'Unknown';

        $foods = $reportData['foods'] ?? [];

        $summary = $reportData['summary'] ?? [];

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Personal Wellness Blueprint</title>

    <style>

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 40px;
            color: #333;
        }

        h1 {
            color: #2E6F40;
        }

        h2 {
            margin-top: 40px;
            border-bottom: 1px solid #cccccc;
            padding-bottom: 6px;
        }

        ul {
            padding-left: 20px;
        }

    </style>

</head>

<body>

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
	$actionPlanSection = new ActionPlanSection();
	$html .= $foodSection->render($reportData);
	$html .= $bodySystemsSection->render($reportData);
	$html .= $actionPlanSection->render($reportData);
        $html .= <<<HTML

</ul>

</body>

</html>
HTML;

        return $html;
    }
}