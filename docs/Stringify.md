# Stringify

Static utility class for converting PHP values to readable string representations.

---

## Overview

The `Stringify` class provides an alternative to PHP's built-in functions for converting values to strings (viz.
`echo()`, `print()`, `var_dump()`, `var_export()`, `print_r()`, `json_encode()`, and `serialize()`). This is a static
utility class and cannot be instantiated.

### Key Features

- **Single-quoted strings**: Strings are wrapped in single quotes, with backslash and single quotes escaped. Unicode
  characters are preserved as-is.
- **Clearer float representation**: Floats are always made distinguishable from integers by appending `.0` if no decimal
  point or `E` is present in the string (e.g., `5.0` instead of `5`). Special values (`NAN`, `INF`, `-INF`) are handled
  correctly.
- **PHP-style array formatting**: Both list arrays and associative arrays use square brackets (`[...]`). List arrays omit keys;
  associative arrays show keys with thick arrows (`=>`).
- **Smart pretty printing**: Simple list arrays use single-line, grid, or one-per-line layout depending on length. Associative arrays and objects align keys/property names.
- **UML-style visibility notation**: Objects use `ClassName #id {...}` with UML visibility symbols (`+` public, `#` protected,
  `-` private).
- **Enum support**: Enums are rendered as `Fully\Qualified\ClassName::CaseName`.
- **Closure support**: Closures are rendered as their original source code, obtained from the file they were
  declared in.
- **Resource formatting**: Resources show both the id (via `get_resource_id()`) and the resource type from
  `get_debug_type()`, e.g. `resource #5 (stream)`.

The output for scalars, strings, enums, arrays, and closures is parseable PHP code. Object and resource output is not parseable,
but is designed for readability.

---

## Constants

| Constant                  | Value | Description                                                                                     |
| ------------------------- | ----- | ----------------------------------------------------------------------------------------------- |
| `DEFAULT_INDENT`          | `4`   | Default number of spaces per indentation level in pretty-printed output.                        |
| `DEFAULT_MAX_LINE_LENGTH` | `120` | Default maximum line length before pretty-printed list arrays wrap to grid or multiline format. |

---

## Configuration Methods

The indent and max line length are configurable via static properties. Changes persist for the lifetime of the process
and affect all subsequent calls.

### setIndent() / getIndent()

```php
public static function setIndent(int $indent): void
public static function getIndent(): int
```

Set or get the number of spaces used for each indentation level in pretty-printed output.

**Throws:**

- `DomainException` - If the supplied indent is negative.

**Example:**

```php
Stringify::setIndent(2);
Stringify::stringify(['a' => 1, 'b' => 2], true);
// [
//   'a' => 1,
//   'b' => 2,
// ]
```

### setMaxLineLength() / getMaxLineLength()

```php
public static function setMaxLineLength(int $maxLineLength): void
public static function getMaxLineLength(): int
```

Set or get the maximum line length for pretty-printed output. This only affects when list arrays wrap from a compact, single-line format to a grid or one-value-per-line format. Other long values (strings, etc.) aren't chopped down to this length.

**Throws:**

- `DomainException` - If the max line length is not greater than 0.

**Example:**

```php
Stringify::setMaxLineLength(60);
Stringify::stringify(range(1, 20), true);
// Grid layout will wrap earlier due to shorter max line length.
```

### resetDefaults()

```php
public static function resetDefaults(): void
```

Reset both indent and max line length to their default constant values. Useful in test teardown to avoid leaking state
between tests.

**Example:**

```php
Stringify::setIndent(2);
Stringify::setMaxLineLength(80);
// ... do work ...
Stringify::resetDefaults(); // Back to 4 spaces and 120 chars.
```

---

## Main Stringification Methods

### stringify()

```php
public static function stringify(mixed $value, bool $prettyPrint = false, int $indentLevel = 0): string
```

Convert any PHP value to a readable string representation. This is the main entry point that dispatches to the
appropriate type-specific method.

**Parameters:**

- `$value` (mixed) - The value to encode.
- `$prettyPrint` (bool) - Whether to use pretty printing with indentation (default: `false`).
- `$indentLevel` (int) - The level of indentation for nested structures (default: `0`).

**Returns:**

- `string` - The string representation of the value.

**Throws:**

- `DomainException` - If the value cannot be stringified (e.g., arrays with circular references).
- `UnexpectedValueException` - If the value has an unknown type (should never happen).

**Examples:**

Basic types:

```php
Stringify::stringify(null);          // 'null'
Stringify::stringify(true);          // 'true'
Stringify::stringify(42);            // '42'
Stringify::stringify('hello');       // "'hello'"
Stringify::stringify(3.14);          // '3.14'
Stringify::stringify(5.0);           // '5.0' (not '5')
```

Arrays:

```php
Stringify::stringify([1, 2, 3]);                       // '[1, 2, 3]'
Stringify::stringify(['name' => 'John', 'age' => 30]); // "['name' => 'John', 'age' => 30]"
```

Enums:

```php
Stringify::stringify(Suit::Hearts); // 'App\Enums\Suit::Hearts'
```

Closures:

```php
Stringify::stringify(static fn (float $x): float => $x * $x); // 'static fn (float $x): float => $x * $x'
```

### abbrev()

```php
public static function abbrev(mixed $value, int $maxLen = 32): string
```

Get a short string representation of a value, truncated to a maximum length. Uses multibyte-safe truncation. Useful for
error messages and logs where space is limited.

**Parameters:**

- `$value` (mixed) - The value to get the string representation for.
- `$maxLen` (int) - The maximum length of the result (default: `32`, minimum: `3`).

**Returns:**

- `string` - The abbreviated string representation.

**Throws:**

- `DomainException` - If the maximum length is less than 3, or if the value cannot be stringified.

**Examples:**

```php
Stringify::abbrev('hello');                            // "'hello'"
Stringify::abbrev('this is a very long string', 15);   // "'this is a ve…'"
Stringify::abbrev([1, 2, 3, 4, 5, 6, 7], 15);          // '[1, 2, 3, 4, …]'
```

---

## Type-Specific Stringification Methods

### stringifyFloat()

```php
public static function stringifyFloat(float $value): string
```

Format a float value as a string, ensuring it doesn't look like an integer. Non-finite values (`NAN`, `INF`, `-INF`) are
returned as-is.

**Parameters:**

- `$value` (float) - The float value to encode.

**Returns:**

- `string` - The string representation of the float.

**Examples:**

```php
Stringify::stringifyFloat(3.14);     // '3.14'
Stringify::stringifyFloat(5.0);      // '5.0' (ensures decimal point)
Stringify::stringifyFloat(1.5e100);  // '1.5E+100'
Stringify::stringifyFloat(-0.0);     // '-0.0'
Stringify::stringifyFloat(NAN);      // 'NAN'
Stringify::stringifyFloat(INF);      // 'INF'
Stringify::stringifyFloat(-INF);     // '-INF'
```

### stringifyString()

```php
public static function stringifyString(string $value): string
```

Convert a string to a parseable single-quoted string representation. Backslashes and single quotes are escaped.
Non-UTF-8 input is converted to UTF-8. Unicode characters are preserved as-is (not escaped to `\uXXXX`).

**Parameters:**

- `$value` (string) - The string value to encode.

**Returns:**

- `string` - The single-quoted, escaped string representation.

**Throws:**

- `DomainException` - If the string is not UTF-8 and the encoding could not be detected or the string could not be converted to UTF-8.

**Examples:**

```php
Stringify::stringifyString('hello');      // "'hello'"
Stringify::stringifyString("it's");       // "'it\\'s'"
Stringify::stringifyString('foo\\bar');   // "'foo\\\\bar'"
Stringify::stringifyString('café');       // "'café'"
```

### stringifyArray()

```php
public static function stringifyArray(
    array $arr,
    bool $prettyPrint = false,
    int $indentLevel = 0,
): string
```

Stringify a PHP array as concise, parseable code. List arrays (sequential integer keys starting at 0) show values only.
Associative arrays show keys and values with fat arrows (`=>`).

When pretty printing is enabled, three layout strategies are used for list arrays of "simple" items (i.e. nulls and scalars):

1. **Single line** - if the result fits within the configured max line length.
2. **Grid** - items padded to equal width and arranged in columns.
3. **One per line** - for list arrays containing non-scalar values.

List arrays of complex items are always one value per line when pretty printing.

Associative arrays are always one pair per line with aligned keys when pretty printing.

The max line length is controlled by `setMaxLineLength()` (default: `120`).

**Parameters:**

- `$arr` (array) - The array to encode.
- `$prettyPrint` (bool) - Whether to use pretty printing (default: `false`).
- `$indentLevel` (int) - The level of indentation (default: `0`).

**Returns:**

- `string` - The string representation of the array.

**Examples:**

List arrays:

```php
Stringify::stringifyArray([1, 2, 3]);           // '[1, 2, 3]'
Stringify::stringifyArray([]);                  // '[]'
```

Associative arrays:

```php
Stringify::stringifyArray(['a' => 1, 'b' => 2]); // "['a' => 1, 'b' => 2]"
```

Pretty-printed grid (scalar list exceeding max line length):

```php
Stringify::stringifyArray(range(1, 50), true);
// [
//     1,  2,  3,  4,  5,  6,  7,  8,  9,  10,
//     11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
//     ...
// ]
```

Pretty-printed associative array (aligned keys):

```php
Stringify::stringifyArray(['name' => 'John', 'age' => 30], true);
// [
//     'name' => 'John',
//     'age'  => 30,
// ]
```

### stringifyEnum()

```php
public static function stringifyEnum(UnitEnum $value): string
```

Get a string representation of an enum case in the form `Fully\Qualified\ClassName::CaseName`. The leading backslash is
removed if present.

**Parameters:**

- `$value` (UnitEnum) - The enum case to stringify.

**Returns:**

- `string` - The string representation.

**Examples:**

```php
Stringify::stringifyEnum(Suit::Hearts);  // 'App\Enums\Suit::Hearts'
```

**Note:** `stringifyObject()` and `stringify()` automatically detect enum instances and delegate to this method.

### stringifyClosure()

```php
public static function stringifyClosure(Closure $value): string
```

Get a string representation of a closure by reading back its original source code from the file it was declared in.
The code is returned exactly as written, including its original whitespace and comments; the surrounding
assignment/statement (e.g. a trailing `;`) is not included.

**Parameters:**

- `$value` (Closure) - The closure to stringify.

**Returns:**

- `string` - The closure's original source code, or `''` if unavailable (e.g. the closure has no file, such as one
  created via `Closure::fromCallable()` wrapping an internal function, or the file has changed on disk since the
  closure was declared).

**Examples:**

```php
$sqr = static fn (float $x): float => $x * $x;
Stringify::stringifyClosure($sqr);  // 'static fn (float $x): float => $x * $x'

$cube = function (float $x): float {
    return $x ** 3;
};
Stringify::stringifyClosure($cube);
// "function (float \$x): float {\n    return \$x ** 3;\n}"
```

A closure embedded inline (e.g. passed directly as a call argument) is captured without the surrounding code:

```php
Stringify::stringifyClosure((fn ($x) => $x * 2));  // 'fn ($x) => $x * 2'
```

### stringifyObject()

```php
public static function stringifyObject(object $obj, bool $prettyPrint = false, int $indentLevel = 0): string
```

Stringify an object using a custom format with the class name, curly braces, and UML visibility symbols.

If the object is an enum, it is automatically delegated to `stringifyEnum()`.

**Parameters:**

- `$obj` (object) - The object to encode.
- `$prettyPrint` (bool) - Whether to use pretty printing (default: `false`).
- `$indentLevel` (int) - The level of indentation (default: `0`).

**Returns:**

- `string` - The string representation of the object.

**Visibility Symbols (UML notation):**

- `+` - Public property
- `#` - Protected property
- `-` - Private property

**Examples:**

Simple object:

```php
class User {
    public string $name = 'John';
    protected int $age = 30;
    private string $id = 'abc123';
}

$user = new User();
Stringify::stringifyObject($user);
// "User #1 {+name => 'John', #age => 30, -id => 'abc123'}"
```

Empty object:

```php
$obj = new stdClass();
Stringify::stringifyObject($obj);  // 'stdClass #2 {}'
```

With pretty printing, property names are aligned:

```php
Stringify::stringifyObject($user, true);
// User #1 {
//     +name => 'John',
//     +age  => 30,
//     -id   => 'abc123',
// }
```

Anonymous class:

```php
$anon = new class { public int $x = 1; };
Stringify::stringifyObject($anon);  // 'class@anonymous #3 {+x => 1}'
```

### stringifyResource()

```php
public static function stringifyResource(mixed $value): string
```

Stringify a resource. Combines the resource id (via `get_resource_id()`) with the resource type from
`get_debug_type()`. Works for both open and closed resources -- `is_resource()` returns `false` for a closed resource,
so the type is checked via `get_debug_type()` instead.

**Parameters:**

- `$value` (mixed) - The resource to stringify.

**Returns:**

- `string` - The string representation of the resource, e.g. `'resource #5 (stream)'`.

**Throws:**

- `InvalidArgumentException` - If the value is not a resource.

**Examples:**

```php
$file = fopen('php://memory', 'r');
Stringify::stringifyResource($file);  // 'resource #5 (stream)'

fclose($file);
Stringify::stringifyResource($file);  // 'resource #5 (closed)'
```

---

## See Also

- **[Types](Types.md)** - Type checking and inspection utilities.
- **[Arrays](Arrays.md)** - Array utility methods including `containsRecursion()`.
- **[Globals](Globals.md)** - `inspect()`, `ex()`, and `to_string()`, plain functions built on `Stringify` methods.
