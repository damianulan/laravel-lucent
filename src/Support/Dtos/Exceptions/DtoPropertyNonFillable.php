<?php

namespace Lucent\Support\Dtos\Exceptions;

class DtoPropertyNonFillable extends \Exception
{
    public function __construct($property)
    {
        parent::__construct("Property {$property} is not fillable, thus unable to be set.");
    }
}
