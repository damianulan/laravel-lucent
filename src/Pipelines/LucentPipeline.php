<?php

namespace Lucent\Pipelines;

use Illuminate\Pipeline\Pipeline;

/**
 * Handles custom laravel pipelines coupled with models.
 *
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @copyright 2025 damianulan
 */
class LucentPipeline
{
    private array $pipeStack = array();

    private $sendable;

    public function __construct(array $pipes)
    {
        foreach ($pipes as $pipe) {
            $this->addPipe($pipe);
        }
    }

    public static function make(array $pipes): self
    {
        return new self($pipes);
    }

    /**
     * Add more pipes on the way.
     *
     * @param  mixed  $pipe
     */
    public function addPipe($pipe): self
    {
        if (class_exists($pipe)) {
            if (is_subclass_of($pipe, Pipe::class)) {
                $this->pipeStack[] = $pipe;
            }
        }

        return $this;
    }

    /**
     * Put ptoperty to the pipe.
     */
    public function put(mixed $value): self
    {
        $this->sendable = $value;

        return $this;
    }

    public function send()
    {
        return app(Pipeline::class)
            ->send($this->sendable)
            ->through($this->pipeStack)
            ->via('handle')

            ->thenReturn();
    }
}
