<?php

namespace Lucent\Support\Magellan;

use Illuminate\Support\Collection;
use Symfony\Component\ErrorHandler\Error\FatalError;

class MagellanCollection extends Collection
{
    public function __construct($items = [])
    {
        if(is_iterable($items)){
            foreach ($items as $item) {
                $this->add($item);
            }
        }
    }

    public function add($item)
    {
        try {
            if(class_exists($item)){

                $this->items[$item] = $item;
            }
        } catch (\Throwable|FatalError $e) {}
        return $this;
    }

    public function push(...$values)
    {
        foreach ($values as $value) {
            $this->add($value);
        }

        return $this;
    }

    public function offsetSet($key, $value): void
    {
        $this->add($value);
    }

}
