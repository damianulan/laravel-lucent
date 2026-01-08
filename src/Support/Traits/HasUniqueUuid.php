<?php

namespace Lucent\Support\Traits;

use Illuminate\Support\Str;

/**
 * Adds UUIDv4 as a unique key support to your Eloquent models.
 *
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @copyright 2026 damianulan
 */
trait HasUniqueUuid
{
    // Helps the application specify the field type in the database
    public static function getUuidKeyType()
    {
        return 'string';
    }

    /**
     * Override key name if you named uuid column differently.
     */
    public static function getUuidKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get model's unique uuid key
     *
     * @return string
     */
    public function getUuidKey()
    {
        return $this->getAttribute($this->getUuidKeyName());
    }

    protected static function bootHasUniqueUuid(): void
    {
        /**
         * Listen for the creating event on the user model.
         * Sets UUID using Str::uuid() on the instance being created
         */
        static::creating(function ($model): void {
            if (null === $model->getUuidKey()) {
                $model->setAttribute($model->getUuidKeyName(), Str::uuid()->toString());
            }
        });
    }

    /**
     * Find model instance by uuid key.
     *
     * @param string $uuid
     * @return static|null
     */
    public static function findByUuid(string $uuid): ?static
    {
        return static::where(self::getUuidKeyName(), $uuid)->first();
    }
}
