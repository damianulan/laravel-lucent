<?php

namespace Lucent\Support\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

/**
 * Adds cascade deletes support to your Eloquent models.
 *
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @copyright 2026 damianulan
 *
 */
trait CascadeDeletes
{
    protected static function bootCascadeDeletes(): void
    {
        static::deleted(function (Model $model): void {
            try {
                $relations = isset($model->cascadeDelete) && is_array($model->cascadeDelete) ? $model->cascadeDelete : [];

                $auto_delete = (bool) config('lucent.models.auto_cascade_deletes', true);
                if (empty($relations) && $auto_delete) {
                    $reflection = new ReflectionClass($model);
                    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

                    $whiteList = config('lucent.models.cascade_delete_relation_types', []);
                    $blackList = [];
                    if (isset($model->donotCascadeDelete) && is_array($model->donotCascadeDelete)) {
                        $blackList = $model->donotCascadeDelete;
                    }

                    $relations = array_map(fn ($needle) => $needle->getName(), array_filter($methods, function ($method) use ($whiteList, $blackList) {
                        $returnType = $method->getReturnType() ?? null;

                        return in_array($returnType, $whiteList) && ! in_array($returnType, $blackList);
                    }));
                }

                if ( ! empty($relations)) {
                    $model->load($relations);
                    foreach ($relations as $method) {
                        $relation = $model->{$method} ?? null;
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
            } catch (Throwable $e) {
                if (true === config('app.debug')) {
                    throw $e;
                }
            }
        });
    }
}
