<?php

namespace Lucent\Support;

use ArrayIterator;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use IteratorAggregate;
use JsonSerializable;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use Throwable;
use Traversable;

/**
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @copyright 2026 damianulan
 */
class Trace implements Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable
{
    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $backtraces = [];

    /**
     * @param  array<int, array<string, mixed>>  $backtraces
     */
    final public function __construct(array $backtraces = [])
    {
        $this->backtraces = array_values($backtraces);
    }

    public static function boot(bool $ignoreArgs = true, int $limit = 0): self
    {
        $options = DEBUG_BACKTRACE_PROVIDE_OBJECT;

        if ($ignoreArgs) {
            $options |= DEBUG_BACKTRACE_IGNORE_ARGS;
        }

        return new self(debug_backtrace($options, $limit));
    }

    public static function fromThrowable(Throwable $throwable): self
    {
        return new self($throwable->getTrace());
    }

    /**
     * @param  array<int, array<string, mixed>>  $backtraces
     */
    public static function fromBacktrace(array $backtraces): self
    {
        return new self($backtraces);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->backtraces;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toArray(): array
    {
        return $this->backtraces;
    }

    public function toJson($options = 0): string|false
    {
        return json_encode($this->details(), $options);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function jsonSerialize(): array
    {
        return $this->details();
    }

    public function count(): int
    {
        return count($this->backtraces);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->backtraces);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function get(int $index): ?array
    {
        return $this->backtraces[$index] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        return $this->get(0);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function last(): ?array
    {
        if ($this->backtraces === []) {
            return null;
        }

        return $this->backtraces[array_key_last($this->backtraces)];
    }

    public function filter(callable $callback): self
    {
        return new self(array_values(array_filter(
            $this->backtraces,
            $callback,
            ARRAY_FILTER_USE_BOTH,
        )));
    }

    public function withoutClasses(string|array $classes): self
    {
        $classes = (array) $classes;

        return $this->filter(function (array $frame) use ($classes): bool {
            return ! in_array($frame['class'] ?? null, $classes, true);
        });
    }

    public function onlyClasses(string|array $classes): self
    {
        $classes = (array) $classes;

        return $this->filter(function (array $frame) use ($classes): bool {
            return in_array($frame['class'] ?? null, $classes, true);
        });
    }

    public function withinNamespace(string|array $namespaces): self
    {
        $namespaces = array_map(static fn (string $namespace): string => trim($namespace, '\\') . '\\', (array) $namespaces);

        return $this->filter(function (array $frame) use ($namespaces): bool {
            $class = $frame['class'] ?? null;

            if (! is_string($class)) {
                return false;
            }

            foreach ($namespaces as $namespace) {
                if (str_starts_with($class, $namespace)) {
                    return true;
                }
            }

            return false;
        });
    }

    public function outsideNamespace(string|array $namespaces): self
    {
        $namespaces = array_map(static fn (string $namespace): string => trim($namespace, '\\') . '\\', (array) $namespaces);

        return $this->filter(function (array $frame) use ($namespaces): bool {
            $class = $frame['class'] ?? null;

            if (! is_string($class)) {
                return true;
            }

            foreach ($namespaces as $namespace) {
                if (str_starts_with($class, $namespace)) {
                    return false;
                }
            }

            return true;
        });
    }

    public function whereFunction(string|array $functions): self
    {
        $functions = (array) $functions;

        return $this->filter(function (array $frame) use ($functions): bool {
            return in_array($frame['function'] ?? null, $functions, true);
        });
    }

    public function inPath(string|array $paths): self
    {
        $paths = array_map([$this, 'normalizePath'], (array) $paths);

        return $this->filter(function (array $frame) use ($paths): bool {
            $file = $frame['file'] ?? null;

            if (! is_string($file)) {
                return false;
            }

            $file = $this->normalizePath($file);

            foreach ($paths as $path) {
                if (str_starts_with($file, $path)) {
                    return true;
                }
            }

            return false;
        });
    }

    public function onlyApplicationFrames(?string $basePath = null): self
    {
        $basePath ??= $this->resolveBasePath();

        return $this->inPath($basePath)->outsideNamespace(__NAMESPACE__);
    }

    public function withoutVendorFrames(?string $basePath = null): self
    {
        $basePath ??= $this->resolveBasePath();
        $vendorPath = $this->normalizePath($basePath . DIRECTORY_SEPARATOR . 'vendor');

        return $this->filter(function (array $frame) use ($vendorPath): bool {
            $file = $frame['file'] ?? null;

            if (! is_string($file)) {
                return true;
            }

            return ! str_starts_with($this->normalizePath($file), $vendorPath);
        });
    }

    /**
     * Returns the first userland frame after Trace itself.
     *
     * @return array<string, mixed>|null
     */
    public function caller(int $depth = 0): ?array
    {
        return $this
            ->outsideNamespace(__NAMESPACE__)
            ->get($depth);
    }

    public function reflection(int $index = 0): ?ReflectionFunctionAbstract
    {
        return static::reflectionForFrame($this->get($index));
    }

    /**
     * @param  array<string, mixed>|null  $frame
     */
    public static function reflectionForFrame(?array $frame): ?ReflectionFunctionAbstract
    {
        if ($frame === null) {
            return null;
        }

        $function = $frame['function'] ?? null;

        if (! is_string($function) || $function === '{closure}') {
            return null;
        }

        try {
            if (isset($frame['class']) && is_string($frame['class'])) {
                $reflection = new ReflectionClass($frame['class']);

                if ($reflection->hasMethod($function)) {
                    return $reflection->getMethod($function);
                }
            }

            if (function_exists($function)) {
                return new ReflectionFunction($function);
            }
        } catch (ReflectionException) {
            return null;
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function details(bool $withSignature = true): array
    {
        $details = [];

        foreach ($this->backtraces as $index => $frame) {
            $reflection = static::reflectionForFrame($frame);
            $class = $frame['class'] ?? null;

            $details[] = [
                'index' => $index,
                'class' => $class,
                'short_class' => is_string($class) ? class_basename($class) : null,
                'function' => $frame['function'] ?? null,
                'callable' => $this->formatCallable($frame),
                'file' => $frame['file'] ?? null,
                'line' => $frame['line'] ?? null,
                'namespace' => $this->resolveNamespace($class, $reflection),
                'is_vendor' => $this->isVendorFrame($frame),
                'is_internal' => ! isset($frame['file']),
                'signature' => $withSignature ? $this->formatSignature($reflection) : null,
            ];
        }

        return $details;
    }

    /**
     * @return array<int, string>
     */
    public function steps(bool $oldestFirst = false, bool $withSignature = false): array
    {
        $frames = $this->backtraces;

        if ($oldestFirst) {
            $frames = array_reverse($frames);
        }

        return array_map(function (array $frame) use ($withSignature): string {
            return $this->describeFrame($frame, $withSignature);
        }, $frames);
    }

    /**
     * @param  array<string, mixed>|null  $frame
     */
    public function describeFrame(?array $frame, bool $withSignature = false): string
    {
        if ($frame === null) {
            return 'unknown';
        }

        $callable = $this->formatCallable($frame);
        $file = $frame['file'] ?? '[internal]';
        $line = $frame['line'] ?? '?';
        $description = sprintf('%s at %s:%s', $callable, $file, $line);

        if (! $withSignature) {
            return $description;
        }

        $signature = $this->formatSignature(static::reflectionForFrame($frame));

        if ($signature === null) {
            return $description;
        }

        return sprintf('%s [%s]', $description, $signature);
    }

    /**
     * @param  array<string, mixed>  $frame
     */
    protected function formatCallable(array $frame): string
    {
        $class = $frame['class'] ?? null;
        $type = $frame['type'] ?? '::';
        $function = $frame['function'] ?? 'unknown';

        if (! is_string($class) || $class === '') {
            return $function . '()';
        }

        return $class . $type . $function . '()';
    }

    protected function formatSignature(?ReflectionFunctionAbstract $reflection): ?string
    {
        if ($reflection === null) {
            return null;
        }

        $parameters = array_map(function ($parameter): string {
            $type = $parameter->hasType() ? $parameter->getType() . ' ' : '';
            $variadic = $parameter->isVariadic() ? '...' : '';
            $default = '';

            if ($parameter->isDefaultValueAvailable()) {
                $defaultValue = $parameter->getDefaultValue();

                if (is_string($defaultValue)) {
                    $default = sprintf(" = '%s'", $defaultValue);
                } elseif (is_bool($defaultValue)) {
                    $default = $defaultValue ? ' = true' : ' = false';
                } elseif ($defaultValue === null) {
                    $default = ' = null';
                } elseif (is_scalar($defaultValue)) {
                    $default = sprintf(' = %s', $defaultValue);
                } else {
                    $default = ' = ...';
                }
            }

            return sprintf('%s%s$%s%s', $type, $variadic, $parameter->getName(), $default);
        }, $reflection->getParameters());

        $signature = sprintf('%s(%s)', $reflection->getName(), implode(', ', $parameters));

        if ($reflection->hasReturnType()) {
            $signature .= ': ' . $reflection->getReturnType();
        }

        return $signature;
    }

    /**
     * @param  array<string, mixed>  $frame
     */
    protected function isVendorFrame(array $frame): bool
    {
        $file = $frame['file'] ?? null;

        if (! is_string($file)) {
            return false;
        }

        $basePath = $this->resolveBasePath();
        $vendorPath = $this->normalizePath($basePath . DIRECTORY_SEPARATOR . 'vendor');

        return str_starts_with($this->normalizePath($file), $vendorPath);
    }

    protected function resolveNamespace(
        mixed $class,
        ?ReflectionFunctionAbstract $reflection = null,
    ): ?string {
        if ($reflection instanceof \ReflectionMethod) {
            return $reflection->getDeclaringClass()->getNamespaceName();
        }

        if ($reflection instanceof ReflectionFunction) {
            return $reflection->getNamespaceName();
        }

        if (! is_string($class)) {
            return null;
        }

        try {
            return (new ReflectionClass($class))->getNamespaceName();
        } catch (ReflectionException) {
            return null;
        }
    }

    protected function resolveBasePath(): string
    {
        if (function_exists('base_path')) {
            return base_path();
        }

        return getcwd() ?: '';
    }

    protected function normalizePath(string $path): string
    {
        return rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
    }
}
