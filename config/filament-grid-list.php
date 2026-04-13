<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Grid Columns
    |--------------------------------------------------------------------------
    | Responsive breakpoints for the grid layout. Keys map to Tailwind
    | breakpoints: default, sm, md, lg, xl, 2xl.
    */
    'grid_columns' => [
        'default' => 1,
        'sm' => 2,
        'md' => 3,
        'lg' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Gap
    |--------------------------------------------------------------------------
    | Tailwind spacing unit for the gap between grid cards (e.g. 4 = 1rem).
    */
    'gap' => 4,

    /*
    |--------------------------------------------------------------------------
    | Default Records Per Page
    |--------------------------------------------------------------------------
    | The default number of records shown per page in the grid view.
    */
    'records_per_page' => 12,

    /*
    |--------------------------------------------------------------------------
    | Records Per Page Options
    |--------------------------------------------------------------------------
    | Options shown in the per-page dropdown. Use 'all' to allow showing
    | all records.
    */
    'records_per_page_options' => [12, 24, 48, 96],

];
