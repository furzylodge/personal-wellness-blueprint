<?php

declare(strict_types=1);

return [

    // ---------------------------------------------------------------------
    // Phase A - Foundation Foods
    // ---------------------------------------------------------------------

    // FD001 - Broccoli

    [
        'id' => 'FB000001',
        'food' => 'FD001',
        'bioactive' => 'OS002',
        'strength' => 5,
        'confidence' => 'High',
        'part' => 'Florets',
        'preparation' => ['Raw','Lightly Steamed'],
        'bioavailability' => 'High',
        'notes' => 'Lightly chop and allow to stand for approximately 30 to 40 minutes before cooking to maximise sulforaphane formation.',
        'references' => ['USDA FoodData Central','Phenol-Explorer']
    ],

    [
        'id' => 'FB000002',
        'food' => 'FD001',
        'bioactive' => 'OS003',
        'strength' => 5,
        'confidence' => 'High',
        'part' => 'Florets',
        'preparation' => ['Raw','Lightly Steamed'],
        'bioavailability' => 'Variable',
        'notes' => 'Converted into sulforaphane by the enzyme myrosinase after chopping or chewing.',
        'references' => ['USDA FoodData Central','Phenol-Explorer']
    ],

    [
        'id' => 'FB000003',
        'food' => 'FD001',
        'bioactive' => 'PP005',
        'strength' => 3,
        'confidence' => 'High',
        'part' => 'Whole',
        'preparation' => ['Raw','Lightly Steamed','Steamed'],
        'bioavailability' => 'Moderate',
        'notes' => null,
        'references' => ['Phenol-Explorer']
    ],

    [
        'id' => 'FB000004',
        'food' => 'FD001',
        'bioactive' => 'CA002',
        'strength' => 3,
        'confidence' => 'High',
        'part' => 'Florets',
        'preparation' => ['Raw','Steamed'],
        'bioavailability' => 'Moderate',
        'notes' => null,
        'references' => ['USDA FoodData Central']
    ],

    [
        'id' => 'FB000005',
        'food' => 'FD001',
        'bioactive' => 'DF004',
        'strength' => 3,
        'confidence' => 'High',
        'part' => 'Whole',
        'preparation' => ['Any'],
        'bioavailability' => 'High',
        'notes' => null,
        'references' => ['USDA FoodData Central']
    ],

    // ---------------------------------------------------------------------
    // TODO - Phase A continuation
    // ---------------------------------------------------------------------
    // FD002 Garlic
    // FD003 Blueberries
    // FD004 Spinach
    // FD005 Kale
    // FD006 Tomatoes
    // FD007 Oats
    // FD008 Flaxseed
    // FD009 Chia Seeds
    // FD010 Walnuts
];
