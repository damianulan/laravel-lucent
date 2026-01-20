# Lucent Magellan Scopes

Magellan scopes filter out and return a collection of classes based on rules you define. Classlist is based on composer autoloaded classes.

### Create a scope from a stub
```
php artisan make:magellan TestScope
```

## Examples

Inline scope
```php
use Lucent\Support\Magellan\MagellanScope;
use FormForge\Base\Form;

// blacklist all provider classes
$localProviders = MagellanScope::blacklist('App\\Providers\\')
        ->get();
// filter out only Form classes from laravel-form-forge package
$localProviders = MagellanScope::filter(function (\ReflectionClass $class) {
            return $class->isSubclassOf(Form::class);
        })
        ->get();
```

Scope class
```php
use App\Models\TestModel;
use Lucent\Support\Magellan\MagellanScope;
use Lucent\Support\Magellan\Workshop\ScopeUsesCache;

class TestScope extends MagellanScope implements ScopeUsesCache
{
    protected function scope(\ReflectionClass $class): bool
    {
        // include your more complex logic here
        return true;
    }

    // use cache for this scope
    // if you want to always load fresh, do not implement ScopeUsesCache
    public function ttl(): int
    {
        return 3600;
    }
}
```

If you want to refresh class list, dump autoload:
```
composer dump-autoload
```

### Other
```php
use Lucent\Support\Magellan\MagellanScope;

// using fill method it only fills an instance with collection of classes
$scope = MagellanScope::filter(function (\ReflectionClass $class) {
    return $class->isSubclassOf(Form::class);
})->fill();

// export collectioon to array
$scope->toArray();

// export collection to json
$scope->toJson();

// get collection count
$scope->count();

// iterate through class collection
foreach($scope as $class){
    // do something
}

```
