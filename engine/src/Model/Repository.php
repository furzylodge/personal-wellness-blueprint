<?php

declare(strict_types=1);

namespace PWB\Model;

class Repository
{
    private array $foods = [];
    private array $monographs = [];
    private array $taxonomies = [];
    private array $schemas = [];

    /**
     * Add a food file.
     */
    public function addFood(string $file): void
    {
        $this->foods[] = $file;
    }

    /**
     * Add a monograph file.
     */
    public function addMonograph(string $file): void
    {
        $this->monographs[] = $file;
    }

    /**
     * Add a taxonomy file.
     */
    public function addTaxonomy(string $file): void
    {
        $this->taxonomies[] = $file;
    }

    /**
     * Add a schema file.
     */
    public function addSchema(string $file): void
    {
        $this->schemas[] = $file;
    }

    /**
     * Get all food files.
     */
    public function getFoods(): array
    {
        return $this->foods;
    }

    /**
     * Get all monograph files.
     */
    public function getMonographs(): array
    {
        return $this->monographs;
    }

    /**
     * Get all taxonomy files.
     */
    public function getTaxonomies(): array
    {
        return $this->taxonomies;
    }

    /**
     * Get all schema files.
     */
    public function getSchemas(): array
    {
        return $this->schemas;
    }

    /**
     * Number of food files.
     */
    public function countFoods(): int
    {
        return count($this->foods);
    }

    /**
     * Number of monograph files.
     */
    public function countMonographs(): int
    {
        return count($this->monographs);
    }

    /**
     * Number of taxonomy files.
     */
    public function countTaxonomies(): int
    {
        return count($this->taxonomies);
    }

    /**
     * Number of schema files.
     */
    public function countSchemas(): int
    {
        return count($this->schemas);
    }

    /**
     * Total number of repository files.
     */
    public function getTotalFiles(): int
    {
        return
            $this->countFoods()
            + $this->countMonographs()
            + $this->countTaxonomies()
            + $this->countSchemas();
    }
}