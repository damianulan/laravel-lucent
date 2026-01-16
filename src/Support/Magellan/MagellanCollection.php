<?php

namespace Lucent\Support\Magellan;

use Illuminate\Support\Collection;

class MagellanCollection extends Collection
{
    public function __construct($items = [])
    {
        $this->items = $this->getArrayableItems(array_filter($items, function($class) {
            try {
                return class_exists($class);
            } catch (\Throwable $e) {
                return false;
            }
        }));
    }
}
