# Floats

Container for useful float-related methods.

## Methods

### isNegativeZero()

```php
public static function isNegativeZero(float $value): bool
```

Determines if a floating-point number is negative zero (-0.0).

### isPositiveZero()

```php
public static function isPositiveZero(float $value): bool
```

Determines if a floating-point number is positive zero (+0.0).

### normalizeZero()

```php
public static function normalizeZero(float $value): float
```

Normalize negative zero to positive zero to avoid surprising results.

### isNegative()

```php
public static function isNegative(float $value): bool
```

Check if a float is negative (including -0.0 and -INF, but not NaN).

### isPositive()

```php
public static function isPositive(float $value): bool
```

Check if a float is positive (including +0.0 and INF, but not NaN).

### isSpecial()

```php
public static function isSpecial(float $value): bool
```

Check if a float is one of the special values: NaN, -0.0, +INF, -INF.

### toHex()

```php
public static function toHex(float $value): string
```

Convert a float to a unique 16-character hexadecimal string representation.
