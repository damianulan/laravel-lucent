# Lucent Traits

## Accessible

Assigns access scopes to your Eloquent models.
Assign laravel scope to your model as $accessScope property.

```php
use Lucent\Support\Traits\Accessible;

class User extends Model
{
    use Accessible;

    protected $accessScope = UserScope::class;
}
```

Then you can use it as:

```php
User::checkAccess()->get();
```

Thanks to this you will be able to retrieve only users that are allowed to retrive by current user. For best performance define a scope based on user's roles and permissions. Best to use a context-based roles system such as [damianulan/laravel-sentinel](https://github.com/damianulan/laravel-sentinel).

### Example

```php
public function apply(Builder $builder, Model $model): void
{
    $user = Auth::user();

    // campaign coordinator fetches onlu campaigns he's assigned to
    if ($user->cannot(PermissionLib::MBO_ADMINISTRATION)) {
        if ($user->can(PermissionLib::MBO_CAMPAIGN_VIEW)) {
            $campaignRoleId = Role::getId(SystemRolesLib::CAMPAIGN_COORDINATOR);
            $campaign_ids = $user->roleAssignments()->where('role_id', $campaignRoleId)->where('context_type', Campaign::class)->get()->pluck('context_id');

            $builder->whereIn('id', $campaign_ids);
        } else {
            $builder->whereRaw('1=0'); // no access - do not fetch any
        }
    }
}

```

## Dispatcher

Adds support for method-based model event listeners, so that global boot() methods won't get overriden. In your model create event static methods like created{ModelName}, updated{ModelName}() etc.

```php
public static function updatingCampaign(Campaign $model)
{
    if ($model->manual == 0) {
        $model->setStageAuto();
    }

    return $model;
}

public static function updatedCampaign(Campaign $model)
{
    $ucs = $model->user_campaigns()->get();
    if ($ucs && $ucs->count()) {
        foreach ($ucs as $uc) {
            $uc->active = $model->draft ? 0 : 1;
            $uc->save();
        }
    }
    CampaignUpdated::dispatch($model);
}
```

## UUID

Adds UUIDv4 as primary key support to your Eloquent models.

```php
use Lucent\Support\Traits\UUID;

class User extends Model
{
    use UUID;
}
```

Migration:

```php
public function up()
{
    Schema::create('users', function (Blueprint $table) {
        $table->uuid('id')->primary();
    });
}
```

Foreign key:

```php
Schema::create('user_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('user_id');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});
```

## VirginModel

Adds operational helpers to Eloquent model, that is based on common boolean attribute flags such as 'draft' and 'active'.

```php
use Lucent\Support\Traits\VirginModel;

class User extends Model
{
    use VirginModel;

    protected $fillable = [
        'draft',
        'active',
    ];
}
```

```php
$activeUsers = User::allActive();
$draftUsers = User::allDrafts();
$publishedUsers = User::allPublished();

$customActiveUsers = User::where('confirmed_at', '<', now())->active()->get();
```

You can also use `empty()` and `notEmpty()` checks on model instances, that represence its existence in database.

```php
$user = new User();
$user->firstname = 'John';
$user->empty(); // returns true
```
