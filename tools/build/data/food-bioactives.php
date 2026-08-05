<?php

declare(strict_types=1);

return [

    // Relationships go here
[
    'id' => 'FB000001',

    'food' => 'FD001',

    'bioactive' => 'OS002',

    'strength' => 5,

    'confidence' => 'High',

    'part' => 'Florets',

    'preparation' => [
        'Raw',
        'Lightly Steamed'
    ],

    'bioavailability' => 'High',

    'notes' =>
        'Lightly chop and allow to stand for approximately 30 to 40 minutes before cooking to maximise sulforaphane formation.',

    'references' => [
        'USDA FoodData Central',
        'Phenol-Explorer'
    ]
],
[
    'id' => 'FB000002',

    'food' => 'FD001',

    'bioactive' => 'OS003',

    'strength' => 5,

    'confidence' => 'High',

    'part' => 'Florets',

    'preparation' => [
        'Raw',
        'Lightly Steamed'
    ],

    'bioavailability' => 'Variable',

    'notes' =>
        'Converted into sulforaphane by the enzyme myrosinase after chopping or chewing.',

    'references' => [
        'USDA FoodData Central',
        'Phenol-Explorer'
    ]
],
[
    'id' => 'FB000003',

    'food' => 'FD001',

    'bioactive' => 'PP005',

    'strength' => 3,

    'confidence' => 'High',

    'part' => 'Whole',

    'preparation' => [
        'Raw',
        'Lightly Steamed',
        'Steamed'
    ],

    'bioavailability' => 'Moderate',

    'notes' => null,

    'references' => [
        'Phenol-Explorer'
    ]
],
[
    'id' => 'FB000004',

    'food' => 'FD001',

    'bioactive' => 'CA002',

    'strength' => 3,

    'confidence' => 'High',

    'part' => 'Florets',

    'preparation' => [
        'Raw',
        'Steamed'
    ],

    'bioavailability' => 'Moderate',

    'notes' => null,

    'references' => [
        'USDA FoodData Central'
    ]
],
[
    'id' => 'FB000005',

    'food' => 'FD001',

    'bioactive' => 'DF004',

    'strength' => 3,

    'confidence' => 'High',

    'part' => 'Whole',

    'preparation' => [
        'Any'
    ],

    'bioavailability' => 'High',

    'notes' => null,

    'references' => [
        'USDA FoodData Central'
    ]
],
    // ---------------------------------------------------------------------
    // FD002 - Garlic
    // ---------------------------------------------------------------------

    [
        'id' => 'FB000006',

        'food' => 'FD002',

        'bioactive' => 'OS001',

        'strength' => 5,

        'confidence' => 'High',

        'part' => 'Clove',

        'preparation' => [
            'Crushed and Rested',
            'Raw'
        ],

        'bioavailability' => 'High',

        'notes' =>
            'Crush or chop garlic and leave for around 10 minutes before cooking to maximise allicin formation.',

        'references' => [
            'USDA FoodData Central',
            'Phenol-Explorer'
        ]
    ],

    [
        'id' => 'FB000007',

        'food' => 'FD002',

        'bioactive' => 'OS004',

        'strength' => 4,

        'confidence' => 'High',

        'part' => 'Clove',

        'preparation' => [
            'Raw',
            'Cooked'
        ],

        'bioavailability' => 'Moderate',

        'notes' =>
            'Oil-soluble sulphur compounds remain after cooking and contribute to garlic\'s biological activity.',

        'references' => [
            'Phenol-Explorer'
        ]
    ],

    [
        'id' => 'FB000008',

        'food' => 'FD002',

        'bioactive' => 'OS005',

        'strength' => 3,

        'confidence' => 'Moderate',

        'part' => 'Clove',

        'preparation' => [
            'Raw',
            'Cooked'
        ],

        'bioavailability' => 'Moderate',

        'notes' => null,

        'references' => [
            'Phenol-Explorer'
        ]
    ],

    [
        'id' => 'FB000009',

        'food' => 'FD002',

        'bioactive' => 'PP002',

        'strength' => 2,

        'confidence' => 'Moderate',

        'part' => 'Whole',

        'preparation' => [
            'Any'
        ],

        'bioavailability' => 'Moderate',

        'notes' => null,

        'references' => [
            'Phenol-Explorer'
        ]
    ],
        // ---------------------------------------------------------------------
    // FD003 - Blueberries
    // ---------------------------------------------------------------------

    [
        'id' => 'FB000010',

        'food' => 'FD003',

        'bioactive' => 'PP001',

        'strength' => 5,

        'confidence' => 'High',

        'part' => 'Skin',

        'preparation' => [
            'Raw'
        ],

        'bioavailability' => 'Moderate',

        'notes' =>
            'Anthocyanins are concentrated in the skin and are best preserved when eaten fresh or minimally processed.',

        'references' => [
            'Phenol-Explorer',
            'USDA FoodData Central'
        ]
    ],

    [
        'id' => 'FB000011',

        'food' => 'FD003',

        'bioactive' => 'PP007',

        'strength' => 3,

        'confidence' => 'High',

        'part' => 'Whole',

        'preparation' => [
            'Raw'
        ],

        'bioavailability' => 'Moderate',

        'notes' => null,

        'references' => [
            'Phenol-Explorer'
        ]
    ],

    [
        'id' => 'FB000012',

        'food' => 'FD003',

        'bioactive' => 'PP008',

        'strength' => 2,

        'confidence' => 'Moderate',

        'part' => 'Whole',

        'preparation' => [
            'Raw'
        ],

        'bioavailability' => 'Moderate',

        'notes' => null,

        'references' => [
            'Phenol-Explorer'
        ]
    ],

    [
        'id' => 'FB000013',

        'food' => 'FD003',

        'bioactive' => 'PP002',

        'strength' => 2,

        'confidence' => 'Moderate',

        'part' => 'Skin',

        'preparation' => [
            'Raw'
        ],

        'bioavailability' => 'Moderate',

        'notes' => null,

        'references' => [
            'Phenol-Explorer'
        ]
    ],

    [
        'id' => 'FB000014',

        'food' => 'FD003',

        'bioactive' => 'DF004',

        'strength' => 2,

        'confidence' => 'High',

        'part' => 'Whole',

        'preparation' => [
            'Any'
        ],

        'bioavailability' => 'High',

        'notes' => null,

        'references' => [
            'USDA FoodData Central'
        ]
    ],
        // ---------------------------------------------------------------------
    // FD004 - Spinach
    // ---------------------------------------------------------------------

    [
        'id' => 'FB000015',

        'food' => 'FD004',

        'bioactive' => 'CA002',

        'strength' => 5,

        'confidence' => 'High',

        'part' => 'Leaves',

        'preparation' => [
            'Raw',
            'Lightly Steamed'
        ],

        'bioavailability' => 'Moderate',

        'notes' =>
            'A small amount of healthy fat improves lutein absorption.',

        'references' => [
            'USDA FoodData Central',
            'Phenol-Explorer'
        ]
    ],

    [
        'id' => 'FB000016',

        'food' => 'FD004',

        'bioactive' => 'CA003',

        'strength' => 4,

        'confidence' => 'High',

        'part' => 'Leaves',

        'preparation' => [
            'Raw',
            'Lightly Steamed'
        ],

        'bioavailability' => 'Moderate',

        'notes' => null,

        'references' => [
            'USDA FoodData Central'
        ]
    ],

    [
        'id' => 'FB000017',

        'food' => 'FD004',

        'bioactive' => 'CA004',

        'strength' => 4,

        'confidence' => 'High',

        'part' => 'Leaves',

        'preparation' => [
            'Raw',
            'Lightly Steamed'
        ],

        'bioavailability' => 'Moderate',

        'notes' => null,

        'references' => [
            'USDA FoodData Central'
        ]
    ],

    [
        'id' => 'FB000018',

        'food' => 'FD004',

        'bioactive' => 'PP005',

        'strength' => 4,

        'confidence' => 'High',

        'part' => 'Leaves',

        'preparation' => [
            'Raw',
            'Steamed'
        ],

        'bioavailability' => 'Moderate',

        'notes' => null,

        'references' => [
            'Phenol-Explorer'
        ]
    ],

    [
        'id' => 'FB000019',

        'food' => 'FD004',

        'bioactive' => 'PP002',

        'strength' => 3,

        'confidence' => 'High',

        'part' => 'Leaves',

        'preparation' => [
            'Raw',
            'Steamed'
        ],

        'bioavailability' => 'Moderate',

        'notes' => null,

        'references' => [
            'Phenol-Explorer'
        ]
    ],

    [
        'id' => 'FB000020',

        'food' => 'FD004',

        'bioactive' => 'DF004',

        'strength' => 2,

        'confidence' => 'High',

        'part' => 'Leaves',

        'preparation' => [
            'Any'
        ],

        'bioavailability' => 'High',

        'notes' => null,

        'references' => [
            'USDA FoodData Central'
        ]
    ],
        // ---------------------------------------------------------------------
    // FD005 - Kale
    // ---------------------------------------------------------------------

    [
        'id' => 'FB000021',

        'food' => 'FD005',

        'bioactive' => 'CA002',

        'strength' => 5,

        'confidence' => 'High',

        'part' => 'Leaves',

        'preparation' => [
            'Raw',
            'Lightly Steamed'
        ],

        'bioavailability' => 'Moderate',

        'notes' =>
            'Consuming with olive oil or another healthy fat increases carotenoid absorption.',

        'references' => [
            'USDA FoodData Central',
            'Phenol-Explorer'
        ]
    ],

    [
        'id' => 'FB000022',

        'food' => 'FD005',

        'bioactive' => 'CA003',

        'strength' => 5,

        'confidence' => 'High',

        'part' => 'Leaves',

        'preparation' => [
            'Raw',
            'Lightly Steamed'
        ],

        'bioavailability' => 'Moderate',

        'notes' => null,

        'references' => [
            'USDA FoodData Central'
        ]
    ],

    [
        'id' => 'FB000023',

        'food' => 'FD005',

        'bioactive' => 'CA004',

        'strength' => 5,

        'confidence' => 'High',

        'part' => 'Leaves',

        'preparation' => [
            'Raw',
            'Lightly Steamed'
        ],

        'bioavailability' => 'Moderate',

        'notes' => null,

        'references' => [
            'USDA FoodData Central'
        ]
    ],

    [
        'id' => 'FB000024',

        'food' => 'FD005',

        'bioactive' => 'PP005',

        'strength' => 5,

        'confidence' => 'High',

        'part' => 'Leaves',

        'preparation' => [
            'Raw',
            'Steamed'
        ],

        'bioavailability' => 'Moderate',

        'notes' => null,

        'references' => [
            'Phenol-Explorer'
        ]
    ],

    [
        'id' => 'FB000025',

        'food' => 'FD005',

        'bioactive' => 'PP002',

        'strength' => 3,

        'confidence' => 'High',

        'part' => 'Leaves',

        'preparation' => [
            'Raw',
            'Steamed'
        ],

        'bioavailability' => 'Moderate',

        'notes' => null,

        'references' => [
            'Phenol-Explorer'
        ]
    ],

    [
        'id' => 'FB000026',

        'food' => 'FD005',

        'bioactive' => 'DF004',

        'strength' => 3,

        'confidence' => 'High',

        'part' => 'Leaves',

        'preparation' => [
            'Any'
        ],

        'bioavailability' => 'High',

        'notes' => null,

        'references' => [
            'USDA FoodData Central'
        ]
    ],
        // ---------------------------------------------------------------------
    // FD006 - Tomatoes
    // ---------------------------------------------------------------------

    [
        'id' => 'FB000027',
        'food' => 'FD006',
        'bioactive' => 'CA001',
        'strength' => 5,
        'confidence' => 'High',
        'part' => 'Flesh',
        'preparation' => [
            'Raw',
            'Cooked'
        ],
        'bioavailability' => 'Variable',
        'notes' =>
            'Cooking with a small amount of olive oil substantially increases lycopene absorption.',
        'references' => [
            'USDA FoodData Central',
            'Phenol-Explorer'
        ]
    ],

    [
        'id' => 'FB000028',
        'food' => 'FD006',
        'bioactive' => 'CA004',
        'strength' => 2,
        'confidence' => 'High',
        'part' => 'Flesh',
        'preparation' => [
            'Raw',
            'Cooked'
        ],
        'bioavailability' => 'Moderate',
        'notes' => null,
        'references' => [
            'USDA FoodData Central'
        ]
    ],

    [
        'id' => 'FB000029',
        'food' => 'FD006',
        'bioactive' => 'PP008',
        'strength' => 2,
        'confidence' => 'Moderate',
        'part' => 'Whole',
        'preparation' => [
            'Any'
        ],
        'bioavailability' => 'Moderate',
        'notes' => null,
        'references' => [
            'Phenol-Explorer'
        ]
    ],

    [
        'id' => 'FB000030',
        'food' => 'FD006',
        'bioactive' => 'PP002',
        'strength' => 2,
        'confidence' => 'Moderate',
        'part' => 'Skin',
        'preparation' => [
            'Raw'
        ],
        'bioavailability' => 'Moderate',
        'notes' => null,
        'references' => [
            'Phenol-Explorer'
        ]
    ],

    [
        'id' => 'FB000031',
        'food' => 'FD006',
        'bioactive' => 'DF004',
        'strength' => 2,
        'confidence' => 'High',
        'part' => 'Whole',
        'preparation' => [
            'Any'
        ],
        'bioavailability' => 'High',
        'notes' => null,
        'references' => [
            'USDA FoodData Central'
        ]
    ],
        // ---------------------------------------------------------------------
    // FD007 - Oats
    // ---------------------------------------------------------------------

    [
        'id' => 'FB000032',
        'food' => 'FD007',
        'bioactive' => 'DF001',
        'strength' => 5,
        'confidence' => 'High',
        'part' => 'Whole Grain',
        'preparation' => [
            'Cooked'
        ],
        'bioavailability' => 'High',
        'notes' => null,
        'references' => [
            'USDA FoodData Central'
        ]
    ],

    [
        'id' => 'FB000033',
        'food' => 'FD007',
        'bioactive' => 'DF005',
        'strength' => 4,
        'confidence' => 'High',
        'part' => 'Whole Grain',
        'preparation' => [
            'Cooked'
        ],
        'bioavailability' => 'High',
        'notes' => null,
        'references' => [
            'USDA FoodData Central'
        ]
    ],

    [
        'id' => 'FB000034',
        'food' => 'FD007',
        'bioactive' => 'PP012',
        'strength' => 5,
        'confidence' => 'High',
        'part' => 'Whole Grain',
        'preparation' => [
            'Cooked'
        ],
        'bioavailability' => 'Moderate',
        'notes' => null,
        'references' => [
            'Phenol-Explorer'
        ]
    ],
        // ---------------------------------------------------------------------
    // FD008 - Flaxseed
    // ---------------------------------------------------------------------

    [
        'id' => 'FB000035',
        'food' => 'FD008',
        'bioactive' => 'FA001',
        'strength' => 5,
        'confidence' => 'High',
        'part' => 'Seeds',
        'preparation' => [
            'Raw'
        ],
        'bioavailability' => 'Moderate',
        'notes' =>
            'Grinding flaxseed substantially improves nutrient availability.',
        'references' => [
            'USDA FoodData Central'
        ]
    ],

    [
        'id' => 'FB000036',
        'food' => 'FD008',
        'bioactive' => 'PP011',
        'strength' => 5,
        'confidence' => 'High',
        'part' => 'Seeds',
        'preparation' => [
            'Raw'
        ],
        'bioavailability' => 'Moderate',
        'notes' => null,
        'references' => [
            'Phenol-Explorer'
        ]
    ],

    [
        'id' => 'FB000037',
        'food' => 'FD008',
        'bioactive' => 'DF006',
        'strength' => 4,
        'confidence' => 'High',
        'part' => 'Seeds',
        'preparation' => [
            'Raw'
        ],
        'bioavailability' => 'High',
        'notes' => null,
        'references' => [
            'USDA FoodData Central'
        ]
    ],

    [
        'id' => 'FB000038',
        'food' => 'FD008',
        'bioactive' => 'DF005',
        'strength' => 3,
        'confidence' => 'High',
        'part' => 'Seeds',
        'preparation' => [
            'Raw'
        ],
        'bioavailability' => 'High',
        'notes' => null,
        'references' => [
            'USDA FoodData Central'
        ]
    ],

    [
        'id' => 'FB000039',
        'food' => 'FD008',
        'bioactive' => 'PS001',
        'strength' => 3,
        'confidence' => 'High',
        'part' => 'Seeds',
        'preparation' => [
            'Raw'
        ],
        'bioavailability' => 'Moderate',
        'notes' => null,
        'references' => [
            'USDA FoodData Central'
        ]
    ],
        // ---------------------------------------------------------------------
    // FD009 - Chia Seeds
    // ---------------------------------------------------------------------

    [
        'id' => 'FB000040',
        'food' => 'FD009',
        'bioactive' => 'FA001',
        'strength' => 5,
        'confidence' => 'High',
        'part' => 'Seeds',
        'preparation' => ['Raw'],
        'bioavailability' => 'Moderate',
        'notes' => null,
        'references' => ['USDA FoodData Central']
    ],

    [
        'id' => 'FB000041',
        'food' => 'FD009',
        'bioactive' => 'DF006',
        'strength' => 4,
        'confidence' => 'High',
        'part' => 'Seeds',
        'preparation' => ['Raw'],
        'bioavailability' => 'High',
        'notes' => null,
        'references' => ['USDA FoodData Central']
    ],

    [
        'id' => 'FB000042',
        'food' => 'FD009',
        'bioactive' => 'DF005',
        'strength' => 4,
        'confidence' => 'High',
        'part' => 'Seeds',
        'preparation' => ['Raw'],
        'bioavailability' => 'High',
        'notes' => null,
        'references' => ['USDA FoodData Central']
    ],

    [
        'id' => 'FB000043',
        'food' => 'FD009',
        'bioactive' => 'PP011',
        'strength' => 3,
        'confidence' => 'Moderate',
        'part' => 'Seeds',
        'preparation' => ['Raw'],
        'bioavailability' => 'Moderate',
        'notes' => null,
        'references' => ['Phenol-Explorer']
    ],
        // ---------------------------------------------------------------------
    // FD010 - Walnuts
    // ---------------------------------------------------------------------

    [
        'id' => 'FB000044',
        'food' => 'FD010',
        'bioactive' => 'FA001',
        'strength' => 5,
        'confidence' => 'High',
        'part' => 'Kernel',
        'preparation' => ['Raw'],
        'bioavailability' => 'High',
        'notes' => null,
        'references' => ['USDA FoodData Central']
    ],

    [
        'id' => 'FB000045',
        'food' => 'FD010',
        'bioactive' => 'PP007',
        'strength' => 3,
        'confidence' => 'High',
        'part' => 'Kernel',
        'preparation' => ['Raw'],
        'bioavailability' => 'Moderate',
        'notes' => null,
        'references' => ['Phenol-Explorer']
    ],

    [
        'id' => 'FB000046',
        'food' => 'FD010',
        'bioactive' => 'PP011',
        'strength' => 2,
        'confidence' => 'Moderate',
        'part' => 'Kernel',
        'preparation' => ['Raw'],
        'bioavailability' => 'Moderate',
        'notes' => null,
        'references' => ['Phenol-Explorer']
    ],

    [
        'id' => 'FB000047',
        'food' => 'FD010',
        'bioactive' => 'PS001',
        'strength' => 3,
        'confidence' => 'High',
        'part' => 'Kernel',
        'preparation' => ['Raw'],
        'bioavailability' => 'Moderate',
        'notes' => null,
        'references' => ['USDA FoodData Central']
    ],

    [
        'id' => 'FB000048',
        'food' => 'FD010',
        'bioactive' => 'FC007',
        'strength' => 2,
        'confidence' => 'Moderate',
        'part' => 'Kernel',
        'preparation' => ['Raw'],
        'bioavailability' => 'Moderate',
        'notes' => 'Walnuts contain naturally occurring melatonin, although concentrations vary by cultivar.',
        'references' => ['Scientific literature']
    ],

    [
        'id' => 'FB000049',
        'food' => 'FD010',
        'bioactive' => 'DF006',
        'strength' => 3,
        'confidence' => 'High',
        'part' => 'Kernel',
        'preparation' => ['Raw'],
        'bioavailability' => 'High',
        'notes' => null,
        'references' => ['USDA FoodData Central']
    ]
    
    

];
