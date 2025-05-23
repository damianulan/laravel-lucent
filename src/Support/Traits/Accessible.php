<?php

namespace Lucent\Support\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Accessible
{
    public function scopeCheckAccess(Builder $builder): void
    {
        if (isset($this->accessScope) && !empty($this->accessScope)) {
            $scope = $this->accessScope;
            if (class_exists($scope)) {
                $instance = new $scope();
                $instance->apply($builder, $this);
            }
        }
    }
}
