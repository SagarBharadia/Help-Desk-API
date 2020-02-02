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
        ],
        'tenant_api' => [
            'driver' => 'jwt',
            'provider' => 'tenant_users'
        ]
    ],

    'providers' => [
        'global_users' => [
            'driver' => 'eloquent',
            'model' => \App\GlobalUser::class
        ],
        'tenant_users' => [
            'driver' => 'eloquent',
            'model' => \App\TenantUser::class
        ]
    ]
];
