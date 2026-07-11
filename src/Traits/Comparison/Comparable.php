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
 * The trait uses Equatable via composition.
 *
 * Type safety should be enforced within the compare() implementation. Use Types::same() or `instanceof` to verify
 * type compatibility, or convert/cast $other to the calling object's type where a sensible conversion exists (e.g.
 * via a toX() method); only throw IncomparableTypesException once no such conversion is possible or appropriate.
 *
 * If a type is not sortable, then these methods should in theory all throw IncomparableTypesException even if the
 * argument has the same type as the calling object; however, in that case, just don't use this trait. Just use
 * Equatable or nothing.
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
     * Implementations must return exactly -1, 0, or 1:
     *   -1 if this object is less than the other value
     *    0 if this object equals the other value
     *    1 if this object is greater than the other value
     *
     * Important: Return values must be exactly -1, 0, or 1 because the convenience methods (lessThan, etc.) use
     * strict equality checks. Use Numbers::sign() to normalize spaceship operator results.
     *
     * Implementation guidelines:
     * - Type juggling is encouraged; convert or cast $other to the calling object's type where a sensible
     *   conversion exists (e.g. via a toX() method) so that comparable-but-differently-typed values can be ordered.
     * - Should throw IncomparableTypesException only once no such conversion is possible or appropriate.
     * - Must be deterministic (same inputs always produce same result).
     * - Should be transitive (if A < B and B < C, then A < C).
     *
     * @param mixed $other The value to compare with.
     * @return int Exactly -1, 0, or 1 indicating the ordering relationship.
     * @throws IncomparableTypesException If the types are incompatible for comparison.
     */
    abstract public function compare(mixed $other): int;

    /**
     * Check if this object is equal to another value.
     *
     * This method overrides the abstract equal() method from the Equatable trait.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if the values are equal, false otherwise.
     * @throws IncomparableTypesException If the types are incompatible for comparison.
     */
    #[Override]
    public function equal(mixed $other): bool
    {
        return $this->compare($other) === 0;
    }

    /**
     * Check if this object is less than another value.
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
     * Check if this object is less than or equal to another value.
     *
     * Implemented as the negation of greaterThan() to maintain consistency.
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
     * Check if this object is greater than another value.
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
     * Check if this object is greater than or equal to another value.
     *
     * Implemented as the negation of lessThan() to maintain consistency.
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
