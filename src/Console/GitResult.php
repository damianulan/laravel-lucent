<?php

namespace Lucent\Console;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

class GitResult implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * @param  array<int, string>  $command
     */
    public function __construct(
        protected array $command,
        protected string $workingDirectory,
        protected string $output,
        protected string $errorOutput,
        protected int $exitCode,
        protected ?string $invoker = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function command(): array
    {
        return $this->command;
    }

    public function commandString(): string
    {
        return implode(' ', $this->command);
    }

    public function workingDirectory(): string
    {
        return $this->workingDirectory;
    }

    public function output(): string
    {
        return $this->output;
    }

    public function errorOutput(): string
    {
        return $this->errorOutput;
    }

    public function exitCode(): int
    {
        return $this->exitCode;
    }

    public function invoker(): ?string
    {
        return $this->invoker;
    }

    public function successful(): bool
    {
        return 0 === $this->exitCode;
    }

    public function failed(): bool
    {
        return ! $this->successful();
    }

    public function toArray(): array
    {
        return [
            'command' => $this->command,
            'command_string' => $this->commandString(),
            'working_directory' => $this->workingDirectory,
            'output' => $this->output,
            'error_output' => $this->errorOutput,
            'exit_code' => $this->exitCode,
            'successful' => $this->successful(),
            'invoker' => $this->invoker,
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
}
