<?php

namespace Lucent\Support\Traits;

use ReflectionClass;

/**
 * Adds support for method-based model event listeners, so that global boot() methods won't get overriden.
 * In your model create event static methods like created{ModelName}, updated{ModelName}() etc.
 *
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @copyright 2025 damianulan
 *
 * @deprecated 1.2. will be removed
 * It collides with laravel's model events in unexpected ways in certain configurations.
 */
trait Dispatcher
{
    protected static function bootDispatcher(): void
    {
        static::retrieved(function ($model): void {
            self::invokeEventMethod('retrieved', $model);
        });
        static::creating(function ($model): void {
            self::invokeEventMethod('creating', $model);
        });
        static::created(function ($model): void {
            self::invokeEventMethod('created', $model);
        });
        static::updating(function ($model): void {
            self::invokeEventMethod('updating', $model);
        });
        static::updated(function ($model): void {
            self::invokeEventMethod('updated', $model);
        });
        static::deleting(function ($model): void {
            self::invokeEventMethod('deleting', $model);
        });
        static::deleted(function ($model): void {
            self::invokeEventMethod('deleted', $model);
        });
        static::restored(function ($model): void {
            self::invokeEventMethod('restored', $model);
        });
    }

    private static function invokeEventMethod(string $type, $model): void
    {
        $rc = new ReflectionClass($model::class);
        $name = $rc->getShortName();
        $method = $type . $name;
        if ($rc->hasMethod($method)) {
            $rc->getMethod($method)->invoke($model, $model);
        }
    }
}
