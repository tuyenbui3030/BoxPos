<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Material Catalog Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the Material Catalog package
    |
    */

    'units' => [
        'weight' => [
            'kg' => 'Kilogram',
            'ton' => 'Tấn',
            'quintal' => 'Tạ',
        ],
        'volume' => [
            'm3' => 'Mét khối',
            'liter' => 'Lít',
        ],
        'area' => [
            'm2' => 'Mét vuông',
        ],
        'length' => [
            'm' => 'Mét',
            'cm' => 'Centimet',
            'mm' => 'Millimet',
        ],
        'count' => [
            'piece' => 'Cái',
            'bag' => 'Bao',
            'box' => 'Thùng',
            'bundle' => 'Bó',
            'roll' => 'Cuộn',
            'sheet' => 'Tấm',
            'bar' => 'Thanh',
        ],
    ],

    'categories' => [
        'cement' => 'Xi măng',
        'steel' => 'Sắt thép',
        'brick' => 'Gạch',
        'sand_stone' => 'Cát đá',
        'tile' => 'Ngói',
        'paint' => 'Sơn',
        'wood' => 'Gỗ',
        'pipe' => 'Ống',
        'electrical' => 'Điện',
        'plumbing' => 'Nước',
        'insulation' => 'Cách nhiệt',
        'roofing' => 'Lợp mái',
    ],

    'quality_standards' => [
        'TCVN' => 'Tiêu chuẩn Việt Nam',
        'ISO' => 'International Organization for Standardization',
        'ASTM' => 'American Society for Testing and Materials',
        'JIS' => 'Japanese Industrial Standards',
        'BS' => 'British Standards',
        'DIN' => 'Deutsches Institut für Normung',
    ],

    'material_attributes' => [
        'cement' => [
            'strength' => 'Cường độ (MPa)',
            'setting_time' => 'Thời gian đông kết (phút)',
            'fineness' => 'Độ mịn (cm²/g)',
        ],
        'steel' => [
            'diameter' => 'Đường kính (mm)',
            'yield_strength' => 'Giới hạn chảy (MPa)',
            'tensile_strength' => 'Cường độ kéo (MPa)',
            'elongation' => 'Độ dãn dài (%)',
        ],
        'brick' => [
            'dimensions' => 'Kích thước (mm)',
            'compressive_strength' => 'Cường độ nén (MPa)',
            'water_absorption' => 'Hấp thụ nước (%)',
        ],
        'sand' => [
            'fineness_modulus' => 'Mô đun độ mịn',
            'clay_content' => 'Hàm lượng sét (%)',
            'organic_impurities' => 'Tạp chất hữu cơ',
        ],
    ],

    'features' => [
        'enable_technical_specs' => true,
        'enable_quality_standards' => true,
        'enable_material_variants' => true,
        'enable_barcode_generation' => true,
        'enable_image_gallery' => true,
        'enable_category_hierarchy' => true,
        'enable_unit_conversion' => true,
        'enable_material_search' => true,
    ],

    'defaults' => [
        'material_code_prefix' => 'MAT',
        'category_code_prefix' => 'CAT',
        'unit_code_prefix' => 'UNIT',
        'image_storage_path' => 'materials',
        'max_images_per_material' => 10,
        'category_max_depth' => 3,
    ],
];
