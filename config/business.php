<?php
return [
    'driver' => [
        'razon_social' => env('DRIVER_RAZON_SOCIAL', ''),
        'address' => env('DRIVER_ADDRESS', ''),
        'autorizacion_spa' => env('DRIVER_AUTORIZACION_SPA', ''),
    ],
    'transportista' => [
        'razon_social' => env('TRANSPORTISTA_RAZON_SOCIAL', ''),
        'address' => env('TRANSPORTISTA_ADDRESS', ''),
        'autorizacion_spa' => env('TRANSPORTISTA_AUTORIZACION_SPA', ''),
        'postal_code' => env('TRANSPORTISTA_POSTAL_CODE', ''),
        'street' => env('TRANSPORTISTA_STREET', ''),
        'num_ext' => env('TRANSPORTISTA_NUM_EXT', ''),
        'num_int' => env('TRANSPORTISTA_NUM_INT', ''),
        'colony' => env('TRANSPORTISTA_COLONY', ''),
        'city' => env('TRANSPORTISTA_CITY', ''),
        'state' => env('TRANSPORTISTA_STATE', ''),
        'phone' => env('TRANSPORTISTA_PHONE', ''),
        'aut_semarnat' => env('TRANSPORTISTA_AUT_SEMARNAT', ''),

    ]
];
