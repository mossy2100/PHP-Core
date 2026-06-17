# Testing Traits

## FloatAssertions

The [FloatAssertions](FloatAssertions.md) trait provides PHPUnit assertions for testing floating-point values:

```php
use OceanMoon\Core\Traits\Asserts\FloatAssertions;
use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    use FloatAssertions;

    public function testCalculation(): void
    {
        $result = someCalculation();

        // Instead of: $this->assertTrue(Floats::approxEqual(3.14159, $result));
        // Which gives: "Failed asserting that false is true."

        // Use this for informative failure messages:
        $this->assertApproxEqual(3.14159, $result, absTol: 1e-4);
        // Gives: "Failed asserting that 2.5 approximately equals 3.14159.
        //         Absolute difference: 0.64159 (tolerance: 0.0001)
        //         Relative difference: 0.204225... (tolerance: 1.0e-9)"
    }
}
```

---

## See Also

- [FloatAssertions.md](FloatAssertions.md) - PHPUnit assertions for float comparison
- [IncomparableTypesException.md](../../Exceptions/IncomparableTypesException.md) - Exception for type mismatches
- [Floats.md](../../Floats.md) - Utilities for floating-point comparison
