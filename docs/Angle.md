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
public static function fromDegrees(float $degrees, float $arcmin = 0.0, float $arcsec = 0.0): self
```

Create angle from degrees, plus optional arcminutes and arcseconds.

**Examples:**
```php
// Simple degrees
$angle = Angle::fromDegrees(45.5);

// Degrees, arcminutes, arcseconds
$angle = Angle::fromDegrees(12, 34, 56);  // 12° 34′ 56″

// Negative angle
$angle = Angle::fromDegrees(-12, -34, -56);
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

### tryParse()

```php
public static function tryParse(string $value, ?self &$result): bool
```

Attempt to parse angle string without throwing. Returns true on success.

**Example:**
```php
if (Angle::tryParse('45deg', $angle)) {
    echo "Parsed: " . $angle->toDegrees();
} else {
    echo "Invalid angle string";
}
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

### wrapRadians()

```php
public static function wrapRadians(float $radians, bool $signed = false): float
```

Normalize radians into [0, τ) or [-π, π) if signed.

**Examples:**
```php
// Unsigned range [0, 2π)
$wrapped = Angle::wrapRadians(7.0); // 0.716... (7 - 2π)

// Signed range [-π, π)
$wrapped = Angle::wrapRadians(4.0, true); // -2.283... (4 - 2π)
```

### wrapDegrees()

```php
public static function wrapDegrees(float $degrees, bool $signed = false): float
```

Normalize degrees into [0, 360) or [-180, 180) if signed.

**Examples:**
```php
// Unsigned range [0, 360)
$wrapped = Angle::wrapDegrees(450); // 90.0

// Signed range [-180, 180)
$wrapped = Angle::wrapDegrees(200, true); // -160.0
```

### wrapGradians()

```php
public static function wrapGradians(float $gradians, bool $signed = false): float
```

Normalize gradians into [0, 400) or [-200, 200) if signed.

**Examples:**
```php
// Unsigned range [0, 400)
$wrapped = Angle::wrapGradians(500); // 100.0

// Signed range [-200, 200)
$wrapped = Angle::wrapGradians(250, true); // -150.0
```

### wrap()

```php
public function wrap(bool $signed = false): self
```

Normalize this angle (mutating method). Returns `$this` for chaining.

**Examples:**
```php
// Unsigned wrapping
$angle = Angle::fromDegrees(450);
$angle->wrap();
echo $angle->toDegrees(); // 90.0

// Signed wrapping
$angle = Angle::fromDegrees(200);
$angle->wrap(true);
echo $angle->toDegrees(); // -160.0

// Chaining
$result = Angle::fromDegrees(540)
    ->wrap()
    ->mul(2);
echo $result->toDegrees(); // 360.0
```

## String Methods

### format()

```php
public function format(string $format = 'rad', ?int $decimals = null): string
```

Format angle as string. Supported formats:
- `'rad'`, `'deg'`, `'grad'`, `'turn'` - CSS-style with units (no space between number and unit)
- `'d'` - Degrees only with ° symbol
- `'dm'` - Degrees and arcminutes with ° ′ symbols
- `'dms'` - Degrees, arcminutes, and arcseconds with ° ′ ″ symbols

The `$decimals` parameter controls decimal places. If `null`, maximum precision is used with trailing zeros removed.

**Examples:**
```php
$angle = Angle::fromDegrees(12.5);

// CSS-style formats
echo $angle->format('rad', 4);  // "0.2182rad"
echo $angle->format('deg', 2);  // "12.50deg"
echo $angle->format('grad', 3); // "13.889grad"
echo $angle->format('turn', 5); // "0.03472turn"

// DMS formats
echo $angle->format('d', 1);    // "12.5°"
echo $angle->format('dm', 0);   // "12° 30′"
echo $angle->format('dms', 2);  // "12° 30′ 0.00″"

// Maximum precision (default)
echo $angle->format('rad');     // "0.21816615649929rad"

// Complex angle
$angle = Angle::fromDegrees(45, 30, 15);
echo $angle->format('dms', 1);  // "45° 30′ 15.0″"
echo $angle->format('deg', 4);  // "45.5042deg"

// Negative angles
$angle = Angle::fromDegrees(-30, -15, -45);
echo $angle->format('dms', 0);  // "-30° 15′ 45″"
```

**Carry behavior:**

When rounding with `$decimals`, the formatter handles carry correctly:

```php
$angle = Angle::fromDegrees(29, 59, 59.9999);
echo $angle->format('dms', 3);  // "30° 0′ 0.000″" (carried to next degree)

$angle = Angle::fromDegrees(29, 59.9999);
echo $angle->format('dm', 3);   // "30° 0.000′" (carried to next degree)
```

### __toString()

```php
public function __toString(): string
```

Convert to string in radians using CSS notation with maximum precision.

**Example:**
```php
$angle = Angle::fromDegrees(45);
echo $angle; // "0.78539816339745rad"
echo (string)$angle; // "0.78539816339745rad"
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
$latitude = Angle::fromDegrees(40, 46, 11.5); // New York City

// Convert to different representations
echo $latitude->toDegrees();           // 40.769861111111

// Get as DMS array
[$d, $m, $s] = $latitude->toDegrees(Angle::UNIT_ARCSECOND);
echo "{$d}° {$m}′ {$s}″";             // 40° 46′ 11.5″

// Format as string
echo $latitude->format('dms', 1);      // "40° 46′ 11.5″"
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
$angle->wrap();
echo $angle->format('deg', 0);         // "90deg"

// Normalize to [-180, 180) range
$angle = Angle::fromDegrees(270);
$angle->wrap(true);
echo $angle->format('deg', 0);         // "-90deg"
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

// Safe parsing with error handling
$input = "invalid";
if (Angle::tryParse($input, $angle)) {
    echo "Valid angle: " . $angle->format('deg');
} else {
    echo "Invalid angle string";
}
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
