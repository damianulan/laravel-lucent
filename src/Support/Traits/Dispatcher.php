<?php

namespace Lucent\Support\Traits;

trait Dispatcher
{

    protected static function bootDispatcher()
    {
        static::retrieved(function ($model) {
            self::invokeEventMethod('retrieved', $model);
        });
        static::creating(function ($model) {
            self::invokeEventMethod('creating', $model);
        });
        static::created(function ($model) {
            self::invokeEventMethod('created', $model);
        });
        static::updating(function ($model) {
            self::invokeEventMethod('updating', $model);
        });
        static::updated(function ($model) {
            self::invokeEventMethod('updated', $model);
        });
        static::deleting(function ($model) {
            self::invokeEventMethod('deleting', $model);
        });
        static::deleted(function ($model) {
            self::invokeEventMethod('deleted', $model);
        });
        static::restored(function ($model) {
            self::invokeEventMethod('restored', $model);
        });
    }

    private static function invokeEventMethod(string $type, $model): void
    {
        $rc = new \ReflectionClass($model::class);
        $name = $rc->getShortName();
        $method = $type . $name;
        if ($rc->hasMethod($method)) {
            $rc->getMethod($method)->invoke($model, $model);
        }
    }
}
