<?php

namespace Lucent\Support\Traits;

use Illuminate\Support\Str;

trait UUID
{
    protected static function bootUUID()
    {
        static::retrieved(function ($model) {
            $model->incrementing = false;  // this is used after instance is loaded from DB
        });
        /**
         * Listen for the creating event on the user model.
         * Sets the 'id' to a UUID using Str::uuid() on the instance being created
         */
        static::creating(function ($model) {
            if ($model->getKey() === null) {
                $model->setAttribute($model->getKeyName(), Str::uuid()->toString());
            }
        });
    }

    // Tells the database not to auto-increment this field
    public function getIncrementing()
    {
        return false;
    }

    // Helps the application specify the field type in the database
    public function getKeyType()
    {
        return 'string';
    }
}
