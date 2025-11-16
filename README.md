# Galaxon Core

A general purpose library containing core utility classes and methods for use by other Galaxon PHP packages.

**[License](LICENSE)** | **[Changelog](CHANGELOG.md)** | **[Documentation](docs/)**

## Description

This package provides a comprehensive set of utility classes for working with various PHP types and common operations. All utility classes are final with static methods and cannot be instantiated.

## Requirements

- PHP ^8.4

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

## Testing

Run the test suite:

```bash
composer test
```

For code coverage analysis:

```bash
composer test-coverage
```

## Contributing

Please report bugs and feature requests on [GitHub Issues](https://github.com/mossy2100/Galaxon/issues).

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
