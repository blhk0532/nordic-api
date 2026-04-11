<?php

declare(strict_types=1);

// config for Adultdate/FilamentBooking
return [
    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency for the booking. This will be used for displaying
    | prices and formatting monetary values.
    |
    */
    'currency' => env('BOOKING_CURRENCY', 'SEK'),

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | The default locale for the booking. This affects number formatting,
    | dates, and other locale-specific features.
    |
    */
    'locale' => env('BOOKING_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Product Images Disk
    |--------------------------------------------------------------------------
    |
    | The filesystem disk to use for storing product images.
    | Make sure this disk is configured in config/filesystems.php
    |
    */
    'product_images_disk' => env('BOOKING_PRODUCT_IMAGES_DISK', 'product-images'),

    /*
    |--------------------------------------------------------------------------
    | Enable Features
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features of the booking.
    |
    */
    'features' => [
        'booking_brands' => true,
        'booking_categories' => true,
        'booking_customers' => true,
        'booking_orders' => true,
        'booking_products' => true,
    ],
];
