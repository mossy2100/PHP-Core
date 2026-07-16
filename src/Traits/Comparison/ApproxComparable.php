<?php

declare(strict_types=1);

namespace OceanMoon\Core\Traits\Comparison;

use OceanMoon\Core\Floats;

/**
 * Trait providing complete comparison operations with both exact and approximate equality.
 *
 * This trait combines Comparable and ApproxEquatable to provide a comprehensive comparison API for numeric value
 * types that contain floating-point components. It's ideal for types like Rational, Quantity, or other numeric
 * types where both exact ordering and tolerance-based comparison are needed.
 *
 * Classes using this trait get:
 * - All methods from Comparable: compare(), equal(), lessThan(), greaterThan(), etc.
 * - All methods from ApproxEquatable: approxEqual()
 * - Additional method: approxCompare() for three-way comparison with tolerance
 *
 * The approxCompare() method provided by this trait uses approximate equality to determine if two values should be
 * considered equal (within tolerance), and falls back to exact comparison for ordering when they're not approximately
 * equal.
 *
 * @see Comparable The exact comparison trait this includes.
 * @see ApproxEquatable The approximate equality trait this includes.
 * @see Floats::approxEqual() The tolerance algorithm used.
 *
 * Full documentation and examples: docs/Traits/Comparison/ApproxComparable.md
 *
 * @codeCoverageIgnore
 * @phpstan-ignore trait.unused
 */
trait ApproxComparable
{
    use Comparable;
    use ApproxEquatable;

    /**
     * Compare this object with another using approximate equality for the equal case.
     *
     * This method performs a three-way comparison that returns:
     *   -1 if this object is less than the other object
     *    0 if this object approximately equals the other object (within tolerances)
     *    1 if this object is greater than the other object
     *
     * The method first checks if the values are approximately equal within the given tolerances. If they are, it
     * returns 0. Otherwise, it delegates to the exact compare() method to determine ordering.
     *
     * This is particularly useful for sorting operations where you want values within a tolerance to be considered
     * equal, while still maintaining a consistent ordering for values outside the tolerance.
     *
     * Return values are exactly -1, 0, or 1 (not just negative/zero/positive) because convenience methods may rely
     * on strict equality checks.
     *
     * The parameter is typed as mixed rather than self; see Equatable::equal() for why.
     *
     * @param mixed $other The value to compare with.
     * @param float $relTol The maximum allowed relative difference (default: 1e-9).
     * @param float $absTol The maximum allowed absolute difference (default: PHP_FLOAT_EPSILON).
     * @return int Exactly -1, 0, or 1 indicating the ordering relationship.
     * @see approxEqual() The method used to check approximate equality.
     * @see compare() The method used for exact ordering when not approximately equal.
     */
    public function approxCompare(
        mixed $other,
        float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
        float $absTol = Floats::DEFAULT_ABSOLUTE_TOLERANCE
    ): int {
        return $this->approxEqual($other, $relTol, $absTol) ? 0 : $this->compare($other);
    }
}
