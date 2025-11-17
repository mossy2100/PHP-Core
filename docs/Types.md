# Types

Type checking and inspection utilities with methods for identifying numbers, unsigned integers, traits, and generating unique string keys.

## Background

This class provides utilities for working with PHP's type system, offering enhanced type checking beyond PHP's built-in functions and methods for trait introspection and value hashing.

## Methods

### isNumber()

```php
public static function isNumber(mixed $value): bool
```

Check if a value is a number (int or float). This differs from PHP's `is_numeric()` function, which also returns `true` for numeric strings.

**Parameters:**
- `$value` (mixed) - The value to check

**Returns:**
- `bool` - Returns `true` if the value is an int or float, `false` otherwise

**Examples:**

```php
Types::isNumber(42);         // true
Types::isNumber(3.14);       // true
Types::isNumber(INF);        // true
Types::isNumber(NAN);        // true
Types::isNumber("42");       // false (numeric string)
Types::isNumber("3.14");     // false (numeric string)
Types::isNumber(true);       // false
Types::isNumber(null);       // false
```

**Use Case:** When you need strict type checking that distinguishes actual numbers from numeric strings.

### isUint()

```php
public static function isUint(mixed $value): bool
```

Check if a value is an unsigned integer (non-negative integer).

**Parameters:**
- `$value` (mixed) - The value to check

**Returns:**
- `bool` - Returns `true` if the value is an integer >= 0, `false` otherwise

**Examples:**

```php
Types::isUint(0);            // true
Types::isUint(42);           // true
Types::isUint(-1);           // false
Types::isUint(3.14);         // false (float, not int)
Types::isUint("42");         // false (string)
```

**Use Case:** For validating array indices, database record IDs, counts, or other values that must be non-negative integers.

### getBasicType()

```php
public static function getBasicType(mixed $value): string
```

Get the basic type of a value as a canonical string name.

**Parameters:**
- `$value` (mixed) - The value to get the type of

**Returns:**
- `string` - One of: `null`, `bool`, `int`, `float`, `string`, `array`, `object`, `resource`, or `unknown`

**Examples:**

```php
Types::getBasicType(null);           // "null"
Types::getBasicType(true);           // "bool"
Types::getBasicType(42);             // "int"
Types::getBasicType(3.14);           // "float"
Types::getBasicType("hello");        // "string"
Types::getBasicType([1, 2, 3]);      // "array"
Types::getBasicType(new stdClass()); // "object"
```

**Use Case:** Getting consistent type names for logging, error messages, or switch statements.

### getUniqueString()

```php
public static function getUniqueString(mixed $value): string
```

Convert any PHP value into a unique string suitable for use as a key in collections like sets or dictionaries. Every distinct value produces a unique string.

**Parameters:**
- `$value` (mixed) - The value to convert

**Returns:**
- `string` - A unique string representation of the value

**String Format (by type):**
- `null`: `"n"`
- `bool`: `"b:T"` or `"b:F"`
- `int`: `"i:{number}"` (e.g., `"i:42"`)
- `float`: `"f:{hex}"` (hex representation for uniqueness)
- `string`: `"s:{length}:{content}"` (e.g., `"s:5:hello"`)
- `array`: `"a:{count}:{stringified}"` (e.g., `"a:3:[1, 2, 3]"`)
- `object`: `"o:{object_id}"` (e.g., `"o:1"`)
- `resource`: `"r:{resource_id}"` (e.g., `"r:5"`)

**Examples:**

```php
Types::getUniqueString(null);              // "n"
Types::getUniqueString(true);              // "b:T"
Types::getUniqueString(false);             // "b:F"
Types::getUniqueString(42);                // "i:42"
Types::getUniqueString(3.14);              // "f:..." (hex representation)
Types::getUniqueString("hello");           // "s:5:hello"
Types::getUniqueString([1, 2, 3]);         // "a:3:[1, 2, 3]"

// Distinguishes between -0.0 and +0.0
Types::getUniqueString(0.0);               // "f:0000000000000000"
Types::getUniqueString(-0.0);              // "f:8000000000000000" (different!)

// Objects use object ID
$obj = new stdClass();
Types::getUniqueString($obj);              // "o:1" (based on object ID)
```

**Use Case:** Implementing collections that can use any PHP value as a key, or generating unique identifiers for values.

### createError()

```php
public static function createError(string $var_name, string $expected_type, mixed $value = null): TypeError
```

Create a new `TypeError` with a formatted message about parameter validation failure.

**Parameters:**
- `$var_name` (string) - The name of the variable or parameter (e.g., `"index"`)
- `$expected_type` (string) - The expected type (e.g., `"int"`, `"string"`, `"callable"`)
- `$value` (mixed) - The actual value that was provided (optional)

**Returns:**
- `TypeError` - A new TypeError exception with formatted message

**Examples:**

Without actual value:
```php
$error = Types::createError('index', 'int');
// Message: "Variable 'index' must be of type int."
throw $error;
```

With actual value:
```php
$error = Types::createError('index', 'int', 'hello');
// Message: "Variable 'index' must be of type int, string given."
throw $error;
```

**Use Case:** Creating consistent, informative error messages for type validation failures.

### usesTrait()

```php
public static function usesTrait(object|string $obj_or_class, string $trait): bool
```

Check if an object or class uses a given trait. This method detects traits used directly, inherited from parent classes, or used by other traits (nested traits).

**Parameters:**
- `$obj_or_class` (object|string) - The object or class name to inspect
- `$trait` (string) - The fully qualified trait name to check for

**Returns:**
- `bool` - Returns `true` if the object or class uses the trait, `false` otherwise

**Throws:**
- `ValueError` - If the provided class name is invalid

**Examples:**

```php
trait LoggerTrait {
    public function log($msg) { /* ... */ }
}

class MyClass {
    use LoggerTrait;
}

// With class name
Types::usesTrait(MyClass::class, LoggerTrait::class);  // true

// With object instance
$obj = new MyClass();
Types::usesTrait($obj, LoggerTrait::class);            // true

// Trait not used
Types::usesTrait(stdClass::class, LoggerTrait::class); // false
```

Inherited traits:
```php
class ParentClass {
    use LoggerTrait;
}

class ChildClass extends ParentClass {
}

Types::usesTrait(ChildClass::class, LoggerTrait::class); // true (inherited)
```

**Use Case:** Runtime trait detection for capability checking or conditional logic based on trait usage.

### getTraits()

```php
public static function getTraits(object|string $obj_or_class): array
```

Get all traits used by an object, class, interface, or trait, including those inherited from parent classes and other traits.

**Parameters:**
- `$obj_or_class` (object|string) - The object or class (or interface or trait) to inspect

**Returns:**
- `string[]` - Array of fully qualified trait names

**Throws:**
- `ValueError` - If the provided class name is invalid

**Examples:**

```php
trait TraitA {
}

trait TraitB {
    use TraitA;
}

class MyClass {
    use TraitB;
}

// Returns all traits including nested ones
Types::getTraits(MyClass::class);  // ['TraitB', 'TraitA']

// With objects
$obj = new MyClass();
Types::getTraits($obj);            // ['TraitB', 'TraitA']

// No traits
Types::getTraits(stdClass::class); // []
```

Works with interfaces and traits:
```php
trait TraitC {
    use TraitA;
}

Types::getTraits(TraitC::class);   // ['TraitA']
```

**Use Case:** Introspection, reflection, dependency analysis, or documentation generation.
