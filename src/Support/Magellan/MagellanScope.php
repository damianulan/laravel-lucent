<?php

namespace Lucent\Support\Magellan;

use ArrayIterator;
use Closure;
use Countable;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use IteratorAggregate;
use Lucent\Support\Magellan\Workshop\ScopeUsesCache;
use ReflectionClass;
use Throwable;
use Traversable;

/**
 * @method static MagellanScope blacklist(string|iterable<string> $classes)
 * @method static MagellanScope filter(Closure $callback)
 *
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @copyright 2026 damianulan
 */
class MagellanScope implements Arrayable, Countable, IteratorAggregate, Jsonable
{
    /**
     * Class blacklist
     *
     * @var array
     */
    protected $blacklist = [];

    /**
     * Collection of classes filtered by scopes
     *
     * @var array
     */
    protected $collection = [];

    /**
     * Callbacks attached by filter()
     *
     * @var array
     */
    protected $callbacks = [];

    /**
     * Remembered instance
     *
     * @var mixed
     */
    protected static $instance = null;

    /**
     * Informs whether cache was used to collect classes
     *
     * @var bool
     */
    private $cache = false;

    public function __call($method, $arguments)
    {
        $reflection = new ReflectionClass($this);
        if ($reflection->hasMethod($method)) {
            return $reflection->getMethod($method)->invokeArgs($this, $arguments);
        }
        throw new Exception("Method [{$method}] not found");
    }

    public static function __callStatic($method, $arguments)
    {
        // if static method is called, we need to call fresh instance
        $instance = static::getInstance();
        if ($instance) {
            $reflection = new ReflectionClass($instance);
            if ($reflection->hasMethod($method)) {
                return $reflection->getMethod($method)->invokeArgs($instance, $arguments);
            }
        }
        throw new Exception("Method [{$method}] not found");
    }

    /**
     * Get unfiltered class list without vendors not included in config.
     */
    public static function getAutoloadedClasses(): array
    {
        $composerPath = base_path('/vendor/composer/autoload_classmap.php');
        if ( ! file_exists($composerPath)) {
            throw new Exception('Composer autoload_classmap.php not found');
        }

        $composerContents = include $composerPath;

        if ( ! $composerContents || ! is_array($composerContents)) {
            throw new Exception('Composer autoload_classmap.php is not of expected type [array]');
        }

        return array_keys(array_filter($composerContents, function ($value, $key) {
            $vendorPath = 'vendor' . DIRECTORY_SEPARATOR;
            if (Str::contains($value, $vendorPath)) {
                $path = Str::after($value, $vendorPath);
                foreach (config('lucent.magellan.vendor_include') as $vendorInclude) {
                    if (Str::startsWith($path, trim($vendorInclude), DIRECTORY_SEPARATOR)) {
                        return true;
                    }
                }

                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Perform filtering and return collected classes.
     */
    public function get(): array
    {
        return $this->fill()->collection;
    }

    /**
     * Fills the collection property
     */
    public function fill(): static
    {
        foreach (static::getClassList() as $class) {
            try {
                if ( ! $this->cache && ! $this->validate($class)) {
                    continue;
                }

                $reflection = new ReflectionClass($class);

                if ( ! $this->cache && ! $this->scope($reflection)) {
                    continue;
                }

                foreach ($this->callbacks as $callback) {
                    if ( ! $callback($reflection)) {
                        continue 2;
                    }
                }
            } catch (Throwable $e) {
                continue;
            }

            $this->collection[] = $class;
        }
        if ($this instanceof ScopeUsesCache && ! Cache::has(static::getCacheKey())) {
            $ttl = $this->ttl();
            if ($ttl > 0) {
                Cache::put(static::getCacheKey(), $this->collection, $this->ttl());
            } else {
                Cache::forever(static::getCacheKey(), $this->collection);
            }
        }

        return $this;
    }

    public function toArray()
    {
        return $this->collection;
    }

    public function toJson($options = 0)
    {
        return json_encode($this->collection, $options);
    }

    public function count(): int
    {
        return count($this->collection);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->attributes);
    }

    /**
     * Get remembered instance of the scope.
     */
    protected static function getInstance(): static
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    protected static function usesCache(): bool
    {
        return (new ReflectionClass(static::class))->implementsInterface(ScopeUsesCache::class);
    }

    /**
     * Blacklist classes by the beginnings of their names.
     *
     * @param  string|iterable<string>  $classes
     */
    protected function blacklist($classes): static
    {
        if ( ! is_iterable($classes)) {
            $classes = [$classes];
        }
        $this->blacklist = array_merge($this->blacklist, $classes);

        return $this;
    }

    protected function filter(Closure $callback): static
    {
        $this->callbacks[] = $callback;

        return $this;
    }

    /**
     * Get classlist from cache or a composer.
     */
    protected function getClassList(): array
    {
        if (static::usesCache() && Cache::has(static::getCacheKey())) {
            $this->cache = true;

            return Cache::get(static::getCacheKey());
        }

        return static::getAutoloadedClasses();
    }

    /**
     * Filter classes by its reflection. Return false to not to include.
     */
    protected function scope(ReflectionClass $class): bool
    {
        return true;
    }

    private static function getCacheKey(): string
    {
        return 'lucent.magellan.' . Str::snake(Str::remove('\\', static::class));
    }

    /**
     * Class hard coded validation.
     *
     * @param  mixed  $class
     */
    private function validate($class): bool
    {
        $blacklist = array_merge($this->blacklist, [
            'Lucent\\Support\\Magellan',
            'App\\Support\\Magellan',
        ]);

        foreach ($blacklist as $item) {
            if (Str::startsWith($class, $item)) {
                return false;
            }
        }

        // always leave namespaces with no prefix - probably core classes
        return ! ( ! Str::contains($class, '\\'));
    }
}
