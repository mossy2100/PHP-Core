<?php

declare(strict_types=1);

namespace OceanMoon\Core\Traits\Comparison;

/**
 * Trait for objects that can be compared for equality.
 *
 * This trait defines a contract for objects that support equality comparison. Implementations should check for type
 * compatibility and return false for incompatible types rather than throwing exceptions.
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
     * - Return false for incompatible types (do not throw exceptions)
     * - Handle type checking gracefully
     * - Consider using epsilon-based comparison for floating-point values
     * - Be reflexive (x.equal(x) is always true)
     * - Be symmetric (if x.equal(y) then y.equal(x))
     * - Be transitive (if x.equal(y) and y.equal(z) then x.equal(z))
     *
     * @param mixed $other The value to compare with (can be any type).
     * @return bool True if the values are equal, false otherwise.
     */
    abstract public function equal(mixed $other): bool;
}
