<?php

declare(strict_types = 1);

namespace Galaxon\Core;

// Throwables
use Stringable;
use TypeError;

/**
 * Some convenience methods for working with types.
 * May move to Core later.
 */
class Type
{
    // region Type inspection

    /**
     * Check if a value is a number, i.e. an integer or a float.
     * This varies from is_numeric(), which also returns true for numeric strings.
     *
     * @param mixed $value The value to check.
     * @return bool True if the value is a number, false otherwise.
     */
    public static function isNumber(mixed $value): bool
    {
        return is_int($value) || is_float($value);
    }

    /**
     * Check if a value is an unsigned integer.
     *
     * @param mixed $value The value to check.
     * @return bool True if the value is an unsigned integer, false otherwise.
     */
    public static function isUint(mixed $value): bool
    {
        return is_int($value) && $value >= 0;
    }

    // endregion

    // region Miscellaneous

    /**
     * Get the basic type of a value.
     *
     * Result will be a string, one of:
     * - null
     * - bool
     * - int
     * - float
     * - string
     * - array
     * - object
     * - resource
     * - unknown
     *
     * @param mixed $value The value to get the type of.
     * @return string The basic type of the value.
     */
    public static function getBasicType(mixed $value): string
    {
        // Try get_debug_type() first as this returns the new, canonical type names.
        $type = get_debug_type($value);
        if (in_array($type, ['null', 'bool', 'int', 'float', 'string', 'array'], true)) {
            return $type;
        }

        // Call gettype() and return the first word, which should be "object", "resource", or "unknown".
        // NB: The documentation for get_debug_type() has no equivalent for "unknown type", so this may never occur.
        $type = gettype($value);
        return explode(' ', $type)[0];
    }

    /**
     * Convert any PHP value into a unique string, for use as a key in a collection like Set or Dictionary.
     *
     * @param mixed $value The value to convert.
     * @return string The unique string key.
     */
    public static function getStringKey(mixed $value): string
    {
        $type = self::getBasicType($value);

        // Core types.
        switch ($type) {
            case 'null':
                $result = 'n';
                break;

            case 'bool':
                $result = 'b:' . ($value ? 'T' : 'F');
                break;

            case 'int':
                $result = 'i:' . $value;
                break;

            case 'float':
                // Use toHex() because it will be unique for every possible float value, including special values.
                // The same cannot be said for a cast to string, or sprintf().
                $result = 'f:' . Double::toHex($value);
                break;

            case 'string':
                $result = 's:' . strlen($value) . ":$value";
                break;

            case 'array':
                $result = 'a:' . count($value) . ':' . Stringify::stringifyArray($value);
                break;

            case 'object':
                $result = 'o:' . spl_object_id($value);
                break;

            case 'resource':
                $result = 'r:' . get_resource_id($value);
                break;

            default:
                // Unknown.
                // Not sure if this can ever actually happen. gettype() can return 'unknown type' but
                // get_debug_type() has no equivalent. Defensive programming.
                throw new TypeError("Value has unknown type.");
        }

        return $result;
    }

    /**
     * Create a new TypeError using information about the parameter and expected type.
     *
     * @param string $var_name The name of the argument or variable that failed validation, e.g. 'index'.
     * @param string $expected_type The expected type (e.g., 'int', 'string', 'callable').
     * @param mixed $value The actual value that was provided (optional).
     */
    public static function createError(string $var_name, string $expected_type, mixed $value = null): TypeError {
        $message = "Variable '$var_name' must be of type $expected_type";

        if (func_num_args() > 2) {
            $actual_type = get_debug_type($value);
            $message .= ", $actual_type given.";
        }
        else {
            $message .= '.';
        }

        return new TypeError($message);
    }

    // endregion

    // region Traits

    /**
     * Check if an object or class uses a given trait.
     * Handle both class names and objects, including trait inheritance.
     */
    public static function usesTrait(object|string $obj_or_class, string $trait): bool
    {
        $all_traits = self::getTraitsRecursive($obj_or_class);
        return in_array($trait, $all_traits, true);
    }

    /**
     * Get all traits used by an object or class, including parent classes and trait inheritance.
     */
    private static function getTraitsRecursive(object|string $obj_or_class): array
    {
        // Get class name.
        $class = is_object($obj_or_class) ? get_class($obj_or_class) : $obj_or_class;

        // Collection for traits.
        $traits = [];

        // Get traits from current class and all parent classes.
        do {
            $class_traits = class_uses($class);
            $traits = array_merge($traits, $class_traits);

            // Also get traits used by the traits themselves.
            foreach ($class_traits as $trait) {
                $trait_traits = self::getTraitsRecursive($trait);
                $traits = array_merge($traits, $trait_traits);
            }
        } while ($class = get_parent_class($class));

        return array_unique($traits);
    }

    // endregion
}
