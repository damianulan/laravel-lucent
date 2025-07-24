<?php

namespace Lucent\Pipelines;

use Closure;

interface Pipe
{
    /**
     * Handle the pipe logic.
     */
    public function handle(mixed $value, Closure $next): string;
}
