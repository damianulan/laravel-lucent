<?php

namespace Lucent\Exceptions\Authorization;

class ServiceUnauthorized extends \Exception
{
    public function __construct(string $service)
    {
        $message = "Service '$service' has not been authorized";
        $code = 500;
        parent::__construct($message, $code);
    }
}
