<?php

declare(strict_types=1);

namespace PWB\Scanner;

use PWB\Model\Repository;
use PWB\Utils\FileLocator;

class RepositoryScanner
{
    private FileLocator $locator;

    public function __construct(FileLocator $locator)
    {
        $this->locator = $locator;
    }

    public function scan(): Repository
    {
        $repository = new Repository();

        foreach (glob($this->locator->getFoodDirectory() . DIRECTORY_SEPARATOR . '*.json') as $file) {
            $repository->addFood($file);
        }

        foreach (glob($this->locator->getMonographDirectory() . DIRECTORY_SEPARATOR . '*.md') as $file) {
            $repository->addMonograph($file);
        }

        foreach (glob($this->locator->getTaxonomyDirectory() . DIRECTORY_SEPARATOR . '*.json') as $file) {
            $repository->addTaxonomy($file);
        }

        foreach (glob($this->locator->getSchemaDirectory() . DIRECTORY_SEPARATOR . '*.json') as $file) {
            $repository->addSchema($file);
        }

        return $repository;
    }
}