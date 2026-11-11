<?php
return [
    'driver' => [
        'razon_social' => env('DRIVER_RAZON_SOCIAL', ''),
        'address' => env('DRIVER_ADDRESS', ''),
        'autorizacion_spa' => env('DRIVER_AUTORIZACION_SPA', ''),
    ],
    'transportista' => [
        'razon_social' => env('TRANSPORTISTA_RAZON_SOCIAL', ''),
        'address' => env('TRANSPORTISTA_ADDRESS', 'Av. Maquiladoras 3458-B, Cd. Industrial, Tijuana, Baja California, México 22444'),
        'autorizacion_spa' => env('TRANSPORTISTA_AUTORIZACION_SPA', ''),
        'postal_code' => env('TRANSPORTISTA_POSTAL_CODE', '22444'),
        'street' => env('TRANSPORTISTA_STREET', 'Av. Maquiladoras'),
        'num_ext' => env('TRANSPORTISTA_NUM_EXT', '3458-B'),
        'num_int' => env('TRANSPORTISTA_NUM_INT', ''),
        'colony' => env('TRANSPORTISTA_COLONY', 'Cd. Industrial'),
        'city' => env('TRANSPORTISTA_CITY', 'Tijuana'),
        'state' => env('TRANSPORTISTA_STATE', 'Baja California'),
        'country' => env('TRANSPORTISTA_COUNTRY', 'México'),
        'phone' => env('TRANSPORTISTA_PHONE', ''),
        'aut_semarnat' => env('TRANSPORTISTA_AUT_SEMARNAT', ''),
        'autorizacion_operacion' => env('TRANSPORTISTA_AUTORIZACION_OPERACION', 'No 02-004-GM-I-36 D/06'),
        'recoleccion_transporte' => env('TRANSPORTISTA_RECOLECCION_TRANSPORTE', 'recolección y transporte'),
        'autorizacion_almacenamiento' => env('TRANSPORTISTA_AUTORIZACION_ALMACENAMIENTO', '02-004-PS-II-13 D-05'),
        'autorizacion_tratamiento' => env('TRANSPORTISTA_AUTORIZACION_TRATAMIENTO', '02-04-PS-V-71-2006'),
        'tratamiento_residuos' => env('TRANSPORTISTA_TRATAMIENTO_RESIDUOS', 'tratamiento de residuos peligrosos biológico infecciosos'),
    ]
];
