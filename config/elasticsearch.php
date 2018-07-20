<?php

return [
    'default' => env('ES_CONNECTION', 'default'),

    'connections' => [

        'default' => [

            'servers' => [
                [
                    'host' => env('ES_HOST', '127.0.0.1'),
                    'port' => env('ES_PORT', 9200),
                    'user' => env('ES_USER', ''),
                    'pass' => env('ES_PASS', ''),
                    'scheme' => env('ES_SCHEME', 'http'),
                ]
            ],

            'index' => env('ES_INDEX', 'my_index'),

            'logging' => [
                'enabled'   => env('ES_LOGGING_ENABLED', false),
                'level'     => env('ES_LOGGING_LEVEL', 'all'),
                'location'  => env('ES_LOGGING_LOCATION', base_path('storage/logs/elasticsearch.log'))
            ],
        ]
    ],
];
