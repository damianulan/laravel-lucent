<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Lucent Configuration file v1.0
    |--------------------------------------------------------------------------
    |
    | These are package's default configuration options.
    |
*/

    'models' => array(

        // days to prune after soft deleted records on models that use `SoftDeletes` and `Lucent\Support\Traits\SoftDeletesPrunable` traits
        'prune_soft_deletes_days' => env('PRUNE_SOFT_DELETES_DAYS', 365),

        // if relations are not defined in model, should relations delete cascade as defined in `cascade_delete_relation_types` array.
        'auto_cascade_deletes' => true,

        // relation types that should be deleted when model using `CascadeDeletes` trait is deleted
        'cascade_delete_relation_types' => array(
            'Illuminate\Database\Eloquent\Relations\MorphMany',
            'Illuminate\Database\Eloquent\Relations\MorphToMany',
            'Illuminate\Database\Eloquent\Relations\BelongsToMany',
            'Illuminate\Database\Eloquent\Relations\HasMany',
            'Illuminate\Database\Eloquent\Relations\HasOne',
        ),
    ),

    // Additional setting for mews/purifier package
    // corresponds with clean_html() helper function
    'mews_purifier_setting' => array(
        'HTML.Allowed' => 'h1,h2,h3,h4,h5,h6,b,strong,i,em,u,a[href|title|target],ul,ol,li,p[style|class],br,span[style|class],img[width|height|alt|src],blockquote,pre,code,table,thead,tbody,tr,th,td',
        'CSS.AllowedProperties' => 'font-weight,font-style,text-decoration,padding-left,color,background-color,text-align',
        'HTML.Nofollow' => true,
        'HTML.TargetBlank' => true,
        'URI.AllowedSchemes' => array(
            'http' => true,
            'https' => true,
            'mailto' => true,
        ),
        'Attr.AllowedClasses' => array(
            'ql-align-center',
            'ql-align-right',
            'ql-align-justify',
            'text-center', // Bootstrap
            'text-right',
        ),
    ),

);
