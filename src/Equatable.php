<?php

declare(strict_types=1);

namespace Galaxon\Core;

interface Equatable
{
    /**
     * Compare two objects and return true if they are equal.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if the two values are equal, false otherwise.
     */
    public function equals(mixed $other): bool;
}
