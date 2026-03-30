<?php

namespace Lucent\Pipelines;

use Closure;

/**
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @copyright 2026 damianulan
 *
 * @deprecated 1.2. will be removed
 */
interface Pipe
{
    /**
     * Handle the pipe logic.
     */
    public function handle(mixed $value, Closure $next): string;
}
