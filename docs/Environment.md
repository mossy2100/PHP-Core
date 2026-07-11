# Environment

Static utility class for detecting runtime environment characteristics.

---

## Overview

The `Environment` class provides methods for querying properties of the runtime environment, such as whether the system
is 64-bit. This is a static utility class and cannot be instantiated.

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

---

## See Also

- **[Floats](Floats.md)** - Uses `require64Bit()` for IEEE-754 bit operations
