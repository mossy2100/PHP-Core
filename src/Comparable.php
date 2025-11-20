<?php

declare(strict_types=1);

namespace Galaxon\Core;

/**
 * This trait implements common comparison methods for objects.
 *
 * The class using the trait must implement the compare() method, and the other methods are provided as convenience
 * wrappers around it.
 */
trait Comparable
{
    /**
     * Compare two values and return an integer indicating if the first value is less than, equal to, or greater than
     * the second value.
     *
     * The result will be:
     *     -1 if this value is less than the other value
     *      0 if the values are equal
     *      1 if this value is greater than the other value
     *
     * @param mixed $other The value to compare with.
     * @return int Either -1, 0, or 1.
     */
    abstract public function compare(mixed $other): int;

    /**
     * Check if this value is equal to another value.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if the values are equal, false otherwise.
     */
    public function equals(mixed $other): bool
    {
        return $other instanceof static && $this->compare($other) === 0;
    }

    /**
     * Check if this value is less than another value.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this value is less than the other value, false otherwise.
     */
    public function isLessThan(mixed $other): bool
    {
        return $this->compare($other) === -1;
    }

    /**
     * Check if this value is less than or equal to another value.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this value is less than or equal to the other value, false otherwise.
     */
    public function isLessThanOrEqual(mixed $other): bool
    {
        return !$this->isGreaterThan($other);
    }

    /**
     * Check if this value is greater than another value.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this value is greater than the other value, false otherwise.
     */
    public function isGreaterThan(mixed $other): bool
    {
        return $this->compare($other) === 1;
    }

    /**
     * Check if this value is greater than or equal to another value.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if this value is greater than or equal to the other value, false otherwise.
     */
    public function isGreaterThanOrEqual(mixed $other): bool
    {
        return !$this->isLessThan($other);
    }
}
