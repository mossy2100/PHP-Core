<?php

declare(strict_types=1);

namespace Mossy2100\Core\Tests\Stringify;

use Closure;
use InvalidArgumentException;
use Mossy2100\Core\Stringify;
use Mossy2100\Core\StringifyOptions;
use Mossy2100\Core\Tests\Fixtures\TestBackedEnum;
use Mossy2100\Core\Tests\Fixtures\TestEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

use const Mossy2100\Core\RECURSION;

/**
 * Test class for Stringify utility class - type-specific stringification methods.
 */
#[CoversClass(Stringify::class)]
final class StringifyTypeSpecificTest extends TestCase
{
    #region Method stringifyBool() tests.

    /**
     * Test stringifying boolean values.
     */
    public function testStringifyBool(): void
    {
        $this->assertSame('true', Stringify::stringify(true));
        $this->assertSame('false', Stringify::stringify(false));
    }

    #endregion

    #region Method stringifyInt() tests.

    /**
     * Test stringifying integer values.
     */
    public function testStringifyInt(): void
    {
        $this->assertSame('0', Stringify::stringify(0));
        $this->assertSame('42', Stringify::stringify(42));
        $this->assertSame('-17', Stringify::stringify(-17));
        $this->assertSame('1000000', Stringify::stringify(1000000));
    }

    #endregion

    #region Method stringifyFloat() tests.

    /**
     * Test stringifying float values.
     */
    public function testStringifyFloat(): void
    {
        $this->assertSame('3.14', Stringify::stringifyFloat(3.14));
        $this->assertSame('-2.5', Stringify::stringifyFloat(-2.5));

        // Float that looks like integer gets .0 appended.
        $this->assertSame('5.0', Stringify::stringifyFloat(5.0));
        $this->assertSame('-10.0', Stringify::stringifyFloat(-10.0));
        $this->assertSame('0.0', Stringify::stringifyFloat(0.0));

        // Float with exponent notation (already distinguishable from int).
        $result = Stringify::stringifyFloat(1.5e100);
        $this->assertMatchesRegularExpression('/[eE]/', $result);

        // Very small float with exponent notation.
        $result = Stringify::stringifyFloat(1.5e-10);
        $this->assertMatchesRegularExpression('/[eE]/', $result);
    }

    /**
     * Test stringifying floats gives the exact value, not one rounded to the `precision` ini setting.
     */
    public function testStringifyFloatIsExact(): void
    {
        $this->assertSame('0.30000000000000004', Stringify::stringifyFloat(0.1 + 0.2));
        $this->assertSame('1.2345678901234568E+17', Stringify::stringifyFloat(123456789012345678.0));
        $this->assertSame('1000000000000000.0', Stringify::stringifyFloat(1e15));

        // The result converts back to the same float.
        $values = [
            0.1 + 0.2,
            M_PI,
            -1e-300,
            5e-324,
            PHP_FLOAT_MAX,
        ];
        foreach ($values as $value) {
            $this->assertSame($value, (float) Stringify::stringifyFloat($value));
        }
    }

    /**
     * Test stringifying special float values.
     */
    public function testStringifyFloatSpecial(): void
    {
        $this->assertSame('NAN', Stringify::stringifyFloat(NAN));
        $this->assertSame('INF', Stringify::stringifyFloat(INF));
        $this->assertSame('-INF', Stringify::stringifyFloat(-INF));
        $this->assertSame('-0.0', Stringify::stringifyFloat(-0.0));
    }

    /**
     * Test that stringify() correctly dispatches floats.
     */
    public function testStringifyFloatIntegration(): void
    {
        $this->assertSame('5.0', Stringify::stringify(5.0));
        $this->assertSame('3.14', Stringify::stringify(3.14));
        $this->assertSame('NAN', Stringify::stringify(NAN));
        $this->assertSame('INF', Stringify::stringify(INF));
    }

    #endregion

    #region Method stringifyString() tests.

    /**
     * Test stringifying string values via stringify() dispatch.
     */
    public function testStringifyString(): void
    {
        $this->assertSame("'hello'", Stringify::stringify('hello'));
        $this->assertSame("''", Stringify::stringify(''));
        $this->assertSame("'hello\nworld'", Stringify::stringify("hello\nworld"));
        $this->assertSame("'hello\tworld'", Stringify::stringify("hello\tworld"));
        $this->assertSame("'say \"hello\"'", Stringify::stringify('say "hello"'));
    }

    /**
     * Test stringifyString() directly, including escaping of backslashes and single quotes.
     */
    public function testStringifyStringDirect(): void
    {
        // Basic string.
        $this->assertSame("'hello'", Stringify::stringifyString('hello'));

        // Single quotes are escaped; double quotes are not.
        $this->assertSame("'it\\'s'", Stringify::stringifyString("it's"));

        // Backslashes are escaped.
        $this->assertSame("'foo\\\\bar'", Stringify::stringifyString("foo\\bar"));

        // Backslash immediately before a double quote.
        $this->assertSame("'it\\\\\"s'", Stringify::stringifyString('it\\"s'));
    }

    /**
     * Test stringifyString() doesn't guess the encoding of a non-UTF-8 string, even when mb_detect_order() would
     * identify it. The bytes are escaped, so the output is the same whatever the detect order.
     */
    public function testStringifyStringIgnoresDetectOrder(): void
    {
        $originalOrder = mb_detect_order();
        if ($originalOrder === []) {
            $originalOrder = ['ASCII', 'UTF-8'];
        }
        mb_detect_order(['ASCII', 'UTF-8', 'ISO-8859-1']);

        try {
            // 'café' encoded as Latin-1 (0xe9 is é in ISO-8859-1).
            $this->assertSame('"caf\xE9"', Stringify::stringifyString("caf\xe9"));
        } finally {
            mb_detect_order($originalOrder);
        }
    }

    /**
     * Test stringifyString() renders a string it can't convert to UTF-8 as a double-quoted string with escapes.
     */
    public function testStringifyStringBinary(): void
    {
        $this->assertSame('"\xFE\xFF"', Stringify::stringifyString("\xfe\xff"));

        // Latin-1 'café', with the default detect order, which can't identify it.
        $this->assertSame('"caf\xE9"', Stringify::stringifyString("caf\xe9"));

        // Valid UTF-8 sequences are kept; a truncated one is escaped byte by byte.
        $this->assertSame('"Ω\xFF\xE2\x82"', Stringify::stringifyString("Ω\xff\xe2\x82"));

        // Backslashes, double quotes, dollar signs and control characters are escaped.
        $this->assertSame('"a\\\\b\"c\$d\x0A\x00\xFF"', Stringify::stringifyString("a\\b\"c\$d\n\0\xff"));
    }

    /**
     * Test the double-quoted rendering of a binary string parses back to the original bytes.
     */
    public function testStringifyStringBinaryRoundTrip(): void
    {
        $values = [
            "\xfe\xff",
            "caf\xe9 \$x \"q\" \\ Ω \xe2\x82",
            "line1\nline2\t\x7f\xff",
            random_bytes(64) . "\xff",
        ];
        foreach ($values as $value) {
            $rendered = Stringify::stringifyString($value);
            $this->assertSame($value, eval("return $rendered;"));
        }
    }

    /**
     * Test stringify(), prepEx() and abbrev() handle binary strings without throwing.
     */
    public function testStringifyStringBinaryInOtherMethods(): void
    {
        $this->assertSame('"\xFF\x81"', Stringify::stringify("\xff\x81"));
        $this->assertSame('Bad: "\xFF\x81".', Stringify::prepEx('Bad: ?.', "\xff\x81"));

        // A truncated binary string closes with its own double quote. Truncation can split an escape, as abbreviated
        // output isn't meant to be parseable.
        $result = Stringify::abbrev(str_repeat("\xff", 20), 10);
        $this->assertSame('"\xFF\xF…"', $result);
    }

    /**
     * Test that Unicode characters in strings are preserved, not escaped.
     */
    public function testStringifyStringUnicode(): void
    {
        $this->assertSame("'Ω'", Stringify::stringify('Ω'));
        $this->assertSame("'café'", Stringify::stringify('café'));
        $this->assertSame("'日本語'", Stringify::stringify('日本語'));
    }

    #endregion

    #region Method stringifyArray() tests.

    /**
     * Test stringifying simple lists without pretty print.
     */
    public function testStringifyListArray(): void
    {
        $this->assertSame('[]', Stringify::stringify([]));
        $this->assertSame('[1, 2, 3]', Stringify::stringify([1, 2, 3]));
        $this->assertSame("[1, 'hello', true, null]", Stringify::stringify([1, 'hello', true, null]));
        $this->assertSame('[1.5, 2.0, 3.14]', Stringify::stringify([1.5, 2.0, 3.14]));
    }

    /**
     * Test stringifying dictionaries without pretty print.
     */
    public function testStringifyAssociativeArray(): void
    {
        // Simple dictionary.
        $this->assertSame("['name' => 'John', 'age' => 30]", Stringify::stringify([
            'name' => 'John',
            'age'  => 30,
        ]));

        // Non-sequential integer keys.
        $this->assertSame("[1 => 'a', 3 => 'b', 5 => 'c']", Stringify::stringify([
            1 => 'a',
            3 => 'b',
            5 => 'c',
        ]));

        // Mixed key types.
        $this->assertSame("['key' => 'value', 0 => 42]", Stringify::stringify([
            'key' => 'value',
            0     => 42,
        ]));
    }

    /**
     * Test stringifying nested arrays without pretty print.
     */
    public function testStringifyNestedArray(): void
    {
        // Nested list.
        $this->assertSame('[[1, 2], [3, 4]]', Stringify::stringify([
            [1, 2],
            [3, 4],
        ]));

        // Nested dictionary.
        $this->assertSame("['user' => ['name' => 'John', 'age' => 30]]", Stringify::stringify([
            'user' => [
                'name' => 'John',
                'age'  => 30,
            ],
        ]));

        // Mixed nesting.
        $this->assertSame("[1, ['a', 'b'], 3]", Stringify::stringify([
            1,
            ['a', 'b'],
            3,
        ]));
    }

    /**
     * Test that a short scalar list fits on one line when pretty printing.
     */
    public function testStringifyArrayPrettyPrintShortList(): void
    {
        $this->assertSame('[1, 2, 3]', Stringify::stringify([1, 2, 3], StringifyOptions::pretty()));
    }

    /**
     * Test that a long scalar list uses grid format when pretty printing.
     */
    public function testStringifyArrayPrettyPrintGrid(): void
    {
        $maxLineLength = StringifyOptions::DEFAULT_MAX_LINE_LENGTH;

        // Create a list long enough to exceed 120 chars and trigger grid format.
        $list = range(1, 50);
        $result = Stringify::stringify($list, StringifyOptions::pretty());

        // Should be multiline.
        $this->assertStringContainsString("\n", $result);

        // Should start with [ and end with ].
        $this->assertStringStartsWith('[', $result);
        $this->assertStringEndsWith(']', $result);

        // Each line (except first/last) should be indented and less than max length.
        $lines = explode("\n", $result);
        for ($i = 1; $i < count($lines) - 1; $i++) {
            $this->assertStringStartsWith('    ', $lines[$i]);
            $this->assertLessThanOrEqual($maxLineLength, strlen($lines[$i]));
        }
    }

    /**
     * Test pretty-printed dictionary with aligned keys.
     */
    public function testStringifyArrayPrettyPrintDictionary(): void
    {
        $result = Stringify::stringify([
            'name' => 'John',
            'age'  => 30,
        ], StringifyOptions::pretty());

        $expected = "[\n    'name' => 'John',\n    'age'  => 30,\n]";
        $this->assertSame($expected, $result);
    }

    /**
     * Test pretty-printed list of arrays uses one item per line.
     */
    public function testStringifyArrayPrettyPrintListOfArrays(): void
    {
        $result = Stringify::stringify([
            [1, 2],
            [3, 4],
        ], StringifyOptions::pretty());

        // Should be single-line.
        $this->assertSame("[\n    [1, 2],\n    [3, 4],\n]", $result);
    }

    /**
     * Test that a list containing an associative array uses one-per-line format.
     */
    public function testStringifyArrayPrettyPrintMultilineItem(): void
    {
        $result = Stringify::stringify([
            [
                'name' => 'John',
                'age'  => 30,
            ],
            42,
        ], StringifyOptions::pretty());

        $expected = "[\n"
            . "    [\n"
            . "        'name' => 'John',\n"
            . "        'age'  => 30,\n"
            . "    ],\n"
            . "    42,\n"
            . ']';
        $this->assertSame($expected, $result);
    }

    /**
     * Test that a list with long items falls back to one-per-line format (no grid padding).
     */
    public function testStringifyArrayPrettyPrintLongItems(): void
    {
        $uuids = [
            'c9e35c00-0f1e-4804-b5fe-6c4c9718db60', 'd2aee4c5-a7f7-4018-a635-c3f4c317033e',
            'd266963a-c4e0-4255-a97d-f070e51fcb5e',
        ];

        // maxLineLength of 40 - items are too wide for 2 per line, so grid is skipped.
        $result = Stringify::stringifyArray($uuids, StringifyOptions::pretty(maxLineLength: 40));

        $expected = "[\n"
            . "    'c9e35c00-0f1e-4804-b5fe-6c4c9718db60',\n"
            . "    'd2aee4c5-a7f7-4018-a635-c3f4c317033e',\n"
            . "    'd266963a-c4e0-4255-a97d-f070e51fcb5e',\n"
            . ']';
        $this->assertSame($expected, $result);
    }

    /**
     * Test that circular references in arrays.
     */
    public function testStringifyArrayCircularReference(): void
    {
        $array = [
            'foo' => 'bar',
        ];
        $array['self'] = &$array;

        $result = Stringify::stringify($array);

        $this->assertStringContainsString(RECURSION, $result);
        $this->assertStringNotContainsString("'" . RECURSION . "'", $result);
    }

    #endregion

    #region Method stringifyResource() tests.

    /**
     * Test stringifying an open resource.
     */
    public function testStringifyResource(): void
    {
        $resource = fopen('php://memory', 'rb');
        $this->assertIsResource($resource);

        $this->assertMatchesRegularExpression('/^resource #\d+ \(stream\)$/', Stringify::stringify($resource));

        fclose($resource);
    }

    /**
     * Test stringifying a closed resource.
     */
    public function testStringifyClosedResource(): void
    {
        $resource = fopen('php://memory', 'rb');
        $this->assertIsResource($resource);
        fclose($resource);

        $this->assertMatchesRegularExpression(
            '/^resource #\d+ \(closed\)$/',
            Stringify::stringifyResource($resource)
        );
    }

    /**
     * Test stringifying resource with non-resource value throws InvalidArgumentException.
     */
    public function testStringifyResourceWithNonResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value type: string. Must be a resource.');
        Stringify::stringifyResource('not a resource');
    }

    #endregion

    #region Method stringifyEnum() tests.

    /**
     * Test stringifying enum cases.
     */
    public function testStringifyEnum(): void
    {
        // Test that stringify() dispatches enums correctly.
        $result = Stringify::stringify(TestEnum::Foo);
        $this->assertSame('Mossy2100\Core\Tests\Fixtures\TestEnum::Foo', $result);
    }

    /**
     * Test stringifyEnum() directly.
     */
    public function testStringifyEnumDirect(): void
    {
        $this->assertSame('Mossy2100\Core\Tests\Fixtures\TestEnum::Foo', Stringify::stringifyEnum(TestEnum::Foo));
        $this->assertSame('Mossy2100\Core\Tests\Fixtures\TestEnum::Bar', Stringify::stringifyEnum(TestEnum::Bar));
    }

    /**
     * Test stringifying backed enum cases.
     */
    public function testStringifyBackedEnum(): void
    {
        $this->assertSame(
            'Mossy2100\Core\Tests\Fixtures\TestBackedEnum::Alpha',
            Stringify::stringifyEnum(TestBackedEnum::Alpha)
        );
    }

    #endregion

    #region Method stringifyClosure() tests.

    /**
     * Test stringifyClosure() returns an anonymous closure's name, which gives the method and line it's defined on.
     */
    public function testStringifyClosure(): void
    {
        $closure = static function (int $x): int {
            $doubled = $x * 2;
            return $doubled + 1;
        };
        $expected = '{closure:' . __METHOD__ . '():' . (__LINE__ - 4) . '}';

        $this->assertSame($expected, Stringify::stringifyClosure($closure));
    }

    /**
     * Test stringifyClosure() with an arrow function.
     */
    public function testStringifyClosureArrow(): void
    {
        $arrow = static fn (float $x): float => $x * $x;
        $expected = '{closure:' . __METHOD__ . '():' . (__LINE__ - 1) . '}';

        $this->assertSame($expected, Stringify::stringifyClosure($arrow));
    }

    /**
     * Test two closures that start on one line both get that line's name, without an error. Reading their source
     * couldn't tell them apart.
     */
    public function testStringifyClosureTwoOnOneLine(): void
    {
        $pair = static fn (Closure ...$closures): array => $closures;
        $closures = $pair(static fn (int $x): int => $x + 1, static fn (int $x): int => $x * 2);
        $expected = '{closure:' . __METHOD__ . '():' . (__LINE__ - 1) . '}';

        $this->assertSame($expected, Stringify::stringifyClosure($closures[0]));
        $this->assertSame($expected, Stringify::stringifyClosure($closures[1]));
    }

    /**
     * Test a closure defined inside another closure is named after the outer one.
     */
    public function testStringifyClosureNested(): void
    {
        $outer = static fn (): Closure => static fn (): int => 1;
        $line = __LINE__ - 1;
        $outerName = '{closure:' . __METHOD__ . "():$line}";

        $this->assertSame($outerName, Stringify::stringifyClosure($outer));
        $this->assertSame("{closure:$outerName:$line}", Stringify::stringifyClosure($outer()));
    }

    /**
     * Test first-class callables are shown in that syntax, with the class for a method.
     */
    public function testStringifyClosureFirstClassCallable(): void
    {
        $this->assertSame('strlen(...)', Stringify::stringifyClosure(strlen(...)));
        $this->assertSame('Mossy2100\Core\Stringify::abbrev(...)', Stringify::stringifyClosure(Stringify::abbrev(...)));
    }

    /**
     * Test stringify(), abbrev() and prepEx() show a closure by its name.
     */
    public function testStringifyClosureInOtherMethods(): void
    {
        $closure = static fn (): int => 1;
        $name = '{closure:' . __METHOD__ . '():' . (__LINE__ - 1) . '}';

        $this->assertSame($name, Stringify::stringify($closure));
        $this->assertSame("[$name]", Stringify::stringify([$closure]));
        $this->assertSame($name, Stringify::abbrev($closure, 200));
        $this->assertSame("Bad: $name.", Stringify::prepEx('Bad: ?.', $closure));
    }

    #endregion

    #region Method stringifyObject() tests.

    /**
     * Test stringifying simple objects.
     */
    public function testStringifyObject(): void
    {
        $obj = new class {
            public string $name = 'John';

            public int $age = 30;
        };

        $result = Stringify::stringify($obj);
        $this->assertStringContainsString('class@anonymous', $result);
        $this->assertStringContainsString("+name => 'John'", $result);
        $this->assertStringContainsString('+age => 30', $result);
        $this->assertStringEndsWith('}', $result);
    }

    /**
     * Test stringifying objects with different visibility modifiers.
     */
    public function testStringifyObjectVisibility(): void
    {
        $obj = new class {
            public string $publicProp = 'public';

            protected string $protectedProp = 'protected';

            // @phpstan-ignore-next-line
            private string $privateProp = 'private';
        };

        $result = Stringify::stringify($obj);

        $this->assertStringContainsString("+publicProp => 'public'", $result);
        $this->assertStringContainsString("#protectedProp => 'protected'", $result);
        $this->assertStringContainsString("-privateProp => 'private'", $result);
    }

    /**
     * Test stringifying empty objects.
     */
    public function testStringifyEmptyObject(): void
    {
        $obj = new class {
        };

        $result = Stringify::stringify($obj);
        $this->assertMatchesRegularExpression('/^class@anonymous #\d+ {}$/', $result);
    }

    /**
     * Test stringifying objects with pretty print.
     */
    public function testStringifyObjectPrettyPrint(): void
    {
        $obj = new class {
            public string $name = 'John';

            public int $age = 30;
        };

        $result = Stringify::stringify($obj, StringifyOptions::pretty());

        $this->assertMatchesRegularExpression(
            '/^class@anonymous #\d+ \{\n\s+\+name\s+=> \'John\',\n\s+\+age\s+=> 30,\n\}$/',
            $result
        );
    }

    /**
     * Test stringifying nested structures with objects and arrays.
     */
    public function testStringifyComplexNesting(): void
    {
        $obj = new class {
            /** @var array<int, int> */
            public array $items = [1, 2, 3];

            public string $name = 'test';
        };

        $array = [
            'object'  => $obj,
            'numbers' => [4, 5, 6],
        ];

        $result = Stringify::stringify($array);
        $this->assertStringContainsString("'object' => class@anonymous", $result);
        $this->assertStringContainsString('+items => [1, 2, 3]', $result);
        $this->assertStringContainsString("+name => 'test'", $result);
        $this->assertStringContainsString("'numbers' => [4, 5, 6]", $result);
    }

    /**
     * Test that an object with a direct self-reference is stringified with the RECURSION marker,
     * instead of recursing forever.
     */
    public function testStringifyObjectDirectSelfReference(): void
    {
        $obj = new class {
            public mixed $self = null;
        };
        $obj->self = $obj;

        $result = Stringify::stringify($obj);

        $this->assertStringContainsString('+self => ' . RECURSION, $result);
    }

    /**
     * Test that mutual (indirect) object-to-object recursion is stringified with the RECURSION
     * marker, instead of recursing forever. Arrays::removeRecursion() alone can't catch this, since
     * it only inspects array values, never object properties.
     */
    public function testStringifyObjectMutualReference(): void
    {
        $a = new class {
            public mixed $other = null;
        };
        $b = new class {
            public mixed $other = null;
        };
        $a->other = $b;
        $b->other = $a;

        $result = Stringify::stringify($a);

        $this->assertStringContainsString(RECURSION, $result);
    }

    /**
     * Test that a cyclic reference reached via a nested array (object -> array -> same object) is
     * still detected.
     */
    public function testStringifyObjectCycleViaArray(): void
    {
        $obj = new class {
            /** @var array<string, mixed> */
            public array $items = [];
        };
        $obj->items = [
            'self' => $obj,
        ];

        $result = Stringify::stringify($obj);

        $this->assertStringContainsString(RECURSION, $result);
    }

    /**
     * Test that the same object referenced by two sibling (non-cyclic) properties is NOT mistaken
     * for recursion - it should render in full both times, since it's a shared reference, not a
     * cycle.
     */
    public function testStringifyObjectSharedReferenceNotRecursive(): void
    {
        $shared = new class {
            public int $value = 42;
        };
        $obj = new class {
            public mixed $a = null;

            public mixed $b = null;
        };
        $obj->a = $shared;
        $obj->b = $shared;

        $result = Stringify::stringify($obj);

        $this->assertStringNotContainsString(RECURSION, $result);
        $this->assertSame(2, substr_count($result, '+value => 42'));
    }

    /**
     * Test calling stringifyObject() directly, compact by default and pretty with options.
     */
    public function testStringifyObjectDirect(): void
    {
        $obj = new stdClass();
        $obj->a = 1;
        $obj->b = 'x';
        $prefix = 'stdClass #' . spl_object_id($obj);

        $this->assertSame("$prefix {+a => 1, +b => 'x'}", Stringify::stringifyObject($obj));
        $this->assertSame(
            "$prefix {\n    +a => 1,\n    +b => 'x',\n}",
            Stringify::stringifyObject($obj, StringifyOptions::pretty())
        );
    }

    #endregion
}
