<?php

return [
    'routes' => [
        'panelPrefix' => 'admin',
        'apiPrefix' => 'admin/api/',
        'modelAliases' => [
            "users" => \App\User::class,
        ],
    ],
    'views' => [
        'index' => 'layouts.index',
    ]
];
