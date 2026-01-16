<?php

namespace Lucent\Support\Magellan\Workshop;

interface ScopeUsesCache
{
    /**
     * Number of seconds
     *
     * @return int
     */
    public function ttl(): int;
}
