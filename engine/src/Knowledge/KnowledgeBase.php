<?php

declare(strict_types=1);

namespace PWB\Knowledge;

use RuntimeException;

final class KnowledgeBase
{
    private string $taxonomyPath;

    private array $foods = [];
    private array $bioactives = [];
    private array $mechanisms = [];
    private array $bodySystems = [];
    
    private const TAXONOMIES = [

    'foods' => [

        'file' => 'foods.json',

        'root' => 'foods'

    ],

    'bioactives' => [

        'file' => 'bioactives.json',

        'root' => 'bioactives'

    ],

    'mechanisms' => [

        'file' => 'mechanisms.json',

        'root' => 'mechanisms'

    ],

    'bodySystems' => [

        'file' => 'body-systems.json',

        'root' => 'bodySystems'

    ]

];

    public function __construct(?string $taxonomyPath = null)
    {
        $this->taxonomyPath = $taxonomyPath
            ?? dirname(__DIR__, 3) . '/knowledgebase/taxonomy';

        $this->load();
    }

    private function load(): void
    {
   	 foreach (self::TAXONOMIES as $property => $taxonomy) {

        $this->{$property} = $this->loadIndex(

            $taxonomy['file'],

            $taxonomy['root']

        );
   	 }
    }


    private function loadIndex(string $file, string $rootKey): array
    {
        $path = $this->taxonomyPath . '/' . $file;

        if (!file_exists($path)) {
            throw new RuntimeException("Knowledge file not found: {$path}");
        }

        $json = json_decode(file_get_contents($path), true);

        if (!is_array($json)) {
            throw new RuntimeException("Invalid JSON: {$file}");
        }

        if (!isset($json[$rootKey]) || !is_array($json[$rootKey])) {
            throw new RuntimeException(
                "Missing '{$rootKey}' in {$file}"
            );
        }

        $index = [];

        foreach ($json[$rootKey] as $record) {

            if (!isset($record['id'])) {
                continue;
            }

            $index[$record['id']] = $record;
        }

        return $index;
    }

    // ---------------------------------------------------------------------
    // Exists
    // ---------------------------------------------------------------------

    public function foodExists(string $id): bool
    {
        return isset($this->foods[$id]);
    }

    public function bioactiveExists(string $id): bool
    {
        return isset($this->bioactives[$id]);
    }

    public function mechanismExists(string $id): bool
    {
        return isset($this->mechanisms[$id]);
    }

    public function bodySystemExists(string $id): bool
    {
        return isset($this->bodySystems[$id]);
    }

    // ---------------------------------------------------------------------
    // Get One
    // ---------------------------------------------------------------------

    public function food(string $id): ?array
    {
        return $this->foods[$id] ?? null;
    }

    public function bioactive(string $id): ?array
    {
        return $this->bioactives[$id] ?? null;
    }

    public function mechanism(string $id): ?array
    {
        return $this->mechanisms[$id] ?? null;
    }

    public function bodySystem(string $id): ?array
    {
        return $this->bodySystems[$id] ?? null;
    }

    // ---------------------------------------------------------------------
    // Get All
    // ---------------------------------------------------------------------

    public function foods(): array
    {
        return $this->foods;
    }

    public function bioactives(): array
    {
        return $this->bioactives;
    }

    public function mechanisms(): array
    {
        return $this->mechanisms;
    }

    public function bodySystems(): array
    {
        return $this->bodySystems;
    }
}