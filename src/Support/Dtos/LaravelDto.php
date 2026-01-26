<?php

namespace Lucent\Support\Dtos;


use DTOs\Dto;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;

abstract class LaravelDto extends Dto implements Arrayable, Jsonable
{
    /**
     * Get all attributes as a collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function toCollection(): Collection
    {
        return new Collection($this->all());
    }
}
