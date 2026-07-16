<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests;

use DomainException;
use InvalidArgumentException;
use OceanMoon\Core\Stringify;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use const OceanMoon\Core\Globals\RECURSION;

/**
 * Test class for Stringify utility class.
 */
#[CoversClass(Stringify::class)]
final class StringifyTest extends TestCase
{
    #region Scalar types

    /**
     * Test stringifying null values.
     */
    public function testStringifyNull(): void
    {
        $this->assertSame('null', Stringify::stringify(null));
    }

    /**
     * Test stringifying boolean values.
     */
    public function testStringifyBool(): void
    {
        $this->assertSame('true', Stringify::stringify(true));
        $this->assertSame('false', Stringify::stringify(false));
    }

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

    /**
     * Test stringifying string values via stringify() dispatch.
     */
    public function testStringifyString(): void
    {
        $this->assertSame('"hello"', Stringify::stringify('hello'));
        $this->assertSame('""', Stringify::stringify(''));
        $this->assertSame('"hello\nworld"', Stringify::stringify("hello\nworld"));
        $this->assertSame('"hello\tworld"', Stringify::stringify("hello\tworld"));
        $this->assertSame('"say \"hello\""', Stringify::stringify('say "hello"'));
    }

    /**
     * Test stringifyString() directly, including escaping of backslashes and single quotes.
     */
    public function testStringifyStringDirect(): void
    {
        // Basic string.
        $this->assertSame('"hello"', Stringify::stringifyString('hello'));

        // Single quotes are not escaped.
        $this->assertSame('"it\'s"', Stringify::stringifyString("it's"));

        // Backslashes are escaped.
        $this->assertSame('"foo\\\\bar"', Stringify::stringifyString("foo\\bar"));

        // Backslash immediately before a double quote.
        $this->assertSame('"it\\\\\\"s"', Stringify::stringifyString('it\\"s'));
    }

    /**
     * Test stringifyString() converts non-UTF-8 input to UTF-8 when encoding is detectable.
     */
    public function testStringifyStringNonUtf8Conversion(): void
    {
        // Add ISO-8859-1 to the detect order so mb_detect_encoding can find it.
        $originalOrder = mb_detect_order();
        if (empty($originalOrder)) {
            $originalOrder = ['ASCII', 'UTF-8', 'ISO-8859-1'];
        }
        mb_detect_order(['ASCII', 'UTF-8', 'ISO-8859-1']);

        try {
            // 'café' encoded as Latin-1 (0xe9 is é in ISO-8859-1).
            $latin1 = "caf\xe9";
            $this->assertSame('"café"', Stringify::stringifyString($latin1));
        } finally {
            mb_detect_order($originalOrder);
        }
    }

    /**
     * Test stringifyString() throws DomainException when encoding cannot be detected.
     */
    public function testStringifyStringUndetectableEncoding(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('String encoding is not UTF-8 and could not be detected.');
        Stringify::stringifyString("\xfe\xff");
    }

    /**
     * Test that Unicode characters in strings are preserved, not escaped.
     */
    public function testStringifyStringUnicode(): void
    {
        $this->assertSame('"Ω"', Stringify::stringify('Ω'));
        $this->assertSame('"café"', Stringify::stringify('café'));
        $this->assertSame('"日本語"', Stringify::stringify('日本語'));
    }

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

    #region Arrays — non-pretty

    /**
     * Test stringifying simple lists without pretty print.
     */
    public function testStringifyListArray(): void
    {
        $this->assertSame('[]', Stringify::stringify([]));
        $this->assertSame('[1, 2, 3]', Stringify::stringify([1, 2, 3]));
        $this->assertSame('[1, "hello", true, null]', Stringify::stringify([1, 'hello', true, null]));
        $this->assertSame('[1.5, 2.0, 3.14]', Stringify::stringify([1.5, 2.0, 3.14]));
    }

    /**
     * Test stringifying dictionaries without pretty print.
     */
    public function testStringifyAssociativeArray(): void
    {
        // Simple dictionary.
        $this->assertSame('["name" => "John", "age" => 30]', Stringify::stringify([
            'name' => 'John',
            'age'  => 30,
        ]));

        // Non-sequential integer keys.
        $this->assertSame('[1 => "a", 3 => "b", 5 => "c"]', Stringify::stringify([
            1 => 'a',
            3 => 'b',
            5 => 'c',
        ]));

        // Mixed key types.
        $this->assertSame('["key" => "value", 0 => 42]', Stringify::stringify([
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
        $this->assertSame('["user" => ["name" => "John", "age" => 30]]', Stringify::stringify([
            'user' => [
                'name' => 'John',
                'age'  => 30,
            ],
        ]));

        // Mixed nesting.
        $this->assertSame('[1, ["a", "b"], 3]', Stringify::stringify([
            1,
            ['a', 'b'],
            3,
        ]));
    }

    #endregion

    #region Arrays — pretty print

    /**
     * Test that a short scalar list fits on one line when pretty printing.
     */
    public function testStringifyArrayPrettyPrintShortList(): void
    {
        $this->assertSame('[1, 2, 3]', Stringify::stringify([1, 2, 3], true));
    }

    /**
     * Test that a long scalar list uses grid format when pretty printing.
     */
    public function testStringifyArrayPrettyPrintGrid(): void
    {
        $maxLineLength = Stringify::getMaxLineLength();

        // Create a list long enough to exceed 120 chars and trigger grid format.
        $list = range(1, 50);
        $result = Stringify::stringify($list, true);

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
        ], true);

        $expected = "[\n    \"name\" => \"John\",\n    \"age\"  => 30,\n]";
        $this->assertSame($expected, $result);
    }

    /**
     * Test pretty-printed list uses one item per line when it doesn't wrap.
     */
    public function testStringifyArrayPrettyPrintNonScalarList(): void
    {
        $result = Stringify::stringify([
            [1, 2],
            [3, 4],
        ], true);

        // Should be single-line.
        $this->assertSame('[[1, 2], [3, 4]]', $result);
    }

    /**
     * Test that a list containing a multiline item (e.g. an associative array) uses one-per-line format, since the
     * grid format only applies when every item's stringified form is itself single-line.
     */
    public function testStringifyArrayPrettyPrintMultilineItem(): void
    {
        $result = Stringify::stringify([
            [
                'name' => 'John',
                'age'  => 30,
            ],
            42,
        ], true);

        $expected = "[\n"
            . "    [\n"
            . "        \"name\" => \"John\",\n"
            . "        \"age\"  => 30,\n"
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

        // maxLineLength of 40 — items are too wide for 2 per line, so grid is skipped.
        Stringify::setMaxLineLength(40);
        try {
            $result = Stringify::stringifyArray($uuids, true);

            $expected = "[\n"
                . "    \"c9e35c00-0f1e-4804-b5fe-6c4c9718db60\",\n"
                . "    \"d2aee4c5-a7f7-4018-a635-c3f4c317033e\",\n"
                . "    \"d266963a-c4e0-4255-a97d-f070e51fcb5e\",\n"
                . ']';
            $this->assertSame($expected, $result);
        } finally {
            Stringify::resetDefaults();
        }
    }

    /**
     * Test that circular references in arrays throw DomainException.
     */
    public function testStringifyArrayCircularReference(): void
    {
        $array = [
            'foo' => 'bar',
        ];
        $array['self'] = &$array;

        $result = Stringify::stringify($array);

        $this->assertStringContainsString(RECURSION, $result);
        $this->assertStringNotContainsString('"' . RECURSION . '"', $result);
    }

    #endregion

    #region Resources

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
        $this->expectExceptionMessage('Value is not a resource.');
        Stringify::stringifyResource('not a resource');
    }

    #endregion

    #region Enums

    /**
     * Test stringifying enum cases.
     */
    public function testStringifyEnum(): void
    {
        // Test that stringify() dispatches enums correctly.
        $result = Stringify::stringify(TestEnum::Foo);
        $this->assertSame('OceanMoon\Core\Tests\TestEnum::Foo', $result);
    }

    /**
     * Test stringifyEnum() directly.
     */
    public function testStringifyEnumDirect(): void
    {
        $this->assertSame('OceanMoon\Core\Tests\TestEnum::Foo', Stringify::stringifyEnum(TestEnum::Foo));
        $this->assertSame('OceanMoon\Core\Tests\TestEnum::Bar', Stringify::stringifyEnum(TestEnum::Bar));
    }

    /**
     * Test stringifying backed enum cases.
     */
    public function testStringifyBackedEnum(): void
    {
        $this->assertSame(
            'OceanMoon\Core\Tests\TestBackedEnum::Alpha',
            Stringify::stringifyEnum(TestBackedEnum::Alpha)
        );
    }

    #endregion

    #region Objects

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
        $this->assertStringContainsString('+name => "John"', $result);
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

        $this->assertStringContainsString('+publicProp => "public"', $result);
        $this->assertStringContainsString('#protectedProp => "protected"', $result);
        $this->assertStringContainsString('-privateProp => "private"', $result);
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

        $result = Stringify::stringify($obj, true);

        $this->assertMatchesRegularExpression(
            '/^class@anonymous #\d+ \{\n\s+\+name\s+=> \"John\",\n\s+\+age\s+=> 30,\n\}$/',
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
        $this->assertStringContainsString('"object" => class@anonymous', $result);
        $this->assertStringContainsString('+items => [1, 2, 3]', $result);
        $this->assertStringContainsString('+name => "test"', $result);
        $this->assertStringContainsString('"numbers" => [4, 5, 6]', $result);
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
     * for recursion — it should render in full both times, since it's a shared reference, not a
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

    #endregion

    #region abbrev

    /**
     * Test abbrev method with short strings.
     */
    public function testAbbrevShortString(): void
    {
        $this->assertSame('"hello"', Stringify::abbrev('hello'));
        $this->assertSame('42', Stringify::abbrev(42));
        $this->assertSame('true', Stringify::abbrev(true));
    }

    /**
     * Test abbrev method with long strings.
     */
    public function testAbbrevLongString(): void
    {
        $longString = 'this is a very long string that should be truncated';
        $result = Stringify::abbrev($longString, 20);

        $this->assertLessThanOrEqual(20, mb_strlen($result));
        $this->assertStringEndsWith('…"', $result);
    }

    /**
     * Test abbrev method with arrays.
     */
    public function testAbbrevArray(): void
    {
        $array = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $result = Stringify::abbrev($array, 20);

        $this->assertLessThanOrEqual(20, mb_strlen($result));
        $this->assertStringEndsWith('…]', $result);
    }

    /**
     * Test abbrev method with maximum length too small.
     */
    public function testAbbrevMaxLenTooSmall(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid maximum string length: 2. Must be at least 3.');
        Stringify::abbrev(123, 2);
    }

    /**
     * Test abbrev with an object whose class name alone is longer than $maxLen: the class name must
     * never be truncated, so the worst case is "ClassName".
     */
    public function testAbbrevObjectNeverTruncatesClassName(): void
    {
        $obj = new StringifyAbbrevAnObjectWithAVeryVeryLongClassNameIndeed();

        $result = Stringify::abbrev($obj, 10);

        $this->assertSame(StringifyAbbrevAnObjectWithAVeryVeryLongClassNameIndeed::class, $result);
    }

    /**
     * Test abbrev with an object where $maxLen comfortably covers the class name: normal truncation
     * (based on $maxLen, not the class-name guard) still applies.
     */
    public function testAbbrevObjectRegularTruncation(): void
    {
        $obj = new class {
            public int $a = 1;

            public int $b = 2;

            public int $c = 3;
        };

        $result = Stringify::abbrev($obj, 30);

        $this->assertLessThanOrEqual(30, mb_strlen($result));
        $this->assertStringEndsWith('…}', $result);
    }

    #endregion

    #region Configuration

    /**
     * Test getIndent() returns the default value initially.
     */
    public function testGetIndentDefault(): void
    {
        $this->assertSame(Stringify::DEFAULT_INDENT, Stringify::getIndent());
    }

    /**
     * Test setIndent() changes the indent value.
     */
    public function testSetIndent(): void
    {
        Stringify::setIndent(2);
        try {
            $this->assertSame(2, Stringify::getIndent());

            // Verify it affects pretty-printed output.
            $result = Stringify::stringify([
                'a' => 1,
                'b' => 2,
            ], true);
            $expected = "[\n  \"a\" => 1,\n  \"b\" => 2,\n]";
            $this->assertSame($expected, $result);
        } finally {
            Stringify::resetDefaults();
        }
    }

    /**
     * Test setIndent() throws for zero.
     */
    public function testSetIndentZeroThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid indent:');
        Stringify::setIndent(0);
    }

    /**
     * Test setIndent() throws for negative value.
     */
    public function testSetIndentNegativeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid indent:');
        Stringify::setIndent(-1);
    }

    /**
     * Test getMaxLineLength() returns the default value initially.
     */
    public function testGetMaxLineLengthDefault(): void
    {
        $this->assertSame(Stringify::DEFAULT_MAX_LINE_LENGTH, Stringify::getMaxLineLength());
    }

    /**
     * Test setMaxLineLength() changes the max line length.
     */
    public function testSetMaxLineLength(): void
    {
        Stringify::setMaxLineLength(60);
        try {
            $this->assertSame(60, Stringify::getMaxLineLength());
        } finally {
            Stringify::resetDefaults();
        }
    }

    /**
     * Test setMaxLineLength() throws for zero.
     */
    public function testSetMaxLineLengthZeroThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid max line length:');
        Stringify::setMaxLineLength(0);
    }

    /**
     * Test setMaxLineLength() throws for negative value.
     */
    public function testSetMaxLineLengthNegativeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid max line length:');
        Stringify::setMaxLineLength(-10);
    }

    /**
     * Test resetDefaults() restores both values.
     */
    public function testResetDefaults(): void
    {
        Stringify::setIndent(8);
        Stringify::setMaxLineLength(40);
        Stringify::resetDefaults();

        $this->assertSame(Stringify::DEFAULT_INDENT, Stringify::getIndent());
        $this->assertSame(Stringify::DEFAULT_MAX_LINE_LENGTH, Stringify::getMaxLineLength());
    }

    #endregion
}

/**
 * Test enum for stringifyEnum tests.
 */
enum TestEnum
{
    case Foo;

    case Bar;
}

/**
 * Backed test enum for stringifyEnum tests.
 */
enum TestBackedEnum: string
{
    case Alpha = 'a';

    case Beta = 'b';
}

/**
 * Test fixture with a deliberately long class name, for abbrev()'s class-name-preservation tests.
 */
class StringifyAbbrevAnObjectWithAVeryVeryLongClassNameIndeed
{
    public int $a = 1;
}
