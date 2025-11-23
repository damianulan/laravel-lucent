<?php

namespace Lucent\Contracts\Models;

interface HasShowRoute
{
    /**
     * return a route heading to show view of this model instance
     */
    public function routeShow(): string;
}
