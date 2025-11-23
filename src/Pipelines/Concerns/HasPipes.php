<?php

namespace Lucent\Pipelines\Concerns;

trait HasPipes
{
    /**
     * Run pipes ad hoc
     *
     * @return mixed
     */
    public function runPipeline()
    {
        return self::pushPipes($this);
    }

    protected static function bootHasPipes(): void
    {
        static::updating(function ($model) {
            if (! self::pushPipes($model)) {
                return false;
            }
        });

        static::creating(function ($model) {
            if (! self::pushPipes($model)) {
                return false;
            }
        });
    }

    /**
     * @param  mixed  $model
     * @return mixed
     */
    private static function pushPipes($model)
    {
        $result = false;
        if (isset($model->pipes) && is_array($model->pipes) && count($model->pipes)) {
            $pipelines = array();
            $result = true;

            foreach ($model->pipes as $attr => $pipe) {
                $pipelines[$attr][] = $pipe;
            }

            foreach ($pipelines as $attr => $pipes) {
                if ('*' === $attr) {
                    $attr = $model;
                }

                $result = LucentPipeline::make($pipes)->put($attr)->send();
                if (! $result) {
                    $result = false;
                    break;
                }
            }
        }

        return $result;
    }
}
