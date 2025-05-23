<?php

namespace Lucent\Helpers;

use Illuminate\Support\Facades\Process;

/**
 * Get git info from current repository and run popular git commands at hand.
 *
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @package Lucent
 * @copyright 2025 damianulan
 */
class Git
{
    /**
     * executable command
     *
     * @var mixed
     */
    protected $execs = [];

    /**
     * command name / method type
     *
     * @var mixed
     */
    protected $command;

    public function get()
    {
        return $this->exec;
    }

    public function run(): ?string
    {
        $result = null;
        foreach ($this->execs as $exec) {
            $e = Process::run($exec);
            $result = $e->output();
        }

        return $result;
    }

    public static function head(): string
    {
        $file = file_get_contents(base_path() . '/.git/HEAD');
        $ref = "ref: refs/heads/";
        return trim(substr($file, strpos($file, $ref) + strlen($ref)));
    }

    public static function checkoutLatestRelease(): self
    {
        return self::register('git fetch --tags', 'git checkout $(git tag | sort -V | tail -n 1)');
    }

    private static function register(...$exec): self
    {
        $trace = debug_backtrace();
        $command = $trace[3]['function'] ?? null;
        $instance = new self();
        $instance->exec = $exec;
        $instance->command = $command;
        return $instance;
    }
}
