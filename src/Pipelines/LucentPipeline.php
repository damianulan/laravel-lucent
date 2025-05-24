<?php

namespace Lucent\Pipelines;

use Illuminate\Pipeline\Pipeline;
use Lucent\Pipelines\Pipe;

class LucentPipeline
{
    private array $pipeStack = [];
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

    public function addPipe($pipe): self
    {
        if (class_exists($pipe)) {
            if (is_subclass_of($pipe, Pipe::class)) {
                $this->pipeStack[] = $pipe;
            }
        }

        return $this;
    }

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
