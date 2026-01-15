<?php

namespace Lucent\Support\Dtos\Exceptions;

class DtoInvalidAttribute extends \Exception
{
    public function __construct($property)
    {
        parent::__construct("Property {$property} was not found in this object.");
    }
}
