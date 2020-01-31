<?php

return [
    'defaults' => [
        'guard' => 'global_api',
        'passwords' => 'users',
    ],

    'guards' => [
        'global_api' => [
            'driver' => 'jwt',
            'provider' => 'global_users',
        ]
    ],

    'providers' => [
        'global_users' => [
            'driver' => 'eloquent',
            'model' => \App\GlobalUser::class
        ]
    ]
];
