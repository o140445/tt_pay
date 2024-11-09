<?php

return [
    'autoload' => false,
    'hooks' => [
        'app_init' => [
            'crontab',
            'manystore',
        ],
        'upload_config_checklogin' => [
            'manystore',
        ],
    ],
    'route' => [],
    'priority' => [],
    'domain' => '',
];
