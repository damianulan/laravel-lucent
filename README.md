# Laravel Lucent

[![Static Badge](https://img.shields.io/badge/made_with-Laravel-red?style=for-the-badge)](https://laravel.com/docs/11.x/releases) &nbsp; [![Licence](https://img.shields.io/github/license/Ileriayo/markdown-badges?style=for-the-badge)](./LICENSE) &nbsp; [![Static Badge](https://img.shields.io/badge/maintainer-damianulan-blue?style=for-the-badge)](https://damianulan.me)

## Package in core production !

Stable version ETA: 2025/06/30.

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

- [Services](docs/SERIVCES.md)
- [Repositories](docs/REPOSITORIES.md)
- [Pipelines](docs/PIPELINES.md)

## Traits

- [Accessible](docs/TRAITS.md#accessible)
- [Dispatcher](docs/TRAITS.md#dispatcher)
- [UUID](docs/TRAITS.md#uuid)
- [VirginModel](docs/TRAITS.md#virginmodel)

### Contact & Contributing

Any question You can submit to **damian.ulan@protonmail.com**.
