# Lucent Services

Single Service should contain a specific business logic, that consists of multiple database operations, when for example, in order to update or create a model from a form, we also need to update a list of administrators and/or a properties of other models (and their repositories) by this occasion.
All operations held inside `handle` method is executed in a single database transaction, that protects you from errors and operational inconsistencies. Nonetheless, every exception thrown inside `handle` method will be caught and logged in Laravel's Log facade.

### Real-World Analogy

Think of a service as a waiter in a restaurant:

- You (the controller) give your order to the waiter.
- The waiter (service) coordinates with the kitchen (repository/data layer).
- The waiter may also apply special rules (like “no onions”).
- Then delivers the final dish (response) to you.

### Create Service from a stub

```
php artisan make:service ServiceName
```

## Examples

Contain all your business logic in the `handle` method

```php
use App\Models\TestModel;
use Lucent\Services\Service;

class CreateOrUpdate extends Service
{
    protected function authorize(): bool
    {
        // optional authorization of a service
    }

    public function handle(): TestModel
    {
        $model = TestModel::fillFromRequest($this->request(), $this->model->id ?? null);
        $user_ids = $this->request()->input('user_ids');

        if ($model->save()) {
            $model->refreshModelAdministrator($user_ids);
            $this->sendNotifications();
            $this->model = $model;
        }

        return $model;
    }
}

```

Execute it in controller like this:

```php
$service = CreateOrUpdate::boot(request: $request, model: $model)->execute();

```

You can pass named parameters to the `boot` method of a service and acces them inside a service as `$this->request` or `$this->model` etc. To retrieve passed parameters in their original form use `$service->getOriginal()` method.

You can also get the result of the service, that is returned by the `handle` method, by calling `getResult` method:

```php
$updatedModel = $service->getResult();

// get result of the service in Array format
$updatedModel = $service->toArray();

// or in JSON format
$updatedModel = $service->toJson();

```

Check if the service passed the validation by calling `passed` method:

```php
if($service->passed()) {
    // do something
} elseif ($service->failed()) {
    // do something else
}
```

## In-built validation

Optionally, you can use validation steps by overriding `rules`, `messages` and `attributes` methods, and then calling `validate` method in your `handle` method.
