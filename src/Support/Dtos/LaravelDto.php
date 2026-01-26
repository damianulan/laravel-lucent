<?php

namespace Lucent\Support\Dtos;


use DTOs\Dto;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;

abstract class LaravelDto extends Dto implements Arrayable, Jsonable
{
    public function toCollection(): Collection
    {
        return new Collection($this->all());
    }
}
