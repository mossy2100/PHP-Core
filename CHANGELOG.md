# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2025-01-16

### Added

- **Angle** - Class for working with angles in radians and degrees
  - `wrapRadians()`, `wrapDegrees()` - Normalize angles to standard ranges
  - `fromDegrees()`, `fromRadians()` - Factory methods
  - Conversion between radians and degrees

- **Floats** - Utility methods for floating-point operations
  - `approxEqual()` - Compare floats with epsilon tolerance
  - `sign()` - Get sign of a float (-1, 0, or 1)
  - Constants for common epsilon values

- **Integers** - Utility methods for integer operations
  - `sign()` - Get sign of an integer
  - `gcd()` - Greatest common divisor
  - `lcm()` - Least common multiple
  - `absExact()` - Absolute value with overflow detection
  - `mulExact()`, `addExact()` - Arithmetic with overflow detection

- **Numbers** - Utility methods for numeric operations
  - Common operations that work with both int and float

- **Arrays** - Utility methods for array operations

- **Stringify** - Utilities for converting values to strings
  - `value()` - Convert any PHP value to a readable string representation

- **Types** - Utility methods for type checking and manipulation
  - `isNumber()` - Check if value is int or float
  - `isUint()` - Check if value is unsigned integer
  - `getBasicType()` - Get canonical type name
  - `getUniqueString()` - Convert any value to unique string
  - `createError()` - Create TypeError with helpful message
  - `usesTrait()` - Check if class/object uses a trait
  - `getTraits()` - Get all traits used by class/interface/trait

- **Equatable** - Interface for value equality comparison
  - `equals(mixed $other): bool` - Check equality with another value

- **Comparable** - Trait providing comparison methods
  - `equals()`, `isLessThan()`, `isGreaterThan()`
  - `isLessThanOrEqual()`, `isGreaterThanOrEqual()`
  - Requires implementing class to provide `compare()` method

### Requirements
- PHP ^8.4

### Development
- PSR-12 coding standards
- PHPStan level 9 static analysis
- PHPUnit test coverage
- Comprehensive test suite with 100% code coverage
