<?php

namespace Lucent\Support\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Adds cascade deletes support to your Eloquent models.
 *
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @copyright 2025 damianulan
 * @link https://github.com/damianulan/laravel-lucent/blob/main/docs/TRAITS.md#cascadedeletes
 */
trait CascadeDeletes
{
    protected static function bootCascadeDeletes()
    {
        static::deleted(function (Model $model) {
            try {
                $relations = isset($model->cascadeDelete) && is_array($model->cascadeDelete) ? $model->cascadeDelete : [];

                if (empty($relations)) {
                    $reflection = new \ReflectionClass($model);
                    $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

                    $whiteList = config('lucent.models.cascade_delete_relation_types', []);
                    $blackList = array();
                    if (isset($model->donotCascadeDelete) && is_array($model->donotCascadeDelete)) {
                        $blackList = $model->donotCascadeDelete;
                    }

                    $relations = array_map(function ($needle) {
                        return $needle->getName();
                    }, array_filter($methods, function ($method) use ($whiteList, $blackList) {
                        $returnType = $method->getReturnType() ?? null;

                        return in_array($returnType, $whiteList) && !in_array($returnType, $blackList);
                    }));
                }

                if (!empty($relations)) {
                    $model->load($relations);
                    foreach ($relations as $method) {
                        $relation = $model->$method ?? null;
                        if ($relation) {
                            if ($relation instanceof Model) {
                                $relation->delete();
                            } elseif ($relation instanceof Collection) {
                                if ($relation->count()) {
                                    foreach ($relation as $rel) {
                                        $rel->delete();
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                if (config('app.debug') === true) {
                    throw $e;
                }
            }
        });
    }
}
