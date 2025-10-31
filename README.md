# Galaxon Core

A general purpose library containing core utility classes and methods for use by other Galaxon PHP packages.

## Description

This package provides a comprehensive set of utility classes for working with various PHP types and common operations. All utility classes are final with static methods and cannot be instantiated.

## Requirements

- PHP ^8.4
- ext-ctype
- ext-mbstring

## Installation

```bash
composer require galaxon/core
```

## Classes

### [Numbers](docs/Numbers.md)

General number-related utility methods including sign operations.

### [Floats](docs/Floats.md)

Float-specific utilities for handling special values like -0.0, ±INF, NaN, and hexadecimal conversion.

### [Integers](docs/Integers.md)

Integer arithmetic operations with overflow checking (add, subtract, multiply, power) and GCD calculation.

### [Arrays](docs/Arrays.md)

Array utility methods including circular reference detection.

### [Types](docs/Types.md)

Type checking and inspection utilities with methods for identifying numbers, unsigned integers, traits, and generating unique string keys.

### [Stringify](docs/Stringify.md)

Advanced value-to-string conversion with pretty printing, supporting all PHP types with improved readability.

### [Angle](docs/Angle.md)

Immutable angle class with support for multiple units (radians, degrees, gradians, turns, DMS), trigonometry, and arithmetic operations.

## License

MIT
