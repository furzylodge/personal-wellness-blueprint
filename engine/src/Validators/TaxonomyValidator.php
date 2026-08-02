<?php

declare(strict_types=1);

namespace PWB\Validators;

use PWB\Model\Repository;
use PWB\Loader\JsonLoader;
use PWB\Utils\FileLocator;
use PWB\Validation\ValidationIssue;
use PWB\Validation\ValidationResult;
use PWB\Validation\ValidatorInterface;

class TaxonomyValidator implements ValidatorInterface
{
    private const NAME = 'TaxonomyValidator';

    public function __construct(
        private Repository $repository,
        private JsonLoader $loader,
        private FileLocator $locator
    ) {
    }

    public function validate(): ValidationResult
    {
        $result = new ValidationResult();

        $this->validateTags($result);

        // Add these as the taxonomy files are created
        // $this->validateFoodGroups($result);
        // $this->validateAllergens($result);
        // $this->validateBodySystems($result);
        // $this->validateVitamins($result);
        // $this->validateMinerals($result);
        // $this->validateHealthGoals($result);

        return $result;
    }

    /**
     * Validate food tags against taxonomy/tags.json
     */
    private function validateTags(ValidationResult $result): void
    {
        $this->validateTaxonomyField(
            taxonomyFile: 'tags.json',
            taxonomyArray: 'tags',
            foodField: 'tags',
            result: $result
        );
    }

    /**
     * Future validator
     */
    private function validateFoodGroups(ValidationResult $result): void
    {
        $this->validateTaxonomyField(
            taxonomyFile: 'food-groups.json',
            taxonomyArray: 'foodGroups',
            foodField: 'foodGroup',
            result: $result
        );
    }

    /**
     * Future validator
     */
    private function validateAllergens(ValidationResult $result): void
    {
        $this->validateTaxonomyField(
            taxonomyFile: 'allergens.json',
            taxonomyArray: 'allergens',
            foodField: 'allergens',
            result: $result
        );
    }

    /**
     * Future validator
     */
    private function validateBodySystems(ValidationResult $result): void
    {
        $this->validateTaxonomyField(
            taxonomyFile: 'body-systems.json',
            taxonomyArray: 'bodySystems',
            foodField: 'bodySystems',
            result: $result
        );
    }

    /**
     * Future validator
     */
    private function validateVitamins(ValidationResult $result): void
    {
        $this->validateTaxonomyField(
            taxonomyFile: 'vitamins.json',
            taxonomyArray: 'vitamins',
            foodField: 'vitamins',
            result: $result
        );
    }

    /**
     * Future validator
     */
    private function validateMinerals(ValidationResult $result): void
    {
        $this->validateTaxonomyField(
            taxonomyFile: 'minerals.json',
            taxonomyArray: 'minerals',
            foodField: 'minerals',
            result: $result
        );
    }

    /**
     * Generic taxonomy validator.
     */
    private function validateTaxonomyField(
        string $taxonomyFile,
        string $taxonomyArray,
        string $foodField,
        ValidationResult $result
    ): void {

        $file =
            $this->locator->getTaxonomyDirectory()
            . DIRECTORY_SEPARATOR
            . $taxonomyFile;

        if (!file_exists($file)) {

            $result->addIssue(
                new ValidationIssue(
                    validator: self::NAME,
                    severity: ValidationIssue::ERROR,
                    file: $file,
                    message: "Taxonomy file '{$taxonomyFile}' not found."
                )
            );

            return;
        }

        $taxonomy = $this->loader->load($file);

        if (
            !isset($taxonomy[$taxonomyArray]) ||
            !is_array($taxonomy[$taxonomyArray])
        ) {
            return;
        }

        $validIds = [];

        foreach ($taxonomy[$taxonomyArray] as $item) {

            if (isset($item['id'])) {
                $validIds[] = $item['id'];
            }

        }

        foreach ($this->repository->getFoods() as $foodFile) {

            $food = $this->loader->load($foodFile);

            if (!isset($food[$foodField])) {
                continue;
            }

            if (!is_array($food[$foodField])) {

                $result->addIssue(
                    new ValidationIssue(
                        validator: self::NAME,
                        severity: ValidationIssue::ERROR,
                        file: $foodFile,
                        message: "'{$foodField}' must be an array."
                    )
                );

                continue;
            }

            foreach ($food[$foodField] as $id) {

                if (!in_array($id, $validIds, true)) {

                    $result->addIssue(
                        new ValidationIssue(
                            validator: self::NAME,
                            severity: ValidationIssue::ERROR,
                            file: $foodFile,
                            message: "Unknown {$foodField} ID '{$id}'."
                        )
                    );

                }

            }

        }

    }
}