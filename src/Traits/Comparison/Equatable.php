<?php

declare(strict_types=1);

namespace OceanMoon\Core\Traits\Comparison;

/**
 * Trait for objects that can be compared for equality.
 *
 * This trait defines a contract for objects that support equality comparison.
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
     * The parameter is typed as mixed, not self, for two reasons:
     * 1. A self type hint is invariant: if a class using this trait is subclassed and the subclass overrides equal(),
     *    self would narrow to the subclass, which PHP rejects as an incompatible override of the trait method (which
     *    is bound to the base class).
     * 2. Implementations often need to accept related types, not just instances of the same class, e.g. a Complex
     *    number being compared with an int or float. There's no way to express "self or number" as a type hint, so
     *    implementations must check the type of $other themselves.
     *
     * Implementations should:
     * - Be reflexive (x.equal(x) is always true)
     * - Be symmetric (if x.equal(y) then y.equal(x))
     * - Be transitive (if x.equal(y) and y.equal(z) then x.equal(z))
     * - Throw for values of a type that cannot meaningfully be compared (typically InvalidArgumentException), rather
     *   than attempting a silent conversion. The only exceptions are the rare, deliberate cases where a related type
     *   is genuinely part of the same value domain (e.g. Complex accepting int or float).
     *
     * @param mixed $other The value to compare with.
     * @return bool True if the values are equal, false otherwise.
     */
    abstract public function equal(mixed $other): bool;
}
