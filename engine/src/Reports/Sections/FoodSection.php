<?php

declare(strict_types=1);

namespace PWB\Reports\Sections;

class FoodSection
{
    public function render(array $reportData): string
    {
        $foods = $reportData['foods'] ?? [];

        $html = <<<HTML

<h2>Recommended Foods</h2>

HTML;

        if (count($foods) === 0) {

            $html .= "<p>No food recommendations available.</p>";

            return $html;
        }

        foreach ($foods as $food) {

            $name =
                $food['identity']['name']
                ?? 'Unknown';

            $scientificName =
                $food['identity']['scientificName']
                ?? '';

            $category =
                $food['classification']['category']
                ?? '';

            $foodGroup =
                $food['classification']['foodGroup']
                ?? '';

            $description =
                $food['knowledge']['description']
                ?? '';

            $recommendedFor =
                $food['personalisation']['recommendedFor']
                ?? [];

            $goalsSupported =
                $food['personalisation']['goalsSupported']
                ?? [];

            $activeCompounds =
                $food['knowledge']['activeCompounds']
                ?? [];

            $preparation =
                $food['practical']['preparation']
                ?? '';

            $tags =
                $food['tags']
                ?? [];

            $html .= <<<HTML

<div class="food">

<h3>{$name}</h3>

<p><strong>Scientific Name:</strong> <em>{$scientificName}</em></p>

<p><strong>Category:</strong> {$category}</p>

<p><strong>Food Group:</strong> {$foodGroup}</p>

<p>{$description}</p>

HTML;

            if (!empty($recommendedFor)) {

                $html .= "<h4>Recommended For</h4>";
                $html .= "<ul>";

                foreach ($recommendedFor as $item) {
                    $html .= "<li>{$item}</li>";
                }

                $html .= "</ul>";

            }

            if (!empty($goalsSupported)) {

                $html .= "<h4>Goals Supported</h4>";
                $html .= "<ul>";

                foreach ($goalsSupported as $item) {
                    $html .= "<li>{$item}</li>";
                }

                $html .= "</ul>";

            }

            if (!empty($activeCompounds)) {

                $html .= "<h4>Key Active Compounds</h4>";
                $html .= "<ul>";

                foreach ($activeCompounds as $item) {
                    $html .= "<li>{$item}</li>";
                }

                $html .= "</ul>";

            }

            if ($preparation !== '') {

                $html .= <<<HTML

<h4>Preparation</h4>

<p>{$preparation}</p>

HTML;

            }

            if (!empty($tags)) {

                $html .= "<h4>Tags</h4>";
                $html .= "<ul>";

                foreach ($tags as $tag) {
                    $html .= "<li>{$tag}</li>";
                }

                $html .= "</ul>";

            }

            $html .= <<<HTML

</div>

<hr>

HTML;

        }

        return $html;
    }
}