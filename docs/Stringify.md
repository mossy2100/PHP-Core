# Stringify

Methods for formatting any PHP value as a string with improved readability.

## Features

- Floats never look like integers
- Arrays as lists don't show keys (JSON-style)
- Objects rendered with UML-style visibility modifiers
- Resources encoded in HTML-tag style

## Methods

### stringify()

```php
public static function stringify(mixed $value, bool $pretty_print = false, int $indent_level = 0): string
```

Convert a value to a readable string representation. Supports pretty printing with indentation.

### stringifyFloat()

```php
public static function stringifyFloat(float $value): string
```

Encode a float ensuring it doesn't look like an integer. Handles special values (NaN, ±INF).

### stringifyArray()

```php
public static function stringifyArray(array $ary, bool $pretty_print = false, int $indent_level = 0): string
```

Stringify array in JSON-like style. Lists use square brackets, associative arrays use curly brackets. Throws `ValueError` for circular references.

### stringifyResource()

```php
public static function stringifyResource(mixed $value): string
```

Stringify a resource showing type and ID. Throws `TypeError` if value is not a resource.

### stringifyObject()

```php
public static function stringifyObject(object $obj, bool $pretty_print = false, int $indent_level = 0): string
```

Stringify object with properties shown using UML visibility notation (+ public, # protected, - private).

### abbrev()

```php
public static function abbrev(mixed $value, int $max_len = 30): string
```

Get a short string representation (max 30 chars by default) for use in error/log messages.
