<?php

namespace Lucent\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;
use Lucent\Exceptions\Authorization\ServiceUnauthorized;

abstract class Service
{
    protected array $original = [];
    protected array $errors = [];
    protected string $cachePrefix = 'service_';
    protected int $defaultCacheTtl = 300; // in seconds

    public function __construct(array $datas)
    {
        $this->original = $datas;
        foreach ($datas as $key => $value) {
            if (!isset($this->$key)) {
                $this->$key = $value;
            }
        }
    }

    public static function boot(...$props): self
    {
        return new static($props);
    }


    /**
     * If you need you can set up conditions, that user must meet to use this service.
     * When returning false, service will throw unauthorized exception.
     *
     * @return bool
     */
    protected function authorize(): bool
    {
        return true;
    }

    /**
     * Run the service main logic.
     * All database operations are transaction protected by default.
     */
    abstract protected function handle(): mixed;

    /**
     * Execute with error handling.
     */
    public function execute(): mixed
    {
        try {
            $auth = $this->authorize();
            if (!$auth) {
                throw new ServiceUnauthorized(static::class);
            }
            return DB::transaction($this->handle());
        } catch (Exception $e) {
            $this->logException($e);
            $this->errors[] = $e->getMessage();
            return null;
        }
    }

    /**
     * Run validation rules.
     */
    protected function validate(array $rules, array $messages = []): bool
    {
        $validator = Validator::make($this->data, $rules, $messages);
        if ($validator->fails()) {
            $this->errors = $validator->errors()->all();
            return false;
        }
        return true;
    }

    /**
     * Log an exception with context.
     */
    protected function logException(Exception $e): void
    {
        Log::error(static::class . ' failed: ' . $e->getMessage(), [
            'exception' => $e,
            'data' => $this->data,
        ]);
    }

    /**
     * Dispatch an event.
     */
    protected function dispatchEvent(object $event): void
    {
        Event::dispatch($event);
    }

    /**
     * Cache helper.
     */
    protected function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->defaultCacheTtl;
        $cacheKey = $this->cachePrefix . Str::slug(static::class . '_' . $key);

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Access a configuration value.
     */
    protected function config(string $key, $default = null): mixed
    {
        return Config::get($key, $default);
    }

    /**
     * Get validation or runtime errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if service has any errors.
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Add a manual error.
     */
    protected function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    /**
     * Set data manually.
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get the current input data.
     */
    public function getOriginal(): array
    {
        return $this->original;
    }
}
