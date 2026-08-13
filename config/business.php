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
    ]
];
