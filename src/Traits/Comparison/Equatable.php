<?php

declare(strict_types=1);

namespace OceanMoon\Core\Traits\Comparison;

use OceanMoon\Core\Exceptions\IncomparableTypesException;
use OceanMoon\Core\Types;

/**
 * Trait for objects that can be compared for equality.
 *
 * This trait defines a contract for objects that support equality comparison. Implementations should check for type
 * compatibility, converting or casting $other to the calling object's type first if a sensible conversion exists
 * (e.g. via a toX() method), and throw an IncomparableTypesException only if no such conversion is possible or
 * appropriate.
 *
 * This is implemented as a trait rather than an interface to enable better composition with other comparison traits
 * like Comparable, ApproxEquatable, and ApproxComparable.
 *
 * For types with a natural ordering (where less than/greater than comparisons make sense), use the Comparable trait
 * instead, which includes equal() along with ordering methods like lessThan(), greaterThan(), etc.
 *
 * @see Comparable For types with natural ordering.
 * @see ApproxEquatable For approximate equality comparison.
 *
 * @codeCoverageIgnore
 * @phpstan-ignore trait.unused
 */
trait Equatable
{
    /**
     * Compare this object with another value and determine if they are equal.
     *
     * Implementations should:
     * - Attempt to convert or cast $other to the calling object's type where a sensible conversion exists (e.g. via
     *   a toX() method), so that comparable-but-differently-typed values (e.g. int vs Complex) can still be compared
     * - Throw IncomparableTypesException only once no such conversion is possible or appropriate
     * - Be reflexive (x.equal(x) is always true)
     * - Be symmetric (if x.equal(y) then y.equal(x))
     * - Be transitive (if x.equal(y) and y.equal(z) then x.equal(z))
     *
     * @param mixed $other The value to compare with (can be any type).
     * @return bool True if the values are equal, false otherwise.
     * @throws IncomparableTypesException If the types are incompatible for comparison.
     */
    abstract public function equal(mixed $other): bool;

    /**
     * Check if this object is identical to another value: the same type, and equal() to it.
     *
     * This is a stricter counterpart to equal() -- useful for implementations that deliberately widen equal() to
     * accept other types (e.g. accepting an int alongside instances of the same class). Types::same() is used
     * rather than instanceof so that, for non-final classes, a subclass instance is not considered identical to a
     * parent-class instance (matching the distinction PHP's own === draws between object identity and equal()'s
     * looser ==-like semantics).
     *
     * This default implementation is correct for any equal() implementation without needing to be overridden:
     * Types::same() already excludes anything that isn't the exact same type, so equal() only ever needs to handle
     * the same-type comparison here, which every equal() implementation must support as a baseline regardless of
     * what other types it also accepts.
     *
     * @param mixed $other The value to compare with (can be any type).
     * @return bool True if the values are the same type and equal, false otherwise.
     */
    public function identical(mixed $other): bool
    {
        return Types::same($this, $other) && $this->equal($other);
    }
}
