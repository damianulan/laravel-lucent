<?php

namespace Lucent\Support\Dtos;


use DTOs\Dto;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

abstract class LaravelDto extends Dto implements Arrayable, Jsonable
{

}
