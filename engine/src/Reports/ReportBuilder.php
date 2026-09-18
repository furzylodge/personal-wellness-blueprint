<?php

declare(strict_types=1);

namespace PWB\Reports;
use PWB\Reports\Sections\MastheadSection;
use PWB\Reports\Sections\DashboardSection;
use PWB\Reports\Sections\FoodSection;
use PWB\Reports\Sections\BodySystemsSection;
use PWB\Reports\Sections\ActionPlanSection;
use PWB\Reports\Sections\BioactiveSection;
use PWB\Reports\Sections\VitaminSection;
use PWB\Reports\Sections\MineralSection;
use PWB\Reports\Components\ScopeBanner;

class ReportBuilder
{
    public function build(array $reportData): string
    {
        $theme = $reportData['theme'] ?? 'theme-green';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Personal Wellness Blueprint</title>
    <link rel="stylesheet" href="../engine/assets/css/questionnaire.css">
</head>

<body class="<?= $theme ?>">
<div class="report-container">

HTML;

$mastheadSection = new MastheadSection();
$dashboardSection = new DashboardSection();
$foodSection = new FoodSection();
$bodySystemsSection = new BodySystemsSection();
$bioactiveSection = new BioactiveSection();
//$vitaminSection = new VitaminSection();
//$mineralSection = new MineralSection();
//$actionPlanSection = new ActionPlanSection();

$html .= $mastheadSection->render($reportData);
$html .= ScopeBanner::render($reportData['scope'] ?? []);
$html .= $dashboardSection->render($reportData['dashboard'] ?? []);
$html .= $foodSection->render($reportData);
$html .= $bodySystemsSection->render(
    $reportData['bodySystems']
);
$html .= $bioactiveSection->render($reportData);
//$html .= $vitaminSection->render($reportData);
//$html .= $mineralSection->render($reportData);
//$html .= $actionPlanSection->render($reportData);

// Standard compliance disclaimer — shown once, at the very end of the
// report. Covers both the report's own educational (not diagnostic)
// nature and the standard "food supplements are not a substitute for
// a varied diet, not intended to diagnose/treat/cure/prevent disease"
// language required whenever products are recommended (see the
// product strip's own supplementation-framing intro higher up the
// dashboard, which covers the same ground in a softer, in-context way).
$html .= <<<HTML

<p class="report-footer-disclaimer">
    This report is generated for educational and informational purposes only, based on the answers you provided. It is not a substitute for professional medical advice, diagnosis, or treatment, and should not be used to diagnose, treat, cure, or prevent any disease. Always consult your GP or another qualified healthcare professional before making changes to your diet, lifestyle, or supplement routine — particularly if you are pregnant, breastfeeding, taking medication, or managing a medical condition. Food supplements should not be used as a substitute for a varied and balanced diet and a healthy lifestyle.
</p>
HTML;

$html .= <<<HTML

</div>
</body>

</html>
HTML;

        return $html;
    }
}