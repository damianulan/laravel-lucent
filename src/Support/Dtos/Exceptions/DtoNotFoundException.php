<?php

namespace Lucent\Support\Dtos\Exceptions;

use Exception;

class DtoNotFoundException extends Exception
{
    public function __construct($class)
    {
        parent::__construct("Dto object for class {$class} not found.");
    }
}
