<?php

declare(strict_types=1);

namespace PWB\Reports;

/**
 * A small set of original, hand-authored line icons (24x24, stroke-based,
 * "currentColor") for the 9 body systems and 9 wellness outcomes/goals used
 * in the dashboard. Deliberately abstract/geometric rather than literal
 * clinical anatomy — the goal is a consistent, tasteful icon family, not
 * medical illustration, and every path here is original (not copied from
 * any icon set).
 *
 * Usage: IconLibrary::render('BS006') or IconLibrary::render('OUT001').
 * Returns '' for an unknown id so callers can render safely without an
 * isset() check first.
 */
final class IconLibrary
{
    private const STROKE = 'fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"';

    public static function render(string $id, string $class = 'pwb-icon'): string
    {
        $inner = self::paths()[$id] ?? null;

        if ($inner === null) {
            return '';
        }

        return '<svg class="' . htmlspecialchars($class) . '" viewBox="0 0 24 24" ' . self::STROKE . ' aria-hidden="true">' . $inner . '</svg>';
    }

    /**
     * @return array<string,string> id => inner SVG markup (paths/shapes only, no outer <svg>)
     */
    private static function paths(): array
    {
        return [

            // ---------------- Body systems ----------------

            // Digestive — a simple rounded pouch (stomach) with an entry line
            'BS001' => '<path d="M10 3v4.5"/><path d="M10 7.5c-3 0-5.5 2.6-5.5 6S6.7 19 10.2 19c3.6 0 6.3-2.1 6.3-5 0-2.2-1.6-3.3-3.3-3.7-1.5-.4-2.2-1.2-2.2-2.8Z"/>',

            // Intestinal — a coiled spiral
            'BS002' => '<path d="M12 4c4 0 7 2.6 7 6.2 0 3-2.2 4.8-5 4.8-2.2 0-3.8-1.3-3.8-3.3 0-1.6 1.1-2.5 2.6-2.5 1.2 0 2 .8 2 1.9"/><path d="M12 4c-4.4 0-8 3.3-8 8s3.6 8 8.4 8"/>',

            // Circulatory — a simple heart outline
            'BS003' => '<path d="M12 20.2S3.8 15.3 3.8 9.4C3.8 6.4 6 4.6 8.3 4.6c1.6 0 3 .9 3.7 2.2.7-1.3 2.1-2.2 3.7-2.2 2.3 0 4.5 1.8 4.5 4.8 0 5.9-8.2 10.8-8.2 10.8Z"/>',

            // Nervous — a neuron: a cell body with radiating signal lines
            'BS004' => '<circle cx="12" cy="12" r="3"/><path d="M15 12h4"/><path d="M13.5 14.6l2 3.46"/><path d="M10.5 14.6l-2 3.46"/><path d="M9 12H5"/><path d="M10.5 9.4l-2-3.46"/><path d="M13.5 9.4l2-3.46"/>',

            // Immune — a shield
            'BS005' => '<path d="M12 3.4l6.8 2.5v5.3c0 4.6-2.9 7.6-6.8 9.4-3.9-1.8-6.8-4.8-6.8-9.4V5.9Z"/><path d="M9.2 12.1l1.9 1.9 3.7-3.9"/>',

            // Respiratory — trachea branching into two lung lobes
            'BS006' => '<path d="M12 4v5"/><path d="M12 9 8.5 12"/><path d="M12 9l3.5 3"/><ellipse cx="8" cy="15.6" rx="3.1" ry="4.2"/><ellipse cx="16" cy="15.6" rx="3.1" ry="4.2"/>',

            // Urinary — a droplet
            'BS007' => '<path d="M12 3.6s5.4 6.4 5.4 10.4a5.4 5.4 0 1 1-10.8 0C6.6 10 12 3.6 12 3.6Z"/>',

            // Glandular — a hexagon (node) with a small core
            'BS008' => '<path d="M12 3.6l6.2 3.6v7.2L12 18l-6.2-3.6V7.2Z"/><circle cx="12" cy="10.8" r="2.1"/>',

            // Structural — an abstract spine/frame
            'BS009' => '<path d="M12 4v16"/><path d="M9 6.5h6"/><path d="M8.5 10h7"/><path d="M8 13.5h8"/><path d="M8.5 17h7"/>',

            // ---------------- Outcomes / goals ----------------

            // Better Sleep — crescent moon
            'OUT001' => '<path d="M15.5 4.3a8 8 0 1 0 4.2 14.9A9.3 9.3 0 0 1 12.6 4c.97 0 1.94.1 2.9.3Z"/>',

            // More Energy — lightning bolt
            'OUT002' => '<path d="M13.2 3.6 6.4 13.4h4.4l-1 7 7.8-10.6h-4.6Z"/>',

            // Healthy Weight Management — balance scale
            'OUT003' => '<path d="M12 4v16"/><path d="M8 20h8"/><path d="M4.5 8h7"/><path d="M12.5 8h7"/><path d="M4.5 8 2.5 12.4a2.5 2.5 0 0 0 4.9 0Z"/><path d="M19.5 8l-2 4.4a2.5 2.5 0 0 0 4.9 0Z"/>',

            // Better Fitness & Physical Performance — dumbbell
            'OUT004' => '<path d="M6 9v6"/><path d="M18 9v6"/><path d="M3.6 10.4v3.2"/><path d="M20.4 10.4v3.2"/><path d="M8 12h8"/>',

            // Digestive & Gut Health — leaf
            'OUT005' => '<path d="M5 19c8.3 0 13-4.9 13-13.5C9.4 5.5 5 10.4 5 19Z"/><path d="M5 19c1-3.6 3-6.4 6.8-9"/>',

            // Mental Clarity & Performance Sharpness — lightbulb
            'OUT006' => '<path d="M9 17.5h6"/><path d="M9.5 20.2h5"/><path d="M12 4a5.6 5.6 0 0 0-3 10.4c.6.4.9 1 .9 1.7v.4h4.2v-.4c0-.7.3-1.3.9-1.7A5.6 5.6 0 0 0 12 4Z"/>',

            // Mobility & Joint Health — hinge/joint
            'OUT007' => '<path d="M4.5 19.5 10 14"/><path d="M14 10l5.5-5.5"/><circle cx="12" cy="12" r="2.3"/>',

            // Healthy Ageing & Resilience — hourglass
            'OUT008' => '<path d="M6 3.6h12"/><path d="M6 20.4h12"/><path d="M7.6 3.6Q11 8 12 11"/><path d="M12 13Q11 16 7.6 20.4"/><path d="M16.4 3.6Q13 8 12 11"/><path d="M12 13Q13 16 16.4 20.4"/>',

            // Menopause Transition Resilience — butterfly (renewal/transition)
            'OUT009' => '<path d="M12 6.4v11.2"/><path d="M12 8c-1-3-3.4-4.4-5.4-3.8-2 .6-2.6 3-1.3 4.8C6.5 10.8 9 11.4 12 10.6"/><path d="M12 8c1-3 3.4-4.4 5.4-3.8 2 .6 2.6 3 1.3 4.8-1.2 1.8-3.7 2.4-6.7 1.6"/><path d="M12 12.4c-.9 2.7-3 3.9-4.8 3.4-1.8-.5-2.4-2.6-1.2-4.2 1-1.5 3.2-2.1 6-1.4"/><path d="M12 12.4c.9 2.7 3 3.9 4.8 3.4 1.8-.5 2.4-2.6 1.2-4.2-1-1.5-3.2-2.1-6-1.4"/>',

            // Heart & Cardiovascular Health — heartbeat/pulse line
            'OUT010' => '<path d="M3 12h4l2-6 3 12 2-9 2 3h5"/>',

            // Immune Resilience — shield with a repair/resilience cross
            // (deliberately echoes BS005's shield, but with a cross rather
            // than a checkmark, to read as related but distinct)
            'OUT011' => '<path d="M12 3.4l6.8 2.5v5.3c0 4.6-2.9 7.6-6.8 9.4-3.9-1.8-6.8-4.8-6.8-9.4V5.9Z"/><path d="M12 9v6"/><path d="M9 12h6"/>',

            // Sexual Health & Libido — two interlocking rings (connection/intimacy)
            'OUT012' => '<circle cx="9.5" cy="12" r="5"/><circle cx="14.5" cy="12" r="5"/>',

            // ---------------- Dashboard radar placeholders ----------------
            // Shown in place of the radar/spider chart when too few body
            // systems are flagged to plot a meaningful shape (see
            // DashboardSection::renderRadar). Same heart glyph as BS003,
            // reused deliberately: a full, even, well-rounded shape is the
            // visual point when there's nothing sharp enough to plot.
            'SHAPE_GOOD' => '<path d="M12 20.2S3.8 15.3 3.8 9.4C3.8 6.4 6 4.6 8.3 4.6c1.6 0 3 .9 3.7 2.2.7-1.3 2.1-2.2 3.7-2.2 2.3 0 4.5 1.8 4.5 4.8 0 5.9-8.2 10.8-8.2 10.8Z"/>',

            // A single location pin — the opposite idea: one sharp, specific
            // point rather than a broad, even shape.
            'SHAPE_FOCUSED' => '<path d="M12 3.6c3.6 0 6.4 2.8 6.4 6.4 0 4.8-6.4 10.4-6.4 10.4S5.6 14.8 5.6 10c0-3.6 2.8-6.4 6.4-6.4Z"/><circle cx="12" cy="10" r="2.2"/>',

            // A simple check mark, used for small "done/step" indicators
            // (e.g. the dashboard's quick-actions list) — not tied to any
            // body system or outcome.
            'CHECK' => '<circle cx="12" cy="12" r="8.6"/><path d="M8.4 12.4l2.4 2.4 4.8-5.6"/>',

            // A simple bullseye/target — used for the dashboard's goal
            // alignment card ("your goal of X lines up with your Y
            // priority") to visually echo the idea of a goal being hit,
            // not tied to any body system or outcome.
            'TARGET' => '<circle cx="12" cy="12" r="8.4"/><circle cx="12" cy="12" r="5.2"/><circle cx="12" cy="12" r="1.6" fill="currentColor" stroke="none"/>',

            // ---------------- Food groups ----------------

            // Wholegrains — a stalk of grain
            'FG001' => '<path d="M12 20V6"/><path d="M12 8l-3-2"/><path d="M12 8l3-2"/><path d="M12 12l-3-2"/><path d="M12 12l3-2"/><path d="M12 16l-3-2"/><path d="M12 16l3-2"/>',

            // Vegetables — a two-leaf sprout
            'FG002' => '<path d="M12 20c-5 0-8-3-8-8 5 0 8 3 8 8Z"/><path d="M12 20c5 0 8-3 8-8-5 0-8 3-8 8Z"/><path d="M12 20V9"/>',

            // Fruit — an apple with a stem/leaf
            'FG003' => '<path d="M12 9c-3-2.6-7-1-7 3.4C5 17 8 20 10.4 20c.9 0 1.1-.4 1.6-.4s.7.4 1.6.4C16 20 19 17 19 12.4 19 8 15 6.4 12 9Z"/><path d="M12 9V6.5"/><path d="M12 6.5c0-1.2.8-2 2-2"/>',

            // Beans & Legumes — a pod with peas
            'FG004' => '<path d="M5 15c0-6 4-10 10-10 2 0 4 2 4 4 0 6-4 10-10 10-2 0-4-2-4-4Z"/><circle cx="10" cy="13" r="1"/><circle cx="13" cy="10" r="1"/><circle cx="16" cy="7.5" r="1"/>',

            // Nuts & Seeds — an acorn
            'FG005' => '<path d="M12 11c2.8 0 4.5 1.8 4.5 4.6 0 3-2 6-4.5 6s-4.5-3-4.5-6C7.5 12.8 9.2 11 12 11Z"/><path d="M8 10c0-2.5 1.8-4.4 4-4.4s4 1.9 4 4.4"/><path d="M7.5 9.4h9"/>',

            // Herbs & Spices — a sprig of leaves
            'FG006' => '<path d="M12 20V8"/><path d="M12 10c-2-2-2-4-1-6 2 1 3 3 1 6Z"/><path d="M12 14c2-1.4 4-1 5 .4-1.6 1.6-3.6 1.6-5-.4Z"/><path d="M12 14c-2-1.4-4-1-5 .4 1.6 1.6 3.6 1.6 5-.4Z"/>',

            // Fish & Seafood — a fish
            'FG007' => '<path d="M3 12c3.5-4 8.5-5.6 12-3.6 2 1.1 3.6 2.2 3.6 3.6s-1.6 2.5-3.6 3.6c-3.5 2-8.5.4-12-3.6Z"/><path d="M18.6 10.4 21 8.2"/><path d="M18.6 13.6 21 15.8"/><circle cx="8.6" cy="12" r=".5"/>',

            // Meat & Poultry — a drumstick silhouette
            'FG008' => '<path d="M15 4c2.4 0 4.4 2 4.4 4.4 0 3-2.4 5.2-5.6 6.4-1 2-2.4 3.6-4 4.6a2 2 0 1 1-2.2-2.2c1-1.6 2.6-3 4.6-4C10.8 11 9.6 9 9.6 6.6 9.6 4.4 12 4 15 4Z"/>',

            // Eggs
            'FG009' => '<ellipse cx="12" cy="13" rx="5.4" ry="7" transform="rotate(-8 12 13)"/>',

            // Dairy — a milk carton/glass
            'FG010' => '<path d="M9 4h6l.6 3.4H8.4Z"/><path d="M8.4 7.4h7.2L16.4 20H7.6Z"/><path d="M8.7 11h6.6"/>',

            // Fermented Foods — a jar with bubbles
            'FG011' => '<path d="M8 8h8v10a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2Z"/><path d="M8.6 8V5.6A1.6 1.6 0 0 1 10.2 4h3.6a1.6 1.6 0 0 1 1.6 1.6V8"/><circle cx="10.6" cy="13" r=".6"/><circle cx="13.4" cy="15.4" r=".6"/><circle cx="11.4" cy="17" r=".6"/>',

            // Oils & Fats — a bottle
            'FG012' => '<path d="M10.4 3.6h3.2v3l1.6 2v10a2 2 0 0 1-2 2h-2.4a2 2 0 0 1-2-2v-10l1.6-2Z"/><path d="M12 13.4c.9 0 1.6.7 1.6 1.6s-.7 1.6-1.6 1.6-1.6-.7-1.6-1.6.7-1.6 1.6-1.6Z"/>',

            // Plant-Based Alternatives — a sprout inside a circle
            'FG013' => '<circle cx="12" cy="12" r="8"/><path d="M12 16c-2.6 0-4.4-1.8-4.4-4.4 2.6 0 4.4 1.8 4.4 4.4Z"/><path d="M12 16c2.6 0 4.4-1.8 4.4-4.4-2.6 0-4.4 1.8-4.4 4.4Z"/><path d="M12 16V9.6"/>',

            // Sea Vegetables — wavy seaweed strands
            'FG014' => '<path d="M4 20c1-4-1-6 0-10s3.4-4 3-8"/><path d="M9 20c1-4-1-6 0-10s3.4-4 3-8"/><path d="M14 20c1-4-1-6 0-10s3.4-4 3-8"/>',

            // Beverages — a cup with steam
            'FG015' => '<path d="M6 9h10v6a4 4 0 0 1-4 4H10a4 4 0 0 1-4-4Z"/><path d="M16 10.5h1.6a2.2 2.2 0 0 1 0 4.4H16"/><path d="M9 5.4c0 1-1 1-1 2"/><path d="M12.5 5.4c0 1-1 1-1 2"/>',

            // Algae & Superfoods — a sparkle burst
            'FG016' => '<path d="M12 3.6l1.3 5 5 1.3-5 1.3-1.3 5-1.3-5-5-1.3 5-1.3Z"/><path d="M18.4 15.6l.6 2 2 .6-2 .6-.6 2-.6-2-2-.6 2-.6Z"/>',
        ];
    }
}
