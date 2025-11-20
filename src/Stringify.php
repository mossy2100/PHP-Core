<?php

declare(strict_types=1);

namespace Galaxon\Core;

use TypeError;
use ValueError;

/**
 * This class provides a method of formatting any PHP value as a string, with a few differences from the default
 * options of var_dump(), var_export(), print_r(), json_encode(), and serialize().
 *
 * - Floats never look like integers.
 * - Arrays that are lists will not show keys (like JSON arrays).
 * - Objects will be rendered in a style similar to an HTML tag, with UML-style visibility modifiers.
 * - Resources are also encoded in a style similar to HTML tags.
 *
 * The purpose of the class is to offer a somewhat more concise, readable, and informative alternative to the usual
 * options. It can be useful for exception, log, and debug messages.
 */
final class Stringify
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
     * Convert a value to a readable string representation.
     *
     * @param mixed $value The value to encode.
     * @param bool $pretty_print Whether to use pretty printing with indentation (default false).
     * @param int $indent_level The level of indentation for this structure (default 0).
     * @return string The string representation of the value.
     * @throws ValueError If the value cannot be stringified.
     * @throws TypeError If the value has an unknown type.
     */
    public static function stringify(mixed $value, bool $pretty_print = false, int $indent_level = 0): string
    {
        // Call the relevant method.
        switch (Types::getBasicType($value)) {
            case 'null':
            case 'bool':
            case 'int':
            case 'string':
                // This function call will never error for these types.
                return json_encode($value); // @phpstan-ignore return.type

            case 'float':
                /** @var float $value */
                return self::stringifyFloat($value);

            case 'array':
                /** @var mixed[] $value */
                return self::stringifyArray($value, $pretty_print, $indent_level);

            case 'resource':
                return self::stringifyResource($value);

            case 'object':
                /** @var object $value */
                return self::stringifyObject($value, $pretty_print, $indent_level);

            // @codeCoverageIgnoreStart
            // This should never happen, but we'll include it for completeness/robustness.
            // We can't test this, so get phpunit to ignore it for code coverage purposes.
            default:
                throw new TypeError('Unknown type.');
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Encode a float in such a way that it doesn't look like an integer.
     *
     * @param float $value The float value to encode.
     * @return string The string representation of the float.
     */
    public static function stringifyFloat(float $value): string
    {
        // Handle special values.
        if (is_nan($value)) {
            return 'NaN';
        }
        if ($value === INF) {
            return '∞';
        }
        if ($value === -INF) {
            return '-∞';
        }

        // Convert the float to a string.
        $s = (string)$value;
        // If the string representation of the float value has no decimal point or exponent (i.e. nothing to distinguish
        // it from an integer), append a decimal point.
        if (!preg_match('/[.eE]/', $s)) {
            $s .= '.0';
        }
        return $s;
    }

    /**
     * Stringify a PHP array in a style similar to JSON arrays and objects.
     *
     * We're not simply using json_encode() here because values might not be stringified in the desired way, especially
     * objects.
     *
     * A list (i.e. an array with sequential integer keys starting at 0) will use square brackets and show values only.
     * An associative array will use curly brackets and show keys and values. String keys will be quoted.
     *
     * If pretty printing is enabled, the result will be formatted with new lines and indentation.
     *
     * @param mixed[] $ary The array to encode.
     * @param bool $pretty_print Whether to use pretty printing (default false).
     * @param int $indent_level The level of indentation for this structure (default 0).
     * @return string The string representation of the array.
     * @throws ValueError If the array contains circular references.
     */
    public static function stringifyArray(array $ary, bool $pretty_print = false, int $indent_level = 0): string
    {
        // Detect circular references.
        if (Arrays::containsRecursion($ary)) {
            throw new ValueError('Cannot stringify arrays containing circular references.');
        }

        $pairs = [];
        $indent = $pretty_print ? str_repeat(' ', 4 * ($indent_level + 1)) : '';
        $is_list = array_is_list($ary);

        // Generate the pairs.
        foreach ($ary as $key => $value) {
            $value_str = self::stringify($value, $pretty_print, $indent_level + 1);
            // Encode a list without keys.
            if ($is_list) {
                $pairs[] = "$indent$value_str";
            } else {
                // Encode an associative array with keys.
                $key_str = self::stringify($key, $pretty_print, $indent_level + 1);
                $pairs[] = "$indent$key_str: $value_str";
            }
        }

        // Determine the opening and closing brackets.
        $open_bracket = $is_list ? '[' : '{';
        $close_bracket = $is_list ? ']' : '}';

        // If pretty print, return string formatted with new lines and indentation.
        if ($pretty_print) {
            $bracket_indent = str_repeat(' ', 4 * $indent_level);
            return $open_bracket . "\n" . implode(",\n", $pairs) . "\n$bracket_indent" . $close_bracket;
        }

        // Otherwise, return the string in a single line.
        return $open_bracket . implode(', ', $pairs) . $close_bracket;
    }

    /**
     * Stringify a resource.
     *
     * @param mixed $value The resource to stringify.
     * @return string The string representation of the resource.
     * @throws TypeError If the value is not a resource.
     * @see stringifyObject()
     *
     */
    public static function stringifyResource(mixed $value): string
    {
        // Can't type hint for resource, so check manually.
        if (!is_resource($value)) {
            throw new TypeError('Value is not a resource.');
        }

        return '(resource type: "' . get_resource_type($value) . '", id: ' . get_resource_id($value) . ')';
    }

    /**
     * Stringify an object.
     *
     * The resulting string borrows syntax from both JSON objects and HTML tags.
     * - enclosed in angle brackets like an HTML or XML tag
     * - the fully qualified class name is used (i.e. with the namespace) as the tag name
     * - property names are not quoted
     * - key-value pairs use colons and are comma-separated (like JSON objects)
     * - the visibility of each property is shown using UML notation (+ for public, # for protected, - for private)
     *
     * If pretty printing is enabled, the result will be formatted with new lines and indentation.
     *
     * @param object $obj The object to encode.
     * @param bool $pretty_print Whether to use pretty printing (default false).
     * @param int $indent_level The level of indentation for this structure (default 0).
     * @return string The string representation of the object.
     * @throws TypeError If the object's class is anonymous.
     */
    public static function stringifyObject(object $obj, bool $pretty_print = false, int $indent_level = 0): string
    {
        // Get the tag name.
        $class = get_class($obj);

        // Check for anonymous classes. We don't want null bytes in the result.
        if (str_contains($class, '@anonymous')) {
            $class = '@anonymous';
        }

        // Convert the object to an array to get its properties.
        // This works better than reflection, as new properties can be created when converting the object to an array.
        $a = (array)$obj;

        // Early return if no properties.
        if (count($a) === 0) {
            return "<$class>";
        }

        // Generate the strings for key-value pairs. Each will be on its own line if pretty printing is enabled.
        $pairs = [];
        $indent = $pretty_print ? str_repeat(' ', 4 * ($indent_level + 1)) : '';

        foreach ($a as $key => $value) {
            // Split on null bytes to determine the property name and visibility.
            $name_parts = explode("\0", $key);
            if (count($name_parts) === 1) {
                // Property is public.
                $vis_symbol = '+';
            } else {
                // Property must be protected or private. If the second item in the $name_parts array is '*', the
                // property is protected; otherwise, it's private.
                $vis_symbol = $name_parts[1] === '*' ? '#' : '-';
                $key = $name_parts[array_key_last($name_parts)];
            }

            $value_str = self::stringify($value, $pretty_print, $indent_level + 1);
            $pairs[] = "$indent$vis_symbol$key: $value_str";
        }

        // If pretty print, return string formatted with new lines and indentation.
        if ($pretty_print) {
            return "<$class\n" . implode(",\n", $pairs) . "\n>";
        }

        return "<$class " . implode(', ', $pairs) . '>';
    }

    /**
     * Get a short string representation of the given value for use in error messages, log messages, and the like.
     *
     * @param mixed $value The value to get the string representation for.
     * @param int $max_len The maximum length of the result.
     * @return string The short string representation.
     * @throws ValueError If the maximum length is less than 10.
     * @throws TypeError If the value has an unknown type.
     * @see stringify()
     */
    public static function abbrev(mixed $value, int $max_len = 30): string
    {
        // Check the max length is reasonable.
        if ($max_len < 10) {
            throw new ValueError('The maximum string length must be at least 10.');
        }

        // Get the value as a string without newlines or indentation.
        $result = self::stringify($value);

        // Trim if necessary.
        if (strlen($result) > $max_len) {
            $result = substr($result, 0, $max_len - 3) . '...';
        }

        return $result;
    }
}
