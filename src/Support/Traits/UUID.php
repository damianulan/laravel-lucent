<?php

namespace Lucent\Support\Traits;

use Illuminate\Support\Str;

/**
 * Adds UUIDv4 as primary key support to your Eloquent models.
 *
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @copyright 2025 damianulan
 */
trait UUID
{
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

    protected static function bootUUID(): void
    {
        static::retrieved(function ($model): void {
            $model->incrementing = false;
        });

        /**
         * Listen for the creating event on the user model.
         * Sets the 'id' to a UUID using Str::uuid() on the instance being created
         */
        static::creating(function ($model): void {
            if (null === $model->getKey()) {
                $model->setAttribute($model->getKeyName(), Str::uuid()->toString());
            }
        });
    }
}
