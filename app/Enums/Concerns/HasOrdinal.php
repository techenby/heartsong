<?php

namespace App\Enums\Concerns;

/**
 * String-backed enums don't sort in declaration order, so expose each case's position.
 */
trait HasOrdinal
{
    public function ordinal(): int
    {
        return (int) array_search($this, self::cases(), true);
    }
}
