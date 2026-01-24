<?php

namespace Lucent\Support\Magellan\Workshop;

interface ScopeUsesCache
{
    /**
     * Number of seconds to cache the result of this scope.
     * return 0 to store forever.
     *
     * @return int
     */
    public function ttl(): int;
}
