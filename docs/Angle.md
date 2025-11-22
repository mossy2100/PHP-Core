# Angle

Immutable class for working with angles in various units with high precision.

## Constants

- `TAU` - 2π
- `RADIANS_PER_TURN`, `DEGREES_PER_TURN`, `GRADIANS_PER_TURN` - Unit conversion constants
- `RAD_EPSILON` - Epsilon for angle comparisons (1e-9)
- `TRIG_EPSILON` - Epsilon for trigonometric comparisons (1e-12)
- `UNIT_DEGREE` (0), `UNIT_ARCMINUTE` (1), `UNIT_ARCSECOND` (2) - Constants for specifying the smallest unit in DMS conversions

## Factory Methods

### fromRadians()

```php
public static function fromRadians(float $radians): self
```

Create angle from radians.

**Example:**
```php
$angle = Angle::fromRadians(M_PI);
echo $angle->toDegrees(); // 180.0
```

### fromDegrees()

```php
public static function fromDegrees(float $degrees): self
```

Create angle from degrees.

**Examples:**
```php
$angle = Angle::fromDegrees(180);
echo $angle->toRadians();  // 3.14159...
```

### fromDMS()

```php
public static function fromDMS(float $degrees, float $arcmin = 0.0, float $arcsec = 0.0): self
```

Create angle from degrees, plus optional arcminutes and arcseconds.

**Examples:**
```php
// Simple degrees
$angle = Angle::fromDMS(45.5);

// Degrees, arcminutes, arcseconds
$angle = Angle::fromDMS(12, 34, 56);  // 12° 34′ 56″

// Negative angle
$angle = Angle::fromDMS(-12, -34, -56);
```

### fromGradians()

```php
public static function fromGradians(float $gradians): self
```

Create angle from gradians.

**Example:**
```php
$angle = Angle::fromGradians(100);
echo $angle->toDegrees(); // 90.0
```

### fromTurns()

```php
public static function fromTurns(float $turns): self
```

Create angle from full rotations.

**Example:**
```php
$angle = Angle::fromTurns(0.5);
echo $angle->toDegrees(); // 180.0
```

### parse()

```php
public static function parse(string $value): self
```

Parse angle from string (supports CSS-style units and symbols for degrees, arcminutes, and arcseconds). Throws `ValueError` if invalid.

**Examples:**
```php
// CSS-style units
$angle = Angle::parse('45deg');
$angle = Angle::parse('1.5708rad');
$angle = Angle::parse('100grad');
$angle = Angle::parse('0.25turn');

// DMS notation (Unicode symbols)
$angle = Angle::parse('12° 34′ 56″');

// DMS notation (ASCII fallback)
$angle = Angle::parse("12°34'56\"");

// Whitespace and case insensitive
$angle = Angle::parse('  45 DEG  ');
```

## Conversion Methods

### toRadians()

```php
public function toRadians(): float
```

Get angle in radians.

**Example:**
```php
$angle = Angle::fromDegrees(180);
echo $angle->toRadians(); // 3.14159...
```

### toDegrees()

```php
public function toDegrees(): float
```

Get angle in degrees.

**Example:**
```php
$angle = Angle::fromRadians(M_PI / 4);
echo $angle->toDegrees(); // 45.0
```

### toDMS()

```php
public function toDMS(int $smallest_unit = Angle::UNIT_ARCSECOND): array
```

Get the angle in degrees, arcminutes, and arcseconds. The result will be an array with 1-3 values, depending on the requested smallest unit. Only the last item may have a fractional part; others will be whole numbers.

If the angle is positive, the resulting values will all be positive. If the angle is zero, the resulting values will all be zero. If the angle is negative, the resulting values will all be negative.

For the `$smallest_unit` parameter, you can use the UNIT_* class constants:
- `UNIT_DEGREE` (0) for degrees only
- `UNIT_ARCMINUTE` (1) for degrees and arcminutes
- `UNIT_ARCSECOND` (2) for degrees, arcminutes, and arcseconds (default)

(Note: If the smallest unit is degrees, you may prefer to use `toDegrees()` instead, which returns a float instead of an array.)

**Parameters:**
- `$smallest_unit` (int) - 0 for degrees, 1 for arcminutes, 2 for arcseconds (default)

**Returns:**
- `float[]` - An array of 1-3 floats with the degrees, arcminutes, and arcseconds

**Throws:**
- `ValueError` - If $smallest_unit is not 0, 1, or 2

**Examples:**
```php
$angle = Angle::fromRadians(M_PI / 4);

// As decimal degrees only
[$deg] = $angle->toDMS(Angle::UNIT_DEGREE);  // [45.0]

// As degrees and arcminutes
[$d, $m] = $angle->toDMS(Angle::UNIT_ARCMINUTE);  // [45.0, 0.0]

// As degrees, arcminutes, and arcseconds
[$d, $m, $s] = $angle->toDMS(Angle::UNIT_ARCSECOND);  // [45.0, 0.0, 0.0]

// Example with actual DMS values
$angle = Angle::fromDegrees(12, 34, 56);
[$d, $m, $s] = $angle->toDMS();  // [12.0, 34.0, 56.0]
```

### toGradians()

```php
public function toGradians(): float
```

Get angle in gradians.

**Example:**
```php
$angle = Angle::fromDegrees(90);
echo $angle->toGradians(); // 100.0
```

### toTurns()

```php
public function toTurns(): float
```

Get angle in turns (full rotations).

**Example:**
```php
$angle = Angle::fromDegrees(180);
echo $angle->toTurns(); // 0.5
```

## Arithmetic Methods

### add()

```php
public function add(self $other): self
```

Add another angle to this angle.

**Example:**
```php
$a = Angle::fromDegrees(45);
$b = Angle::fromDegrees(30);
$sum = $a->add($b);
echo $sum->toDegrees(); // 75.0
```

### sub()

```php
public function sub(self $other): self
```

Subtract another angle from this angle.

**Example:**
```php
$a = Angle::fromDegrees(90);
$b = Angle::fromDegrees(45);
$diff = $a->sub($b);
echo $diff->toDegrees(); // 45.0
```

### mul()

```php
public function mul(float $k): self
```

Multiply angle by a scalar. Throws `ValueError` if the scalar is non-finite (±∞ or NaN).

**Example:**
```php
$angle = Angle::fromDegrees(30);
$doubled = $angle->mul(2);
echo $doubled->toDegrees(); // 60.0
```

### div()

```php
public function div(float $k): self
```

Divide angle by a scalar. Throws `DivisionByZeroError` if divisor is zero, `ValueError` if divisor is non-finite.

**Example:**
```php
$angle = Angle::fromDegrees(90);
$half = $angle->div(2);
echo $half->toDegrees(); // 45.0
```

### abs()

```php
public function abs(): self
```

Get absolute value of angle.

**Example:**
```php
$angle = Angle::fromDegrees(-45);
$positive = $angle->abs();
echo $positive->toDegrees(); // 45.0
```

## Comparison Methods

Angle implements the `Equatable` interface and uses the `Comparable` trait, providing a full set of comparison operations.

### compare()
```php
public function compare(mixed $other): int
```

Compare angles by their raw numeric values with epsilon tolerance. Returns -1 if this angle is less, 0 if equal (within epsilon), 1 if greater.

Angles are compared using their raw radian values without normalization, so 10° < 370° even though they represent the same angular position. Use `wrap()` before comparing if you need to treat equivalent positions as equal.

Two angles are considered equal if their difference in radians is less than `RAD_EPSILON` (1e-9).

**Parameters:**
- `$other` (mixed) - The value to compare with (must be an Angle)

**Returns:**
- `int` - -1 if this < other, 0 if equal (within epsilon), 1 if this > other

**Throws:**
- `TypeError` - If $other is not an Angle

**Example:**
```php
$a = Angle::fromDegrees(10);
$b = Angle::fromDegrees(370);
echo $a->compare($b); // -1 (10 < 370)

$c = Angle::fromDegrees(10);
echo $a->compare($c); // 0 (equal)

// Wrapped comparison
$aWrapped = Angle::fromDegrees(10)->wrap();
$bWrapped = Angle::fromDegrees(370)->wrap();
echo $aWrapped->compare($bWrapped); // 0 (both normalized to 10°)
```

### equals()
```php
public function equals(mixed $other): bool
```

Check if two angles are equal within epsilon tolerance (`RAD_EPSILON` = 1e-9). Provided by the `Comparable` trait - delegates to `compare()`.

Angles are not normalized before comparison, so use `wrap()` first if you need to compare angular positions rather than raw values.

**Parameters:**
- `$other` (mixed) - The value to compare with

**Returns:**
- `bool` - True if angles are equal within epsilon tolerance; false otherwise

**Note:** Returns `false` gracefully for non-Angle types (doesn't throw).

**Example:**
```php
$a = Angle::fromDegrees(45);
$b = Angle::fromDegrees(45);
$c = Angle::fromDegrees(405); // 45° + 360°

var_dump($a->equals($b)); // true
var_dump($a->equals($c)); // false (45 ≠ 405)

// After wrapping
$cWrapped = Angle::fromDegrees(405)->wrap();
var_dump($a->equals($cWrapped)); // true (both are 45°)

// Gracefully handles wrong types
var_dump($a->equals(45)); // false (not an Angle)
var_dump($a->equals("45deg")); // false (not an Angle)
```

### isLessThan()
```php
public function isLessThan(mixed $other): bool
```

Check if this angle is less than another. Provided by the `Comparable` trait.

**Example:**
```php
$a = Angle::fromDegrees(30);
$b = Angle::fromDegrees(60);

var_dump($a->isLessThan($b)); // true
var_dump($b->isLessThan($a)); // false
```

### isLessThanOrEqual()
```php
public function isLessThanOrEqual(mixed $other): bool
```

Check if this angle is less than or equal to another. Provided by the `Comparable` trait.

**Example:**
```php
$a = Angle::fromDegrees(45);
$b = Angle::fromDegrees(45);
$c = Angle::fromDegrees(90);

var_dump($a->isLessThanOrEqual($b)); // true (equal)
var_dump($a->isLessThanOrEqual($c)); // true (less than)
```

### isGreaterThan()
```php
public function isGreaterThan(mixed $other): bool
```

Check if this angle is greater than another. Provided by the `Comparable` trait.

**Example:**
```php
$a = Angle::fromDegrees(90);
$b = Angle::fromDegrees(45);

var_dump($a->isGreaterThan($b)); // true
var_dump($b->isGreaterThan($a)); // false
```

### isGreaterThanOrEqual()
```php
public function isGreaterThanOrEqual(mixed $other): bool
```

Check if this angle is greater than or equal to another. Provided by the `Comparable` trait.

**Example:**
```php
$a = Angle::fromDegrees(60);
$b = Angle::fromDegrees(60);
$c = Angle::fromDegrees(30);

var_dump($a->isGreaterThanOrEqual($b)); // true (equal)
var_dump($a->isGreaterThanOrEqual($c)); // true (greater than)
```


## Trigonometry Methods

### sin(), cos(), tan()

```php
public function sin(): float
public function cos(): float
public function tan(): float
```

Standard trigonometric functions.

**Example:**
```php
$angle = Angle::fromDegrees(30);
echo $angle->sin(); // 0.5
echo $angle->cos(); // 0.866...
echo $angle->tan(); // 0.577...
```

### sinh(), cosh(), tanh()

```php
public function sinh(): float
public function cosh(): float
public function tanh(): float
```

Hyperbolic trigonometric functions.

**Example:**
```php
$angle = Angle::fromRadians(1.0);
echo $angle->sinh(); // 1.175...
echo $angle->cosh(); // 1.543...
echo $angle->tanh(); // 0.761...
```

## Wrapping Methods

Wrapping normalizes angles to a canonical range, following the mathematical convention where:
- **Unsigned range [0, 2π)**: Includes lower bound (0), excludes upper bound (2π)
- **Signed range (-π, π]**: Excludes lower bound (-π), includes upper bound (π)

This convention matches the [standard principal value for complex number arguments](https://en.wikipedia.org/wiki/Principal_value#Complex_argument) and ensures uniqueness.

NB: The methods `wrapRadians()`, `wrapDegrees()`, `wrapGradians()`, and `wrapTurns()` are utility methods for working with angles as floats, and do not operate on Angle objects. To wrap an Angle object, use the `wrap()` instance method, which returns a new Angle.

### wrapRadians()

```php
public static function wrapRadians(float $radians, bool $signed = true): float
```

Normalize radians into the signed range (-π, π] by default, or unsigned range [0, τ) when `$signed = false`.

**Parameters:**
- `$radians` (float) - The angle in radians to normalize
- `$signed` (bool) - Whether to use signed range (default: `true`)

**Examples:**
```php
// Signed range (-π, π] - DEFAULT
$wrapped = Angle::wrapRadians(4.0); // -2.283... (4 - 2π)
$wrapped = Angle::wrapRadians(-M_PI); // π (lower bound excluded, wraps to upper)
$wrapped = Angle::wrapRadians(M_PI); // π (upper bound included)

// Unsigned range [0, 2π)
$wrapped = Angle::wrapRadians(7.0, false); // 0.716... (7 - 2π)
$wrapped = Angle::wrapRadians(-M_PI, false); // π (negative wraps to positive)
```

### wrapDegrees()

```php
public static function wrapDegrees(float $degrees, bool $signed = true): float
```

Normalize degrees into the signed range (-180, 180] by default, or unsigned range [0, 360) when `$signed = false`.

**Parameters:**
- `$degrees` (float) - The angle in degrees to normalize
- `$signed` (bool) - Whether to use signed range (default: `true`)

**Examples:**
```php
// Signed range (-180, 180] - DEFAULT
$wrapped = Angle::wrapDegrees(200); // -160.0
$wrapped = Angle::wrapDegrees(-180); // 180.0 (lower bound excluded, wraps to upper)
$wrapped = Angle::wrapDegrees(180); // 180.0 (upper bound included)

// Unsigned range [0, 360)
$wrapped = Angle::wrapDegrees(450, false); // 90.0
$wrapped = Angle::wrapDegrees(-90, false); // 270.0 (negative wraps to positive)
```

### wrapGradians()

```php
public static function wrapGradians(float $gradians, bool $signed = true): float
```

Normalize gradians into the signed range (-200, 200] by default, or unsigned range [0, 400) when `$signed = false`.

**Parameters:**
- `$gradians` (float) - The angle in gradians to normalize
- `$signed` (bool) - Whether to use signed range (default: `true`)

**Examples:**
```php
// Signed range (-200, 200] - DEFAULT
$wrapped = Angle::wrapGradians(250); // -150.0
$wrapped = Angle::wrapGradians(-200); // 200.0 (lower bound excluded, wraps to upper)
$wrapped = Angle::wrapGradians(200); // 200.0 (upper bound included)

// Unsigned range [0, 400)
$wrapped = Angle::wrapGradians(500, false); // 100.0
$wrapped = Angle::wrapGradians(-100, false); // 300.0 (negative wraps to positive)
```

### wrapTurns()

```php
public static function wrapTurns(float $turns, bool $signed = true): float
```

Normalize turns into the signed range (-0.5, 0.5] by default, or unsigned range [0, 1) when `$signed = false`.

**Parameters:**
- `$turns` (float) - The angle in turns to normalize
- `$signed` (bool) - Whether to use signed range (default: `true`)

**Examples:**
```php
// Signed range (-0.5, 0.5] - DEFAULT
$wrapped = Angle::wrapTurns(0.75); // -0.25
$wrapped = Angle::wrapTurns(-0.5); // 0.5 (lower bound excluded, wraps to upper)
$wrapped = Angle::wrapTurns(0.5); // 0.5 (upper bound included)

// Unsigned range [0, 1)
$wrapped = Angle::wrapTurns(1.25, false); // 0.25
$wrapped = Angle::wrapTurns(-0.25, false); // 0.75 (negative wraps to positive)
```

### wrap()

```php
public function wrap(bool $signed = true): self
```

Normalize this angle. Returns a new Angle with the wrapped value (the original is unchanged).

**Parameters:**
- `$signed` (bool) - Whether to use signed range (default: `true`)

**Examples:**
```php
// Signed wrapping - DEFAULT
$angle = Angle::fromDegrees(200);
$wrapped = $angle->wrap();
echo $wrapped->toDegrees(); // -160.0

// Unsigned wrapping
$angle = Angle::fromDegrees(450);
$wrapped = $angle->wrap(false);
echo $wrapped->toDegrees(); // 90.0

// Chaining
$result = Angle::fromDegrees(540)
    ->wrap()
    ->mul(2);
echo $result->toDegrees(); // 360.0
```

## String Methods

### format()

```php
public function format(string $unit = 'rad', ?int $decimals = null): string
```

Format angle in CSS style, with no space between number and unit.
Supported units are `'rad'`, `'deg'`, `'grad'`, and `'turn'`.

The `$decimals` parameter controls decimal places. If `null`, maximum precision is used with trailing zeros removed.

**Examples:**
```php
$angle = Angle::fromDegrees(12.5);

// Different units
echo $angle->format('rad', 4);  // 0.2182rad
echo $angle->format('deg', 2);  // 12.50deg
echo $angle->format('grad', 3); // 13.889grad
echo $angle->format('turn', 5); // 0.03472turn

// Maximum precision (default)
echo $angle->format('rad'); // 0.21816615649929rad

// Complex angle
$angle = Angle::fromDegrees(45, 30, 15);
echo $angle->format('deg', 4); // 45.5042deg
```

### formatDMS()

```php
public function formatDMS(int $smallest_unit = UNIT_ARCSECOND, ?int $decimals = null): string
```

Options for $smallest_unit:
- `UNIT_DEGREE` - Degrees only with ° symbol
- `UNIT_ARCMINUTE` - Degrees and arcminutes with ° ′ symbols
- `UNIT_ARCSECOND` - Degrees, arcminutes, and arcseconds with ° ′ ″ symbols


**Examples:**
```php
$angle = Angle::fromDegrees(12.5);

// DMS formats
echo $angle->formatDMS(Angle::UNIT_DEGREE, 1);     // 12.5°
echo $angle->formatDMS(Angle::UNIT_ARCMINUTE, 0);  // 12° 30′
echo $angle->formatDMS(Angle::UNIT_ARCSECOND, 2);  // 12° 30′ 0.00″

// Complex angle
$angle = Angle::fromDegrees(45, 30, 15);
echo $angle->formatDMS(decimals: 1); // 45° 30′ 15.0″

// Negative angles
$angle = Angle::fromDegrees(-30, -15, -45);
echo $angle->formatDMS(); // -30° 15′ 45″
```

**Carry behavior:**

When rounding with `$decimals`, the formatter handles carry correctly:

```php
$angle = Angle::fromDegrees(29, 59, 59.9999);
echo $angle->formatDMS(decimals: 3); // 30° 0′ 0.000″ (carried to next degree)

$angle = Angle::fromDegrees(29, 59.9999);
echo $angle->formatDMS(Angle::UNIT_ARCMINUTE, 3); // 30° 0.000′ (carried to next degree)
```

### __toString()

```php
public function __toString(): string
```

Convert to string in CSS notation using radians as the unit, with maximum precision.

**Example:**
```php
$angle = Angle::fromDegrees(45);
echo $angle; // 0.78539816339745rad
echo (string)$angle; // 0.78539816339745rad
```

## Usage Examples

### Basic angle creation and conversion

```php
// Create angle in various units
$rad = Angle::fromRadians(M_PI / 2);
$deg = Angle::fromDegrees(90);
$grad = Angle::fromGradians(100);
$turn = Angle::fromTurns(0.25);

// All represent the same angle (90°)
var_dump($rad->equals($deg)); // true
var_dump($deg->equals($grad)); // true
var_dump($grad->equals($turn)); // true
```

### Working with DMS (degrees, minutes, seconds)

```php
// Create from DMS
$latitude = Angle::fromDMS(40, 46, 11.5);  // New York City

// Convert to different representations
echo $latitude->toDegrees();  // 40.769861111111

// Get as DMS array
[$d, $m, $s] = $latitude->toDMS();
echo "{$d}° {$m}′ {$s}″";  // 40° 46′ 11.5″

// Format as string
echo $latitude->formatDMS(decimals: 1);  // "40° 46′ 11.5″"
```

### Angle arithmetic

```php
// Calculate the sum of two angles
$bearing1 = Angle::fromDegrees(45);
$adjustment = Angle::fromDegrees(30);
$newBearing = $bearing1->add($adjustment);
echo $newBearing->format('deg', 0);    // "75deg"

// Scale an angle
$angle = Angle::fromDegrees(30);
$tripled = $angle->mul(3);
echo $tripled->format('deg', 0);       // "90deg"

// Calculate average angle
$a1 = Angle::fromDegrees(30);
$a2 = Angle::fromDegrees(60);
$avg = $a1->add($a2)->div(2);
echo $avg->format('deg', 0);           // "45deg"
```

### Wrapping angles

```php
// Normalize angle to [0, 360) range
$angle = Angle::fromDegrees(450);
$wrapped = $angle->wrap(false);
echo $wrapped->format('deg', 0);       // "90deg"

// Normalize to (-180, 180] range
$angle = Angle::fromDegrees(270);
$wrapped = $angle->wrap();
echo $wrapped->format('deg', 0);       // "-90deg"
```

### Parsing angle strings

```php
// Parse various formats
$angles = [
    Angle::parse('45deg'),
    Angle::parse('1.5708rad'),
    Angle::parse('100grad'),
    Angle::parse('0.25turn'),
    Angle::parse('45° 30′ 0″'),
];
```

### Trigonometry

```php
// Calculate height of a building
$distance = 100; // meters
$angle = Angle::fromDegrees(30);
$height = $distance * $angle->tan();
echo round($height, 2); // 57.74 meters

// Navigate using bearings
$bearing = Angle::fromDegrees(45);
$distance = 100;
$eastward = $distance * $bearing->sin();
$northward = $distance * $bearing->cos();
```
