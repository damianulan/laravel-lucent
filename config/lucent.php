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

        // days to prune after soft deleted records on models that use `SoftDeletes` and `Lucent\Support\Traits\SoftDeletesPrunable` traits
        'prune_soft_deletes_days' => env('PRUNE_SOFT_DELETES_DAYS', 365),

        // relation types that should be deleted when model using `CascadeDeletes` trait is deleted
        'cascade_delete_relation_types' => [
            'Illuminate\Database\Eloquent\Relations\MorphMany',
            'Illuminate\Database\Eloquent\Relations\MorphToMany',
            'Illuminate\Database\Eloquent\Relations\BelongsToMany',
            'Illuminate\Database\Eloquent\Relations\HasMany',
            'Illuminate\Database\Eloquent\Relations\HasOne',
        ]
    ]

];
