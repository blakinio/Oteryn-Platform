<?php

return [
    'header' => [
        [
            'label' => 'Download',
            'route' => 'downloads.index',
            'active' => 'downloads.*',
            'priority' => 60,
        ],
    ],
    'footer' => [
        [
            'key' => 'learn',
            'label' => 'Learn',
            'priority' => 20,
            'items' => [
                [
                    'label' => 'Download',
                    'route' => 'downloads.index',
                    'active' => 'downloads.*',
                    'priority' => 10,
                ],
            ],
        ],
    ],
];
