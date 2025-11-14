<?php

declare(strict_types=1);

namespace Galaxon\Core;

use JsonException;

/**
 * Container for useful array-related methods.
 */
final class Arrays
{
    /**
     * Private constructor to prevent instantiation.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Checks if an array contains recursion.
     *
     * @param array $arr The array to check.
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
}
