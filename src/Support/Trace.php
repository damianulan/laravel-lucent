<?php

namespace Lucent\Support;

/**
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @copyright 2025 damianulan
 */
class Trace
{
    protected $backtraces = array();

    public static function boot(): self
    {
        $instance = new self();
        $instance->backtraces = debug_backtrace();

        return $instance;
    }
}
