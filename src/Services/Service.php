<?php

namespace Lucent\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Collection;
use Lucent\Exceptions\Services\ServiceUnauthorized;
use Illuminate\Http\Request;

abstract class Service
{
    private Collection $original;
    private array $errors = [];
    private string $cachePrefix = 'service_';
    private int $defaultCacheTtl = 300; // in seconds
    private bool $passed = false;
    private mixed $returnValue;

    public function __construct(array $datas)
    {
        $this->original = new Collection();
        foreach ($datas as $key => $value) {
            if (!isset($this->$key)) {
                $this->$key = $value;
                $this->original->put($key, $value);
            }
        }
    }

    /**
     * Pass properties as arguments.
     * Use named arguments eg. ::boot(request: $request, name: $name)
     *
     * @param mixed ...$props
     * @return self
     */
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
    public function execute(): self
    {
        $result = false;
        try {
            $auth = $this->authorize();
            if (!$auth) {
                throw new ServiceUnauthorized(static::class);
            }
            $result = DB::transaction($this->handle());
        } catch (Exception $e) {
            $this->logException($e);
            $this->errors[] = $e->getMessage();
        }

        if ($result) {
            $this->passed = true;
        }
        $this->returnValue = $result;

        return $this;
    }

    /**
     * Laravel validation rules.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * Laravel validation messages.
     *
     * @return array
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * Laravel validation attributes.
     *
     * @return array
     */
    protected function attributes(): array
    {
        return [];
    }

    /**
     * Run validation rules.
     */
    protected function validate(): bool
    {
        $rules = $this->rules();
        $messages = $this->messages();
        $attributes = $this->attributes();

        $validator = Validator::make($this->data, $rules, $messages, $attributes);
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
     * Cache helper.
     */
    protected function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->defaultCacheTtl;
        $cacheKey = $this->cachePrefix . Str::slug(static::class . '_' . $key);

        return Cache::remember($cacheKey, $ttl, $callback);
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
     * Check if service has passed.
     *
     * @return bool
     */
    public function passed(): bool
    {
        return $this->passed && empty($this->errors);
    }

    /**
     * Check if service has failed.
     *
     * @return bool
     */
    public function failed(): bool
    {
        return !$this->passed || empty($this->errors);
    }

    /**
     * Add a manual error.
     */
    protected function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    /**
     * Add more data manually. Use named arguments.
     */
    public function add(...$props): static
    {
        foreach ($props as $key => $prop) {
            if (!isset($this->$key)) {
                $this->original->put($key, $prop);
                $this->$key = $prop;
            }
        }
        return $this;
    }

    public function toArray(): array
    {
        $stack = [];
        $keys = $this->original->keys()->all();
        foreach ($keys as $key) {
            if (isset($this->$key)) {
                $stack[$key] = $this->$key;
            }
        }
        return $stack;
    }

    /**
     * Get the current input data.
     */
    public function getOriginal(): Collection
    {
        return $this->original;
    }

    /**
     * Returns a return value of a handle method.
     *
     * @return mixed
     */
    public function getResult(): mixed
    {
        if (isset($this->returnValue)) {
            return $this->returnValue;
        }
        return false;
    }

    /**
     * Return request object.
     *
     * @return \Illuminate\Http\Request
     */
    public function request(): Request
    {
        return isset($this->request) ? $this->request : new Request();
    }
}
