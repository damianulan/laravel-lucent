# Laravel Lucent

[![Static Badge](https://img.shields.io/badge/made_with-Laravel-red?style=for-the-badge)](https://laravel.com/docs/11.x/releases) &nbsp; [![Licence](https://img.shields.io/github/license/Ileriayo/markdown-badges?style=for-the-badge)](./LICENSE) &nbsp; [![Static Badge](https://img.shields.io/badge/maintainer-damianulan-blue?style=for-the-badge)](https://damianulan.me)

## Description

Laravel Lucent is a package that provides a set of custom resources, components and traits for laravel projects and provides comprehensive support for popular design patterns (eg. pipelines services, repository pattern, builders).

## Installation

You can install the package via composer in your laravel project:

```
composer require damianulan/laravel-lucent
```

The package will automatically register itself.

Next step is to publish necessary vendor assets.

```
php artisan vendor:publish --tag=lucent
```

## Components

- [Services](docs/SERVICES.md)
- [Magellan Scopes](docs/MAGELLAN.md)

## Traits

- [Accessible](docs/TRAITS.md#accessible)
- [UUID](docs/TRAITS.md#uuid)
- [HasUniqueUuid](docs/TRAITS.md#hasuniqueuuid)
- [VirginModel](docs/TRAITS.md#virginmodel)
- [CascadeDeletes](docs/TRAITS.md#cascadedeletes)

## Helpers
### clean_html
```php
clean_html('<script>alert("XSS");</script>'); // returns empty string
```

Uses [mews/purifier](https://github.com/mewebstudio/Purifier) package to clean HTML input off of possible XSS vulnerabilities.
Best suited for cleaning before placing in rich text editors.

### class_uses_trait
This helper function checks if trait is used by a target class.
It recurses through the whole class inheritance tree.
```php

class User extends Model
{
    use Accessible;
}

class UserController extends Controller
{
    public function index()
    {
        if (class_uses_trait(User::class, Accessible::class)) {
            // do something
        }
    }
}
```

## Support 

### Lucent\Support\Str\Alphabet
A library of letter manipulation functions.
- getAlphabetPosition - returns a position of a letter (including UTF-8 letters like Ą, É, Ç, etc.) in the ext-Latin alphabet.
- normalizeToASCII - normalizes a UTF-8 letter (e.g., Ą, É, Ç) to its base ASCII character.

### Lucent\Support\Str\Currencies\CurrencyLib
A library of currency conversion functions. It follows ISO 4217 standard.

## Artisan Console Commands

### Prune Soft Deletes
```
php artisan model:prune-soft-deletes
```
Schedule this command to periodically prune outdated records of models, that use `Illuminate\Database\Eloquent\SoftDeletes` and `Lucent\Support\Traits\SoftDeletesPrunable` traits.
```php
$schedule->command('model:prune-soft-deletes')->daily();
```
In env file set `PRUNE_SOFT_DELETES_DAYS` to desired number of days after soft deleting, which records will be considered outdated.


### Contact & Contributing

Any question You can submit to **damian.ulan@protonmail.com**.
