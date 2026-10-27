<?php

declare(strict_types=1);

namespace Mossy2100\Core;

use BadMethodCallException;
use Closure;
use DomainException;
use ErrorException;
use InvalidArgumentException;
use ReflectionFunction;
use Throwable;
use UnexpectedValueException;
use UnitEnum;

/**
 * This class provides a method of formatting any PHP value as a string, with a few differences from the default
 * options of echo(), print(), var_dump(), var_export(), print_r(), json_encode(), and serialize().
 *
 * - Floats never look like integers.
 * - Strings are single-quoted, or double-quoted with escapes if they aren't valid UTF-8.
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
 *
 * It's for display, not collection keys: the output format may change. Use Types::getUniqueString() for keys.
 */
final class Stringify
{
    #region Properties

    /**
     * Object IDs (via spl_object_id()) currently being stringified by stringifyObject(), forming a
     * stack that mirrors the call chain of nested calls. Used to detect genuine object-to-object
     * reference cycles.
     *
     * Arrays::removeRecursion() can't see this kind of cycle, because it doesn't follow objects. Locating
     * array cycles has to lean on print_r()'s own cycle detection, because plain PHP arrays have no
     * reliable identity check from userland code. Objects do, via spl_object_id(), so a simple
     * visited-set here is enough.
     *
     * @var array<int, true>
     */
    private static array $objectsBeingStringified = [];

    #endregion

    #region Constructor

    /**
     * Private constructor to prevent instantiation.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    #endregion

    #region Main stringification methods

    /**
     * Convert a value to a readable string representation.
     *
     * @param mixed $value The value to encode.
     * @param ?StringifyOptions $options The options, e.g. StringifyOptions::pretty(). Null (default) gives compact
     *     output.
     * @return string The string representation of the value.
     * @throws UnexpectedValueException If the value has an unknown type. Defensive only: every type
     * Types::getBasicType() can currently return is handled, so this isn't reachable in practice.
     */
    public static function stringify(mixed $value, ?StringifyOptions $options = null): string
    {
        return self::stringifyAtLevel($value, $options ?? StringifyOptions::compact(), 0);
    }

    /**
     * Convert any value to a string, without errors.
     *
     * - Default string conversion is used when it works.
     * - Falls back to stringify() for values that emit warnings or errors on default string conversion (e.g. arrays,
     *   NAN, non-Stringable objects).
     *
     * @param mixed $value Whatever you want converted to a string.
     * @return string The value as a string.
     */
    public static function toString(mixed $value): string
    {
        // Temporarily convert warnings to exceptions to catch cases where the default cast would emit a warning.
        set_error_handler(static fn () => throw new ErrorException());

        try {
            return (string) $value; // @phpstan-ignore cast.string
        } catch (Throwable) {
            // Fall through to stringify() below.
        } finally {
            restore_error_handler();
        }

        // Fallback to stringify() which will handle anything else.
        return self::stringify($value);
    }

    /**
     * Get a short string representation of the given value for use in error messages, log messages, and the like.
     *
     * @param mixed $value The value to get the string representation for.
     * @param int $maxLen The maximum length of the result. The minimum value is 3.
     * @return string The short string representation.
     * @throws DomainException If the maximum length is less than the minimum.
     * @throws UnexpectedValueException If the type cannot be inferred. Defensive only - see stringify(), which this
     * method calls internally; not reachable in practice.
     */
    public static function abbrev(mixed $value, int $maxLen = 32): string
    {
        // Check the max length.
        if ($maxLen < 3) {
            throw new DomainException("Invalid maximum string length: $maxLen. Must be at least 3.");
        }

        // Get the value as a string without newlines or indentation.
        $result = self::stringify($value);

        // Get the basic type.
        $type = Types::getBasicType($value);

        // Abbreviate if necessary, but only for certain types.
        if (in_array($type, ['string', 'array', 'object'], true) && mb_strlen($result) > $maxLen) {
            $truncateAt = $maxLen - 2;

            // For objects, never truncate before the end of the class name. +1 to include the brace itself
            // (mb_strpos() gives the index of the brace, i.e. the count of characters before it).
            if ($type === 'object') {
                $bracePos = mb_strpos($result, '{');
                if ($bracePos !== false) {
                    $truncateAt = max($truncateAt, $bracePos + 1);
                }
            }

            // A string is single-quoted, or double-quoted if it isn't valid UTF-8, so close it with its own quote.
            $result = mb_substr($result, 0, $truncateAt) . '…' . match ($type) {
                'string' => $result[0],
                'array' => ']',
                'object' => '}'
            };

            if ($type === 'object') {
                // If an object is rendered as "ClassName #99 {…}" and this is still too long, trim off the braces.
                if (mb_strlen($result) > $maxLen) {
                    $result = mb_substr($result, 0, mb_strlen($result) - 4);
                }

                // If it's still too long, trim off the id.
                if (mb_strlen($result) > $maxLen) {
                    $hashPos = mb_strrpos($result, '#');
                    $result = mb_substr($result, 0, $hashPos - 1);
                }
            }
        }

        return $result;
    }

    /**
     * Prepare an exception message, substituting each '?' placeholder, left to right, with abbrev() of the
     * matching value.
     *
     * A value that itself contains '?' is inserted verbatim - it is not re-scanned for further placeholders, since
     * all splitting happens against the original $message before any substitution occurs.
     *
     * For example:
     * ```php
     * Stringify::prepEx('Invalid range: [?, ?]. Min must not exceed max.', $min, $max);
     * // "Invalid range: [-5, -10]. Min must not exceed max."
     * ```
     *
     * @param string $message The message template, with a '?' placeholder for each value.
     * @param mixed ...$values The values to substitute, one per placeholder, in order.
     * @return string The prepared message.
     * @throws BadMethodCallException If the number of values doesn't match the number of placeholders.
     */
    public static function prepEx(string $message, mixed ...$values): string
    {
        $parts = explode('?', $message);
        $placeholderCount = count($parts) - 1;

        if ($placeholderCount !== count($values)) {
            throw new BadMethodCallException(
                self::prepEx(
                    'Cannot prepare exception message due to incorrect value count: ?. Expected ?.',
                    count($values),
                    $placeholderCount
                )
            );
        }

        $result = $parts[0];
        foreach (array_values($values) as $i => $value) {
            $result .= self::abbrev($value) . $parts[$i + 1];
        }

        return $result;
    }

    #endregion

    #region Type-specific stringification methods

    /**
     * Stringify a boolean.
     *
     * @param bool $value The boolean value to encode.
     * @return string 'true' or 'false'.
     */
    public static function stringifyBool(bool $value): string
    {
        return $value ? 'true' : 'false';
    }

    /**
     * Stringify an integer.
     *
     * @param int $value The integer value to encode.
     * @return string The string representation of the integer.
     */
    public static function stringifyInt(int $value): string
    {
        return (string) $value;
    }

    /**
     * Encode a float exactly, in such a way that it doesn't look like an integer.
     *
     * Uses var_export(), which gives the shortest string that converts back to the same float (e.g. 0.1 + 0.2 gives
     * '0.30000000000000004'), and always includes a decimal point or exponent (e.g. '5.0'). A (string) cast would
     * round to the `precision` ini setting (14 significant digits by default) and drop the '.0'.
     *
     * The shortest round-trip form depends on the `serialize_precision` ini setting being -1, which is the default.
     *
     * @param float $value The float value to encode.
     * @return string The string representation of the float.
     */
    public static function stringifyFloat(float $value): string
    {
        return var_export($value, true);
    }

    /**
     * Convert a string to a parseable single-quoted string.
     *
     * A string that isn't valid UTF-8, e.g. binary data or text in another encoding, is rendered as a double-quoted
     * string instead, with escapes for the invalid bytes, so it parses back to the original bytes.
     *
     * @param string $value The string value to encode.
     * @return string The single-quoted, escaped string representation.
     */
    public static function stringifyString(string $value): string
    {
        if ($value === RECURSION) {
            return RECURSION;
        }

        // A string that isn't valid UTF-8 (e.g. binary data, or text in another encoding) is rendered with escapes. Its
        // encoding isn't guessed: single-byte encodings like ISO-8859-1 accept any bytes, so a guess can't fail, and a
        // wrong one would show the wrong characters.
        if (!mb_check_encoding($value, 'UTF-8')) {
            return self::stringifyBinaryString($value);
        }

        // Replace special characters with escape codes. The backslash must be escaped first: str_replace() with
        // parallel arrays processes each pattern in order over the whole string, so if it ran later, it would
        // also catch (and double-escape) the backslashes just inserted by other replacements.
        $search = ['\\', "'"];
        $replace = ['\\\\', "\\'"];
        return "'" . str_replace($search, $replace, $value) . "'";
    }

    /**
     * Stringify a PHP array as concise, parseable code.
     *
     * A list (i.e. an array with sequential integer keys starting at 0) will show values only.
     * An associative array will show keys and values. String keys will be quoted.
     *
     * If pretty printing is enabled, the result will be formatted with new lines and indentation.
     *
     * Any circular (self-referencing) sub-arrays are replaced with the RECURSION marker via
     * Arrays::removeRecursion() before stringifying, so a recursive array can still be stringified
     * (as much as possible) rather than failing outright.
     *
     * @param array<array-key, mixed> $arr The array to encode.
     * @param ?StringifyOptions $options The options, e.g. StringifyOptions::pretty(). Null (default) gives compact
     *     output.
     * @return string The string representation of the array.
     */
    public static function stringifyArray(array $arr, ?StringifyOptions $options = null): string
    {
        return self::stringifyArrayAtLevel($arr, $options ?? StringifyOptions::compact(), 0, false);
    }

    /**
     * Get a string representation of an enum case in the form "Fully\Qualified\ClassName::CaseName".
     *
     * @param UnitEnum $value The enum case to stringify.
     * @return string The string representation (e.g. "App\Enums\Suit::Hearts").
     */
    public static function stringifyEnum(UnitEnum $value): string
    {
        return $value::class . '::' . $value->name;
    }

    /**
     * Get a string representation of a closure, by its name.
     *
     * An anonymous closure's name, from PHP 8.4, says where it's defined, e.g.
     * '{closure:App\Models\User::sorter():19}', or '{closure:/path/to/file.php:6}' at the top level of a file. A
     * first-class callable is shown in that syntax, with the class for a method, e.g. 'strlen(...)' or
     * 'App\Models\User::sorter(...)'.
     *
     * The closure's source code isn't shown: reading it back from the file can't reliably tell apart two closures that
     * start on the same line.
     *
     * @param Closure $value The closure to stringify.
     * @return string The closure's name.
     */
    public static function stringifyClosure(Closure $value): string
    {
        $reflection = new ReflectionFunction($value);
        $name = $reflection->getName();

        // An anonymous closure's name says where it's defined.
        if (str_starts_with($name, '{closure')) {
            return $name;
        }

        // A first-class callable, e.g. strlen(...). Show it in that syntax, with the class for a method.
        $class = $reflection->getClosureScopeClass()?->getName();
        return ($class === null ? $name : "$class::$name") . '(...)';
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
     * Detects genuine object-to-object reference cycles (an object that directly or indirectly
     * contains itself) via a stack of spl_object_id()s currently being rendered, and replaces a
     * repeated object with the RECURSION marker instead of recursing forever.
     *
     * @param object $obj The object to encode.
     * @param ?StringifyOptions $options The options, e.g. StringifyOptions::pretty(). Null (default) gives compact
     *     output.
     * @return string The string representation of the object.
     */
    public static function stringifyObject(object $obj, ?StringifyOptions $options = null): string
    {
        return self::stringifyObjectAtLevel($obj, $options ?? StringifyOptions::compact(), 0);
    }

    /**
     * Stringify a resource.
     *
     * Result combines the result of casting the resource to string with the resource type.
     * Example: 'resource #15 (stream)'
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
            throw new InvalidArgumentException("Invalid value type: $type. Must be a resource.");
        }

        /** @var resource $value */
        return 'resource #' . get_resource_id($value) . substr($type, 8);
    }

    #endregion

    #region Helper methods

    /**
     * Implementation of stringify(), for a value at a given nesting depth.
     *
     * @param mixed $value The value to encode.
     * @param StringifyOptions $options The options.
     * @param int $indentLevel The nesting depth of this value (0 for the outermost).
     * @return string The string representation of the value.
     * @throws UnexpectedValueException If the value has an unknown type. Defensive only.
     */
    private static function stringifyAtLevel(mixed $value, StringifyOptions $options, int $indentLevel): string
    {
        // Call the relevant method.
        switch (Types::getBasicType($value)) {
            case 'null':
                assert($value === null);
                return 'null';

            case 'bool':
                assert(is_bool($value));
                return self::stringifyBool($value);

            case 'int':
                assert(is_int($value));
                return self::stringifyInt($value);

            case 'float':
                assert(is_float($value));
                return self::stringifyFloat($value);

            case 'string':
                assert(is_string($value));
                return self::stringifyString($value);

            case 'array':
                assert(is_array($value));
                return self::stringifyArrayAtLevel($value, $options, $indentLevel, false);

            case 'enum':
                assert($value instanceof UnitEnum);
                return self::stringifyEnum($value);

            case 'closure':
                assert($value instanceof Closure);
                return self::stringifyClosure($value);

            case 'object':
                assert(is_object($value));
                return self::stringifyObjectAtLevel($value, $options, $indentLevel);

            case 'resource':
                return self::stringifyResource($value);

            default:
                // @codeCoverageIgnoreStart
                throw new UnexpectedValueException('Value has unknown type.');
                // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Implementation of stringifyArray(), for an array at a given nesting depth.
     *
     * @param array<array-key, mixed> $arr The array to encode.
     * @param StringifyOptions $options The options.
     * @param int $indentLevel The nesting depth of this array (0 for the outermost).
     * @param bool $alreadyCleaned Whether $arr is already known to be free of circular references, because it's
     *     nested in an array that an ancestor call already passed through Arrays::removeRecursion(), which cleans the
     *     whole subtree in one pass.
     * @param int $surroundingLength The number of other characters on the line where the value starts, apart from
     *     its indent, such as a key and ' => ' before it and a comma after it.
     * @return string The string representation of the array.
     */
    private static function stringifyArrayAtLevel(
        array $arr,
        StringifyOptions $options,
        int $indentLevel,
        bool $alreadyCleaned,
        int $surroundingLength = 0
    ): string {
        // Replace any circular references with the RECURSION marker so the rest of the array can still be
        // stringified. Skipped if the caller already knows $arr is clean, to avoid redundantly re-scanning a
        // subtree that an ancestor's Arrays::removeRecursion() call already cleaned in full.
        if (!$alreadyCleaned) {
            $arr = Arrays::removeRecursion($arr);
        }

        return array_is_list($arr)
            ? self::stringifyListArray($arr, $options, $indentLevel, $surroundingLength)
            : self::stringifyAssociativeArray($arr, $options, $indentLevel);
    }

    /**
     * Implementation of stringifyObject(), for an object at a given nesting depth.
     *
     * @param object $obj The object to encode.
     * @param StringifyOptions $options The options.
     * @param int $indentLevel The nesting depth of this object (0 for the outermost).
     * @return string The string representation of the object.
     */
    private static function stringifyObjectAtLevel(object $obj, StringifyOptions $options, int $indentLevel): string
    {
        // Detect a reference cycle: this exact object is already being stringified further up the call chain.
        $objectId = spl_object_id($obj);
        if (isset(self::$objectsBeingStringified[$objectId])) {
            return RECURSION;
        }
        self::$objectsBeingStringified[$objectId] = true;

        try {
            // Get the object's class and id.
            $classWithId = get_debug_type($obj) . ' #' . $objectId;

            // Convert the object to an array to get its properties. Remove any recursion.
            $arr = Arrays::removeRecursion((array) $obj);

            // Early return if no properties.
            if (count($arr) === 0) {
                return $classWithId . ' {}';
            }

            // Generate the strings for key-value pairs. Each will be on its own line if pretty printing is enabled.
            [$nSpacesBracketIndent, $bracketIndent, $nSpacesItemIndent, $itemIndent] =
                self::prepareIndents($indentLevel, $options);

            $keys = array_keys($arr);
            $values = array_values($arr);
            $propNames = [];
            $viz = [];

            foreach ($values as $i => $value) {
                // Split the array key on null bytes to get the property name.
                $key = $keys[$i];
                $nameParts = explode("\0", $key);
                $propNames[$i] = Arrays::last($nameParts);

                // Get the property visibility symbol.
                if (count($nameParts) === 1) {
                    // Property is public.
                    $viz[$i] = '+';
                } else {
                    // Property must be protected or private. If the second item in the $nameParts array is '*', the
                    // property is protected; otherwise, it's private.
                    $viz[$i] = $nameParts[1] === '*' ? '#' : '-';
                }
            }

            // Generate the property => value pairs.
            $pairs = [];
            foreach ($propNames as $i => $propName) {
                assert(is_string($propName));
                $name = $viz[$i] . $propName;
                // The name, ' => ' and the trailing comma share the line with the value.
                $surroundingLength = mb_strlen($name) + 5;
                $valueStr = self::stringifyCleanedValue($values[$i], $options, $indentLevel + 1, $surroundingLength);
                $pairs[] = $name . ' => ' . $valueStr;
            }

            // If pretty print, return string formatted with new lines and indentation. Include trailing comma
            // after every pair (including the last), matching stringifyListArray()/stringifyAssociativeArray().
            if ($options->prettyPrint) {
                $result = "$classWithId {\n";
                foreach ($pairs as $pair) {
                    $result .= $itemIndent . $pair . ",\n";
                }
                return $result . $bracketIndent . '}';
            }

            // Return string without newlines or extra spaces.
            return "$classWithId {" . implode(', ', $pairs) . '}';
        } finally {
            unset(self::$objectsBeingStringified[$objectId]);
        }
    }

    /**
     * Render a string that isn't valid UTF-8 as a parseable double-quoted string.
     *
     * Valid UTF-8 sequences are kept as they are. Invalid bytes and control characters are escaped as \xNN, and \, "
     * and $ are escaped with a backslash, so the result parses back to the original bytes.
     *
     * @param string $value The string to render.
     * @return string The double-quoted string.
     */
    private static function stringifyBinaryString(string $value): string
    {
        $result = '';
        $n = strlen($value);
        for ($i = 0; $i < $n;) {
            // Keep a complete, valid multibyte sequence as it is. A lead byte 0xC2-0xF4 says how long it should be.
            $byte = ord($value[$i]);
            $len = $byte >= 0xC2 && $byte <= 0xF4 ? ($byte < 0xE0 ? 2 : ($byte < 0xF0 ? 3 : 4)) : 1;
            $seq = substr($value, $i, $len);
            if ($len > 1 && strlen($seq) === $len && mb_check_encoding($seq, 'UTF-8')) {
                $result .= $seq;
                $i += $len;
                continue;
            }

            // Otherwise, escape the single byte if necessary.
            $ch = $value[$i];
            $result .= match (true) {
                $ch === '\\', $ch === '"', $ch === '$' => '\\' . $ch,
                $byte < 0x20 || $byte >= 0x7F => sprintf('\\x%02X', $byte),
                default => $ch,
            };
            $i++;
        }
        return '"' . $result . '"';
    }

    /**
     * Stringify a value that is an element of an array whose own recursion has already been removed
     * via Arrays::removeRecursion(). Array values are guaranteed clean by that ancestor call, so
     * they're routed to stringifyArrayAtLevel() with $alreadyCleaned = true to skip re-scanning an
     * already-clean subtree; everything else goes through the normal stringifyAtLevel() dispatch.
     *
     * @param mixed $value The value to stringify.
     * @param StringifyOptions $options The options.
     * @param int $indentLevel The nesting depth of this value.
     * @param int $surroundingLength The number of other characters on the line where the value starts, apart from
     *     its indent, such as a key and ' => ' before it and a comma after it.
     * @return string The string representation of the value.
     */
    private static function stringifyCleanedValue(
        mixed $value,
        StringifyOptions $options,
        int $indentLevel,
        int $surroundingLength = 0
    ): string {
        return is_array($value)
            ? self::stringifyArrayAtLevel($value, $options, $indentLevel, true, $surroundingLength)
            : self::stringifyAtLevel($value, $options, $indentLevel);
    }

    /**
     * Stringify a list (sequential integer keys starting at 0).
     *
     * Without pretty printing, values are comma-separated on one line (compact format).
     * With pretty printing, a list of simple values uses the compact format if it fits on one line; otherwise, each
     * value goes on its own line.
     *
     * @param list<mixed> $arr The list to stringify.
     * @param StringifyOptions $options The options.
     * @param int $indentLevel The nesting depth of this list.
     * @param int $surroundingLength The number of other characters on the line where the value starts, apart from
     *     its indent, such as a key and ' => ' before it and a comma after it.
     * @return string The string representation of the list.
     */
    private static function stringifyListArray(
        array $arr,
        StringifyOptions $options,
        int $indentLevel,
        int $surroundingLength = 0
    ): string {
        $values = array_values($arr);

        // Without pretty printing, use the compact (single-line) format. No trailing comma.
        if (!$options->prettyPrint) {
            $valueStrings = array_map(static fn ($value) => self::stringifyCleanedValue($value, $options, 0), $values);
            return '[' . implode(', ', $valueStrings) . ']';
        }

        // Set up for multi-line pretty printing.
        [$nSpacesBracketIndent, $bracketIndent, $nSpacesItemIndent, $itemIndent] =
            self::prepareIndents($indentLevel, $options);

        // If every value is simple (null or scalar), use the compact format if the whole line fits.
        if (array_all($arr, static fn ($value) => $value === null || is_scalar($value))) {
            $valueStrings = array_map(static fn ($value) => self::stringifyCleanedValue($value, $options, 0), $values);
            $compactList = '[' . implode(', ', $valueStrings) . ']';
            if (mb_strlen($compactList) <= $options->maxLineLength - $nSpacesBracketIndent - $surroundingLength) {
                return $compactList;
            }
        }

        // Otherwise, use one value per line. Each non-simple value is stringified once, at its own indent; stringifying
        // it at another indent first would double the work at every level of nesting.
        $multilineList = "[\n";
        foreach ($values as $value) {
            // Pretty print each value. Include trailing comma.
            $multilineList .= $itemIndent . self::stringifyCleanedValue($value, $options, $indentLevel + 1, 1) . ",\n";
        }
        return $multilineList . $bracketIndent . ']';
    }

    /**
     * Stringify a dictionary (non-sequential or string keys).
     *
     * Without pretty printing, key-value pairs are comma-separated on one line.
     * With pretty printing, uses one pair per line.
     *
     * @param array<array-key, mixed> $arr The dictionary to stringify.
     * @param StringifyOptions $options The options.
     * @param int $indentLevel The nesting depth of this array.
     * @return string The string representation of the associative array.
     */
    private static function stringifyAssociativeArray(array $arr, StringifyOptions $options, int $indentLevel): string
    {
        // Get keys as strings.
        $keyStrings = [];
        foreach (array_keys($arr) as $key) {
            $keyStrings[] = self::stringify($key);
        }

        $values = array_values($arr);
        $nItems = count($arr);

        // Unpretty format. No newlines or extra spaces.
        if (!$options->prettyPrint) {
            $pairs = [];
            for ($i = 0; $i < $nItems; $i++) {
                $pairs[] = $keyStrings[$i] . ' => ' . self::stringifyCleanedValue($values[$i], $options, 0);
            }
            return '[' . implode(', ', $pairs) . ']';
        }

        // Set up for pretty printing.
        [$nSpacesBracketIndent, $bracketIndent, $nSpacesItemIndent, $itemIndent] =
            self::prepareIndents($indentLevel, $options);

        // Generate the result string.
        $result = "[\n";
        for ($i = 0; $i < $nItems; $i++) {
            // The key, ' => ' and the trailing comma share the line with the value.
            $surroundingLength = mb_strlen($keyStrings[$i]) + 5;
            $result .= $itemIndent . $keyStrings[$i] . ' => ' .
                self::stringifyCleanedValue($values[$i], $options, $indentLevel + 1, $surroundingLength) . ",\n";
        }
        return $result . $bracketIndent . ']';
    }

    /**
     * Prepare indent values for pretty printing.
     *
     * The two indent strings are empty when pretty printing is off, so callers can use them unconditionally
     * without an extra branch; the two indent widths are still computed either way.
     *
     * @param int $indentLevel The current nesting depth (0 for the outermost structure).
     * @param StringifyOptions $options The options, which give the indent and whether pretty printing is on.
     * @return array{0: int, 1: string, 2: int, 3: string} [$nSpacesBracketIndent, $bracketIndent,
     * $nSpacesItemIndent, $itemIndent]: the number of spaces to indent a closing bracket and that many spaces as
     * a string, followed by the number of spaces to indent an item and that many spaces as a string.
     */
    private static function prepareIndents(int $indentLevel, StringifyOptions $options): array
    {
        $nSpacesBracketIndent = $indentLevel * $options->indent;
        $bracketIndent = $options->prettyPrint ? str_repeat(' ', $nSpacesBracketIndent) : '';
        $nSpacesItemIndent = $nSpacesBracketIndent + $options->indent;
        $itemIndent = $options->prettyPrint ? str_repeat(' ', $nSpacesItemIndent) : '';
        return [$nSpacesBracketIndent, $bracketIndent, $nSpacesItemIndent, $itemIndent];
    }

    #endregion
}
