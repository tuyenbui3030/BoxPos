<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Material Suppliers Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the Material Suppliers package
    |
    */

    'supplier_types' => [
        'manufacturer' => 'Nhà sản xuất',
        'distributor' => 'Nhà phân phối',
        'retailer' => 'Nhà bán lẻ',
        'importer' => 'Nhà nhập khẩu',
    ],

    'payment_terms' => [
        'cash' => 'Tiền mặt',
        'cod' => 'Thanh toán khi giao hàng',
        'net_15' => 'Thanh toán trong 15 ngày',
        'net_30' => 'Thanh toán trong 30 ngày',
        'net_60' => 'Thanh toán trong 60 ngày',
        'net_90' => 'Thanh toán trong 90 ngày',
    ],

    'rating' => [
        'min' => 0,
        'max' => 5,
        'default' => 0,
    ],

    'credit_limit' => [
        'default' => 0,
        'currency' => 'VND',
    ],

    'lead_time' => [
        'default_days' => 7,
        'min_days' => 1,
        'max_days' => 365,
    ],

    'features' => [
        'enable_credit_management' => true,
        'enable_rating_system' => true,
        'enable_delivery_areas' => true,
        'enable_certifications' => true,
        'enable_contact_management' => true,
        'enable_preferred_suppliers' => true,
    ],

    'defaults' => [
        'supplier_code_prefix' => 'SUP',
        'country' => 'Vietnam',
        'currency' => 'VND',
        'payment_terms' => 'net_30',
        'supplier_type' => 'distributor',
    ],
];
