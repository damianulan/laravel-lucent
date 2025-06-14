<?php

namespace Lucent\Support\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Scope;

/**
 * Assign access scopes to your Eloquent models.
 * invoke as a Model::checkAccess()->get()
 * 
 * eg.: protected $accessScope = UserScope::class;
 *
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @copyright 2025 damianulan
 * @package Lucent
 */
trait Accessible
{
    public function scopeCheckAccess(Builder $builder): void
    {
        if (isset($this->accessScope) && !empty($this->accessScope)) {
            $scope = $this->accessScope;
            if (class_exists($scope)) {
                if (is_subclass_of($scope, Scope::class)) {
                    $instance = new $scope();
                    $instance->apply($builder, $this);
                }
            }
        }
    }
}
