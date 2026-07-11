# FormatException

Exception thrown when a string has an invalid format for the desired operation.

---

## Overview

`FormatException` is used when a value is of the correct type (string) but has an invalid format that doesn't match an
expected pattern. This is typically thrown by parse methods or constructors that accept string arguments.

The exception extends `DomainException` to indicate that the input value is outside the domain of acceptable values for
the operation.

This can help distinguish between string format errors and other types of `DomainException` exceptions, such as a
numeric value being out of range.

---

## Class Definition

```php
namespace OceanMoon\Core\Exceptions;

class FormatException extends DomainException
```

---

## Usage Example

```php
use OceanMoon\Core\Exceptions\FormatException;

class PhoneNumber
{
    private string $number;

    public function __construct(string $number)
    {
        if (!preg_match('/^\+?[0-9]{10,15}$/', $number)) {
            throw new FormatException("Invalid phone number format: '$number'.");
        }
        $this->number = $number;
    }
}

try {
    $phone = new PhoneNumber("not-a-number");
} catch (FormatException $e) {
    echo $e->getMessage();
    // Output: Invalid phone number format: 'not-a-number'.
}
```

---

## When to Use

Throw this exception when:

- A string argument doesn't match an expected pattern or format
- Parsing a string fails due to invalid syntax
- A string representation of a value cannot be converted to the target type
- User input doesn't conform to a required format

---

## When Not to Use

Use other exceptions when:

- The value is the wrong type entirely (use `InvalidArgumentException`)
- The value is valid but outside an acceptable range (use `DomainException`)
- The value would cause an overflow or underflow (use `OverflowException` or `UnderflowException`)

---

## See Also

- **[DomainException](https://www.php.net/manual/en/class.domainexception.php)** - Parent class
- **[Integers](../Integers.md)** - Uses `FormatException` for invalid subscript/superscript strings
