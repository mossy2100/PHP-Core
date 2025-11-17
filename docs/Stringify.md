# Stringify

Advanced value-to-string conversion with pretty printing, supporting all PHP types with improved readability.

## Background

This class provides an alternative to PHP's built-in functions for converting values to strings (`var_dump()`, `var_export()`, `print_r()`, `json_encode()`, `serialize()`). It offers:

- **Clearer float representation**: Floats always include a decimal point (e.g., `5.0` instead of `5`)
- **JSON-like array formatting**: Lists use `[...]` without keys; associative arrays use `{...}` with keys
- **UML-style object formatting**: Objects use angle brackets `<...>` with visibility symbols (`+` public, `#` protected, `-` private)
- **Resource formatting**: Resources formatted as `(resource type: "...", id: ...)`
- **Pretty printing**: Optional indentation for nested structures

These features make the output more concise, readable, and informative for use in exception messages, logs, and debugging.

## Methods

### stringify()

```php
public static function stringify(mixed $value, bool $pretty_print = false, int $indent_level = 0): string
```

Convert any PHP value to a readable string representation.

**Parameters:**
- `$value` (mixed) - The value to encode
- `$pretty_print` (bool) - Whether to use pretty printing with indentation (default: `false`)
- `$indent_level` (int) - The level of indentation for nested structures (default: `0`)

**Returns:**
- `string` - The string representation of the value

**Throws:**
- `ValueError` - If the value cannot be stringified (e.g., arrays with circular references)
- `TypeError` - If the value has an unknown type

**Examples:**

Basic types:
```php
Stringify::stringify(null);        // null
Stringify::stringify(true);        // true
Stringify::stringify(42);          // 42
Stringify::stringify("hello");     // "hello"
Stringify::stringify(3.14);        // 3.14
Stringify::stringify(5.0);         // 5.0 (not 5)
```

Arrays:
```php
Stringify::stringify([1, 2, 3]);                    // [1, 2, 3]
Stringify::stringify(["name" => "John", "age" => 30]); // {"name": "John", "age": 30}
```

With pretty printing:
```php
Stringify::stringify([1, 2, 3], true);
// Output:
// [
//     1,
//     2,
//     3
// ]
```

### stringifyFloat()

```php
public static function stringifyFloat(float $value): string
```

Encode a float value ensuring it doesn't look like an integer.

**Parameters:**
- `$value` (float) - The float value to encode

**Returns:**
- `string` - The string representation of the float

**Examples:**

```php
Stringify::stringifyFloat(3.14);   // 3.14
Stringify::stringifyFloat(5.0);    // 5.0 (ensures decimal point)
Stringify::stringifyFloat(NAN);    // NaN
Stringify::stringifyFloat(INF);    // ∞
Stringify::stringifyFloat(-INF);   // -∞
```

**Use Case:** When you need to distinguish floats from integers in output, especially for debugging or logging.

### stringifyArray()

```php
public static function stringifyArray(array $ary, bool $pretty_print = false, int $indent_level = 0): string
```

Stringify an array in JSON-like style. Lists (sequential integer keys starting at 0) use square brackets and show values only. Associative arrays use curly brackets and show keys and values.

**Parameters:**
- `$ary` (array) - The array to encode
- `$pretty_print` (bool) - Whether to use pretty printing (default: `false`)
- `$indent_level` (int) - The level of indentation (default: `0`)

**Returns:**
- `string` - The string representation of the array

**Throws:**
- `ValueError` - If the array contains circular references

**Examples:**

Lists (no keys shown):
```php
Stringify::stringifyArray([1, 2, 3]);           // [1, 2, 3]
Stringify::stringifyArray([]);                  // []
```

Associative arrays (keys shown):
```php
Stringify::stringifyArray(["a" => 1, "b" => 2]); // {"a": 1, "b": 2}
Stringify::stringifyArray([1 => "a", 5 => "b"]); // {1: "a", 5: "b"}
```

Nested structures:
```php
Stringify::stringifyArray([[1, 2], [3, 4]]);    // [[1, 2], [3, 4]]
```

### stringifyResource()

```php
public static function stringifyResource(mixed $value): string
```

Stringify a resource, showing its type and ID.

**Parameters:**
- `$value` (mixed) - The resource to stringify

**Returns:**
- `string` - The string representation of the resource

**Throws:**
- `TypeError` - If the value is not a resource

**Examples:**

```php
$file = fopen('php://memory', 'r');
Stringify::stringifyResource($file);  // (resource type: "stream", id: 123)
```

### stringifyObject()

```php
public static function stringifyObject(object $obj, bool $pretty_print = false, int $indent_level = 0): string
```

Stringify an object with properties shown using UML visibility notation.

**Parameters:**
- `$obj` (object) - The object to encode
- `$pretty_print` (bool) - Whether to use pretty printing (default: `false`)
- `$indent_level` (int) - The level of indentation (default: `0`)

**Returns:**
- `string` - The string representation of the object

**Throws:**
- `TypeError` - If the object's class is anonymous (shown as `@anonymous`)

**Visibility Symbols (UML notation):**
- `+` - Public property
- `#` - Protected property
- `-` - Private property

**Examples:**

Simple object:
```php
class User {
    public string $name = "John";
    protected int $age = 30;
    private string $id = "abc123";
}

$user = new User();
Stringify::stringifyObject($user);
// <User +name: "John", #age: 30, -id: "abc123">
```

Empty object:
```php
$obj = new stdClass();
Stringify::stringifyObject($obj);  // <stdClass>
```

With pretty printing:
```php
Stringify::stringifyObject($user, true);
// Output:
// <User
//     +name: "John",
//     #age: 30,
//     -id: "abc123"
// >
```

### abbrev()

```php
public static function abbrev(mixed $value, int $max_len = 30): string
```

Get a short string representation of a value, truncated to a maximum length. Useful for error messages and logs where space is limited.

**Parameters:**
- `$value` (mixed) - The value to get the string representation for
- `$max_len` (int) - The maximum length of the result (default: `30`, minimum: `10`)

**Returns:**
- `string` - The abbreviated string representation

**Throws:**
- `ValueError` - If the maximum length is less than 10
- `TypeError` - If the value has an unknown type

**Examples:**

```php
Stringify::abbrev("hello");                    // "hello"
Stringify::abbrev("this is a very long string", 15); // "this is a...
Stringify::abbrev([1, 2, 3, 4, 5, 6, 7], 15);  // [1, 2, 3, 4,...
```

**Use Case:** When you need to include value information in error messages but want to avoid extremely long output.
