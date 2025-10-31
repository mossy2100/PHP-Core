# Types

Convenience methods for working with PHP types.

## Methods

### isNumber()

```php
public static function isNumber(mixed $value): bool
```

Check if a value is an int or float (differs from `is_numeric()` which also returns true for numeric strings).

### isUint()

```php
public static function isUint(mixed $value): bool
```

Check if a value is an unsigned integer (non-negative int).

### getBasicType()

```php
public static function getBasicType(mixed $value): string
```

Get the basic type of a value as a string: null, bool, int, float, string, array, object, resource, or unknown.

### getStringKey()

```php
public static function getStringKey(mixed $value): string
```

Convert any PHP value into a unique string suitable for use as a key in collections.

### createError()

```php
public static function createError(string $var_name, string $expected_type, mixed $value = null): TypeError
```

Create a new `TypeError` with formatted message about parameter validation failure.

### usesTrait()

```php
public static function usesTrait(object|string $obj_or_class, string $trait): bool
```

Check if an object or class uses a given trait, including trait inheritance.
