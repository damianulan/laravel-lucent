<?php

namespace Lucent\Pipelines;

use Closure;

interface Pipe
{
    /**
     * Handle the pipe logic.
     *
     * @param  mixed  $value
     * @param  Closure  $next
     * @return string
     */
    public function handle(mixed $value, Closure $next): string;
}
