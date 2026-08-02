<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PWB\Knowledge\FoodGenerator;

if ($argc !== 2) {

    echo PHP_EOL;
    echo "Personal Wellness Blueprint - Food Generator" . PHP_EOL;
    echo PHP_EOL;
    echo "Usage:" . PHP_EOL;
    echo "  php tools/generate-food.php <food-slug>" . PHP_EOL;
    echo PHP_EOL;
    echo "Example:" . PHP_EOL;
    echo "  php tools/generate-food.php blueberries" . PHP_EOL;
    echo PHP_EOL;

    exit(1);

}

$slug = strtolower(trim($argv[1]));

try {

    $generator = new FoodGenerator();

    $generator->generate($slug);

    echo PHP_EOL;
    echo "Food generated successfully." . PHP_EOL;
    echo PHP_EOL;

} catch (Throwable $exception) {

    echo PHP_EOL;
    echo "Generation failed." . PHP_EOL;
    echo $exception->getMessage() . PHP_EOL;
    echo PHP_EOL;

    exit(1);

}