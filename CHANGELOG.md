# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.0.0] - 2026-06-18

### Changed

- **Renamed package** from `galaxon/core` to `oceanmoon/core` — update your `composer.json` require accordingly.
- **Renamed PHP namespaces** from `Galaxon\Core\*` to `OceanMoon\Core\*` throughout all source and test files.
- Updated dev dependency `galaxon/coding-standard` → `oceanmoon/coding-standard: ^2.0`.
- `composer.json`: updated author email, homepage, and support URLs to Ocean Moon Software.

---

## [1.6.0] - 2026-04-09

### Added

- **Floats::format()** — New method for formatting floats as strings with control over precision, notation, and trailing zeros. Supports Unicode scientific notation (e.g. `1.50×10³`) as well as ASCII. Moved from `Quantity::formatValue()`. When `$precision` is `null`, defaults to 6 for `e`/`E`/`f`/`F` and 7 for `g`/`G`/`h`/`H` so that `g` is genuinely "the shorter of `e` and `f` at matching precision". Format string is always explicit `%.Nspec`.
- **Traits reorganisation** — Traits split into `Traits/Asserts/` (testing assertion traits) and `Traits/Comparison/` (value comparison traits).

### Changed

- **Integers::fromSubscript() / fromSuperscript()** — Now throw `FormatException` instead of `DomainException` when the input contains characters that are not valid sub/superscript digits. Invalid input is a parsing/format problem, not a domain-of-values problem.

### Removed

- **NullArgumentException** — Removed before its first release. The downstream Quantities use case that motivated it ended up not needing it, and no other consumers materialised. Never shipped, so not a breaking change.

### Fixed

- **Floats::frac()** — Returns `0.0` for ±INF instead of `NAN`, so the identity `x = trunc(x) + frac(x)` now holds for all values.

---

## [1.5.1] - 2026-04-02

### Added

- **Strings** — New static utility class for string conversion and output:
  - `toString()` — Convert any value to a string. Strings pass through, Stringable objects use `__toString()`, all other types go through `Stringify::stringify()`.
  - `print()` — Print a value to stdout via `toString()`.
  - `println()` — Print a value to stdout with a newline via `toString()`.
- **docs/Strings.md** — Documentation for the new Strings class.
- **StringsTest** — 12 tests covering toString, print, and println.

### Changed

- **functions.php** — `println()` now delegates to `Strings::println()`.

### Removed

- **Stringify::print()** and **Stringify::println()** — replaced by `Strings::print()` and `Strings::println()`.

### Documentation

- Added Strings to README Classes section.
- Removed print/println from Stringify.md.

---

## [1.5.0] - 2026-03-30

### Added

- **functions.php** - New namespaced convenience functions with `files` autoload entry:
  - `println()` - Print a value with a newline. Strings output as-is, `Stringable` objects use `__toString()`, all other types go through `Stringify::stringify()`.
- **Numbers::isNumber()** - Strict type check for `int` or `float` (rejects numeric strings unlike `is_numeric()`).
- **Numbers::isZero()** - Check if a number is zero (`0`, `0.0`, or `-0.0`).
- **Types::getBasicType()** - Now returns `'enum'` for `UnitEnum` instances (previously returned `'object'`).
- **Types::getUniqueString()** - Now supports enums with format `"e:{ClassName}::{CaseName}"`.

### Changed

- **Stringify** - Indent and max line length are now configurable:
  - `setIndent()` / `getIndent()` - Configure spaces per indentation level.
  - `setMaxLineLength()` / `getMaxLineLength()` - Configure pretty-print line wrapping.
  - `resetDefaults()` - Reset both to defaults.
  - Renamed constant `NUM_SPACES_INDENT` → `DEFAULT_INDENT`.
  - Skip grid format when items are too wide for 2 per line.
- **Floats::floatToBits()** - Simplified from byte-array loop to single `pack`/`unpack` call.
- **Floats::bitsToFloat()** - Same simplification.
- **Exception messages** - Standardised across all classes to follow the guidelines in `EXCEPTIONS.md`: "state what went wrong" format with offending values and concise constraints where useful.
- **composer.json** - Updated `galaxon/coding-standard` dependency to `^1.0`.

### Fixed

- **Floats::tryConvertToInt()** - Fixed overflow bug where floats near `PHP_INT_MAX` could silently overflow to `PHP_INT_MIN` during `(int)` cast. Now detects and returns `null`.

### Removed

- **`dump()`** function removed from `functions.php`.
- **`_dev/`** directory removed from version control.

### Documentation

- **Functions.md** - New documentation page for `println()` and `is_number()`, including autoloading setup.
- **README.md** - Added Functions section linking to new docs.
- **Types.md** - Added `enum` to `getBasicType()` return values and examples. Added `enum` format to `getUniqueString()`. Fixed `throws` annotation (`TypeError` → `UnexpectedValueException`).
- **Floats.md** - Fixed `bitsToFloat()` examples that used overflowing hex literals. Added note about PHP signed int overflow.
- **Stringify.md** - Updated for configurable indent/max line length, new constant name.

### Tests

- **FunctionsTest** - Tests for `println()` (strings, ints, floats, booleans, null, empty, stringable objects).
- **NumbersTest** - Tests for `isNumber()` and `isZero()`.
- **FloatsBitOperationsTest** - 15 new tests for `floatToBits()` and `bitsToFloat()` covering known bit patterns, special values (±0, ±INF, NAN), and round-trip verification.
- **TypesTest** - Added `testGetBasicTypeEnum` (unit and backed enums) and `testGetStringKeyEnum` (unique string format, different cases, different enum classes).
- **StringifyTest** - Additional tests for configurable indent and max line length.

---

## [1.4.0] - 2026-02-25

### Changed

- **Stringify** - Major overhaul of output formatting:
  - Strings now use single quotes with backslash and single-quote escaping instead of `json_encode()` double quotes. Unicode characters are preserved as-is.
  - Arrays use PHP square bracket syntax for both lists and associative arrays (`['key' => 'value']`) instead of JSON-style formatting with curly braces and colons.
  - Objects use `ClassName {+prop => 'value'}` format instead of `<ClassName +prop: "value">`.
  - Resources use `get_debug_type()` output (e.g. `resource (stream)`, `resource (closed)`) instead of custom format with type and id. Closed resources are now supported.
  - `null`, `bool`, and `int` are now rendered inline instead of via `json_encode()`.
  - `echo()` renamed to `print()` and now accepts a `$prettyPrint` parameter.
  - `stringifyFloat()` uses `(string)` cast instead of `sprintf('%.16H')`, with early return for non-finite values. Suppresses PHP 8.5 NAN cast warning.
  - `abbrev()` now uses `mb_strlen()`/`mb_substr()` for multibyte-safe truncation.
  - `stringifyObject()` now detects enums via `instanceof UnitEnum` and delegates to `stringifyEnum()`. Property names are aligned in pretty-print mode.
  - `stringifyArray()` now supports three pretty-print layout strategies for scalar lists: single-line, grid, and one-per-line. Associative arrays align keys. New `$maxLineLen` parameter.

### Added

- **Stringify** - New methods:
  - `stringifyString()` - Single-quoted output with backslash/single-quote escaping and UTF-8 normalisation.
  - `stringifyEnum()` - Renders enums as `Fully\Qualified\ClassName::CaseName`.
  - `println()` - Like `print()` but appends a newline.
  - Constants `NUM_SPACES_INDENT` (4) and `DEFAULT_MAX_LINE_LENGTH` (120).

- **Arrays** - New methods:
  - `toSerialList()` - Convert an array of strings to a serial list with Oxford comma (e.g. `'a, b, and c'`). Supports custom conjunctions.
  - `removeValue()` - Remove all instances of a value from an array using strict comparison. Keys are preserved.

### Fixed

- **Floats::toHex()** - Added missing `@throws RuntimeException` to docblock for 64-bit requirement.

### Documentation

- **Stringify.md** - Completely rewritten for new output format, new methods, and updated examples.
- **Arrays.md** - Added documentation for `toSerialList()` and `removeValue()`. Updated overview to include String Methods and Transformation Methods sections.

### Tests

- **StringifyTest** - Completely rewritten (36 tests, 100+ assertions) covering single-quoted strings, escaping, UTF-8 conversion, undetectable encoding, open/closed resources, enums, grid layout, and all other formatting changes.
- **ArraysTest** - Added 15 tests for `toSerialList()` (empty, one/two/three/four items, custom conjunction, non-string validation) and `removeValue()` (existing/missing values, multiple instances, key preservation, strict comparison, null removal).

---

## [1.3.0] - 2026-01-30

### Added

- **FloatAssertions trait** - PHPUnit assertions for approximate floating-point comparison
  - `assertApproxEqual()` - Assert two floats are approximately equal with informative failure messages
  - `assertApproxZero()` - Assert a float is approximately zero
  - Produces detailed failure messages showing expected/actual values and differences
  - Replaces uninformative `assertTrue(Floats::approxEqual(...))` pattern

### Documentation

- **FloatAssertions.md** - New documentation for the FloatAssertions trait
- **Traits.md** - Added "Testing Trait" section for FloatAssertions
- **README.md** - Added FloatAssertions to the Traits section

### Tests

- Added 12 tests for FloatAssertions trait covering passing/failing assertions, error messages, infinity handling, and assertApproxZero

---

## [1.2.0] - 2026-01-27

### Added

- **Arrays class** - New extraction methods (PHP 8.5 polyfills):
  - `first()` - Get the first value in an array, throws `LengthException` if empty
  - `last()` - Get the last value in an array, throws `LengthException` if empty

- **FormatException** - New exception class for string format validation errors
  - Extends `DomainException`
  - For use when a string has an invalid format for the desired operation

### Fixed

- **Floats::normalizeZero()** - Simplified logic using `=== 0.0` comparison
- **Floats::tryConvertToInt()** - Added bounds check for `PHP_INT_MIN`/`PHP_INT_MAX` before conversion to prevent overflow
- **Stringify::stringify()** - Added error suppression for non-finite float string conversion

### Tests

- Added 11 tests for `Arrays::first()` and `Arrays::last()`
- Added 18 tests for `Integers::isSubscript()`, `isSuperscript()`, `fromSubscript()`, `fromSuperscript()`
- Added region comments to ArraysTest and IntegersTest for better organization

### Documentation

- **Arrays.md** - Added "Extraction Methods" section with `first()` and `last()` documentation
- **Integers.md** - Renamed "Conversion Methods" to "Subscript/Superscript Methods", added documentation for validation and parsing methods

---

## [1.1.0] - 2026-01-18

### Added

- **Floats class** - New methods for integer/fractional part operations:
  - `isApproxInt()` - Check if a float is approximately an integer within tolerance
  - `trunc()` - Truncate a float towards zero (integer part)
  - `frac()` - Get the fractional part of a float (satisfies x = trunc(x) + frac(x))

- **Integers class** - New methods for Unicode sub/superscript conversion:
  - `isSubscript()` - Check if a string is a valid subscript integer
  - `isSuperscript()` - Check if a string is a valid superscript integer
  - `fromSubscript()` - Convert subscript string to integer (e.g., ₁₂₃ → 123)
  - `fromSuperscript()` - Convert superscript string to integer (e.g., ¹²³ → 123)

- **Numbers class**:
  - `REGEX` constant - Regular expression pattern for matching numbers

### Documentation

- **Floats.md** - Added documentation for `isApproxInt()`, `trunc()`, and `frac()`
- **Trait documentation** - Updated to use `IncomparableTypesException`
- Minor documentation updates to Arrays.md and IncomparableTypesException.md

### Tests

- Added 7 tests for `isApproxInt()` covering exact integers, near-integers, fractions, logarithms, custom tolerance, and non-finite values
- Added 6 tests for `trunc()` and `frac()` covering positive/negative values, zero, non-finite values, and the identity property

---

## [1.0.1] - 2026-01-05

### Added

- **IncomparableTypesException** - Documentation and tests for the custom exception
  - Added PHPDoc comments to the exception class
  - Added `docs/Exceptions/IncomparableTypesException.md`
  - Added `tests/Exceptions/IncomparableTypesExceptionTest.php` (10 tests)
  - Added Exceptions section to README.md

### Fixed

- Fixed GitHub URLs in README.md (`PHP-Core` → `Galaxon-PHP-Core`)
- Removed `Types::createError()` from documentation (method was removed)

---

## [1.0.0] - 2026-01-04

### First Stable Release

This is the first stable release of Galaxon Core, ready for publication on Packagist.

### Breaking Changes

- **Exception types standardized** - All domain validation errors now throw `DomainException` consistently:
  - `Floats::approxEqual()` - Throws `DomainException` for negative tolerances (was `ValueError`)
  - `Floats::approxCompare()` - Throws `DomainException` for NAN or negative tolerances (was `ValueError`)
  - `Floats::rand()` - Throws `DomainException` for non-finite min/max (was `ValueError`)
  - `Floats::randUniform()` - Throws `DomainException` for non-finite min/max (was `ValueError`)
  - `Floats::assemble()` - Throws `DomainException` for invalid components (was `ValueError`)
  - `Integers::pow()` - Throws `DomainException` for negative exponents (was `UnderflowException`)
  - `Integers::gcd()` - Throws `DomainException` for `PHP_INT_MIN` (was `RangeException`)
  - `Numbers::copySign()` - Throws `DomainException` for NAN (was `ValueError`)
  - `Stringify::stringify()` - Throws `DomainException` for circular references (was `ValueError`)
  - `Stringify::stringifyArray()` - Throws `DomainException` for circular references (was `ValueError`)
  - `Stringify::abbrev()` - Throws `DomainException` for maxLen < 10 (was `ValueError`)
  - `Types::usesTrait()` - Throws `DomainException` for invalid class name (was `ValueError`)
  - `Types::getTraits()` - Throws `DomainException` for invalid class name (was `ValueError`)

### Changed

- **composer.json** - Updated for Packagist publication:
  - Added keywords for discoverability
  - Added author information
  - Added homepage and support URLs
  - Improved description

### Documentation

- Updated all class documentation to reflect new exception types

---

## [0.6.0] - 2025-12-27

### Added

- **Environment class** - New utility class for runtime environment detection
  - `is64Bit()` - Check if the system is 64-bit
  - `require64Bit()` - Throw `RuntimeException` if not 64-bit (used by Floats bit operations)

- **Integers formatting methods** - Convert integers to Unicode sub/superscript
  - `SUBSCRIPT_CHARACTERS` constant - Unicode subscript character mappings
  - `SUPERSCRIPT_CHARACTERS` constant - Unicode superscript character mappings
  - `toSubscript()` - Convert integer to subscript characters (e.g., 123 → ₁₂₃)
  - `toSuperscript()` - Convert integer to superscript characters (e.g., 123 → ¹²³)

### Changed

- **Floats::ulp()** - Now uses `next()` for exact ULP calculation instead of approximation
- **Integers::pow()** - Now throws `UnderflowException` instead of `ValueError` for negative exponents
- **Floats class** - Reorganized with region markers for better code navigation

### Tests

- **Floats::wrap() tests** - Expanded from 1 test with 4 assertions to 9 tests with 57 assertions
  - Added tests for degrees (360) with signed and unsigned ranges
  - Added boundary condition tests (included/excluded bounds)
  - Added tests for radians (default), gradians, turns, and hours
  - Fixed deprecated `assertEquals` with delta to use `assertEqualsWithDelta`

### Documentation

- **New documentation**: `docs/Environment.md`
- **Floats.md** - Comprehensive rewrite of `wrap()` documentation
  - Added Returns, Behavior, and Use Cases sections
  - Added examples with degrees, radians, gradians, turns, and hours
  - Added table explaining boundary inclusion/exclusion rules
- **Integers.md** - Added documentation for formatting methods
- **Various docs** - Minor updates to Numbers.md, Stringify.md, Types.md, and trait documentation

---

## [0.5.0] - 2025-12-10

### Breaking Changes

- **`Types::haveSameType()` renamed to `Types::same()`**
  - Shorter, cleaner name for checking if two values have the same type
  - Update any code using `Types::haveSameType($a, $b)` to `Types::same($a, $b)`

- **`Comparable::checkSameType()` removed**
  - Type checking is now the responsibility of the `compare()` implementation
  - Classes using this trait should handle type checking within their `compare()` method

### Changed

- **`Numbers::equal()`** - Improved int/float comparison logic
  - Now uses `Types::same()` for same-type comparison
  - Uses `Floats::tryConvertToInt()` for lossless cross-type comparison
  - More accurate than previous float casting approach

- **`Floats::compare()`** - Simplified implementation to single expression

- **Comparable trait** - Streamlined comparison methods
  - `greaterThan()` and `greaterThanOrEqual()` now rely on `compare()` for type checking
  - Reduces redundant type checks

- **ApproxComparable trait** - Removed redundant type check from `approxCompare()`

### Documentation

- Updated trait documentation to reflect new type checking approach
- README.md minor fixes

---

## [0.4.0] - 2025-02-08

### Breaking Changes

- **Equatable converted from interface to trait**
  - Changed from `interface Equatable` to `trait Equatable`
  - Classes must now use `use Equatable;` instead of `implements Equatable`
  - Provides better composition with other comparison traits
  - Namespace unchanged: `Galaxon\Core\Traits\Equatable`

- **Comparable namespace changed and converted to abstract trait**
  - Moved from `Galaxon\Core\Comparable` to `Galaxon\Core\Traits\Comparable`
  - No longer provides default `compare()` implementation
  - Now requires implementing class to provide `compare()` method
  - Uses Equatable trait via composition
  - Method `equals()` renamed to `equal()` for consistency

- **Floats::approxEqual() signature changed**
  - Now mimics Python's `math.isclose()` behavior
  - Parameters changed from `($f1, $f2, $epsilon, $relative)` to `($a, $b, $relTol, $absTol)`
  - Default relative tolerance: `1e-9` (new constant `DEFAULT_RELATIVE_TOLERANCE`)
  - Default absolute tolerance: `PHP_FLOAT_EPSILON` (new constant `DEFAULT_ABSOLUTE_TOLERANCE`)
  - Removed `$relative` parameter - now uses combined relative and absolute tolerance
  - IEEE-754 special value handling: `INF === INF`, `-INF === -INF`, `NAN` never equals anything
  - Removed `approxEqualAbsolute()` and `approxEqualRelative()` methods

- **Comparison method names standardized**
  - `equals()` → `equal()` across all traits
  - `isLessThan()` → `lessThan()`
  - `isGreaterThan()` → `greaterThan()`
  - `isLessThanOrEqual()` → `lessThanOrEqual()`
  - `isGreaterThanOrEqual()` → `greaterThanOrEqual()`

### Added

- **ApproxEquatable trait** (`Galaxon\Core\Traits\ApproxEquatable`)
  - Extends Equatable with tolerance-based comparison
  - Abstract `approxEqual()` method for floating-point equality within tolerances
  - For types without natural ordering (e.g., Complex numbers)

- **ApproxComparable trait** (`Galaxon\Core\Traits\ApproxComparable`)
  - Combines Comparable and ApproxEquatable for complete comparison suite
  - Provides `approxCompare()` method for approximate ordering comparison
  - For types with natural ordering that contain floating-point values (e.g., Rational numbers)

- **Floats constants**
  - `DEFAULT_RELATIVE_TOLERANCE` (1e-9) - Default relative tolerance for `approxEqual()`
  - `DEFAULT_ABSOLUTE_TOLERANCE` (PHP_FLOAT_EPSILON) - Default absolute tolerance
  - `MAX_EXACT_INT` (2⁵³) - Maximum integer exactly representable as float

- **Types::same()** - Check if two values have the same type using `get_debug_type()`

- **Comparable::checkSame()** - Public method to verify type compatibility, throws `TypeError` if types don't match

### Changed

- **Floats class reorganized**
  - Methods grouped by functionality: comparison, conversion, precision, rounding, random
  - Improved PHPDoc comments throughout
  - Better separation of concerns

- **Numbers::approxEqual()** - Updated to use new Floats::approxEqual() signature

- **Traits now use composition**
  - Comparable uses Equatable
  - ApproxEquatable uses Equatable
  - ApproxComparable uses both Comparable and ApproxEquatable
  - PHP automatically resolves diamond inheritance of Equatable

### Documentation

- **New comprehensive trait documentation**:
  - `docs/Traits/Traits.md` - Complete overview with hierarchy diagram and usage guide
  - `docs/Traits/Equatable.md` - Rewritten for trait (previously interface)
  - `docs/Traits/Comparable.md` - Updated for new namespace and behavior
  - `docs/Traits/ApproxEquatable.md` - New documentation for approximate equality
  - `docs/Traits/ApproxComparable.md` - New documentation for approximate comparison

- **Updated class documentation**:
  - `docs/Floats.md` - Completely reorganized to match new class structure
  - `docs/Numbers.md` - Updated for new comparison method signatures
  - `docs/Types.md` - Added `same()` documentation

- **README.md** - Added Traits section with links to all four traits and overview

### Tests

- **FloatsTest** - Comprehensive rewrite for new `approxEqual()` behavior
  - Tests for relative and absolute tolerance
  - Tests for IEEE-754 special values (INF, -INF, NAN)
  - Tests for combined tolerance behavior
  - Reduced from 478 to ~280 lines (test consolidation)

- **NumbersTest** - Updated for new `approxEqual()` signature
- **TypesTest** - Added tests for `same()`


---

## [0.3.0] - 2025-01-15

### Added

- **Arrays::quoteValues()** - Wrap string array values in quotes for formatting
  - Supports both single quotes (default) and double quotes
  - Useful for formatting lists in error messages or output
  - Throws `TypeError` if array contains non-string values
  - Preserves array keys

- **Floats::ulp()** - Calculate Unit in Last Place (ULP) for floating-point precision analysis
  - Returns the spacing between adjacent representable floats at a given magnitude
  - Useful for understanding floating-point precision limits and calculating error bounds
  - Moved from `NumberWithError` in Units package to provide general-purpose float utility

- **Floats::isExactInt()** - Check if a float represents an exact integer without rounding error
  - Validates integers are within IEEE-754 double's exact integer range (±2⁵³)
  - Returns `true` for whole numbers that can be exactly represented as floats
  - Moved from `NumberWithError::isExactFloat()` in Units package with improved naming

### Tests

- Added 19 comprehensive tests for `Arrays::quoteValues()`:
  - Both single and double quote modes
  - Empty arrays and strings
  - Special characters, whitespace, and unicode
  - Type validation (TypeError for non-string values)
  - Key preservation and immutability

- Added 18 comprehensive tests for Floats precision methods:
  - `ulp()` tests: standard values, zero handling, negative values, large/small magnitudes, non-finite values, relationship with `next()`
  - `isExactInt()` tests: whole numbers, fractional values, boundary cases (±2⁵³), non-finite values, comparison with `tryConvertToInt()`

### Documentation

- **Arrays.md** - Added documentation for `quoteValues()`
- **Floats.md** - Added documentation for `ulp()` and `isExactInt()`

---

## [0.2.0] - 2025-01-29

### Breaking Changes

- **Angle class removed** - Moved to `galaxon/units` package
  - Use `Galaxon\Units\Angle` instead of `Galaxon\Core\Angle`
  - All Angle functionality now available in the separate Units package

- **Floats::approxEqual() behavior changed**
  - Now uses relative comparison by default instead of absolute comparison
  - New 4th parameter `$relative` (defaults to `true`)
  - Relative comparison scales epsilon with magnitude, better for comparing values across different scales
  - To maintain old behavior, pass `false` for the `$relative` parameter

### Added

- **Floats** - New constants and methods for float operations
  - `TAU` constant - The mathematical constant τ (tau) = 2π, useful for angular calculations
  - `EPSILON` constant - Default epsilon value (1e-10) for approximate comparisons
  - `approxEqualAbsolute()` - Explicit absolute epsilon comparison
  - `approxEqualRelative()` - Explicit relative epsilon comparison (scales with magnitude)
  - `compare()` - Three-way comparison with approximate equality support

- **Numbers** - New comparison methods
  - `equal()` - Exact equality check for int|float values
  - `approxEqual()` - Approximate equality with relative/absolute mode selection

### Changed

- **Floats::compare()** - Now uses `Numbers::sign()` to guarantee exactly -1, 0, or 1 return values
- **Floats::approxEqual()** - Added 4th parameter `$relative` (defaults to `true`)
- **Numbers::approxEqual()** - Added 3rd parameter `$epsilon` and 4th parameter `$relative`

### Documentation

- **Enhanced PHPDoc comments** in `Equatable` and `Comparable` with detailed implementation guidelines
- **Comprehensive documentation updates**:
  - `docs/Floats.md` - Added TAU, wrap(), compare(), and all three approxEqual variants
  - `docs/Numbers.md` - Added equal() and updated approxEqual() documentation
  - `docs/Equatable.md` - Updated epsilon examples and best practices
  - `docs/Comparable.md` - Corrected implementation details and added TypeError documentation
  - Removed `docs/Angle.md` (moved to Units package)

### Tests

- Added comprehensive tests for new Floats methods (approxEqualAbsolute, approxEqualRelative, compare, wrap)
- Added comprehensive tests for Numbers methods (equal, approxEqual)
- All tests passing with 100% code coverage maintained

---

## [0.1.0] - 2025-01-16

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
