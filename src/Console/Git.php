<?php

namespace Lucent\Console;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use JsonSerializable;
use Lucent\Support\Trace;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Inspect and execute git commands in a structured way.
 *
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @copyright 2026 damianulan
 */
class Git implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * @var Collection<int, array<int, string>>
     */
    protected Collection $commands;

    /**
     * @var Collection<int, GitResult>
     */
    protected Collection $results;

    public function __construct(
        protected ?string $repositoryPath = null,
        protected ?string $invoker = null,
    ) {
        $this->repositoryPath = $repositoryPath ?: base_path();
        $this->invoker = $invoker ?: static::resolveInvoker();
        $this->commands = new Collection();
        $this->results = new Collection();
    }

    public static function repository(?string $path = null): self
    {
        return new self($path);
    }

    public static function head(?string $path = null): string
    {
        return static::repository($path)->branch();
    }

    public static function getLatestTagName(?string $path = null): string
    {
        return static::repository($path)
            ->fetchTags()
            ->latestTag();
    }

    /**
     * Get all tags in your main repository. They are already sorted by the newest.
     */
    public static function getTags(?string $path = null, bool $fetch = true): array
    {
        $git = static::repository($path);

        if ($fetch) {
            $git->fetchTags();
        }

        return $git->tags();
    }

    public static function checkoutRelease(string $tag, ?string $path = null): string
    {
        return static::repository($path)
            ->fetchTags()
            ->checkout($tag)
            ->lastOutput();
    }

    public static function checkoutLatestRelease(?string $path = null): string
    {
        $git = static::repository($path)->fetchTags();

        return $git->checkout($git->latestTag())->lastOutput();
    }

    public function branch(): string
    {
        $result = $this->runCommand(['git', 'rev-parse', '--abbrev-ref', 'HEAD']);

        return trim($result->output());
    }

    /**
     * @return array<int, string>
     */
    public function tags(): array
    {
        $result = $this->runCommand(['git', 'tag', '--sort=-version:refname']);
        $blacklist = ['origin', 'composer'];

        return array_values(array_filter(
            preg_split('/\r\n|\r|\n/', trim($result->output())) ?: [],
            static fn (string $item): bool => '' !== $item && ! in_array($item, $blacklist, true),
        ));
    }

    public function latestTag(): string
    {
        $tag = $this->tags()[0] ?? null;

        if (null === $tag) {
            throw new RuntimeException('No git tags found.');
        }

        return $tag;
    }

    public function fetchTags(): self
    {
        if ( ! $this->hasRemotes()) {
            return $this;
        }

        $this->runCommand(['git', 'fetch', '--tags']);

        return $this;
    }

    public function checkout(string $reference): self
    {
        $this->runCommand(['git', 'checkout', $reference]);

        return $this;
    }

    /**
     * @param  array<int, string>  $command
     */
    public function queue(array $command): self
    {
        $this->commands->push($command);

        return $this;
    }

    public function run(): self
    {
        $queued = $this->commands->values()->all();
        $this->commands = new Collection();

        foreach ($queued as $command) {
            $this->runCommand($command);
        }

        return $this;
    }

    public function lastResult(): ?GitResult
    {
        return $this->results->last();
    }

    /**
     * @return array<int, GitResult>
     */
    public function results(): array
    {
        return $this->results->all();
    }

    public function lastOutput(): string
    {
        return $this->lastResult()?->output() ?? '';
    }

    public function successful(): bool
    {
        return $this->results->every(static fn (GitResult $result): bool => $result->successful());
    }

    public function failed(): bool
    {
        return ! $this->successful();
    }

    public function toArray(): array
    {
        return [
            'repository_path' => $this->repositoryPath,
            'invoker' => $this->invoker,
            'queued_commands' => $this->commands->values()->all(),
            'results' => array_map(
                static fn (GitResult $result): array => $result->toArray(),
                $this->results(),
            ),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toJson($options = 0): string|false
    {
        return json_encode($this->toArray(), $options);
    }

    protected static function resolveInvoker(): ?string
    {
        return Trace::boot()
            ->outsideNamespace(['Lucent\\Support', __NAMESPACE__])
            ->first()['function'] ?? null;
    }

    /**
     * @param  array<int, string>  $command
     */
    protected function runCommand(array $command): GitResult
    {
        $process = new Process($command, $this->repositoryPath);
        $process->run();

        $result = new GitResult(
            command: $command,
            workingDirectory: $this->repositoryPath,
            output: $process->getOutput(),
            errorOutput: $process->getErrorOutput(),
            exitCode: $process->getExitCode() ?? 1,
            invoker: $this->invoker,
        );

        $this->results->push($result);

        $this->logResult($result);

        if ($result->failed()) {
            throw new RuntimeException($result->errorOutput() ?: $result->output() ?: 'Git command failed.');
        }

        return $result;
    }

    protected function hasRemotes(): bool
    {
        $result = new Process(['git', 'remote'], $this->repositoryPath);
        $result->run();

        return '' !== trim($result->getOutput());
    }

    protected function logResult(GitResult $result): void
    {
        try {
            Log::debug('Lucent Git command executed.', $result->toArray());
        } catch (Throwable) {
            // Config files may call Git before facades are bootstrapped.
        }
    }
}
