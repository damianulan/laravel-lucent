# Laravel Lucent

[![Laravel](https://img.shields.io/badge/made_with-Laravel-red?style=for-the-badge)](https://laravel.com) [![License](https://img.shields.io/badge/license-MIT-blue?style=for-the-badge)](LICENSE)

Laravel Lucent is a utility package for Laravel applications that bundles a small set of reusable primitives:

- Transaction-oriented service classes
- Composer class discovery via Magellan scopes
- Eloquent traits for UUIDs, access scopes, cascade deletes, pruning, and model state helpers
- HTML sanitizing and trait inspection helpers
- String and currency lookup utilities
- A few console generators and maintenance commands

The package is intentionally lightweight. Most features are opt-in and can be used independently.

## Requirements

- PHP `^8.3`
- `illuminate/support` `^9.0|^10.0|^11.0|^12.0`
- `mews/purifier` `^3.4`

## Installation

Install the package with Composer:

```bash
composer require damianulan/laravel-lucent
```

Laravel package discovery registers the service provider automatically.

If you want the package config, translations, and stubs in your application, publish them:

```bash
php artisan vendor:publish --tag=lucent
```

You can also publish individual groups:

```bash
php artisan vendor:publish --tag=lucent-config
php artisan vendor:publish --tag=lucent-langs
```

## What The Package Includes

### Services

`Lucent\Services\Service` is a base class for application services that:

- accepts named boot parameters
- runs `handle()` inside a database transaction
- supports optional authorization and validation
- collects runtime or validation errors
- exposes the original input and the returned result
- provides a small cache helper via `remember()`

Generate a service class:

```bash
php artisan make:service CreateOrUpdateCampaign
```

Example service:

```php
<?php

namespace App\Services;

use App\Models\Campaign;
use Lucent\Services\Service;

class CreateOrUpdateCampaign extends Service
{
    protected function authorize(): bool
    {
        return $this->request()->user()?->can('campaigns.manage') ?? false;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    protected function handle(): Campaign|false
    {
        if (! $this->validate($this->request()->all())) {
            return false;
        }

        $campaign = $this->campaign ?? new Campaign();
        $campaign->fill($this->request()->only(['name', 'description']));
        $campaign->save();

        return $campaign;
    }
}
```

Execute it from a controller or action:

```php
$service = CreateOrUpdateCampaign::boot(
    request: $request,
    campaign: $campaign,
)->execute();

if (! $service->passed()) {
    return back()->withErrors($service->getErrors());
}

$savedCampaign = $service->getResult();
```

Useful methods:

- `boot(...$props)`: instantiate the service with named arguments
- `execute()`: run authorization, then `handle()` inside `DB::transaction()`
- `add(...$props)`: append more named data after booting
- `request()`: access the bound request or an empty request object
- `getOriginal()`: get the original booted input as a collection
- `getResult()`: get the value returned by `handle()`
- `getErrors()`: get collected validation/runtime messages
- `hasErrors()`: check whether any errors were collected
- `toArray()` / `toJson()`: serialize the original input payload

Notes:

- `execute()` only marks the service as passed when `handle()` returns a truthy value.
- If authorization fails or an exception is thrown, the exception is reported and the message is added to the error bag.

### Magellan Scopes

`Lucent\Support\Magellan\MagellanScope` lets you discover classes from Composer's class map and filter them with reflection-based rules.

This is useful when you want to locate application classes or approved vendor classes dynamically, for example form builders, policies, handlers, or plugin-like classes.

Generate a scope:

```bash
php artisan make:magellan AdminFormScope
```

Inline usage:

```php
use App\Forms\BaseForm;
use Lucent\Support\Magellan\MagellanScope;

$forms = MagellanScope::blacklist([
        'App\\Console\\',
        'App\\Providers\\',
    ])
    ->filter(fn (\ReflectionClass $class) => $class->isSubclassOf(BaseForm::class))
    ->get();
```

Custom scope class:

```php

namespace App\Support\Magellan;

use Lucent\Support\Magellan\MagellanScope;
use Lucent\Support\Magellan\Workshop\ScopeUsesCache;

class PolicyScope extends MagellanScope implements ScopeUsesCache
{
    protected function scope(\ReflectionClass $class): bool
    {
        return str_ends_with($class->getName(), 'Policy');
    }

    public function ttl(): int
    {
        return 3600;
    }
}
```

Then use it:

```php
$policies = PolicyScope::get();
```

Important behavior:

- The scope reads from `vendor/composer/autoload_classmap.php`
- Application classes are included by default
- Vendor classes are excluded by default unless allowed in `config/lucent.php` under `magellan.vendor_include`
- Implement `ScopeUsesCache` to cache the collected class list

Helpers available on a filled scope:

- `get()`
- `fill()`
- `toArray()`
- `toJson()`
- `count()`

### Eloquent Traits

#### `Accessible`

Adds a local scope named `checkAccess()` which applies a custom Eloquent scope stored on the model.

```php
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model;
use Lucent\Support\Traits\Accessible;

class User extends Model
{
    use Accessible;

    protected string $accessScope = UserScope::class;
}
```

```php
$visibleUsers = User::query()->checkAccess()->get();
```

The configured scope class must extend `Illuminate\Database\Eloquent\Scope`.

#### `UUID`

Adds UUID primary key support.

```php
use Illuminate\Database\Eloquent\Model;
use Lucent\Support\Traits\UUID;

class Order extends Model
{
    use UUID;
}
```

Migration example:

```php
Schema::create('orders', function (Blueprint $table) {
    $table->uuid('id')->primary();
});
```

The trait disables incrementing and fills the primary key with `Str::uuid()` on create.

#### `HasUniqueUuid`

Adds a unique UUID column without replacing the model primary key.

```php
use Illuminate\Database\Eloquent\Model;
use Lucent\Support\Traits\HasUniqueUuid;

class Order extends Model
{
    use HasUniqueUuid;
}
```

Migration example:

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
});
```

Lookup methods:

```php
$order = Order::findByUuid($uuid);
$uuid = $order->getUuidKey();
```

If your UUID column has a different name, override:

```php
public static function getUuidKeyName(): string
{
    return 'public_id';
}
```

#### `VirginModel`

Adds convenience helpers around common boolean `active` and `draft` flags.

```php
use Illuminate\Database\Eloquent\Model;
use Lucent\Support\Traits\VirginModel;

class Post extends Model
{
    use VirginModel;

    protected $fillable = [
        'title',
        'active',
        'draft',
    ];
}
```

Available helpers:

```php
Post::getAll();
Post::allActive();
Post::allInactive();
Post::allPublished();
Post::allDrafts();

Post::query()->active()->get();
Post::query()->inactive()->get();
Post::query()->published()->get();
Post::query()->drafted()->get();

$post->empty();
$post->notEmpty();
```

`active()` and `drafted()` scopes only apply when the corresponding fields are present in `$fillable`.

#### `CascadeDeletes`

Deletes related models when the parent model is deleted. This works through the `deleted` model event, so it does not run for mass deletes that bypass model events.

```php
use Illuminate\Database\Eloquent\Model;
use Lucent\Support\Traits\CascadeDeletes;

class User extends Model
{
    use CascadeDeletes;

    protected array $cascadeDelete = ['profile', 'posts'];
}
```

If `cascadeDelete` is omitted and `lucent.models.auto_cascade_deletes` is enabled, Lucent tries to detect deletable relation methods automatically based on the configured relation return types.

You can also block specific relations:

```php
protected array $donotCascadeDelete = ['auditLogs'];
```

Configuration:

```php
'models' => [
    'auto_cascade_deletes' => true,
    'cascade_delete_relation_types' => [
        Illuminate\Database\Eloquent\Relations\MorphMany::class,
        Illuminate\Database\Eloquent\Relations\MorphToMany::class,
        Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
        Illuminate\Database\Eloquent\Relations\HasMany::class,
        Illuminate\Database\Eloquent\Relations\HasOne::class,
    ],
]
```

#### `SoftDeletesPrunable`

Provides a `prunableSoftDeletes()` scope for models using Laravel's `SoftDeletes`.

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Lucent\Support\Traits\SoftDeletesPrunable;

class Archive extends Model
{
    use SoftDeletes;
    use SoftDeletesPrunable;
}
```

This scope is used by the pruning command described below.

#### `Dispatcher` (deprecated)

Adds method-based model event dispatching such as `createdUser()`, `updatedCampaign()`, and similar hooks on the model itself.

The trait is marked deprecated and should only be used in legacy code.

### Helpers

#### `clean_html()`

Sanitizes rich HTML input using the package's dedicated Purifier preset.

```php
$safe = clean_html($request->input('body'));
```

Examples:

```php
clean_html('<script>alert(1)</script>'); // ''
clean_html('<p class="text-center"><strong>Hello</strong></p>');
```

Lucent merges an extra `lucent_config` entry into `purifier.settings` at boot time. The defaults are defined in `config/lucent.php` and allow common rich-text tags, safe links, some formatting classes, and selected inline CSS properties.

#### `class_uses_trait()`

Checks whether a class uses a trait anywhere in its inheritance tree.

```php
use App\Models\User;
use Lucent\Support\Traits\Accessible;

if (class_uses_trait(Accessible::class, User::class)) {
    // ...
}
```

Signature:

```php
class_uses_trait(string $traitClass, string $targetClass): bool
```

### Support Utilities

#### `Lucent\Support\Str\Alphabet`

Utility for working with Latin letters, including accented UTF-8 variants.

```php
use Lucent\Support\Str\Alphabet;

Alphabet::getAlphabetPosition('A'); // 1
Alphabet::getAlphabetPosition('É'); // 5
Alphabet::getAlphabetPosition('Ż'); // 26
Alphabet::getAlphabetPosition('ß'); // null
```

The class normalizes accented characters to their ASCII base where possible before calculating the alphabet position.

#### `Lucent\Support\Str\Currencies\CurrencyLib`

Provides an in-memory ISO 4217 currency dataset.

```php
use Lucent\Support\Str\Currencies\CurrencyLib;

$currencies = new CurrencyLib();

$eur = $currencies->getByAlpha3('EUR');
$usd = $currencies->getByCode('840');
$all = $currencies->getAll();
```

Returned items use this shape:

```php
[
    'name' => 'Euro',
    'alpha3' => 'EUR',
    'numeric' => '978',
    'exp' => 2,
    'country' => 'EU',
]
```

Methods:

- `getByCode(string $code)`
- `getByAlpha3(string $alpha3)`
- `getByNumeric(string $numeric)`
- `getAll()`

`getByAlpha3()` and `getByNumeric()` validate input format before lookup and throw an exception on invalid values.

#### `Lucent\Console\Git`

Structured helper around common git queries and release-oriented commands:

```php
use Lucent\Console\Git;

$branch = Git::head();
$tags = Git::getTags();
$latest = Git::getLatestTagName();
```

For richer inspection, build a repository-scoped instance:

```php
$git = Git::repository(base_path())
    ->queue(['git', 'status', '--short'])
    ->queue(['git', 'rev-parse', '--abbrev-ref', 'HEAD'])
    ->run();

$result = $git->lastResult();
```

It also exposes:

- `checkoutRelease(string $tag)`
- `checkoutLatestRelease()`

Each executed command is captured as a `GitResult` object with the command, working directory, exit code, output, error output, and inferred caller metadata.

#### `Lucent\Support\Trace`

Captures and inspects the current backtrace:

```php
use Lucent\Support\Trace;

$trace = Trace::boot();
```

Useful helpers:

```php
$caller = $trace->caller();
$steps = $trace->steps(oldestFirst: true, withSignature: true);
$appFrames = $trace->onlyApplicationFrames()->withoutVendorFrames();
$details = $trace->details();
```

You can also build a trace from an exception:

```php
try {
    // ...
} catch (Throwable $exception) {
    $trace = Trace::fromThrowable($exception);
}
```

Reflection is used internally to enrich frames with method signatures, namespaces, and callable metadata, making the tool suitable for debugging chained service calls, controller pipelines, and vendor-to-app handoffs.

### Artisan Commands

#### `make:service`

Creates a service class in `App\Services`.

```bash
php artisan make:service PublishArticle
```

#### `make:magellan`

Creates a Magellan scope in `App\Support\Magellan`.

```bash
php artisan make:magellan FormScope
```

#### `model:prune-soft-deletes`

Permanently deletes soft-deleted records for models that:

- live under `App\Models`
- extend `Illuminate\Database\Eloquent\Model`
- use Laravel's `SoftDeletes` trait
- use `Lucent\Support\Traits\SoftDeletesPrunable`

Run it manually:

```bash
php artisan model:prune-soft-deletes
```

Schedule it:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('model:prune-soft-deletes')->daily();
```

Configure the age threshold in `config/lucent.php` or through environment:

```env
PRUNE_SOFT_DELETES_DAYS=365
```

### Configuration

Published config file: `config/lucent.php`

Main options:

```php
return [
    'models' => [
        'prune_soft_deletes_days' => env('PRUNE_SOFT_DELETES_DAYS', 365),
        'auto_cascade_deletes' => true,
        'cascade_delete_relation_types' => [
            Illuminate\Database\Eloquent\Relations\MorphMany::class,
            Illuminate\Database\Eloquent\Relations\MorphToMany::class,
            Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            Illuminate\Database\Eloquent\Relations\HasMany::class,
            Illuminate\Database\Eloquent\Relations\HasOne::class,
        ],
    ],
    'mews_purifier_setting' => [
        // custom purifier preset used by clean_html()
    ],
    'magellan' => [
        'vendor_include' => [
            'spatie/',
        ],
    ],
];
```

Use `magellan.vendor_include` when a scope should inspect specific vendor namespaces from the Composer class map.

## Typical Use Cases

- Wrap multi-step create/update flows in a dedicated service class
- Apply model-specific access constraints through reusable Eloquent scopes
- Add UUID primary keys or public UUID identifiers to Eloquent models
- Automatically cascade deletes through selected relations
- Sanitize rich text input before persisting or rendering it
- Discover application classes dynamically using reflection and Composer metadata
- Prune stale soft-deleted records on a schedule

## Caveats

- Services are considered successful only when `handle()` returns a truthy value.
- Cascade delete logic relies on model events and will not run for mass delete queries that skip events.
- Magellan depends on Composer's generated class map. If class discovery looks stale, refresh autoload metadata with `composer dump-autoload`.
- The Lucent pipeline layer is deprecated.

## License

MIT. See [LICENSE](LICENSE).

## Contact

Questions and contributions: `damian.ulan@protonmail.com`
