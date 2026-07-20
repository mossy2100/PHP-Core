<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests\Globals;

use ArgumentCountError;
use DateTime;
use Error;
use OceanMoon\Core\Stringify;
use PHPUnit\Framework\TestCase;
use Stringable;

use function OceanMoon\Core\Globals\ex;
use function OceanMoon\Core\Globals\inspect;
use function OceanMoon\Core\Globals\println;
use function OceanMoon\Core\Globals\to_string;
use function OceanMoon\Core\Globals\write;
use function OceanMoon\Core\Globals\writeln;

use const OceanMoon\Core\Globals\RECURSION;

/**
 * Tests the functions in strings.php.
 */
final class StringsTest extends TestCase
{
    #region println() tests

    /**
     * Test println() with no argument prints just a newline.
     */
    public function testPrintlnWithNoArgument(): void
    {
        $this->expectOutputString(PHP_EOL);
        println();
    }

    /**
     * Test println() with a string.
     */
    public function testPrintlnWithString(): void
    {
        $this->expectOutputString('Hello' . PHP_EOL);
        println('Hello');
    }

    /**
     * Test println() with an empty string prints just a newline.
     */
    public function testPrintlnWithEmptyString(): void
    {
        $this->expectOutputString(PHP_EOL);
        println('');
    }

    /**
     * Test println() with an integer.
     */
    public function testPrintlnWithInt(): void
    {
        $this->expectOutputString('42' . PHP_EOL);
        println(42);
    }

    /**
     * Test println() with a float. Uses PHP's raw string conversion, so a whole-number float
     * doesn't get a distinguishing ".0" suffix the way Stringify::stringifyFloat() would add.
     */
    public function testPrintlnWithFloat(): void
    {
        $this->expectOutputString('3.14' . PHP_EOL);
        println(3.14);
    }

    /**
     * Test println() with true.
     */
    public function testPrintlnWithTrue(): void
    {
        $this->expectOutputString('1' . PHP_EOL);
        println(true);
    }

    /**
     * Test println() with false. PHP casts false to an empty string, so only the newline prints.
     */
    public function testPrintlnWithFalse(): void
    {
        $this->expectOutputString(PHP_EOL);
        println(false);
    }

    /**
     * Test println() with null. PHP casts null to an empty string, so only the newline prints.
     */
    public function testPrintlnWithNull(): void
    {
        $this->expectOutputString(PHP_EOL);
        println(null);
    }

    /**
     * Test println() with a Stringable object uses __toString().
     */
    public function testPrintlnWithStringableObject(): void
    {
        $this->expectOutputString('custom' . PHP_EOL);
        println(new StringableThing());
    }

    /**
     * Test println() with a non-Stringable object throws, since PHP can't concatenate it to a
     * string. Unlike write()/writeln() (which go via to_string()), println() uses PHP's raw string
     * conversion and has no fallback for this case.
     */
    public function testPrintlnWithNonStringableObjectThrows(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('could not be converted to string');
        println(new Foo());
    }

    #endregion

    #region to_string() tests

    /**
     * Test to_string() with a string returns it unchanged.
     */
    public function testToStringWithString(): void
    {
        $this->assertSame('Hello', to_string('Hello'));
    }

    /**
     * Test to_string() with an integer.
     */
    public function testToStringWithInt(): void
    {
        $this->assertSame('42', to_string(42));
        $this->assertSame('-17', to_string(-17));
    }

    /**
     * Test to_string() with a float. Uses a raw (string) cast, not Stringify::stringifyFloat(), so
     * a whole-number float loses its distinguishing ".0" suffix.
     */
    public function testToStringWithFloat(): void
    {
        $this->assertSame('3.14', to_string(3.14));
        $this->assertSame('5', to_string(5.0));
    }

    /**
     * Test to_string() with non-finite floats (NAN, INF, -INF). Casting these directly emits a "coerced to string"
     * warning (PHP 8.5+), which to_string() must absorb internally rather than let escape as an error.
     */
    public function testToStringWithNonFiniteFloats(): void
    {
        $this->assertSame('NAN', to_string(NAN));
        $this->assertSame('INF', to_string(INF));
        $this->assertSame('-INF', to_string(-INF));
    }

    /**
     * Test to_string() with null uses PHP's raw (string) cast, giving an empty string.
     */
    public function testToStringWithNull(): void
    {
        $this->assertSame('', to_string(null));
    }

    /**
     * Test to_string() with booleans uses PHP's raw (string) cast: '1' for true, '' for false.
     */
    public function testToStringWithBool(): void
    {
        $this->assertSame('1', to_string(true));
        $this->assertSame('', to_string(false));
    }

    /**
     * Test to_string() with an array falls back to Stringify's concise representation.
     */
    public function testToStringWithArray(): void
    {
        $this->assertSame('[1, 2, 3]', to_string([1, 2, 3]));
        $this->assertSame('["a" => 1]', to_string([
            'a' => 1,
        ]));
    }

    /**
     * Test to_string() with an array containing a circular reference doesn't error.
     */
    public function testToStringWithRecursiveArray(): void
    {
        $arr = [
            'x' => 1,
        ];
        $arr['self'] = &$arr;

        $this->assertSame('["x" => 1, "self" => ' . RECURSION . ']', to_string($arr));
    }

    /**
     * Test to_string() with a Stringable object uses __toString() directly.
     */
    public function testToStringWithStringableObject(): void
    {
        $this->assertSame('custom', to_string(new StringableThing()));
    }

    /**
     * Test to_string() with an enum case falls back to Stringify.
     */
    public function testToStringWithEnum(): void
    {
        $this->assertSame('OceanMoon\Core\Tests\Globals\Suit::Hearts', to_string(Suit::Hearts));
    }

    /**
     * Test to_string() with a DateTime formats it as an ISO 8601 (ATOM) string, since DateTime doesn't
     * implement Stringable and the default (string) cast would otherwise throw.
     */
    public function testToStringWithDateTime(): void
    {
        $dateTime = new DateTime('2026-07-17T12:34:56+00:00');

        $this->assertSame('2026-07-17T12:34:56+00:00', to_string($dateTime));
    }

    /**
     * Test to_string() with a non-Stringable object falls back to Stringify, showing the class name
     * and properties. The object ID Stringify now includes in its output is non-deterministic, so
     * this checks the shape rather than an exact string.
     */
    public function testToStringWithNonStringableObject(): void
    {
        $result = to_string(new Foo());

        $this->assertMatchesRegularExpression(
            '/^OceanMoon\\\\Core\\\\Tests\\\\Globals\\\\Foo #\d+ \{\+a => 1, #b => 2, -c => 3\}$/',
            $result
        );
    }

    /**
     * Test to_string() with a resource.
     */
    public function testToStringWithResource(): void
    {
        $resource = fopen('php://memory', 'rb');
        $this->assertIsResource($resource);

        $this->assertMatchesRegularExpression('/^Resource id #\d+$/', to_string($resource));

        fclose($resource);
    }

    /**
     * Test to_string() with no argument throws.
     */
    public function testToStringWithNoArgumentThrows(): void
    {
        $this->expectException(ArgumentCountError::class);
        to_string(); // @phpstan-ignore arguments.count
    }

    #endregion

    #region write() tests

    /**
     * Test write() prints the value via to_string(), without a trailing newline.
     */
    public function testWriteWithString(): void
    {
        $this->expectOutputString('Hello');
        write('Hello');
    }

    /**
     * Test write() with null prints nothing, matching to_string()'s raw-cast behavior.
     */
    public function testWriteWithNull(): void
    {
        $this->expectOutputString('');
        write(null);
    }

    /**
     * Test write() with a non-Stringable object doesn't throw, unlike println() — it goes via
     * to_string(), which falls back to Stringify instead of PHP's raw string conversion.
     */
    public function testWriteWithNonStringableObjectDoesNotThrow(): void
    {
        $this->expectOutputRegex(
            '/^OceanMoon\\\\Core\\\\Tests\\\\Globals\\\\Foo #\d+ \{\+a => 1, #b => 2, -c => 3\}$/'
        );
        write(new Foo());
    }

    /**
     * Test write() with no argument throws.
     */
    public function testWriteWithNoArgumentThrows(): void
    {
        $this->expectException(ArgumentCountError::class);
        write(); // @phpstan-ignore arguments.count
    }

    #endregion

    #region writeln() tests

    /**
     * Test writeln() prints the value via to_string(), with a trailing newline.
     */
    public function testWritelnWithString(): void
    {
        $this->expectOutputString('Hello' . PHP_EOL);
        writeln('Hello');
    }

    /**
     * Test writeln() with null prints just a newline, matching to_string()'s raw-cast behavior.
     */
    public function testWritelnWithNull(): void
    {
        $this->expectOutputString(PHP_EOL);
        writeln(null);
    }

    /**
     * Test writeln() with no argument throws.
     */
    public function testWritelnWithNoArgumentThrows(): void
    {
        $this->expectException(ArgumentCountError::class);
        writeln(); // @phpstan-ignore arguments.count
    }

    #endregion

    #region inspect() tests

    /**
     * Test inspect() prints the value via Stringify::stringify(), without pretty printing by
     * default.
     */
    public function testDumpVarWithArray(): void
    {
        $this->expectOutputString('[1, 2, 3]' . PHP_EOL);
        inspect([1, 2, 3]);
    }

    /**
     * Test inspect() with pretty printing enabled.
     */
    public function testDumpVarWithArrayPrettyPrint(): void
    {
        $this->expectOutputString("[\n    \"a\" => 1,\n    \"b\" => 2,\n]" . PHP_EOL);
        inspect([
            'a' => 1,
            'b' => 2,
        ], true);
    }

    /**
     * Test inspect() handles a circular reference instead of erroring.
     */
    public function testDumpVarHandlesRecursion(): void
    {
        $arr = [
            'x' => 1,
        ];
        $arr['self'] = &$arr;

        $this->expectOutputString('["x" => 1, "self" => ' . RECURSION . ']' . PHP_EOL);
        inspect($arr);
    }

    /**
     * Test inspect() with an object shows the class name and properties.
     */
    public function testDumpVarWithObject(): void
    {
        $this->expectOutputRegex(
            '/^OceanMoon\\\\Core\\\\Tests\\\\Globals\\\\Foo #\d+ \{\+a => 1, #b => 2, -c => 3\}' . PHP_EOL . '$/'
        );
        inspect(new Foo());
    }

    /**
     * Test inspect() with no argument throws.
     */
    public function testDumpVarWithNoArgumentThrows(): void
    {
        $this->expectException(ArgumentCountError::class);
        inspect(); // @phpstan-ignore arguments.count
    }

    /**
     * Test inspect() with $return false (the default) prints and returns null.
     */
    public function testInspectWithReturnFalseReturnsNull(): void
    {
        $this->expectOutputString('[1, 2, 3]' . PHP_EOL);
        $this->assertNull(inspect([1, 2, 3]));
    }

    /**
     * Test inspect() with $return true returns the stringified value instead of printing it.
     */
    public function testInspectWithReturnTrueReturnsString(): void
    {
        $this->expectOutputString('');
        $this->assertSame('[1, 2, 3]', inspect([1, 2, 3], return: true));
    }

    /**
     * Test inspect() with $return true and pretty printing enabled.
     */
    public function testInspectWithReturnTrueAndPrettyPrint(): void
    {
        $this->expectOutputString('');
        $this->assertSame(
            "[\n    \"a\" => 1,\n    \"b\" => 2,\n]",
            inspect([
                'a' => 1,
                'b' => 2,
            ], prettyPrint: true, return: true)
        );
    }

    #endregion

    #region ex() tests

    /**
     * Test ex() with short values matches Stringify::abbrev(), unmodified.
     */
    public function testExWithShortValue(): void
    {
        $this->assertSame('"hello"', ex('hello'));
        $this->assertSame('42', ex(42));
        $this->assertSame('true', ex(true));
    }

    /**
     * Test ex() truncates long strings, matching Stringify::abbrev() at its default max length.
     */
    public function testExWithLongString(): void
    {
        $longString = str_repeat('a', 100);

        $result = ex($longString);

        $this->assertSame(Stringify::abbrev($longString), $result);
        $this->assertLessThanOrEqual(32, mb_strlen($result));
        $this->assertStringEndsWith('…"', $result);
    }

    /**
     * Test ex() truncates long arrays, matching Stringify::abbrev() at its default max length.
     */
    public function testExWithLongArray(): void
    {
        $array = range(1, 20);

        $result = ex($array);

        $this->assertSame(Stringify::abbrev($array), $result);
        $this->assertLessThanOrEqual(32, mb_strlen($result));
        $this->assertStringEndsWith('…]', $result);
    }

    /**
     * Test ex() always delegates to Stringify::abbrev() with its default max length, for a range of value types.
     */
    public function testExDelegatesToStringifyAbbrev(): void
    {
        $values = [
            'short string' => 'hello',
            'long string'  => str_repeat('x', 50),
            'int'          => 42,
            'float'        => 3.14,
            'bool'         => false,
            'null'         => null,
            'array'        => range(1, 20),
            'object'       => new Foo(),
        ];

        foreach ($values as $value) {
            $this->assertSame(Stringify::abbrev($value), ex($value));
        }
    }

    #endregion
}

/**
 * Test fixture with properties of every visibility, for object-stringification tests.
 */
class Foo
{
    public int $a = 1;

    protected int $b = 2;

    private int $c = 3; // @phpstan-ignore property.onlyWritten
}

/**
 * Test fixture implementing Stringable, for testing the Stringable fast path in to_string().
 */
class StringableThing implements Stringable
{
    public function __toString(): string
    {
        return 'custom';
    }
}

/**
 * Test fixture enum, for testing enum handling in to_string().
 */
enum Suit
{
    case Hearts;

    case Spades;
}
