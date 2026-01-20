<?php

namespace Lucent\Support\Magellan\Workshop;

interface ScopeUsesCache
{
    /**
     * Number of seconds to cache the result of this scope.
     *
     * @return int
     */
    public function ttl(): int;
}
