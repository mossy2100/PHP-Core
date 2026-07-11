<?php

declare(strict_types=1);

namespace OceanMoon\Core\Traits\Comparison;

use OceanMoon\Core\Exceptions\IncomparableTypesException;
use OceanMoon\Core\Floats;

/**
 * Trait for objects that support approximate equality comparison with configurable tolerances.
 *
 * This trait extends Equatable by adding an approxEqual() method that allows comparison within specified absolute
 * and relative tolerances. This is essential for value types that contain floating-point components (like Complex
 * numbers or Quantities), where exact equality comparison may fail due to floating-point precision limitations.
 *
 * The trait uses Equatable via composition, so classes using ApproxEquatable get both exact equality (equal()) and
 * approximate equality (approxEqual()) methods. Both throw IncomparableTypesException for incompatible types, per
 * Equatable's contract.
 *
 * Implementations should use the same tolerance algorithm as Floats::approxEqual():
 * 1. Check absolute tolerance first (useful for values near zero)
 * 2. If that fails, check relative tolerance (scales with magnitude)
 *
 * Example usage:
 * <code>
 * class Complex
 * {
 *     use ApproxEquatable;
 *
 *     public function __construct(private float $real, private float $imag) {}
 *
 *     #[Override]
 *     public function equal(mixed $other): bool
 *     {
 *         if (!$other instanceof self) {
 *             throw new IncomparableTypesException($this, $other);
 *         }
 *         return $this->real === $other->real && $this->imag === $other->imag;
 *     }
 *
 *     #[Override]
 *     public function approxEqual(mixed $other, float $relTol = ..., float $absTol = ...): bool
 *     {
 *         if (!$other instanceof self) {
 *             throw new IncomparableTypesException($this, $other);
 *         }
 *         return Floats::approxEqual($this->real, $other->real, $relTol, $absTol)
 *             && Floats::approxEqual($this->imag, $other->imag, $relTol, $absTol);
 *     }
 * }
 * </code>
 *
 * @see Equatable The base equality trait this includes.
 * @see ApproxComparable For types with both ordering and approximate equality.
 * @see Floats::approxEqual() The algorithm to use for tolerance checking.
 *
 * @codeCoverageIgnore
 * @phpstan-ignore trait.unused
 */
trait ApproxEquatable
{
    use Equatable;

    /**
     * Check if this object approximately equals another within specified tolerances.
     *
     * This method uses a combined absolute and relative tolerance approach, matching the algorithm in
     * Floats::approxEqual(). The absolute tolerance is checked first (useful for comparisons near zero), and if
     * that fails, the relative tolerance is checked (which scales with the magnitude of the values).
     *
     * To compare using only absolute difference, set $relTol to 0.0.
     * To compare using only relative difference, set $absTol to 0.0.
     *
     * As with equal(), implementations should first attempt to convert or cast $other to the calling object's type
     * where a sensible conversion exists (e.g. via a toX() method), and throw IncomparableTypesException only once
     * no such conversion is possible or appropriate: an incompatible type indicates a programming error worth
     * surfacing, not merely "not equal."
     *
     * @param mixed $other The value to compare with.
     * @param float $relTol The maximum allowed relative difference (default: 1e-9).
     * @param float $absTol The maximum allowed absolute difference (default: PHP_FLOAT_EPSILON).
     * @return bool True if the values are equal within the given tolerances, false otherwise.
     * @throws IncomparableTypesException If the types are incompatible for comparison.
     * @see Floats::approxEqual() For the tolerance algorithm details.
     */
    abstract public function approxEqual(
        mixed $other,
        float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
        float $absTol = Floats::DEFAULT_ABSOLUTE_TOLERANCE
    ): bool;
}
