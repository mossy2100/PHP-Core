<?php

declare(strict_types=1);

namespace OceanMoon\Core;

use InvalidArgumentException;
use JsonException;
use LengthException;

/**
 * Container for useful array-related methods.
 */
final class Arrays
{
    // region Constructor

    /**
     * Private constructor to prevent instantiation.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    // endregion

    // region Inspection methods

    /**
     * Checks if an array contains recursion.
     *
     * @param array<array-key, mixed> $arr The array to check.
     * @return bool True if the array contains recursion, false otherwise.
     */
    public static function containsRecursion(array $arr): bool
    {
        try {
            json_encode($arr, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            if ($e->getCode() === JSON_ERROR_RECURSION) {
                return true;
            }
        }

        return false;
    }

    // endregion

    // region String methods

    /**
     * Wrap each string value in the array with quotes.
     *
     * Useful for formatting lists in error messages or output.
     * Does not perform escaping - values containing quotes will not be escaped.
     * Array keys are preserved.
     *
     * @param array<string> $arr Array of strings to quote.
     * @param bool $doubleQuotes Use double quotes instead of single quotes.
     * @return array<string> Array with each value wrapped in quotes.
     * @throws InvalidArgumentException If any array value is not a string.
     */
    public static function quoteValues(array $arr, bool $doubleQuotes = false): array
    {
        $quoteChar = $doubleQuotes ? '"' : "'";

        $quoteFn = static function ($value) use ($quoteChar) {
            // Type check.
            if (!is_string($value)) {
                throw new InvalidArgumentException('Cannot process non-string array values.');
            }

            // Wrap the value in quotes.
            return $quoteChar . $value . $quoteChar;
        };

        // Apply the quotes. array_map() preserves the array keys.
        return array_map($quoteFn, $arr);
    }

    /**
     * Convert an array of strings to a serial list, e.g. 'apples, oranges, and bananas'.
     *
     * The Oxford comma is always used if there are more than two items.
     * This could be made an option later, but generally it's a good idea.
     *
     * The default conjunction is 'and'; a common alternative would be 'or'.
     * Of course, you could use words from other languages, such as 'y' or 'o' (Spanish), or 'et' or 'ou' (French).
     *
     * @param array<string> $arr Array of strings.
     * @param string $conjunction The conjunction to use between the last two items, e.g. 'and'.
     * @return string Serial list of strings.
     * @throws InvalidArgumentException If any array value is not a string.
     */
    public static function toSerialList(array $arr, string $conjunction = 'and'): string
    {
        // Ensure all the array values are strings.
        foreach ($arr as $value) {
            if (!is_string($value)) {
                throw new InvalidArgumentException('Cannot process non-string array values.');
            }
        }

        $nItems = count($arr);

        return match ($nItems) {
            0 => '',
            1 => $arr[0],
            2 => $arr[0] . " $conjunction " . $arr[1],
            default => implode(', ', array_slice($arr, 0, -1)) .
                ", $conjunction " . $arr[$nItems - 1],
        };
    }

    // endregion

    // region Extraction methods

    /**
     * Get the first value in an array.
     *
     * This is for PHP versions prior to 8.5, which provides the array_first() function.
     *
     * @param non-empty-array<array-key, mixed> $arr The array to extract from.
     * @return mixed The first value in the array.
     * @throws LengthException If the array is empty.
     */
    public static function first(array $arr): mixed
    {
        // Check the array is not empty.
        if (count($arr) === 0) {
            throw new LengthException('Cannot get the first element of an empty array.');
        }

        return $arr[array_key_first($arr)];
    }

    /**
     * Get the last value in an array.
     *
     * This is for PHP versions prior to 8.5, which provides the array_last() function.
     *
     * @param non-empty-array<array-key, mixed> $arr The array to extract from.
     * @return mixed The last value in the array.
     * @throws LengthException If the array is empty.
     */
    public static function last(array $arr): mixed
    {
        // Check the array is not empty.
        if (count($arr) === 0) {
            throw new LengthException('Cannot get the last element of an empty array.');
        }

        return $arr[array_key_last($arr)];
    }

    // endregion

    // region Transformation methods

    /**
     * Remove all instances of a value from an array.
     *
     * Keys are preserved.
     *
     * @param array<array-key, mixed> $arr The original array.
     * @param mixed $valueToRemove The value to remove.
     * @return array<array-key, mixed> A new array without the given value, if it was there.
     */
    public static function removeValue(array $arr, mixed $valueToRemove): array
    {
        return array_filter($arr, static fn ($value) => $value !== $valueToRemove);
    }

    // endregion
}
