<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lucent Configuration file v1.0
    |--------------------------------------------------------------------------
    |
    | These are package's default configuration options.
    |
*/

    'models' => [
        'prune_soft_deletes_days' => env('PRUNE_SOFT_DELETES_DAYS', 365),
    ]

];
