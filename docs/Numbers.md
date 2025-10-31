# Numbers

Container for general number-related utility methods.

## Methods

### sign()

```php
public static function sign(int|float $value, bool $zeroForZero = true): int
```

Get the sign of a number. Returns 1 for positive, -1 for negative, 0 for zero (by default). If `$zeroForZero` is false, returns -1 or 1 based on the sign of zero.

### copySign()

```php
public static function copySign(int|float $num, int|float $sign_source): int|float
```

Copy the sign of one number to another. Throws `ValueError` if NaN is passed as either parameter.
