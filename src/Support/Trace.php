<?php

namespace Lucent\Support;

use Illuminate\Support\Facades\Process;

/**
 *
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @package Lucent
 * @copyright 2025 damianulan
 */
class Trace
{
    protected $backtraces = [];

    public static function boot(): self
    {
        $instance = new self();
        $instance->backtraces = debug_backtrace();
        return $instance;
    }
}
