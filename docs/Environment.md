# Environment

Static utility class for detecting runtime environment characteristics.

---

## Overview

The `Environment` class provides methods for querying properties of the runtime environment, such as whether the system
is 64-bit. This is a static utility class and cannot be instantiated.

---

## Constants

### INVARIANT_LOCALE

```php
public const string INVARIANT_LOCALE = 'en_US_POSIX';
```

The invariant locale, matching the root locale in the Unix Common Locale Data Repository (CLDR).

Use this wherever number or date formatting must be stable and locale-independent — e.g. `Floats::format()` constructs
its `NumberFormatter` with `INVARIANT_LOCALE` rather than `getLocale()`, so a request's `Accept-Language` header can't
change whether a formatted float uses `.` or `,` as its decimal separator.

---

## Methods

### is64Bit()

```php
public static function is64Bit(): bool
```

Check if the system is 64-bit.

**Returns:**

- `bool` - True if the system has 64-bit integers, false otherwise

**Examples:**

```php
// On a 64-bit system
Environment::is64Bit();  // true

// On a 32-bit system
Environment::is64Bit();  // false

// Conditional logic based on architecture
if (Environment::is64Bit()) {
    // Perform 64-bit specific operations
}
```

**Use Cases:**

- Conditional logic based on platform architecture
- Checking system capabilities before performing bit-level operations
- Displaying system information

### require64Bit()

```php
public static function require64Bit(): void
```

Require that the system is 64-bit. Throws an exception if the current system does not use 64-bit integers.

**Returns:**

- `void`

**Throws:**

- `RuntimeException` - If the system is not 64-bit

**Examples:**

```php
// On a 64-bit system - no exception
Environment::require64Bit();

// On a 32-bit system - throws RuntimeException
Environment::require64Bit();  // throws RuntimeException
```

**Use Cases:**

- Guard clause at the start of methods that require 64-bit operations
- Validating environment before performing IEEE-754 bit manipulation
- Early failure with a clear error message on unsupported systems

### getLocale()

```php
public static function getLocale(): string
```

Get the current request's locale. Auto-detects it from the HTTP `Accept-Language` header, falling back to PHP's current
default locale (`Locale::getDefault()`) when no header is present or it can't be parsed.

**Returns:**

- `string` - The detected locale, e.g. `'en_US'`, `'de_DE'`. Always a non-empty string.

**Examples:**

```php
// Browser sent 'Accept-Language: de-DE,de;q=0.9,en;q=0.8'.
Environment::getLocale(); // 'de_DE'

// No Accept-Language header present (e.g. CLI script).
Environment::getLocale(); // Falls back to Locale::getDefault().
```

**Use Cases:**

- Formatting numbers, currencies, or dates for the current visitor rather than the server's default locale
- Passing to `NumberFormatter`/`IntlDateFormatter` for locale-aware output

> For output that must stay locale-independent regardless of the visitor (e.g. a machine-readable format), use
> [`INVARIANT_LOCALE`](#invariant_locale) instead.

---

## See Also

- **[Floats](Floats.md)** - Uses `require64Bit()` for IEEE-754 bit operations, and `INVARIANT_LOCALE` for
  locale-independent number formatting in `format()`
