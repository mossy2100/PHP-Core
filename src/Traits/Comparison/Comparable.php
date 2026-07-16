<?php

declare(strict_types=1);

namespace OceanMoon\Core\Traits\Comparison;

use Override;

/**
 * Trait providing a complete set of comparison operations based on a single compare() method.
 *
 * This trait follows the Template Method Pattern: you implement the abstract compare() method that returns exactly
 * -1, 0, or 1, and the trait provides all other comparison methods automatically: equal(), lessThan(),
 * lessThanOrEqual(), greaterThan(), and greaterThanOrEqual().
 *
 * The trait uses Equatable via composition. It should only be used for types that can be ordered.
 *
 * @see Equatable The base equality trait this includes.
 * @see ApproxComparable For types needing approximate comparison with tolerance.
 *
 * Full documentation and examples: docs/Traits/Comparison/Comparable.md
 *
 * @codeCoverageIgnore
 * @phpstan-ignore trait.unused
 */
trait Comparable
{
    use Equatable;

    /**
     * Compare this object with another and return an integer indicating the ordering relationship.
     *
     * Implementations must return exactly -1, 0, or 1:
     *   -1 if this object is less than the other value
     *    0 if this object equals the other value
     *    1 if this object is greater than the other value
     *
     * Important: Return values must be exactly -1, 0, or 1 because the convenience methods (lessThan, etc.) use
     * strict equality checks. Use sign() to normalize spaceship operator results.
     *
     * Implementation guidelines:
     * - Must be deterministic (same inputs always produce same result).
     * - Should be transitive (if A < B and B < C, then A < C).
     *
     * The parameter is typed as mixed rather than self; see Equatable::equal() for why.
     *
     * @param mixed $other The value to compare with.
     * @return int Exactly -1, 0, or 1 indicating the ordering relationship.
     */
    abstract public function compare(mixed $other): int;

    /**
     * Check if this object is equal to another value.
     *
     * This method overrides the abstract equal() method from the Equatable trait.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if the values are equal, false otherwise.
     */
    #[Override] // Equatable
    public function equal(mixed $other): bool
    {
        return $this->compare($other) === 0;
    }

    /**
     * Check if this object is less than another value.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this object is less than the other object, false otherwise.
     */
    public function lessThan(mixed $other): bool
    {
        return $this->compare($other) === -1;
    }

    /**
     * Check if this object is less than or equal to another value.
     *
     * Implemented as the negation of greaterThan() to maintain consistency.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this object is less than or equal to the other object, false otherwise.
     */
    public function lessThanOrEqual(mixed $other): bool
    {
        return $this->compare($other) !== 1;
    }

    /**
     * Check if this object is greater than another value.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this object is greater than the other object, false otherwise.
     */
    public function greaterThan(mixed $other): bool
    {
        return $this->compare($other) === 1;
    }

    /**
     * Check if this object is greater than or equal to another value.
     *
     * Implemented as the negation of lessThan() to maintain consistency.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this object is greater than or equal to the other object, false otherwise.
     */
    public function greaterThanOrEqual(mixed $other): bool
    {
        return $this->compare($other) !== -1;
    }
}
