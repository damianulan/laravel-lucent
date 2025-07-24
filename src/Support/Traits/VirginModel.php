<?php

namespace Lucent\Support\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Adds operational helpers to Eloquent model, that is based on common boolean attribute flags such as 'draft' and 'active'.
 *
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @copyright 2025 damianulan
 */
trait VirginModel
{
    /**
     * Checks if the model is empty (i.e., has no ID or is not persisted).
     */
    public function empty(): bool
    {
        $key = $this->getKeyName();

        return empty($this->$key) || ! $this->exists();
    }

    /**
     * Checks if the model is not empty (i.e., has an ID and is not persisted).
     */
    public function notEmpty(): bool
    {
        $key = $this->getKeyName();

        return ! empty($this->$key) && $this->exists();
    }

    /**
     * Get all model records without any global scopes.
     */
    public static function getAll(): Collection
    {
        return static::withoutGlobalScopes()->get();
    }

    /**
     * Get records based on 'active' == 1 attribute.
     */
    public static function allActive(): Collection
    {
        return static::active()->get();
    }

    /**
     * Get records based on 'active' == 0 attribute.
     */
    public static function allInactive(): Collection
    {
        return static::inactive()->get();
    }

    /**
     * Get records based on 'draft' == 0 attribute.
     */
    public static function allPublished(): Collection
    {
        return static::published()->get();
    }

    /**
     * Get records based on 'draft' == 1 attribute.
     */
    public static function allDrafts(): Collection
    {
        return static::drafted()->get();
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

    public function scopeInactive(Builder $query): void
    {
        if (in_array('active', $this->fillable)) {
            $query->where('active', 0);
        }
    }

    public function scopePublished(Builder $query): void
    {
        if (in_array('draft', $this->fillable)) {
            $query->where('draft', 0);
        }
    }

    public function scopeDrafted(Builder $query): void
    {
        if (in_array('draft', $this->fillable)) {
            $query->where('draft', 1);
        }
    }
}
