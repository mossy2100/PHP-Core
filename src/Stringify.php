<?php

declare(strict_types=1);

namespace OceanMoon\Core;

use DomainException;
use InvalidArgumentException;
use Stringable;
use UnexpectedValueException;
use UnitEnum;

/**
 * This class provides a method of formatting any PHP value as a string, with a few differences from the default
 * options of echo(), print(), var_dump(), var_export(), print_r(), json_encode(), and serialize().
 *
 * - Floats never look like integers.
 * - Strings are single-quoted.
 * - Arrays are rendered as parseable PHP code using modern square bracket syntax.
 * - Arrays that are lists will not show keys; associative arrays will show keys.
 * - Objects are rendered like arrays but with a class name and curly braces instead of square brackets.
 * - Object properties are shown with UML visibility modifiers: + (public), # (protected), and - (private).
 * - Enums are rendered as Fully\Qualified\ClassName::CaseName
 * - Resources show both id and type.
 *
 * The stringify results for objects and resources are not parseable by PHP, but they are for other types.
 *
 * The purpose of the class is to offer a somewhat more concise, readable, and informative alternative to the usual
 * options. This can be useful for exception, log, and debug messages.
 */
final class Stringify
{
    #region Constants

    /**
     * The default number of spaces to indent each level.
     */
    public const int DEFAULT_INDENT = 4;

    /**
     * The default maximum line length for pretty-printed output.
     */
    public const int DEFAULT_MAX_LINE_LENGTH = 120;

    /**
     * The current number of spaces to indent each level.
     */
    private static int $indent = self::DEFAULT_INDENT;

    /**
     * The current maximum line length for pretty-printed output.
     */
    private static int $maxLineLength = self::DEFAULT_MAX_LINE_LENGTH;

    #endregion

    #region Configuration

    /**
     * Set the number of spaces used for each indentation level.
     *
     * @param int $indent The number of spaces (must be > 0).
     * @throws InvalidArgumentException If the indent is not positive.
     */
    public static function setIndent(int $indent): void
    {
        if ($indent <= 0) {
            throw new InvalidArgumentException("Invalid indent: $indent. Must be positive.");
        }
        self::$indent = $indent;
    }

    /**
     * Get the current number of spaces used for each indentation level.
     *
     * @return int The current indent value.
     */
    public static function getIndent(): int
    {
        return self::$indent;
    }

    /**
     * Set the maximum line length for pretty-printed output.
     *
     * @param int $maxLineLength The maximum line length (must be > 0).
     * @throws InvalidArgumentException If the max line length is not positive.
     */
    public static function setMaxLineLength(int $maxLineLength): void
    {
        if ($maxLineLength <= 0) {
            throw new InvalidArgumentException("Invalid max line length: $maxLineLength. Must be positive.");
        }
        self::$maxLineLength = $maxLineLength;
    }

    /**
     * Get the current maximum line length for pretty-printed output.
     *
     * @return int The current max line length value.
     */
    public static function getMaxLineLength(): int
    {
        return self::$maxLineLength;
    }

    /**
     * Reset indent and max line length to their default values.
     */
    public static function resetDefaults(): void
    {
        self::$indent = self::DEFAULT_INDENT;
        self::$maxLineLength = self::DEFAULT_MAX_LINE_LENGTH;
    }

    #endregion

    #region Constructor

    /**
     * Private constructor to prevent instantiation.
     *
     * @codeCoverageIgnore
     */
    private function __construct() {}

    #endregion

    #region Main stringification methods

    /**
     * Convert a value to a readable string representation.
     *
     * @param mixed $value The value to encode.
     * @param bool $prettyPrint Whether to use pretty printing with indentation (default false).
     * @param int $indentLevel The level of indentation for this structure (default 0).
     * @return string The string representation of the value.
     * @throws DomainException If the value cannot be stringified.
     * @throws UnexpectedValueException If the value has an unknown type.
     * @throws InvalidArgumentException
     */
    public static function stringify(mixed $value, bool $prettyPrint = false, int $indentLevel = 0): string
    {
        // Call the relevant method.
        switch (Types::getBasicType($value)) {
            case 'null':
                assert($value === null);
                return 'null';

            case 'bool':
                assert(is_bool($value));
                return $value ? 'true' : 'false';

            case 'int':
                assert(is_int($value));
                return (string) $value;

            case 'float':
                assert(is_float($value));
                return self::stringifyFloat($value);

            case 'string':
                assert(is_string($value));
                return self::stringifyString($value);

            case 'array':
                assert(is_array($value));
                return self::stringifyArray($value, $prettyPrint, $indentLevel);

            case 'enum':
                assert($value instanceof UnitEnum);
                return self::stringifyEnum($value);

            case 'object':
                assert(is_object($value));
                return self::stringifyObject($value, $prettyPrint, $indentLevel);

            case 'resource':
                return self::stringifyResource($value);

            default:
                // @codeCoverageIgnoreStart
                throw new UnexpectedValueException('Unknown type.');
                // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Get a short string representation of the given value for use in error messages, log messages, and the like.
     *
     * @param mixed $value The value to get the string representation for.
     * @param int $maxLen The maximum length of the result.
     * @return string The short string representation.
     * @throws DomainException If the maximum length is less than the minimum, or if the value cannot be stringified.
     * @throws UnexpectedValueException If the type cannot be inferred.
     */
    public static function abbrev(mixed $value, int $maxLen = 30): string
    {
        // Check the max length is reasonable.
        $minMaxLen = 10;
        if ($maxLen < $minMaxLen) {
            throw new DomainException("Invalid maximum string length: $maxLen. Must be at least $minMaxLen.");
        }

        // Get the value as a string without newlines or indentation.
        $result = self::stringify($value);

        // Trim if necessary.
        if (mb_strlen($result) > $maxLen) {
            $result = mb_substr($result, 0, $maxLen - 3) . '...';
        }

        return $result;
    }

    /**
     * Convert any value to a string.
     *
     * Strings pass through as-is. Stringable objects use __toString(). All other types are
     * converted via Stringify::stringify() (without pretty printing).
     *
     * @param mixed $value The value to convert.
     * @return string The string representation.
     */
    public static function toString(mixed $value): string
    {
        return is_string($value) || $value instanceof Stringable ? (string) $value : self::stringify($value);
    }

    #endregion

    #region Type-specific stringification methods

    /**
     * Encode a float in such a way that it doesn't look like an integer.
     *
     * @param float $value The float value to encode.
     * @return string The string representation of the float.
     */
    public static function stringifyFloat(float $value): string
    {
        // Convert the float to a string. This will also work for ±INF and NAN.
        // The @ suppresses PHP 8.5's warning when casting NAN to string.
        $s = @(string) $value;

        // Handle non-finite values.
        if (!is_finite($value)) {
            return $s;
        }

        // If the string representation of the float value has no decimal point or exponent (i.e. nothing to distinguish
        // it from an integer), append a decimal point.
        if (!preg_match('/[.eE]/', $s)) {
            $s .= '.0';
        }

        return $s;
    }

    /**
     * Convert a string to a parseable single-quoted string.
     *
     * @param string $value The string value to encode.
     * @return string The single-quoted, escaped string representation.
     * @throws DomainException If the string is not UTF-8 and the encoding could not be detected.
     */
    public static function stringifyString(string $value): string
    {
        // Get the string as UTF-8 if not already.
        if (!mb_check_encoding($value, 'UTF-8')) {
            // Try to detect the encoding.
            $encoding = mb_detect_encoding($value, mb_detect_order(), true);
            if ($encoding === false) {
                throw new DomainException('String encoding is not UTF-8 and could not be detected.');
            }

            // Convert the string to UTF-8.
            $value = mb_convert_encoding($value, 'UTF-8', $encoding);
            if ($value === false) {
                // @codeCoverageIgnoreStart
                throw new DomainException('String was not UTF-8 and could not be converted to UTF-8.');
                // @codeCoverageIgnoreEnd
            }
        }

        // Escape backslashes and single quotes.
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace("'", "\\'", $value);

        return "'$value'";
    }

    /**
     * Stringify a PHP array as concise, parseable code.
     *
     * A list (i.e. an array with sequential integer keys starting at 0) will show values only.
     * An associative array will show keys and values. String keys will be quoted.
     *
     * If pretty printing is enabled, the result will be formatted with new lines and indentation.
     *
     * @param array<array-key, mixed> $arr The array to encode.
     * @param bool $prettyPrint Whether to use pretty printing (default false).
     * @param int $indentLevel The level of indentation for this structure (default 0).
     * @return string The string representation of the array.
     * @throws DomainException If the array contains circular references.
     */
    public static function stringifyArray(array $arr, bool $prettyPrint = false, int $indentLevel = 0): string
    {
        // Detect circular references.
        if (Arrays::containsRecursion($arr)) {
            throw new DomainException('Cannot stringify arrays containing circular references.');
        }

        return array_is_list($arr)
            ? self::stringifyList($arr, $prettyPrint, $indentLevel)
            : self::stringifyDictionary($arr, $prettyPrint, $indentLevel);
    }

    /**
     * Stringify a list (sequential integer keys starting at 0).
     *
     * Without pretty printing, values are comma-separated on one line.
     * With pretty printing, uses single-line, grid, or one-per-line format depending on content.
     *
     * @param list<mixed> $arr The list to stringify.
     * @param bool $prettyPrint Whether to use pretty printing.
     * @param int $indentLevel The level of indentation for this structure.
     * @return string The string representation of the list.
     * @throws DomainException If a value cannot be stringified.
     */
    private static function stringifyList(array $arr, bool $prettyPrint, int $indentLevel): string
    {
        // Get the values as strings.
        $values = array_values($arr);
        $valueStrings = array_map(static fn($value) => self::stringify($value), $values);

        // Generate the compact (single-line) format. No trailing comma.
        $compactList = '[' . implode(', ', $valueStrings) . ']';

        // If we don't want pretty printing, return the compact format.
        if (!$prettyPrint) {
            return $compactList;
        }

        // Set up for multi-line pretty printing.
        $nSpacesBracketIndent = $indentLevel * self::$indent;
        $bracketIndent = str_repeat(' ', $nSpacesBracketIndent);
        $nSpacesItemIndent = $nSpacesBracketIndent + self::$indent;
        $itemIndent = str_repeat(' ', $nSpacesItemIndent);
        $nItems = count($arr);

        // Check if all items are null or scalar.
        if (array_all($values, static fn($value) => $value === null || is_scalar($value))) {
            // If the compact format fits on one line, return it.
            if (mb_strlen($compactList) <= self::$maxLineLength - $nSpacesBracketIndent) {
                return $compactList;
            }

            // Get the max item width.
            $maxValueWidth = 0;
            foreach ($valueStrings as $valueString) {
                $len = mb_strlen($valueString);
                if ($len > $maxValueWidth) {
                    $maxValueWidth = $len;
                }
            }

            // Calculate the number of items per line.
            $nItemsPerLine = (int) floor((self::$maxLineLength + 1 - $nSpacesItemIndent) / ($maxValueWidth + 2));
            if ($nItemsPerLine === 0) {
                $nItemsPerLine = 1;
            }

            // Generate the grid.
            $gridList = "[\n";
            $itemCountThisLine = 0;
            foreach ($valueStrings as $i => $valueString) {
                // Indent the first item on the line.
                if ($itemCountThisLine === 0) {
                    $gridList .= $itemIndent;
                }

                $itemCountThisLine++;
                $isLastOnRow = $itemCountThisLine === $nItemsPerLine || $i === $nItems - 1;

                if ($isLastOnRow) {
                    // Last item on row: no padding to avoid trailing whitespace.
                    $gridList .= $valueString . ",\n";
                    $itemCountThisLine = 0;
                } else {
                    // Non-last item: pad to uniform width, then space.
                    $gridList .= mb_str_pad($valueString . ',', $maxValueWidth + 1) . ' ';
                }
            }
            return $gridList . $bracketIndent . ']';
        }

        // At least one item is neither null nor a scalar. Format the list with one item per line.
        $multilineList = "[\n";
        foreach ($values as $value) {
            // Pretty print each value. Include trailing comma.
            $multilineList .= $itemIndent . self::stringify($value, true, $indentLevel + 1) . ",\n";
        }
        return $multilineList . $bracketIndent . ']';
    }

    /**
     * Stringify a dictionary (non-sequential or string keys).
     *
     * Without pretty printing, key-value pairs are comma-separated on one line.
     * With pretty printing, uses one pair per line with aligned keys.
     *
     * @param array<array-key, mixed> $arr The dictionary to stringify.
     * @param bool $prettyPrint Whether to use pretty printing.
     * @param int $indentLevel The level of indentation for this structure.
     * @return string The string representation of the associative array.
     */
    private static function stringifyDictionary(array $arr, bool $prettyPrint, int $indentLevel): string
    {
        // Get keys as strings.
        $keyStrings = [];
        foreach ($arr as $key => $value) {
            $keyStrings[] = self::stringify($key);
        }

        $values = array_values($arr);
        $nItems = count($arr);

        // Unpretty format. No newlines or extra spaces.
        if (!$prettyPrint) {
            $pairs = [];
            for ($i = 0; $i < $nItems; $i++) {
                $pairs[] = $keyStrings[$i] . ' => ' . self::stringify($values[$i]);
            }
            return '[' . implode(', ', $pairs) . ']';
        }

        // Set up for pretty printing.
        $nSpacesBracketIndent = $indentLevel * self::$indent;
        $bracketIndent = str_repeat(' ', $nSpacesBracketIndent);
        $nSpacesItemIndent = $nSpacesBracketIndent + self::$indent;
        $itemIndent = str_repeat(' ', $nSpacesItemIndent);

        // Get the maximum key width.
        $maxKeyWidth = 0;
        foreach ($keyStrings as $keyString) {
            $keyStrLen = mb_strlen($keyString);
            if ($keyStrLen > $maxKeyWidth) {
                $maxKeyWidth = $keyStrLen;
            }
        }

        // Generate the result string.
        $result = "[\n";
        for ($i = 0; $i < $nItems; $i++) {
            $result .= $itemIndent . mb_str_pad($keyStrings[$i], $maxKeyWidth) . ' => ' .
                self::stringify($values[$i], true, $indentLevel + 1) . ",\n";
        }
        return $result . $bracketIndent . ']';
    }

    /**
     * Get a string representation of an enum case in the form "Fully\Qualified\ClassName::CaseName".
     * The leading slash is removed.
     *
     * @param UnitEnum $value The enum case to stringify.
     * @return string The string representation (e.g. "UnitSystem::Financial").
     */
    public static function stringifyEnum(UnitEnum $value): string
    {
        return ltrim($value::class, '\\') . '::' . $value->name;
    }

    /**
     * Stringify an object.
     *
     * The resulting string uses a custom format.
     * - the fully qualified class name is used (i.e. with the namespace) before the opening brace
     * - property names are not quoted
     * - property-value pairs use fat arrows (=>) and are comma-separated
     * - the visibility of each property is shown using UML notation (+ for public, # for protected, - for private)
     *
     * If pretty printing is enabled, the result will be formatted with new lines and indentation.
     *
     * @param object $obj The object to encode.
     * @param bool $prettyPrint Whether to use pretty printing (default false).
     * @param int $indentLevel The level of indentation for this structure (default 0).
     * @return string The string representation of the object.
     * @throws UnexpectedValueException
     */
    public static function stringifyObject(object $obj, bool $prettyPrint = false, int $indentLevel = 0): string
    {
        // Get the object's class.
        $class = $obj::class;

        // Check for anonymous classes. We don't want null bytes in the result.
        if (str_contains($class, '@anonymous')) {
            $class = '@anonymous';
        }

        // Convert the object to an array to get its properties.
        $arr = (array) $obj;

        // Early return if no properties.
        if (count($arr) === 0) {
            return $class . ' {}';
        }

        // Generate the strings for key-value pairs. Each will be on its own line if pretty printing is enabled.
        $nSpacesBracketIndent = $indentLevel * self::$indent;
        $bracketIndent = $prettyPrint ? str_repeat(' ', $nSpacesBracketIndent) : '';
        $nSpacesItemIndent = $nSpacesBracketIndent + self::$indent;
        $itemIndent = $prettyPrint ? str_repeat(' ', $nSpacesItemIndent) : '';

        $keys = array_keys($arr);
        $values = array_values($arr);
        $propNames = [];
        $visibilitySymbols = [];
        $maxPropNameLen = 0;

        foreach ($values as $i => $value) {
            // Split the array key on null bytes to get the property name.
            $key = $keys[$i];
            $nameParts = explode("\0", $key);
            $propNames[$i] = Arrays::last($nameParts);

            // Get the property visibility symbol.
            if (count($nameParts) === 1) {
                // Property is public.
                $visibilitySymbols[$i] = '+';
            } else {
                // Property must be protected or private. If the second item in the $nameParts array is '*', the
                // property is protected; otherwise, it's private.
                $visibilitySymbols[$i] = $nameParts[1] === '*' ? '#' : '-';
            }

            // Track the maximum property name length.
            assert(is_string($propNames[$i]));
            $propNameLen = mb_strlen($propNames[$i]);
            if ($propNameLen > $maxPropNameLen) {
                $maxPropNameLen = $propNameLen;
            }
        }

        // Generate the property => value pairs.
        $pairs = [];
        foreach ($propNames as $i => $propName) {
            assert(is_string($propName));
            $paddedPropName = $prettyPrint ? mb_str_pad($propName, $maxPropNameLen) : $propName;
            $valueStr = self::stringify($values[$i], $prettyPrint, $indentLevel + 1);
            $pairs[] = $visibilitySymbols[$i] . $paddedPropName . ' => ' . $valueStr;
        }

        // If pretty print, return string formatted with new lines and indentation.
        if ($prettyPrint) {
            $result = "$class {\n";
            foreach ($pairs as $pair) {
                $result .= $itemIndent . $pair . ",\n";
            }
            return $result . $bracketIndent . '}';
        }

        // Return string without newlines or extra spaces.
        return "$class {" . implode(', ', $pairs) . '}';
    }

    /**
     * Stringify a resource.
     *
     * Result combines the result of casting the resource to string with the resource type.
     * Example: 'Resource id #15 (stream)'
     *
     * @param mixed $value The resource to stringify.
     * @return string The string representation of the resource.
     * @throws InvalidArgumentException If the value is not a resource.
     */
    public static function stringifyResource(mixed $value): string
    {
        // We can't type hint for resource, and is_resource() returns false for a closed resource.
        // So, check the debug type.
        $type = get_debug_type($value);
        if (!str_starts_with($type, 'resource (')) {
            throw new InvalidArgumentException('Value is not a resource.');
        }

        /** @var resource $value */
        return (string) $value . ' ' . substr($type, 9);
    }

    #endregion
}
