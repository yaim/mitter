<?php

return [
    'routes' => [
        'panelPrefix' => 'admin',
        'apiPrefix' => 'admin/api/',
        'modelAliases' => [
            "users" => \App\User::class,
        ],

        'routeGroupConfig' => [
            'middleware' => 'web',
            'prefix' => 'admin',
            'namespace' => '\\Yami\\Mitter\\',
        ]
    ],

    'views' => [
        'index' => 'layouts.index',
    ]
];
