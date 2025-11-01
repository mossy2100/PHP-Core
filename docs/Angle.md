# Angle

Immutable class for working with angles in various units with high precision.

## Constants

- `TAU` - 2π
- `RADIANS_PER_TURN`, `DEGREES_PER_TURN`, `GRADIANS_PER_TURN` - Unit conversion constants
- `RAD_EPSILON` - Epsilon for angle comparisons (1e-9)
- `TRIG_EPSILON` - Epsilon for trigonometric comparisons (1e-12)

## Factory Methods

### fromRadians()

```php
public static function fromRadians(float $radians): self
```

Create angle from radians.

### fromDegrees()

```php
public static function fromDegrees(float $degrees): self
```

Create angle from degrees.

### fromDMS()

```php
public static function fromDMS(float $degrees, float $minutes = 0.0, float $seconds = 0.0): self
```

Create angle from degrees, arcminutes, and arcseconds.

### fromGradians()

```php
public static function fromGradians(float $gradians): self
```

Create angle from gradians.

### fromTurns()

```php
public static function fromTurns(float $turns): self
```

Create angle from full rotations.

### parse()

```php
public static function parse(string $value): self
```

Parse angle from string (supports CSS-style units and DMS format). Throws `ValueError` if invalid.

### tryParse()

```php
public static function tryParse(string $value, ?self &$result): bool
```

Attempt to parse angle string without throwing. Returns true on success.

## Conversion Methods

### toRadians()

```php
public function toRadians(): float
```

Get angle in radians.

### toDegrees()

```php
public function toDegrees(): float
```

Get angle in degrees.

### toDMS()

```php
public function toDMS(int $smallest_unit = 2, ?int $decimals = null): array
```

Get angle as [degrees, arcminutes, arcseconds] array.

### toGradians()

```php
public function toGradians(): float
```

Get angle in gradians.

### toTurns()

```php
public function toTurns(): float
```

Get angle in turns.

## Arithmetic Methods

### add()

```php
public function add(self $other): self
```

Add another angle to this angle.

### sub()

```php
public function sub(self $other): self
```

Subtract another angle from this angle.

### mul()

```php
public function mul(float $k): self
```

Multiply angle by a scalar.

### div()

```php
public function div(float $k): self
```

Divide angle by a scalar. Throws `DivisionByZeroError` if divisor is zero.

### abs()

```php
public function abs(): self
```

Get absolute value of angle.

## Comparison Methods

### cmp()

```php
public function cmp(self $other, float $eps = self::RAD_EPSILON): int
```

Compare angles with tolerance. Returns -1, 0, or 1.

### eq()

```php
public function eq(self $other, float $eps = self::RAD_EPSILON): bool
```

Check if two angles are equal within tolerance.

## Trigonometry Methods

### sin(), cos(), tan()

```php
public function sin(): float
public function cos(): float
public function tan(): float
```

Standard trigonometric functions.

### sinh(), cosh(), tanh()

```php
public function sinh(): float
public function cosh(): float
public function tanh(): float
```

Hyperbolic trigonometric functions.

## Wrapping Methods

### wrapRadians()

```php
public static function wrapRadians(float $radians, bool $signed = false): float
```

Normalize radians into [0, τ) or [-π, π) if signed.

### wrapDegrees()

```php
public static function wrapDegrees(float $degrees, bool $signed = false): float
```

Normalize degrees into [0, 360) or [-180, 180) if signed.

### wrapGradians()

```php
public static function wrapGradians(float $gradians, bool $signed = false): float
```

Normalize gradians into [0, 400) or [-200, 200) if signed.

### wrap()

```php
public function wrap(bool $signed = false): self
```

Normalize this angle (mutating method). Returns `$this` for chaining.

## String Methods

### format()

```php
public function format(string $format = 'rad', ?int $decimals = null): string
```

Format angle as string. Formats: 'rad', 'deg', 'grad', 'turn', 'd', 'dm', 'dms'.

### __toString()

```php
public function __toString(): string
```

Convert to string in radians (CSS notation).
