<?php

declare(strict_types=1);

namespace PWB\Reports\Sections;
use PWB\Reports\Components\MechanismPopover;

/**
 * "Recommended Products" — sits directly beneath Priority Bioactives,
 * deliberately styled as the same card grid (see BioactiveSection) so the
 * two read as one family: a score badge, a short summary, and a "Key
 * Nutritional Pathways" mechanism-chip row built from this person's own
 * matched mechanisms (ReportDataBuilder::buildProductMechanisms(), which
 * mirrors enrichBioactives() exactly so MechanismPopover needs no changes).
 *
 * Two differences from the bioactive card, both deliberate (Simon's
 * request): no "Best food sources" row — a product isn't a food source of
 * itself — and the "More about" expander carries a factsheet download link
 * alongside the overview text rather than just prose, since a product (and
 * only a product) has real product literature to link out to.
 *
 * Only the top 3 already-ranked, already allergy/dietary-excluded products
 * from ProductResolver — the single top pick gets its own fuller
 * treatment in the dashboard's product strip (DashboardSection), so this
 * section is the "next few worth knowing about" rather than a duplicate of
 * that recommendation.
 */
class ProductSection
{
    public function render(array $reportData): string
    {
        $products = array_slice($reportData['products'] ?? [], 0, 3);

        if (empty($products)) {
            return '';
        }

        $html = '
        <section class="report-section">

            <h2>Recommended Products</h2>

            <p class="section-intro">
                Based on your own results, these Synergy products target the nutritional pathways scored highest for you.
            </p>

            <div class="bioactive-grid">';

        foreach ($products as $product) {
            $html .= $this->renderProductCard($product);
        }

        $html .= '
            </div>
        </section>';

        return $html;
    }

    private function renderProductCard(array $product): string
    {
        $name = htmlspecialchars($product['name'] ?? 'Unknown');
        $score = (int) ($product['reportScore'] ?? 0);
        $category = htmlspecialchars($product['category'] ?? '');
        $description = htmlspecialchars($product['description'] ?? '');

        $summary = $description !== ''
            ? $description
            : 'A Synergy product matched against your own nutritional pathways.';

        $html = '
                <div class="bioactive-card">

<div class="bioactive-header">

    <div class="bioactive-title">
        <div class="card-title-row">
            <h3>' . $name . '</h3>';

        if ($category !== '') {
            $html .= '
            <span class="card-subtitle">' . $category . '</span>';
        }

        $html .= '
        </div>
    </div>

    <div class="score-badge">
        ' . $score . '
        <span class="score-label">/100</span>
    </div>

</div>

                    <p class="bioactive-summary">' . $summary . '</p>';

        // Supporting mechanisms — same chip treatment as bioactives, built
        // from this person's own matched mechanisms
        // (ReportDataBuilder::buildProductMechanisms()), never generic
        // marketing copy.
        if (!empty($product['mechanisms'])) {

            $html .= '
        <div class="body-label">
            KEY NUTRITIONAL PATHWAYS
        </div>

        <div class="mechanism-chip-row">';

            foreach (array_slice($product['mechanisms'], 0, 3) as $m) {
                $html .= MechanismPopover::render($m, 'product-' . $name);
            }

            $html .= '</div>';
        }

        // "More about" — overview text plus (when supplied) a factsheet
        // download link. Deliberately always offered for products, unlike
        // the bioactive card's "only if there's more to say" condition:
        // the factsheet link alone is worth surfacing even when the
        // overview text matches the summary above.
        $factsheetUrl = $product['factsheetUrl'] ?? '';

        $factsheetLink = !empty($factsheetUrl)
            ? '<a class="factsheet-link" href="' . htmlspecialchars($factsheetUrl) . '" target="_blank" rel="noopener">Download factsheet</a>'
            : '';

        $html .= '
                    <details class="bioactive-more">
                        <summary>More about ' . $name . ' <span class="expand-chevron">&#9662;</span></summary>
                        <p>' . $summary . '</p>' . $factsheetLink . '
                    </details>';

        $html .= '
                </div>';

        return $html;
    }
}
