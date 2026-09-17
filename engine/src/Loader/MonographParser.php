<?php

declare(strict_types=1);

namespace PWB\Loader;

/**
 * Parses a food monograph (knowledgebase/monographs/FDxxx-*.md) into the
 * same shape ReportDataBuilder/FoodSection expect from a food record:
 * identity, knowledge, tags, classification, supports.bodySystems.
 *
 * Monographs vary in how complete they are:
 *  - Most have a full "JSON Mapping (Parser Reference)" footer with Tags,
 *    Body Systems, etc. — parsed and used directly.
 *  - Some (roughly half, at the time this was written) have all the
 *    narrative sections but no JSON Mapping footer at all — tags and
 *    supported body systems come back empty for these, but the food's
 *    name/description/food group still resolve from the header block.
 *  - A handful (FD053-FD064) are short informal stub files with a
 *    different structure entirely (no "Knowledge Base ID" line, no
 *    "# Overview" heading) — best-effort fields only.
 *
 * A file that doesn't match the FDxxx-*.md naming (e.g. index.md) is not
 * a food record and parse() returns null for it.
 */
final class MonographParser
{
    public function parse(string $file): ?array
    {
        $basename = basename($file, '.md');

        if (!preg_match('/^(FD\d+)/i', $basename, $idMatch)) {
            return null;
        }

        $id = strtoupper($idMatch[1]);

        $text = file_get_contents($file);

        if ($text === false) {
            return null;
        }

        $title = $id;

        foreach (preg_split('/\r\n|\n/', $text) as $line) {
            if (preg_match('/^#\s+(.+)$/', trim($line), $titleMatch)) {
                $title = trim($titleMatch[1]);
                break;
            }
        }

        $title = preg_replace('/^' . preg_quote($id, '/') . '\s*-\s*/i', '', $title);

        $header = $this->extractHeaderFields($text);

        $description = $this->extractSection($text, 'Overview')
            ?? $this->extractSection($text, 'PWB relevance')
            ?? '';

        $activeCompounds = $this->extractSubheadings($text, 'Active Compounds');

        $mapping = $this->extractJsonMapping($text);

        return [
            'id' => $id,
            'identity' => [
                'name'           => $title !== '' ? $title : $id,
                'scientificName' => $header['scientificName'] ?? '',
                'commonNames'    => $header['commonNames'] ?? '',
            ],
            'knowledge' => [
                'description'     => $description,
                'activeCompounds' => $activeCompounds,
            ],
            'tags' => $mapping['tags'] ?? [],
            'classification' => [
                'foodGroup' => $header['foodGroupId']
                    ?? $header['foodGroupText']
                    ?? $mapping['category']
                    ?? '',
            ],
            'supports' => [
                'bodySystems' => $mapping['bodySystems'] ?? [],
            ],
            'evidenceLevel' => $header['evidenceLevel'] ?? '',
            'status'        => $mapping['status'] ?? [],
        ];
    }

    /**
     * Pulls the "**Label:** value" header block (Scientific Name, Common
     * Names, Food Group, Evidence Level), plus the differently-shaped
     * "- Scientific name: value" bullets used by the stub-format files.
     */
    private function extractHeaderFields(string $text): array
    {
        $fields = [];

        foreach (preg_split('/\r\n|\n/', $text) as $line) {

            $line = trim($line);

            if (preg_match('/^\*\*([^:*]+):\*\*\s*(.*)$/', $line, $m)) {

                $label = strtolower(trim($m[1]));
                $value = trim(rtrim(trim($m[2]), '\\'));
                $value = trim($value, '* ');

                switch ($label) {
                    case 'scientific name':
                        $fields['scientificName'] = $value;
                        break;
                    case 'common names':
                        $fields['commonNames'] = $value;
                        break;
                    case 'food group':
                        $this->applyFoodGroup($fields, $value);
                        break;
                    case 'evidence level':
                        $fields['evidenceLevel'] = $value;
                        break;
                }

                continue;
            }

            if (!isset($fields['scientificName']) && preg_match('/^-\s*Scientific name:\s*(.+)$/i', $line, $m)) {
                $fields['scientificName'] = trim(str_replace('*', '', $m[1]));
                continue;
            }

            if (!isset($fields['foodGroupText']) && preg_match('/^-\s*Food group:\s*(.+)$/i', $line, $m)) {
                $this->applyFoodGroup($fields, trim($m[1]));
            }
        }

        return $fields;
    }

    private function applyFoodGroup(array &$fields, string $value): void
    {
        if (preg_match('/^(.*?)\s*\((FG\d+)\)$/i', $value, $m)) {
            $fields['foodGroupText'] = trim($m[1]);
            $fields['foodGroupId']   = strtoupper($m[2]);
        } else {
            $fields['foodGroupText'] = $value;
        }
    }

    /**
     * Captures the body of a heading, stopping at the next heading of the
     * same or shallower level (so a "# Section" containing its own
     * "## Subsection" headings still captures those as part of its body),
     * or at a "------" divider line. With $toEndOfFile, keeps going to the
     * end of the file instead — used for the JSON Mapping footer, which
     * has no divider after it.
     *
     * Returns null when the heading isn't found at all, distinguishing
     * "section missing" from "section present but empty".
     */
    private function extractSection(string $text, string $heading, bool $toEndOfFile = false): ?string
    {
        $capturing = false;
        $level = 1;
        $buffer = [];

        foreach (preg_split('/\r\n|\n/', $text) as $line) {

            $trimmed = trim($line);

            if (!$capturing) {

                if (preg_match('/^(#{1,6})\s+' . preg_quote($heading, '/') . '\s*$/i', $trimmed, $m)) {
                    $capturing = true;
                    $level = strlen($m[1]);
                }

                continue;
            }

            if (!$toEndOfFile) {

                if (preg_match('/^(#{1,6})\s+/', $trimmed, $m) && strlen($m[1]) <= $level) {
                    break;
                }

                if (preg_match('/^-{5,}$/', $trimmed)) {
                    break;
                }
            }

            $buffer[] = $line;
        }

        if (!$capturing) {
            return null;
        }

        return trim(implode("\n", $buffer));
    }

    /**
     * Within a section, collects the text of every nested subheading
     * (e.g. the "## Anthocyanins" / "## Flavonoids" compound names under
     * "# Active Compounds").
     */
    private function extractSubheadings(string $text, string $heading): array
    {
        $section = $this->extractSection($text, $heading);

        if ($section === null) {
            return [];
        }

        $items = [];

        foreach (preg_split('/\r\n|\n/', $section) as $line) {
            if (preg_match('/^##\s+(.+)$/', trim($line), $m)) {
                $items[] = trim($m[1]);
            }
        }

        return $items;
    }

    private function extractJsonMapping(string $text): array
    {
        $section = $this->extractSection($text, 'JSON Mapping (Parser Reference)', true);

        if ($section === null) {
            return [];
        }

        $mapping = [
            'tags'        => $this->extractListAfterLabel($section, 'Tags'),
            'bodySystems' => $this->extractListAfterLabel($section, 'Body Systems'),
        ];

        if (preg_match('/^Category:\s*(.+)$/mi', $section, $m)) {
            $mapping['category'] = trim($m[1]);
        }

        $mapping['status'] = [
            'draft'     => $this->extractBool($section, 'Draft'),
            'approved'  => $this->extractBool($section, 'Approved'),
            'published' => $this->extractBool($section, 'Published'),
        ];

        return $mapping;
    }

    /**
     * "Tags:\n\n- TAG005\n- TAG006\n\n" style block: a label line, an
     * optional blank line, then "- value" bullets until the next blank
     * line (or a non-bullet line).
     */
    private function extractListAfterLabel(string $text, string $label): array
    {
        $collecting = false;
        $items = [];

        foreach (preg_split('/\r\n|\n/', $text) as $line) {

            $trimmed = trim($line);

            if (!$collecting) {
                if (strcasecmp($trimmed, $label . ':') === 0) {
                    $collecting = true;
                }
                continue;
            }

            if ($trimmed === '') {
                if (!empty($items)) {
                    break;
                }
                continue;
            }

            if (str_starts_with($trimmed, '-')) {
                $items[] = trim(substr($trimmed, 1));
            } else {
                break;
            }
        }

        return $items;
    }

    private function extractBool(string $text, string $label): ?bool
    {
        if (preg_match('/^' . preg_quote($label, '/') . ':\s*(true|false)\s*$/mi', $text, $m)) {
            return strtolower($m[1]) === 'true';
        }

        return null;
    }
}
