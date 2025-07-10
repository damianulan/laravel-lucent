# Lucent Services

Single Service should contain a specific business logic, that consists of multiple database operations, when for example, in order to update or create a model from a form, we also need to update a list of administrators and/or a properties of other models (and their repositories) by this occasion.
All operations held inside `handle` method is executed in a single database transaction, that protects you from errors and operational inconsistencies.

### Real-World Analogy

Think of a service as a waiter in a restaurant:

- You (the controller) give your order to the waiter.
- The waiter (service) coordinates with the kitchen (repository/data layer).
- The waiter may also apply special rules (like “no onions”).
- Then delivers the final dish (response) to you.

### Create Service from a stub

```
php artisan makeLservice ServiceName
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

        if ($campaign->save()) {
            $campaign->refreshModelAdministrator($user_ids);
            $this->sendNotifications();
            $this->model = $model;
        }

        return $campaign;
    }
}

```

Execute it in controller like this:

```php
$service = CreateOrUpdate::boot(request: $request)->execute();

```

You can pass named parameters to the `boot` method of a service and acces them inside a service as `$this->request` or `$this->model` etc.
