<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Personal Wellness Blueprint
| Taxonomy Integrity Validator v1.0
|--------------------------------------------------------------------------
|
| Validates the integrity of the complete taxonomy knowledgebase.
| This script performs NO modifications.
|
| Usage:
|   php taxonomy-validator.php
|
*/

$base = __DIR__;

$files = [
    'mechanisms'             => "$base/mechanisms.json",
    'mechanismContent'       => "$base/mechanism-content.json",

    'bodySystems'            => "$base/body-systems.json",
    'bodySystemMechanisms'   => "$base/body-systems-mechanisms.json",

    'foods'                  => "$base/foods.json",
    'foodGroups'             => "$base/food-groups.json",
    'foodMechanisms'         => "$base/food-mechanisms.json",
    'foodBioactives'         => "$base/food-bioactives.json",
    'foodNutrients'          => "$base/food-nutrients.json",

    'bioactives'             => "$base/bioactives.json",

    'vitamins'              => "$base/vitamins.json",
    'minerals'              => "$base/minerals.json",
    'nutrientMechanisms'    => "$base/nutrient-mechanisms.json",

    'products'              => "$base/products.json",
    'productMechanisms'     => "$base/product-mechanisms.json",
    'productBioactives'     => "$base/product-bioactives.json",
    'productNutrients'      => "$base/product-nutrients.json",
    'productOutcomes'       => "$base/product-outcomes.json",

    'outcomes'             => "$base/outcomes.json",
];

$errors = [];
$warnings = [];

/* ---------------------------------------------------------
   JSON LOADER
--------------------------------------------------------- */

function loadJson(string $file): array
{
    if (!file_exists($file)) {
        throw new Exception("Missing file: " . basename($file));
    }

    $json = json_decode(file_get_contents($file), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception(
            basename($file) . ": " . json_last_error_msg()
        );
    }

    return $json;
}

function addError(string $msg): void
{
    global $errors;
    $errors[] = $msg;
}

/* ---------------------------------------------------------
   LOAD FILES
--------------------------------------------------------- */

try {

    foreach ($files as $key => $path) {
        $$key = loadJson($path);
    }

} catch (Exception $e) {

    echo PHP_EOL;
    echo "VALIDATION ERROR" . PHP_EOL;
    echo "----------------" . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}

/* ---------------------------------------------------------
   BUILD MASTER LOOKUPS
--------------------------------------------------------- */

$validMechanisms = [];
foreach ($mechanisms['mechanisms'] as $m) {
    $validMechanisms[$m['id']] = true;
}

$validBodySystems = [];
foreach ($bodySystems['bodySystems'] as $bs) {
    $validBodySystems[$bs['id']] = true;
}

$validFoods = [];
foreach ($foods['foods'] as $f) {
    $validFoods[$f['id']] = true;
}

$validFoodGroups = [];
foreach ($foodGroups['foodGroups'] as $fg) {
    $validFoodGroups[$fg['id']] = true;
}

$validBioactives = [];
foreach ($bioactives['bioactives'] as $b) {
    $validBioactives[$b['id']] = true;
}

$validProducts = [];
foreach ($products['products'] as $p) {
    $validProducts[$p['id']] = true;
}

$validOutcomes = [];
foreach ($outcomes['outcomes'] as $o) {
    $validOutcomes[$o['id']] = true;
}

$validNutrients = [];

foreach ($vitamins['vitamins'] as $v) {
    $validNutrients[$v['id']] = true;
}

foreach ($minerals['minerals'] as $m) {
    $validNutrients[$m['id']] = true;
}

/* ---------------------------------------------------------
   FOODS
--------------------------------------------------------- */

foreach ($foods['foods'] as $food) {

    if (!isset($validFoodGroups[$food['foodGroup']])) {
        addError("Food {$food['id']} has invalid food group {$food['foodGroup']}");
    }
}

/* ---------------------------------------------------------
   BODY SYSTEM → MECHANISM
--------------------------------------------------------- */

$bodyLinks = 0;

foreach ($bodySystemMechanisms['bodySystems'] as $bs) {

    if (!isset($validBodySystems[$bs['id']])) {
        addError("Unknown Body System {$bs['id']}");
    }

    $seen = [];

    foreach ($bs['mechanisms'] as $m) {

        $bodyLinks++;

        if (!isset($validMechanisms[$m['id']])) {
            addError("{$bs['id']} references missing {$m['id']}");
        }

        if (isset($seen[$m['id']])) {
            addError("Duplicate {$m['id']} in {$bs['id']}");
        }

        $seen[$m['id']] = true;

        if (is_numeric($m['priority'])) {

            $p = intval($m['priority']);

            if ($p < 1 || $p > 5) {
                addError("Invalid priority {$m['priority']} for {$m['id']}");
            }
        }

        if (isset($m['weight'])) {

            if (!is_numeric($m['weight']) ||
                $m['weight'] < 0 ||
                $m['weight'] > 100) {

                addError("Invalid weight for {$m['id']}");
            }
        }
    }
}

/* ---------------------------------------------------------
   FOOD → MECHANISM
--------------------------------------------------------- */

$foodMechanismLinks = 0;

foreach ($foodMechanisms['relationships'] as $rel) {

    if (!isset($validFoods[$rel['foodId']])) {
        addError("Food mechanism uses unknown food {$rel['foodId']}");
    }

    $seen = [];

    foreach ($rel['mechanisms'] as $m) {

        $foodMechanismLinks++;

        if (!isset($validMechanisms[$m['id']])) {
            addError("Food {$rel['foodId']} references {$m['id']}");
        }

        if (isset($seen[$m['id']])) {
            addError("Duplicate mechanism {$m['id']} in {$rel['foodId']}");
        }

        $seen[$m['id']] = true;

        if ($m['strength'] < 1 || $m['strength'] > 5) {
            addError("Invalid strength {$m['id']}");
        }

        if ($m['confidence'] < 0 || $m['confidence'] > 1) {
            addError("Invalid confidence {$m['id']}");
        }
    }
}

/* ---------------------------------------------------------
   FOOD → BIOACTIVE
--------------------------------------------------------- */

$foodBioactiveLinks = 0;

foreach ($foodBioactives['relationships'] as $rel) {

    if (!isset($validFoods[$rel['foodId']])) {
        addError("Food bioactives uses unknown food {$rel['foodId']}");
    }

    $seen = [];

    foreach ($rel['bioactives'] as $b) {

        $foodBioactiveLinks++;

        if (!isset($validBioactives[$b['id']])) {
            addError("Food {$rel['foodId']} references bioactive {$b['id']}");
        }

        if (isset($seen[$b['id']])) {
            addError("Duplicate bioactive {$b['id']} in {$rel['foodId']}");
        }

        $seen[$b['id']] = true;
    }
}

/* ---------------------------------------------------------
   FOOD → NUTRIENT
--------------------------------------------------------- */

$foodNutrientLinks = 0;

foreach ($foodNutrients['relationships'] as $rel) {

    if (!isset($validFoods[$rel['foodId']])) {
        addError("Food nutrients uses unknown food {$rel['foodId']}");
    }

    foreach ($rel['nutrients'] as $n) {

        $foodNutrientLinks++;

        if (!isset($validNutrients[$n['id']])) {
            addError("Food {$rel['foodId']} references nutrient {$n['id']}");
        }
    }
}

/* ---------------------------------------------------------
   NUTRIENT → MECHANISM
--------------------------------------------------------- */

$nutrientMechanismLinks = 0;

foreach ($nutrientMechanisms['relationships'] as $rel) {

    $nutrientMechanismLinks++;

    if (!isset($validNutrients[$rel['nutrientId']])) {
        addError("Unknown nutrient {$rel['nutrientId']}");
    }

    if (!isset($validMechanisms[$rel['mechanismId']])) {
        addError("Unknown mechanism {$rel['mechanismId']}");
    }
}

/* ---------------------------------------------------------
   PRODUCT → MECHANISM
--------------------------------------------------------- */

$productMechanismLinks = 0;

foreach ($productMechanisms['products'] as $p) {

    if (!isset($validProducts[$p['productId']])) {
        addError("Unknown product {$p['productId']}");
    }

    foreach ($p['mechanisms'] as $m) {

        $productMechanismLinks++;

        if (!isset($validMechanisms[$m['id']])) {
            addError("Product {$p['productId']} references {$m['id']}");
        }
    }
}

/* ---------------------------------------------------------
   PRODUCT → BIOACTIVE
--------------------------------------------------------- */

$productBioactiveLinks = 0;

foreach ($productBioactives['products'] as $p) {

    if (!isset($validProducts[$p['id']])) {
        addError("Unknown product {$p['id']}");
    }

    foreach ($p['bioactives'] as $b) {

        $productBioactiveLinks++;

        if (!isset($validBioactives[$b['id']])) {
            addError("Product {$p['id']} references {$b['id']}");
        }
    }
}

/* ---------------------------------------------------------
   PRODUCT → NUTRIENT
--------------------------------------------------------- */

$productNutrientLinks = 0;

foreach ($productNutrients['products'] as $p) {

    if (!isset($validProducts[$p['productId']])) {
        addError("Unknown product {$p['productId']}");
    }

    foreach ($p['vitamins'] as $v) {

        $productNutrientLinks++;

        if (!isset($validNutrients[$v['id']])) {
            addError("Invalid vitamin {$v['id']}");
        }
    }

    foreach ($p['minerals'] as $m) {

        $productNutrientLinks++;

        if (!isset($validNutrients[$m['id']])) {
            addError("Invalid mineral {$m['id']}");
        }
    }
}

/* ---------------------------------------------------------
   PRODUCT → OUTCOME
--------------------------------------------------------- */

$productOutcomeLinks = 0;

foreach ($productOutcomes['products'] as $p) {

    if (!isset($validProducts[$p['productId']])) {
        addError("Unknown product {$p['productId']}");
    }

    foreach ($p['outcomes'] as $o) {

        $productOutcomeLinks++;

        if (!isset($validOutcomes[$o['outcomeId']])) {
            addError("Invalid outcome {$o['outcomeId']}");
        }

        foreach ($o['matchedMechanisms'] as $mid) {

            if (!isset($validMechanisms[$mid])) {
                addError("Outcome {$o['outcomeId']} references {$mid}");
            }
        }
    }
}

/* ---------------------------------------------------------
   MECHANISM CONTENT
--------------------------------------------------------- */

foreach ($mechanismContent['mechanisms'] as $id => $content) {

    if (!isset($validMechanisms[$id])) {
        addError("Mechanism content exists for unknown {$id}");
    }

    foreach ([
        'tooltip',
        'plainEnglish',
        'description',
        'whyItMatters'
    ] as $field) {

        if (empty($content[$field])) {
            addError("{$id} missing {$field}");
        }
    }
}

/* ---------------------------------------------------------
   OUTPUT
--------------------------------------------------------- */

echo PHP_EOL;
echo "PWB KNOWLEDGEBASE VALIDATION" . PHP_EOL;
echo "============================" . PHP_EOL . PHP_EOL;

if (empty($errors)) {

    echo "PASS" . PHP_EOL . PHP_EOL;

    echo str_pad("Mechanisms:", 32) . count($validMechanisms) . PHP_EOL;
    echo str_pad("Body Systems:", 32) . count($validBodySystems) . PHP_EOL;
    echo str_pad("Foods:", 32) . count($validFoods) . PHP_EOL;
    echo str_pad("Food Groups:", 32) . count($validFoodGroups) . PHP_EOL;
    echo str_pad("Bioactives:", 32) . count($validBioactives) . PHP_EOL;
    echo str_pad("Products:", 32) . count($validProducts) . PHP_EOL;
    echo str_pad("Outcomes:", 32) . count($validOutcomes) . PHP_EOL;

    echo PHP_EOL;

    echo str_pad("Body-System Links:", 32) . $bodyLinks . PHP_EOL;
    echo str_pad("Food Mechanism Links:", 32) . $foodMechanismLinks . PHP_EOL;
    echo str_pad("Food Bioactive Links:", 32) . $foodBioactiveLinks . PHP_EOL;
    echo str_pad("Food Nutrient Links:", 32) . $foodNutrientLinks . PHP_EOL;
    echo str_pad("Nutrient Mechanism Links:", 32) . $nutrientMechanismLinks . PHP_EOL;
    echo str_pad("Product Mechanism Links:", 32) . $productMechanismLinks . PHP_EOL;
    echo str_pad("Product Bioactive Links:", 32) . $productBioactiveLinks . PHP_EOL;
    echo str_pad("Product Nutrient Links:", 32) . $productNutrientLinks . PHP_EOL;
    echo str_pad("Product Outcome Links:", 32) . $productOutcomeLinks . PHP_EOL;

    exit(0);
}

echo "FAILED" . PHP_EOL;
echo "------" . PHP_EOL . PHP_EOL;

foreach ($errors as $e) {
    echo "ERROR: {$e}" . PHP_EOL;
}

if (!empty($warnings)) {

    echo PHP_EOL;

    foreach ($warnings as $w) {
        echo "WARNING: {$w}" . PHP_EOL;
    }
}

exit(1);

?>