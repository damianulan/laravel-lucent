<?php

namespace Lucent\Support\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Determine soft deleted records that are older than a certain number of days, destined for pruning.
 *
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @copyright 2025 damianulan
 */
trait SoftDeletesPrunable
{
    public function scopePrunableSoftDeletes(Builder $builder): void
    {
        $builder->onlyTrashed()->where('deleted_at', '<', now()->subDays(config('lucent.models.prune_soft_deletes_days')));
    }
}
