<?php

return [
    'driver' => env('BROADCAST_DRIVER', 'log'),
    'connections' => [
        'log' => ['driver' => 'log'],
        'null' => ['driver' => 'null'],
    ],
];
