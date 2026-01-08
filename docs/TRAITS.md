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

## HasUniqueUuid

Adds UUIDv4 as a unique key support to your Eloquent models. It does not replace your primary key, but adds a new unique key.

```php
use Lucent\Support\Traits\HasUniqueUuid;

class User extends Model
{
    use HasUniqueUuid;
}
```

Migration:
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid');
});
```

Find model instance by uuid key:
```php
User::findByUuid($uuid);
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

## CascadeDeletes

Adds cascading behavior to your Eloquent models when deleting them. It works based on `deleted` event, thus it does not support mass deletes, as they do not trigger `deleted` event.

```php
use Lucent\Support\Traits\CascadeDeletes;

class User extends Model
{
    use CascadeDeletes;

    // optionally declare relations that should be deleted when model is deleted
    // if not declared all relations [being: belongsToMany, hasMany, morphToMany, morphMany, hasOne] will be deleted
    protected $cascadeDelete = ['user_profiles'];

    // optionally declare relations that should not be deleted when model is deleted
    protected $donotCascadeDelete = ['user_profiles'];

    public function user_profile(): HasOne
    {
        return $this->hasOne(UserProfile::class); // this instance will be deleted when model is deleted
    }
}
```
When property `$cascadeDelete` is not declared, all relations suitable for deletion will be also deleted. List of those relations can be modified via config file `lucent.php` at `models.cascade_delete_relation_types`.
Only modify if you know what you are doing.
```php
'models' => [
    'cascade_delete_relation_types' => [
        'Illuminate\Database\Eloquent\Relations\MorphMany',
        'Illuminate\Database\Eloquent\Relations\MorphToMany',
        'Illuminate\Database\Eloquent\Relations\BelongsToMany',
        'Illuminate\Database\Eloquent\Relations\HasMany',
        'Illuminate\Database\Eloquent\Relations\HasOne',
    ]
]
```
