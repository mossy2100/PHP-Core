<?php

declare(strict_types=1);

namespace OceanMoon\Core\Traits\Comparison;

use OceanMoon\Core\Exceptions\IncomparableTypesException;
use Override;

/**
 * Trait providing a complete set of comparison operations based on a single compare() method.
 *
 * This trait follows the Template Method Pattern: you implement the abstract compare() method that returns exactly
 * -1, 0, or 1, and the trait provides all other comparison methods automatically: equal(), lessThan(),
 * lessThanOrEqual(), greaterThan(), and greaterThanOrEqual().
 *
 * The trait uses Equatable via composition, providing an equal() method that returns false gracefully for incompatible
 * types (rather than throwing IncomparableTypesException like the ordering methods do).
 *
 * Type safety should be enforced within the compare() implementation. Use Types::same() or `instanceof` to verify
 * type compatibility, and throw IncomparableTypesException if no ordering relationship exists between the two types.
 *
 * Example usage:
 * <code>
 * class Score
 * {
 *     use Comparable;
 *
 *     public function __construct(private int $value) {}
 *
 *     #[Override]
 *     public function compare(mixed $other): int
 *     {
 *         if (!Types::same($this, $other)) {
 *             throw new IncomparableTypesException($this, $other);
 *         }
 *         return Numbers::sign($this->value <=> $other->value);
 *     }
 * }
 * </code>
 *
 * @see Equatable The base equality trait this includes.
 * @see ApproxComparable For types needing approximate comparison with tolerance.
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
     * Implementations must return exactly -1, 0, or 1 (not just negative/zero/positive):
     *   -1 if this object is less than the other object
     *    0 if this object equals the other object
     *    1 if this object is greater than the other object
     *
     * Important: Return values must be exactly -1, 0, or 1 because the convenience methods (lessThan, etc.) use
     * strict equality checks. Use Numbers::sign() to normalize spaceship operator results.
     *
     * Implementation guidelines:
     * - May throw IncomparableTypesException for incompatible types (this is expected behavior)
     * - Must be consistent (same inputs always produce same result)
     * - Should be transitive (if A < B and B < C, then A < C)
     *
     * @param mixed $other The value to compare with.
     * @return int Exactly -1, 0, or 1 indicating the ordering relationship.
     * @throws IncomparableTypesException If the types are incompatible for comparison.
     */
    abstract public function compare(mixed $other): int;

    /**
     * Check if this object is equal to another value.
     *
     * This method overrides the abstract equal() method from the Equatable trait. Unlike the other comparison methods
     * in this trait (lessThan, greaterThan, etc.), equal() returns false gracefully for incompatible types instead
     * of throwing IncomparableTypesException.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if the values are equal, false otherwise (including for incomparable types).
     */
    #[Override]
    public function equal(mixed $other): bool
    {
        try {
            return $this->compare($other) === 0;
        } catch (IncomparableTypesException) {
            return false;
        }
    }

    /**
     * Check if this object is less than another object.
     *
     * Verifies type compatibility before delegating to compare(). Throws exception for incompatible types.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this object is less than the other object, false otherwise.
     * @throws IncomparableTypesException If the types are incompatible for comparison.
     */
    public function lessThan(mixed $other): bool
    {
        return $this->compare($other) === -1;
    }

    /**
     * Check if this object is less than or equal to another object.
     *
     * Implemented as the negation of greaterThan() to maintain consistency. Throws IncomparableTypesException for
     * incompatible types.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this object is less than or equal to the other object, false otherwise.
     * @throws IncomparableTypesException If the types are incompatible for comparison.
     */
    public function lessThanOrEqual(mixed $other): bool
    {
        return $this->compare($other) !== 1;
    }

    /**
     * Check if this object is greater than another object.
     *
     * Verifies type compatibility before delegating to compare(). Throws IncomparableTypesException for incompatible
     * types.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this object is greater than the other object, false otherwise.
     * @throws IncomparableTypesException If the types are incompatible for comparison.
     */
    public function greaterThan(mixed $other): bool
    {
        return $this->compare($other) === 1;
    }

    /**
     * Check if this object is greater than or equal to another object.
     *
     * Implemented as the negation of lessThan() to maintain consistency. Throws IncomparableTypesException for
     * incompatible types.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this object is greater than or equal to the other object, false otherwise.
     * @throws IncomparableTypesException If the types are incompatible for comparison.
     */
    public function greaterThanOrEqual(mixed $other): bool
    {
        return $this->compare($other) !== -1;
    }
}
