<?php

namespace Lucent\Support\Traits;

use Illuminate\Database\Eloquent\Builder;

trait VirginModel
{
    public function empty()
    {
        return empty($this->id) || !$this->exists();
    }

    public function notEmpty()
    {
        return !empty($this->id) && $this->exists();
    }

    /**
     * Get all without global scopes. Be careful since it does not respect permission based access.
     *
     * @return void
     */
    public static function getAll()
    {
        return static::withoutGlobalScopes()->get();
    }

    public static function allActive()
    {
        return static::active()->get();
    }

    public static function allInactive()
    {
        return static::inactive()->get();
    }

    /**
     * QUERY LOCAL SCOPES
     */

    public function scopeActive(Builder $query): void
    {
        if (in_array('active', $this->fillable)) {
            $query->where('active', 1);
        }
    }

    public function scopeInactive(Builder $query)
    {
        if (in_array('active', $this->fillable)) {
            $query->where('active', 0);
        }
    }

    public function scopePublished(Builder $query)
    {
        if (in_array('draft', $this->fillable)) {
            $query->where('draft', 0);
        }
    }

    public function scopeDrafted(Builder $query)
    {
        if (in_array('draft', $this->fillable)) {
            $query->where('draft', 1);
        }
    }
}
