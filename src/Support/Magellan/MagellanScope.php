<?php

namespace Lucent\Support\Magellan;

use Closure;
use Illuminate\Support\Collection;

class MagellanScope
{

    public static function search(Closure $callback): Collection
    {
        return self::getAutoloadedCollection()->filter(fn($reflection) => $callback($reflection));
    }

    public static function getAutoloadedCollection(): MagellanCollection
    {
        $composerPath = base_path('/vendor/composer/autoload_classmap.php');
        if (! file_exists($composerPath)) {
            throw new \Exception('Composer autoload_classmap.php not found');
        }

        $composerContents = include $composerPath;

        if(!$composerContents || !is_array($composerContents)){
            throw new \Exception('Composer autoload_classmap.php is not of expected type [array]');
        }

        return MagellanCollection::make(array_keys($composerContents));
    }
}
