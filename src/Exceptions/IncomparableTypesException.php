<?php

declare(strict_types=1);

namespace OceanMoon\Core\Exceptions;

use InvalidArgumentException;

/**
 * Exception thrown when attempting to compare values of incompatible types.
 *
 * This exception is used by the Comparable and ApproxComparable traits when a comparison
 * is attempted between objects of different types that cannot be meaningfully compared.
 *
 * The exception automatically generates a descriptive message based on the types of
 * the values being compared, using `get_debug_type()` for accurate type names.
 *
 * @see \OceanMoon\Core\Traits\Comparison\Comparable
 * @see \OceanMoon\Core\Traits\Comparison\ApproxComparable
 */
class IncomparableTypesException extends InvalidArgumentException
{
    /**
     * Create a new IncomparableTypesException.
     *
     * @param mixed $a The first value in the failed comparison.
     * @param mixed $b The second value in the failed comparison.
     */
    public function __construct(mixed $a, mixed $b)
    {
        $typeA = get_debug_type($a);
        $typeB = get_debug_type($b);
        parent::__construct("Cannot compare $typeA with $typeB.");
    }
}
