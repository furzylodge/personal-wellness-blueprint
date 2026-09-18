<?php

declare(strict_types=1);

namespace PWB\Reports\Sections;

use PWB\Reports\IconLibrary;

/**
 * Renders the "at a glance" dashboard shown at the very top of the report,
 * above the longer-form Priority Foods / Wellness Priorities / Bioactives
 * sections. A personalised paragraph, the goals they picked in the
 * questionnaire, a body system heat grid + radar chart, a key-mechanisms
 * bar read-out, the top food matches and a handful of quick actions.
 *
 * All figures are pre-computed by ReportDataBuilder::buildDashboard(); this
 * class only lays them out (and looks up icons by id via IconLibrary).
 */
class DashboardSection
{
    public function render(array $dashboard): string
    {
        if (empty($dashboard)) {
            return '';
        }

        $html = '
        <section class="report-section dashboard-section">

            <h2>Your Wellness Snapshot</h2>

            <p class="section-intro">
                A quick read of where you stand today. The full detail — every
                pathway, food and body system — follows below.
            </p>

            <div class="dashboard-grid">';

        $html .= $this->renderSummary($dashboard);

        // Goals (up to 3 chips) never needed the full dashboard width it
        // was given before — narrowed here and paired with a dedicated
        // "goal alignment" card so that note has room to be a real visual
        // moment (a target graphic) rather than a single sentence tacked
        // under the chip row.
        $html .= '
                <div class="dashboard-goals-row">';
        $html .= $this->renderGoals($dashboard['goals'] ?? []);
        $html .= $this->renderGoalAlignment($dashboard['goalAlignment'] ?? null, $dashboard['topSystem'] ?? null);
        $html .= '
                </div>';

        $html .= $this->renderHeatMap($dashboard['heatMap'] ?? []);
        $html .= $this->renderRadar($dashboard['radarSystems'] ?? []);
        $html .= $this->renderProductStrip($dashboard['topProduct'] ?? null, $dashboard['supplementationIntro'] ?? '');
        // These four "insight" cards get their own nested 4-column row
        // rather than sharing the outer grid's fixed 2fr/1fr/1fr template,
        // which was sized for exactly the three cards that used to live
        // here (mechanisms/foods/bioactives) before nutrients joined them.
        $html .= '
                <div class="dashboard-insights-row">';
        $html .= $this->renderMechanisms($dashboard['topMechanisms'] ?? []);
        $html .= $this->renderFoods($dashboard['topFoods'] ?? []);
        $html .= $this->renderBioactives($dashboard['topBioactives'] ?? []);
        $html .= $this->renderNutrients($dashboard['topNutrients'] ?? []);
        $html .= '
                </div>';
        // "This Week" is deliberately not rendered here — its content
        // (buildActionPlan()) is currently three fixed, generic lines with
        // no connection to the person's own answers, which isn't worth the
        // dashboard space, especially with Products coming. The data/method
        // is left in place rather than deleted in case it's revived as a
        // genuinely personalised feature later.
        // $html .= $this->renderActions($dashboard['actions'] ?? []);

        $html .= '
            </div>

        </section>';

        return $html;
    }

    /**
     * "Your top takeaways" — three colour-accented cards pulled straight
     * from this person's own results (top body system, top pathway, top
     * food match) rather than a single generic paragraph. Replaces the
     * old plain-text summary, which was capped at max-width:78ch and read
     * as a bland subtitle rather than the headline moment this section
     * deserves. Falls back gracefully if any one of the three is missing
     * (e.g. an edge-case/empty assessment) by simply omitting that card.
     */
    private function renderSummary(array $dashboard): string
    {
        $topSystem = $dashboard['topSystem'] ?? null;
        $topMechanism = ($dashboard['topMechanisms'] ?? [])[0] ?? null;
        $topFood = ($dashboard['topFoods'] ?? [])[0] ?? null;

        $cards = '';

        if ($topSystem) {
            $icon = IconLibrary::render($topSystem['id'] ?? '');
            $score = (int) round($topSystem['score'] ?? 0);

            $cards .= '
                        <div class="hero-takeaway-card hero-takeaway-a">
                            <div class="hero-takeaway-icon">' . $icon . '</div>
                            <div class="hero-takeaway-tag">Strongest priority</div>
                            <div class="hero-takeaway-title">' . htmlspecialchars($topSystem['name'] ?? '') . '</div>
                            <div class="hero-takeaway-detail">Scored ' . $score . '/100 — the highest of any body system in your results.</div>
                        </div>';
        }

        if ($topMechanism) {
            $icon = IconLibrary::render('TARGET');

            $cards .= '
                        <div class="hero-takeaway-card hero-takeaway-b">
                            <div class="hero-takeaway-icon">' . $icon . '</div>
                            <div class="hero-takeaway-tag">Key pathway</div>
                            <div class="hero-takeaway-title">' . htmlspecialchars($topMechanism['name'] ?? '') . '</div>
                            <div class="hero-takeaway-detail">The mechanism driving most of what you\'ll see below.</div>
                        </div>';
        }

        if ($topFood) {
            $icon = !empty($topFood['foodGroupId'])
                ? IconLibrary::render($topFood['foodGroupId'])
                : IconLibrary::render('CHECK');
            $recommendation = htmlspecialchars($topFood['recommendation'] ?? 'A strong match');

            $cards .= '
                        <div class="hero-takeaway-card hero-takeaway-c">
                            <div class="hero-takeaway-icon">' . $icon . '</div>
                            <div class="hero-takeaway-tag">Top food match</div>
                            <div class="hero-takeaway-title">' . htmlspecialchars($topFood['name'] ?? '') . '</div>
                            <div class="hero-takeaway-detail">' . $recommendation . ' for your profile.</div>
                        </div>';
        }

        // Truly nothing to show (edge-case/empty assessment) — fall back
        // to the plain paragraph rather than an empty card grid.
        if ($cards === '') {
            $paragraph = htmlspecialchars($dashboard['paragraph'] ?? '');

            return $paragraph === '' ? '' : '
                <div class="dashboard-card dashboard-summary">
                    <p>' . $paragraph . '</p>
                </div>';
        }

        return '
                <div class="dashboard-card dashboard-summary hero-card-grid-wrap">
                    <div class="hero-eyebrow">Your top takeaways</div>
                    <p class="hero-subline">The things your answers point to most clearly.</p>
                    <div class="hero-card-grid">' . $cards . '
                    </div>
                </div>';
    }

    private function renderGoals(array $goals): string
    {
        // Nothing selected — a short note instead of an empty chip row.
        if (empty($goals)) {
            return '
                <div class="dashboard-card dashboard-goals">
                    <div class="dashboard-card-label">Your Goals</div>
                    <p class="dashboard-card-caption">You didn\'t select any wellness goals in the questionnaire.</p>
                </div>';
        }

        $chips = '';

        foreach ($goals as $goal) {
            $icon = IconLibrary::render($goal['id']);

            $chips .= '
                        <div class="goal-chip">
                            <div class="goal-chip-icon">' . $icon . '</div>
                            <div class="goal-chip-name">' . htmlspecialchars($goal['name']) . '</div>
                        </div>';
        }

        return '
                <div class="dashboard-card dashboard-goals">
                    <div class="dashboard-card-label">Your Goals</div>
                    <p class="dashboard-card-caption">Chosen at the start of the questionnaire.</p>
                    <div class="goal-chip-row">' . $chips . '
                    </div>
                </div>';
    }

    /**
     * The "your goal of X lines up with your Y priority" note, given its
     * own card with a target graphic — previously a single sentence tacked
     * under the goal chips, easy to skim past. Falls back to a plain
     * top-priority note when there's no goal/system alignment to show (no
     * goals selected, or none of them matched a flagged body system), and
     * disappears entirely if there's truly nothing to say yet.
     */
    private function renderGoalAlignment(?array $goalAlignment, ?array $topSystem): string
    {
        if ($goalAlignment) {
            $text = 'Your goal of <strong>' . htmlspecialchars($goalAlignment['goalName']) . '</strong> lines up with your <strong>' . htmlspecialchars($goalAlignment['systemName']) . '</strong> priority.';
        } elseif ($topSystem !== null) {
            $text = 'Your top priority right now is <strong>' . htmlspecialchars($topSystem['name']) . '</strong>.';
        } else {
            return '';
        }

        return '
                <div class="dashboard-card dashboard-goal-alignment">
                    <div class="goal-alignment-icon">' . IconLibrary::render('TARGET') . '</div>
                    <p class="goal-alignment-text">' . $text . '</p>
                </div>';
    }

    private function renderHeatMap(array $heatMap): string
    {
        if (empty($heatMap)) {
            return '';
        }

        $cells = '';

        foreach ($heatMap as $cell) {

            $topClass = !empty($cell['top']) ? ' heat-cell-top' : '';
            $topTag = !empty($cell['top']) ? '<span class="heat-cell-tag">Top</span>' : '';
            $icon = $cell['id'] ? IconLibrary::render($cell['id']) : '';
            $score = (int) $cell['score'];

            $cells .= '
                        <div class="heat-cell' . $topClass . '">
                            <div class="heat-cell-head">
                                <div class="heat-cell-icon">' . $icon . '</div>
                                <div class="heat-cell-name">' . htmlspecialchars($cell['name']) . '</div>
                            </div>
                            <div class="heat-cell-score-row">
                                <span class="heat-cell-score">' . $score . '</span>
                                ' . $topTag . '
                            </div>
                            <div class="heat-cell-meter">
                                <div class="heat-cell-meter-fill" style="width:' . $score . '%"></div>
                            </div>
                        </div>';
        }

        return '
                <div class="dashboard-card dashboard-heatmap">
                    <div class="dashboard-card-label">Body Systems</div>
                    <p class="dashboard-card-caption">Each score is out of 100 — the higher it is, the more your answers suggest that system could benefit from dietary support.</p>
                    <div class="heat-grid">' . $cells . '
                    </div>
                </div>';
    }

    /**
     * A pure-SVG radar/spider chart (no JS) plotting every flagged body
     * system as an axis, purely as a visual companion to the heat grid
     * above — the heat grid remains the precise, readable reference, this
     * is the "shape of your results at a glance" picture.
     */
    // Matches ReportDataBuilder::buildBodySystems()'s own "Lower current
    // focus" band boundary, so the placeholder's positive framing and the
    // interpretation text elsewhere in the report never disagree with
    // each other about what counts as low.
    private const LOW_SCORE_THRESHOLD = 40;

    private function renderRadar(array $systems): string
    {
        $count = count($systems);

        // A radar needs a handful of axes to read as a shape rather than
        // a random polygon. Rather than just disappearing when there
        // aren't enough, show something that still fits "Shape of Your
        // Results": a full, rounded heart when the reason is genuinely
        // good news (nothing scored high enough to stand out), or a
        // single pin when it's the opposite reason — one sharp, specific
        // priority rather than too few to plot.
        if ($count < 3) {
            return $this->renderRadarPlaceholder($systems);
        }

        $size = 300;
        $center = $size / 2;
        $maxRadius = $center - 55; // leave generous room for edge labels
        $angleStep = (2 * M_PI) / $count;
        $startAngle = -M_PI / 2; // first axis points straight up

        $ringsSvg = '';

        foreach ([0.25, 0.5, 0.75, 1.0] as $ringFraction) {

            $points = [];

            for ($i = 0; $i < $count; $i++) {
                $angle = $startAngle + $i * $angleStep;
                $r = $maxRadius * $ringFraction;
                $points[] = round($center + $r * cos($angle), 1) . ',' . round($center + $r * sin($angle), 1);
            }

            $ringsSvg .= '<polygon class="radar-grid" points="' . implode(' ', $points) . '"/>';
        }

        $spokesSvg = '';
        $shapePoints = [];
        $labelsSvg = '';

        foreach ($systems as $i => $system) {

            $angle = $startAngle + $i * $angleStep;

            $ex = $center + $maxRadius * cos($angle);
            $ey = $center + $maxRadius * sin($angle);

            $spokesSvg .= '<line class="radar-spoke" x1="' . $center . '" y1="' . $center . '" x2="' . round($ex, 1) . '" y2="' . round($ey, 1) . '"/>';

            $scoreFraction = max(0, min(100, $system['score'])) / 100;
            $sx = $center + ($maxRadius * $scoreFraction) * cos($angle);
            $sy = $center + ($maxRadius * $scoreFraction) * sin($angle);

            $shapePoints[] = round($sx, 1) . ',' . round($sy, 1);

            // Labels sit just outside the outer ring, nudged so they don't
            // overlap the plot; short body-system names keep this simple.
            $lx = $center + ($maxRadius + 14) * cos($angle);
            $ly = $center + ($maxRadius + 14) * sin($angle) + 3;

            $anchor = 'middle';
            if (cos($angle) > 0.3) {
                $anchor = 'start';
            } elseif (cos($angle) < -0.3) {
                $anchor = 'end';
            }

            $shortName = str_replace(' System', '', $system['name']);

            $labelsSvg .= '<text class="radar-label" x="' . round($lx, 1) . '" y="' . round($ly, 1) . '" text-anchor="' . $anchor . '">' . htmlspecialchars($shortName) . '</text>';
        }

        $shapeSvg = '<polygon class="radar-shape" points="' . implode(' ', $shapePoints) . '"/>';

        $pointsSvg = '';
        foreach ($systems as $i => $system) {
            $angle = $startAngle + $i * $angleStep;
            $scoreFraction = max(0, min(100, $system['score'])) / 100;
            $sx = $center + ($maxRadius * $scoreFraction) * cos($angle);
            $sy = $center + ($maxRadius * $scoreFraction) * sin($angle);
            $pointsSvg .= '<circle class="radar-point" cx="' . round($sx, 1) . '" cy="' . round($sy, 1) . '" r="2.5"/>';
        }

        $svg = '<svg class="radar-svg" viewBox="0 0 ' . $size . ' ' . $size . '" xmlns="http://www.w3.org/2000/svg">'
            . $ringsSvg . $spokesSvg . $shapeSvg . $pointsSvg . $labelsSvg
            . '</svg>';

        return '
                <div class="dashboard-card dashboard-radar">
                    <div class="dashboard-card-label">The Shape of Your Results</div>
                    <p class="dashboard-card-caption">Every body system your answers have flagged, shown together.</p>
                    ' . $svg . '
                </div>';
    }

    private function renderRadarPlaceholder(array $systems): string
    {
        $topScore = 0;

        foreach ($systems as $system) {
            $topScore = max($topScore, (int) $system['score']);
        }

        // Nothing flagged at all, or what is flagged is genuinely mild —
        // there just isn't enough of a shape to plot, and that's good news.
        if (empty($systems) || $topScore < self::LOW_SCORE_THRESHOLD) {
            $icon = IconLibrary::render('SHAPE_GOOD', 'pwb-icon radar-placeholder-icon');
            $message = 'Great news — nothing in your results stood out as a strong priority, so there isn\'t much of a shape to draw yet. A well-rounded, low-key result is exactly what we\'d hope to see here.';
        } else {
            // Too few systems to plot a shape, but at least one of them
            // scored meaningfully — a single sharp priority rather than a
            // broad pattern, so the framing should stay neutral rather
            // than falsely reassuring.
            $icon = IconLibrary::render('SHAPE_FOCUSED', 'pwb-icon radar-placeholder-icon');
            $message = 'Your results are concentrated in one or two areas rather than spread across many — there isn\'t enough spread yet to draw a shape, but the detail below covers exactly where to focus.';
        }

        return '
                <div class="dashboard-card dashboard-radar dashboard-radar-placeholder">
                    <div class="dashboard-card-label">The Shape of Your Results</div>
                    <div class="radar-placeholder-icon-wrap">' . $icon . '</div>
                    <p class="radar-placeholder-message">' . htmlspecialchars($message) . '</p>
                </div>';
    }

    /**
     * The product strip — the single top-scoring product from
     * ProductResolver, already ranked and allergy/dietary-excluded for this
     * person by ReportDataBuilder. Deliberately singular (not a top-5 list
     * like Foods/Bioactives/Nutrients above): this is a recommendation, not
     * a leaderboard. Carries the compliance-framing intro paragraph
     * (approved copy, see ReportDataBuilder::buildDashboard()) directly
     * above the product itself, and the auto-generated "why this was
     * selected" sentence (buildProductWhySelected(), built from this
     * person's own matched mechanisms) rather than generic marketing copy.
     * Renders nothing if no product could be matched (e.g. every product
     * was excluded by the person's own allergies/dietary preferences) —
     * a false "no exclusions" strip would be worse than no strip at all.
     */
    private function renderProductStrip(?array $product, string $intro): string
    {
        if ($product === null) {
            return '';
        }

        $image = !empty($product['image'])
            ? '<img class="product-strip-image" src="../engine/assets/images/products/' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name'] ?? '') . '">'
            : '<div class="product-strip-image product-strip-image-placeholder"></div>';

        $metaParts = array_filter([
            $product['format'] ?? null,
            $product['servingSize'] ?? null,
            !empty($product['lastsDays']) ? 'Lasts ~' . (int) $product['lastsDays'] . ' days' : null,
        ]);

        $claims = '';
        foreach (array_slice($product['claims'] ?? [], 0, 4) as $claim) {
            $claims .= '
                                <li>
                                    <span class="product-strip-claim-icon">' . IconLibrary::render('CHECK', 'pwb-icon') . '</span>
                                    <span>' . htmlspecialchars($claim) . '</span>
                                </li>';
        }

        $why = !empty($product['whySelected'])
            ? '<p class="product-strip-why">' . htmlspecialchars($product['whySelected']) . '</p>'
            : '';

        return '
                <div class="dashboard-card dashboard-product-strip">
                    <div class="dashboard-card-label">Recommended For You</div>
                    <p class="product-strip-intro">' . htmlspecialchars($intro) . '</p>
                    <div class="product-strip-card">
                        <div class="product-strip-image-wrap">' . $image . '</div>
                        <div class="product-strip-body">
                            <div class="product-strip-name">' . htmlspecialchars($product['name'] ?? '') . '</div>
                            <div class="product-strip-meta">' . htmlspecialchars(implode(' · ', $metaParts)) . '</div>
                            ' . $why . '
                            <ul class="product-strip-claims">' . $claims . '
                            </ul>
                        </div>
                    </div>
                </div>';
    }

    private function renderMechanisms(array $mechanisms): string
    {
        if (empty($mechanisms)) {
            return '';
        }

        $rows = '';

        foreach ($mechanisms as $mechanism) {

            $rows .= '
                        <div class="bar-row">
                            <div class="bar-row-label">' . htmlspecialchars($mechanism['name']) . '</div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width:' . (int) $mechanism['percent'] . '%"></div>
                            </div>
                        </div>';
        }

        return '
                <div class="dashboard-card dashboard-mechanisms">
                    <div class="dashboard-card-label">Key Mechanisms</div>
                    <p class="dashboard-card-caption">The nutritional pathways doing the most work for your top priorities, ranked strongest first.</p>' . $rows . '
                </div>';
    }

    private function renderFoods(array $foods): string
    {
        if (empty($foods)) {
            return '';
        }

        $chips = '';

        foreach ($foods as $food) {

            $icon = !empty($food['foodGroupId']) ? IconLibrary::render($food['foodGroupId']) : '';

            $chips .= '
                        <div class="dash-food-chip">
                            <span class="dash-food-name"><span class="dash-food-icon">' . $icon . '</span>' . htmlspecialchars($food['name']) . '</span>
                            <span class="dash-food-score">' . (int) $food['score'] . '</span>
                        </div>';
        }

        return '
                <div class="dashboard-card dashboard-foods">
                    <div class="dashboard-card-label">Top Foods</div>
                    <p class="dashboard-card-caption">Scored out of 100 on how well each food matches your key mechanisms.</p>
                    <div class="dash-food-list">' . $chips . '
                    </div>
                </div>';
    }

    private function renderBioactives(array $bioactives): string
    {
        if (empty($bioactives)) {
            return '';
        }

        $rows = '';

        foreach ($bioactives as $bioactive) {

            $rows .= '
                        <div class="bar-row">
                            <div class="bar-row-label">' . htmlspecialchars($bioactive['name']) . '</div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width:' . (int) $bioactive['percent'] . '%"></div>
                            </div>
                        </div>';
        }

        return '
                <div class="dashboard-card dashboard-bioactives">
                    <div class="dashboard-card-label">Key Bioactives</div>
                    <p class="dashboard-card-caption">The plant compounds doing the most work behind your key mechanisms.</p>' . $rows . '
                </div>';
    }

    private function renderNutrients(array $nutrients): string
    {
        if (empty($nutrients)) {
            return '';
        }

        $rows = '';

        foreach ($nutrients as $nutrient) {

            $typeLabel = $nutrient['type'] === 'mineral' ? 'Mineral' : 'Vitamin';

            $rows .= '
                        <div class="bar-row">
                            <div class="bar-row-label">' . htmlspecialchars($nutrient['name']) . ' <span class="nutrient-type-tag">' . $typeLabel . '</span></div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width:' . (int) $nutrient['percent'] . '%"></div>
                            </div>
                        </div>';
        }

        return '
                <div class="dashboard-card dashboard-nutrients">
                    <div class="dashboard-card-label">Top Nutrients</div>
                    <p class="dashboard-card-caption">The vitamins &amp; minerals your answers point to most strongly.</p>' . $rows . '
                </div>';
    }

    private function renderActions(array $actions): string
    {
        if (empty($actions)) {
            return '';
        }

        $items = '';

        foreach ($actions as $action) {

            $items .= '
                        <div class="dash-action-item">
                            <span class="dash-action-check">' . IconLibrary::render('CHECK', 'pwb-icon') . '</span>
                            <span>' . htmlspecialchars($action) . '</span>
                        </div>';
        }

        return '
                <div class="dashboard-card dashboard-actions">
                    <div class="dashboard-card-label">This Week</div>
                    <p class="dashboard-card-caption">Simple starting points based on your results.</p>
                    <div class="dash-action-row">' . $items . '
                    </div>
                </div>';
    }
}
