# Stringify

Static utility class for converting PHP values to readable string representations.

---

## Overview

The `Stringify` class provides an alternative to PHP's built-in functions for converting values to strings (viz.
`echo()`, `print()`, `var_dump()`, `var_export()`, `print_r()`, `json_encode()`, and `serialize()`). This is a static
utility class and cannot be instantiated.

### Key Features

- **Single-quoted strings**: Strings are wrapped in single quotes with backslash and single-quote escaping. Unicode
  characters are preserved as-is.
- **Clearer float representation**: Floats are always made distinguishable from integers by appending `.0` if no decimal
  point or `E` is present in the string (e.g., `5.0` instead of `5`). Special values (`NAN`, `INF`, `-INF`) are handled
  correctly.
- **PHP-style array formatting**: Both lists and associative arrays use square brackets (`[...]`). Lists omit keys;
  associative arrays show keys with thick arrows (`=>`).
- **Smart pretty printing**: Scalar lists use single-line, grid, or one-per-line layout depending on length. Associative
  arrays and objects align keys/property names.
- **UML-style visibility notation**: Objects use `ClassName {...}` with visibility symbols (`+` public, `#` protected,
  `-` private).
- **Enum support**: Enums are rendered as `Fully\Qualified\ClassName::CaseName`.
- **Resource formatting**: Resources show both the id (via `get_resource_id()`) and the resource type from
  `get_debug_type()`, e.g. `resource #5 (stream)`.
- **Simple string conversion**: `toString()` provides a lightweight alternative to `stringify()` for user-facing output,
  where strings should pass through unquoted and `Stringable` objects should use their own `__toString()`.

The output for scalars, strings, enums, and arrays is parseable PHP code. Object and resource output is not parseable,
but is designed for readability.

---

## Constants

| Constant                  | Value | Description                                                                               |
| ------------------------- | ----- | ----------------------------------------------------------------------------------------- |
| `DEFAULT_INDENT`          | `4`   | Default number of spaces per indentation level in pretty-printed output.                  |
| `DEFAULT_MAX_LINE_LENGTH` | `120` | Default maximum line length before pretty-printed lists wrap to grid or multiline format. |

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

- `InvalidArgumentException` - If the indent is not greater than 0.

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

Set or get the maximum line length for pretty-printed output. This controls when scalar lists wrap from single-line to
grid or one-per-line format.

**Throws:**

- `InvalidArgumentException` - If the max line length is not greater than 0.

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

### abbrev()

```php
public static function abbrev(mixed $value, int $maxLen = 30): string
```

Get a short string representation of a value, truncated to a maximum length. Uses multibyte-safe truncation. Useful for
error messages and logs where space is limited.

**Parameters:**

- `$value` (mixed) - The value to get the string representation for.
- `$maxLen` (int) - The maximum length of the result (default: `30`, minimum: `10`).

**Returns:**

- `string` - The abbreviated string representation.

**Throws:**

- `DomainException` - If the maximum length is less than 10, or if the value cannot be stringified.

**Examples:**

```php
Stringify::abbrev('hello');                           // "'hello'"
Stringify::abbrev('this is a very long string', 15); // "'this is a v..."
Stringify::abbrev([1, 2, 3, 4, 5, 6, 7], 15);       // '[1, 2, 3, 4,...'
```

### toString()

```php
public static function toString(mixed $value): string
```

Convert any value to a string, without the code-like quoting/escaping `stringify()` applies. Strings pass through as-is.
`Stringable` objects use their own `__toString()`. All other types are converted via `stringify()` (without pretty
printing).

This is a lighter-weight alternative to `stringify()` for user-facing output (e.g. `write()`/`writeln()` in
[Functions](Functions.md)), where `stringify()`'s code-like representation (quoted strings, etc.) would be unwanted.

**Parameters:**

- `$value` (mixed) - The value to convert.

**Returns:**

- `string` - The string representation.

**Examples:**

```php
Stringify::toString('hello');       // 'hello' (passed through, not quoted)
Stringify::toString(42);            // '42'
Stringify::toString(true);          // 'true'
Stringify::toString(false);         // 'false'
Stringify::toString(null);          // 'null'
Stringify::toString([1, 2, 3]);     // '[1, 2, 3]'

// Stringable objects use __toString().
$date = new DateTime('2026-03-31');
Stringify::toString($date);         // '2026-03-31 00:00:00'
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
Stringify::stringifyFloat(3.14);    // '3.14'
Stringify::stringifyFloat(5.0);     // '5.0' (ensures decimal point)
Stringify::stringifyFloat(1.5e100); // '1.5E+100'
Stringify::stringifyFloat(-0.0);    // '-0.0'
Stringify::stringifyFloat(NAN);     // 'NAN'
Stringify::stringifyFloat(INF);     // 'INF'
Stringify::stringifyFloat(-INF);    // '-INF'
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

Stringify a PHP array as concise, parseable code. Lists (sequential integer keys starting at 0) show values only.
Associative arrays show keys and values with fat arrows (`=>`).

When pretty printing is enabled, three layout strategies are used for lists of scalars:

1. **Single line** - if the result fits within the configured max line length.
2. **Grid** - items padded to equal width and arranged in columns.
3. **One per line** - for lists containing non-scalar values.

Associative arrays are always one pair per line with aligned keys when pretty printing.

The max line length is controlled by `setMaxLineLength()` (default: `120`).

**Parameters:**

- `$arr` (array) - The array to encode.
- `$prettyPrint` (bool) - Whether to use pretty printing (default: `false`).
- `$indentLevel` (int) - The level of indentation (default: `0`).

**Returns:**

- `string` - The string representation of the array.

**Throws:**

- `DomainException` - If the array contains circular references.

**Examples:**

Lists:

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
// "User {+name => 'John', #age => 30, -id => 'abc123'}"
```

Empty object:

```php
$obj = new stdClass();
Stringify::stringifyObject($obj);  // 'stdClass {}'
```

With pretty printing (property names are aligned):

```php
Stringify::stringifyObject($user, true);
// User {
//     +name => 'John',
//     +age  => 30,
//     -id   => 'abc123',
// }
```

Anonymous class:

```php
$anon = new class { public int $x = 1; };
Stringify::stringifyObject($anon);  // '@anonymous {+x => 1}'
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
- **[Functions](Functions.md)** - `write()` and `writeln()`, plain functions built on `toString()`.
