<?php

declare(strict_types=1);

namespace PWB\Knowledge;

use RuntimeException;

class MarkdownParser
{
public function parse(string $markdown): array
{
    $header = $this->parseHeader($markdown);

    $sections = $this->parseSections($markdown);

    $mapping = $this->parseParserReference($markdown);

    return $this->buildFoodArray(
        $header,
        $sections,
        $mapping
    );
}
    private function parseHeader(string $markdown): array
    {
        return [
            'name' => $this->extractFoodName($markdown),
        
            'id' => $this->extractValue(
                $markdown,
                'Knowledge Base ID:'
            ),

            'scientificName' => trim(
                $this->extractValue(
                    $markdown,
                    'Scientific Name:'
                ),
                '*'
            ),

            'commonNames' => array_map(
                'trim',
                explode(
                    ',',
                    $this->extractValue(
                        $markdown,
                        'Common Names:'
                    )
                )
            ),

            'foodGroup' => $this->extractFoodGroup($markdown),

            'evidenceLevel' => $this->extractValue(
                $markdown,
                'Evidence Level:'
            ),

            'status' => $this->extractValue(
                $markdown,
                'Status:'
            ),
        ];
    }

    private function parseParserReference(string $markdown): array
    {
        return [
            'slug' => $this->extractValue(
                $markdown,
                'Slug:'
            ),

            'category' => $this->extractValue(
                $markdown,
                'Category:'
            ),
        ];
    }
    
    private function parseSections(string $markdown): array
{
    return [

        'overview' => $this->extractSection(
            $markdown,
            'Overview'
        ),

        'whyItMatters' => $this->extractSection(
            $markdown,
            'Why It Matters'
        ),

       'nutritionalHighlights' => $this->parseNutritionHighlights($markdown),

        'suitableFor' => $this->extractList(
            $this->extractSection(
                $markdown,
                'Suitable For'
            )
        ),

        'foodPairings' => $this->extractList(
            $this->extractSection(
                $markdown,
                'Food Pairings'
            )
        ),

        'references' => $this->extractList(
            $this->extractSection(
                $markdown,
                'References'
            )
        ),
        'practical' => $this->parsePracticalGuidance(
    $markdown
),

    ];
}
    
    private function extractSection(
    string $markdown,
    string $heading
): string
{
    $pattern =
        '/#\s+'
        . preg_quote($heading, '/')
        . '\R(.*?)(?=\R[-]{20,}|\R# |\z)/s';

    if (!preg_match($pattern, $markdown, $matches)) {

        throw new RuntimeException(
            "Unable to find section '{$heading}'."
        );

    }

    return trim($matches[1]);
}

private function extractList(
    string $text
): array
{
    $items = [];

    foreach (preg_split('/\R/', $text) as $line) {

        $line = trim($line);

        if (str_starts_with($line, '-')) {

            $items[] = trim(substr($line, 1));

        }

    }

    return $items;
}

private function extractKeyValuePairs(
    string $text
): array
{
    $values = [];

    foreach (preg_split('/\R/', $text) as $line) {

        $line = trim($line);

        if (
            $line === '' ||
            !str_contains($line, ':')
        ) {
            continue;
        }

        [$key, $value] = explode(
            ':',
            $line,
            2
        );

        $values[trim($key)] = trim($value);

    }

    return $values;
}

    private function extractValue(
        string $markdown,
        string $label
    ): string {

        if (
            preg_match(
                '/\*\*' . preg_quote($label, '/') . '\*\*\s*(.+)/',
                $markdown,
                $matches
            )
        ) {
            return trim(
                str_replace('\\', '', $matches[1])
            );
        }

        if (
            preg_match(
                '/^' . preg_quote($label, '/') . '\s*(.+)$/m',
                $markdown,
                $matches
            )
        ) {
            return trim($matches[1]);
        }

        throw new RuntimeException(
            "Unable to find '{$label}'"
        );
    }

    private function extractFoodGroup(
        string $markdown
    ): array {

        if (
            preg_match(
                '/Food Group:\*\*\s*(.+)\s+\((.+)\)/',
                $markdown,
                $matches
            )
        ) {

            return [
                'name' => trim($matches[1]),
                'id' => trim($matches[2]),
            ];

        }

        throw new RuntimeException(
            'Unable to parse Food Group.'
        );
    }

private function buildFoodArray(
    array $header,
    array $sections,
    array $mapping
): array {

    return [
    	'name' => $header['name'],

        'id' => $header['id'],

        'version' => '1.1',

        'identity' => [

            'name' => $header['name'],

            'scientificName' => $header['scientificName'],

            'slug' => $mapping['slug'],

            'commonNames' => $header['commonNames']

        ],

        'classification' => [

            'foodGroup' => $header['foodGroup']['id'],

            'category' => $mapping['category'],

            'plantBased' => true,

            'minimallyProcessed' => true

        ],
        'nutritionHighlights' => [

    'highIn' =>
        $sections['nutritionalHighlights']['highIn'],

    'contains' =>
        $sections['nutritionalHighlights']['contains'],

    'claims' =>
        $sections['nutritionalHighlights']['claims']

],

        'knowledge' => [

            'description' => $sections['overview']

        ],

        'metadata' => [

            'schemaVersion' => '1.1',

            'author' => 'Simon Bradley'

        ],

        'status' => [

            'draft' => false,

            'approved' => true,

            'published' => true

        ],
        'practical' => [

    'availability' => '',

    'seasonality' => '',

    'typicalCost' => '',

    'storage' =>
        $sections['practical']['storage'],

    'preparation' =>
        $sections['practical']['preparation'],

    'shoppingTips' =>
        $sections['practical']['shoppingTips']

],

    ];

}

private function extractFoodName(string $markdown): string
{
    if (
        preg_match(
            '/^#\s+FD\d+\s*-\s*(.+)$/m',
            $markdown,
            $matches
        )
    ) {
        return trim($matches[1]);
    }

    throw new RuntimeException(
        'Unable to determine food name.'
    );
}

private function parseNutritionHighlights(
    string $markdown
): array
{
    $section = $this->extractSection(
        $markdown,
        'Nutritional Highlights'
    );

    $highIn = [];
    $contains = [];
    $claims = [];

    $current = '';

    foreach (preg_split('/\R/', $section) as $line) {

        $line = trim($line);

        if ($line === '') {
            continue;
        }

        if (stripos($line, 'High In') === 0) {

            $current = 'highIn';
            continue;

        }

        if (stripos($line, 'Contains') === 0) {

            $current = 'contains';
            continue;

        }

        if (stripos($line, 'Claims') === 0) {

            $current = 'claims';
            continue;

        }

        if (!str_starts_with($line, '-')) {
            continue;
        }

        $value = trim(substr($line, 1));

        switch ($current) {

            case 'highIn':
                $highIn[] = $value;
                break;

            case 'contains':
                $contains[] = $value;
                break;

            case 'claims':
                $claims[] = $value;
                break;

        }

    }

    return [

        'highIn' => $highIn,

        'contains' => $contains,

        'claims' => $claims

    ];
}
 
private function parsePracticalGuidance(
    string $markdown
): array
{
    $section = $this->extractSection(
        $markdown,
        'Practical Guidance'
    );

    return [

        'buying' =>
            $this->extractSubSection(
                $section,
                'Buying'
            ),

        'storage' =>
            $this->extractSubSection(
                $section,
                'Storage'
            ),

        'preparation' =>
            $this->extractSubSection(
                $section,
                'Preparation'
            ),

        'shoppingTips' =>
            $this->extractSubSection(
                $section,
                'Shopping Tips'
            )

    ];
} 
 
private function extractSubSection(
    string $text,
    string $heading
): string
{
    $pattern =
        '/##\s+'
        . preg_quote($heading, '/')
        . '\R(.*?)(?=\R## |\z)/s';

    if (!preg_match($pattern, $text, $matches)) {

        return '';

    }

    return trim($matches[1]);
} 
   
}