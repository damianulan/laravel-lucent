<?php

namespace Lucent\Console;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

/**
 * Get git info from current repository and run popular git commands at hand.
 *
 * @author Damian Ułan <damian.ulan@protonmail.com>
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

    public function __construct($exec, $command = null)
    {
        $this->execs = $exec;
        $this->command = $command;
    }

    public function get()
    {
        return $this->execs;
    }

    public function run(): ?string
    {
        $result = null;
        foreach ($this->execs as $exec) {
            $e = Process::run($exec);
            $result = $e->output();
            Log::debug("Lucent Git command {$exec} output: '$result'.");
        }

        return $result;
    }

    public static function head(): string
    {
        $file = file_get_contents(base_path() . '/.git/HEAD');
        $ref = 'ref: refs/heads/';

        return trim(substr($file, strpos($file, $ref) + strlen($ref)));
    }

    public static function getLatestTagName(): string
    {
        return self::register('git fetch --tags', 'git describe --tags --abbrev=0')->run();
    }

    /**
     * Get all tags in your main repository. They are already sorted by the newest.
     *
     * @return array
     */
    public static function getTags(): array
    {
        $tags = array();
        $raw = self::register('git fetch --tags', "git tag --sort=creatordate")->run();
        if (!empty($raw)) {
            $tags = array_filter(explode("\n", $raw), function ($item) {
                $blacklist = ['origin', 'composer'];
                return !empty($item) && !in_array($item, $blacklist);
            });

            $tags = array_reverse($tags);
        }

        return $tags;
    }

    /**
     * Checkout your main repository to a given tag.
     *
     * @param string $tag
     * @return string - output
     */
    public static function checkoutRelease(string $tag): string
    {
        return self::register("git fetch --tags", "git checkout $tag")->run();
    }

    /**
     * Checkout to latest release
     *
     * @return string - output
     */
    public static function checkoutLatestRelease(): string
    {
        return self::register('git fetch --tags', 'git checkout $(git tag | sort -V | tail -n 1)')->run();
    }

    private static function register(...$exec): self
    {
        $trace = debug_backtrace();
        $command = $trace[3]['function'] ?? null;
        $instance = new self($exec, $command);

        if (is_array($exec)) {
            $exec = implode('; ', $exec);
        }
        Log::debug("Lucent Git command {$exec} initialized.");

        return $instance;
    }
}
