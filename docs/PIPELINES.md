# Lucent Pipelines

Lucent Pipelines enable creating custom pipes and binding them to your models. Pipes are executed while creating and updating model and can be used to perform complex operations on models data each time they are being changed. You are able to pass certain properties or whole model instance.

### Create a Pipe from a stub

```
php artisan make:pipe PipeName
```

### Declare your pipe

```php
use Closure;
use Lucent\Pipelines\Pipe;

final readonly class Pipe1 implements Pipe
{

    /**
     * Handle the pipe logic.
     *
     * @param  mixed  $value
     * @param  Closure  $next
     * @return string
     */
    public function handle(mixed $value, Closure $next): string
    {
        // include your logic here

        return $next($value);
    }
}
```

### Assing your pipes to your model

```php

use Lucent\Pipelines\HasPipes;
use App\Pipelines\Pipe1;
use App\Pipelines\Pipe2;

class User extends Model
{
    use HasPipes;

    protected $pipes = [
        // pass firstname as a value to the pipe
        'firstname' => Pipe1::class,
        // pass whole model instance to the pipe
        '*' => Pipe2::class
    ];

}

```
