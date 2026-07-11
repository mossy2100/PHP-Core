# Types

Static utility class for type checking, inspection, and trait introspection.

---

## Overview

The `Types` class provides utilities for working with PHP's type system, offering enhanced type checking beyond PHP's
built-in functions, methods for generating unique string keys from any value, and comprehensive trait introspection.
This is a static utility class and cannot be instantiated.

### Key Features

- **Basic type identification**: Get canonical type names for any value
- **Unique string keys**: Convert any PHP value to a unique string for use as collection keys
- **Type comparison**: Check if two values have the same type
- **Trait introspection**: Detect trait usage including inherited and nested traits

---

## Inspection Methods

### getBasicType()

```php
public static function getBasicType(mixed $value): string
```

Get the basic type of a value as a canonical string name.

**Parameters:**

- `$value` (mixed) - The value to get the type of

**Returns:**

- `string` - One of: `null`, `bool`, `int`, `float`, `string`, `array`, `enum`, `object`, `resource`, or `unknown`

**Examples:**

```php
Types::getBasicType(null);           // "null"
Types::getBasicType(true);           // "bool"
Types::getBasicType(42);             // "int"
Types::getBasicType(3.14);           // "float"
Types::getBasicType("hello");        // "string"
Types::getBasicType([1, 2, 3]);      // "array"
Types::getBasicType(Suit::Hearts);   // "enum"
Types::getBasicType(new stdClass()); // "object"
```

**Use Case:** Getting consistent type names for logging, error messages, or switch statements.

---

## Formatting Methods

### getUniqueString()

```php
public static function getUniqueString(mixed $value): string
```

Convert any PHP value into a unique string suitable for use as a key in collections like sets or dictionaries. Every
distinct value produces a unique string.

**Parameters:**

- `$value` (mixed) - The value to convert

**Returns:**

- `string` - A unique string representation of the value

**Throws:**

- `UnexpectedValueException` - If the value has an unknown type

**String Format (by type):**

- `null`: `"n"`
- `bool`: `"b:T"` or `"b:F"`
- `int`: `"i:{number}"` (e.g., `"i:42"`)
- `float`: `"f:{hex}"` (hex representation for uniqueness)
- `string`: `"s:{length}:{content}"` (e.g., `"s:5:hello"`)
- `array`: `"a:{count}:{stringified}"` (e.g., `"a:3:[1, 2, 3]"`)
- `enum`: `"e:{ClassName}::{CaseName}"` (e.g., `"e:App\Enums\Suit::Hearts"`)
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

// Enums use class name and case name
Types::getUniqueString(Suit::Hearts);      // "e:App\Enums\Suit::Hearts"

// Objects use object ID
$obj = new stdClass();
Types::getUniqueString($obj);              // "o:1" (based on object ID)
```

**Use Case:** Implementing collections that can use any PHP value as a key, or generating unique identifiers for values.

---

## Type Checking Methods

### same()

```php
public static function same(mixed $obj1, mixed $obj2): bool
```

Check if two values have the same type using `get_debug_type()` for comparison.

**Parameters:**

- `$obj1` (mixed) - The first value to compare
- `$obj2` (mixed) - The second value to compare

**Returns:**

- `bool` - True if the types are the same, false otherwise

**Examples:**

```php
Types::same(1, 2);                    // true (both int)
Types::same(1, 1.0);                  // false (int vs float)
Types::same('hello', 'world');        // true (both string)
Types::same(new Foo(), new Foo());    // true (same class)
Types::same(new Foo(), new Bar());    // false (different classes)
```

**Use Case:** Type comparison for equality checks or conditional logic based on type matching.

---

## Trait-related Methods

### usesTrait()

```php
public static function usesTrait(object|string $objOrClass, string $trait): bool
```

Check if an object or class uses a given trait. This method detects traits used directly, inherited from parent classes,
or used by other traits (nested traits).

**Parameters:**

- `$objOrClass` (object|string) - The object or class name to inspect
- `$trait` (string) - The fully qualified trait name to check for

**Returns:**

- `bool` - Returns `true` if the object or class uses the trait, `false` otherwise

**Throws:**

- `DomainException` - If the provided class name is invalid

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
public static function getTraits(object|string $objOrClass): array
```

Get all traits used by an object, class, interface, or trait, including those inherited from parent classes and other
traits.

**Parameters:**

- `$objOrClass` (object|string) - The object or class (or interface or trait) to inspect

**Returns:**

- `string[]` - Array of fully qualified trait names

**Throws:**

- `DomainException` - If the provided class name is invalid

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

---

## Usage Examples

### Using Types with Collections

```php
use OceanMoon\Core\Types;

// Generate unique keys for a custom dictionary that accepts any value type
$dictionary = [];

$key1 = Types::getUniqueString([1, 2, 3]);
$key2 = Types::getUniqueString(new DateTime());
$key3 = Types::getUniqueString(3.14);

$dictionary[$key1] = 'array value';
$dictionary[$key2] = 'object value';
$dictionary[$key3] = 'float value';
```

### Type Validation in Functions

```php
use OceanMoon\Core\Types;

function processItems(array $items): void
{
    foreach ($items as $index => $item) {
        $type = Types::getBasicType($item);
        if ($type !== 'object') {
            throw new InvalidArgumentException("Item must be an object, $type given.");
        }
    }
}
```

### Capability Detection with Traits

```php
use OceanMoon\Core\Types;
use OceanMoon\Core\Traits\Comparison\Comparable;

function sortIfComparable(array $items): array
{
    if (empty($items)) {
        return $items;
    }

    // Check if items can be compared
    if (Types::usesTrait($items[0], Comparable::class)) {
        usort($items, fn ($a, $b) => $a->compare($b));
    }

    return $items;
}
```

---

## See Also

- **[Floats](Floats.md)** - Used internally by `getUniqueString()` for float-to-hex conversion
- **[Stringify](Stringify.md)** - Used internally by `getUniqueString()` for array stringification
