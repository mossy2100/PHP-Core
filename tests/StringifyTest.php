<?php

declare(strict_types=1);

namespace Galaxon\Core\Tests;

use Galaxon\Core\Stringify;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TypeError;
use ValueError;

/**
 * Test class for Stringify utility class.
 */
#[CoversClass(Stringify::class)]
final class StringifyTest extends TestCase
{
    /**
     * Test stringifying null values.
     */
    public function testStringifyNull(): void
    {
        // Test that null is encoded as JSON null.
        $this->assertSame('null', Stringify::stringify(null));
    }

    /**
     * Test stringifying boolean values.
     */
    public function testStringifyBool(): void
    {
        // Test that true is encoded as JSON true.
        $this->assertSame('true', Stringify::stringify(true));

        // Test that false is encoded as JSON false.
        $this->assertSame('false', Stringify::stringify(false));
    }

    /**
     * Test stringifying integer values.
     */
    public function testStringifyInt(): void
    {
        // Test positive integer.
        $this->assertSame('0', Stringify::stringify(0));
        $this->assertSame('42', Stringify::stringify(42));

        // Test negative integer.
        $this->assertSame('-17', Stringify::stringify(-17));

        // Test large integer.
        $this->assertSame('1000000', Stringify::stringify(1000000));
    }

    /**
     * Test stringifying string values.
     */
    public function testStringifyString(): void
    {
        // Test simple string.
        $this->assertSame('"hello"', Stringify::stringify('hello'));

        // Test empty string.
        $this->assertSame('""', Stringify::stringify(''));

        // Test string with special characters.
        $this->assertSame('"hello\nworld"', Stringify::stringify("hello\nworld"));
        $this->assertSame('"hello\tworld"', Stringify::stringify("hello\tworld"));

        // Test string with quotes.
        $this->assertSame('"say \\"hello\\""', Stringify::stringify('say "hello"'));
    }

    /**
     * Test stringifying float values.
     */
    public function testStringifyFloat(): void
    {
        // Test regular float with decimal.
        $this->assertSame('3.14', Stringify::stringifyFloat(3.14));
        $this->assertSame('-2.5', Stringify::stringifyFloat(-2.5));

        // Test float that looks like integer gets .0 appended.
        $this->assertSame('5.0', Stringify::stringifyFloat(5.0));
        $this->assertSame('-10.0', Stringify::stringifyFloat(-10.0));
        $this->assertSame('0.0', Stringify::stringifyFloat(0.0));

        // Test float with exponent notation (already distinguishable from int).
        $result = Stringify::stringifyFloat(1.5e100);
        $this->assertMatchesRegularExpression('/[eE]/', $result);

        // Test very small float with exponent notation.
        $result = Stringify::stringifyFloat(1.5e-10);
        $this->assertMatchesRegularExpression('/[eE]/', $result);
    }

    /**
     * Test stringifying special float values.
     */
    public function testStringifyFloatSpecial(): void
    {
        // Test NaN.
        $this->assertSame('NaN', Stringify::stringifyFloat(NAN));

        // Test positive infinity.
        $this->assertSame('∞', Stringify::stringifyFloat(INF));

        // Test negative infinity.
        $this->assertSame('-∞', Stringify::stringifyFloat(-INF));
    }

    /**
     * Test stringifying simple arrays (lists).
     */
    public function testStringifyList(): void
    {
        // Test empty array.
        $this->assertSame('[]', Stringify::stringify([]));

        // Test simple list.
        $this->assertSame('[1, 2, 3]', Stringify::stringify([1, 2, 3]));

        // Test list with mixed types.
        $this->assertSame('[1, "hello", true, null]', Stringify::stringify([1, 'hello', true, null]));

        // Test list with floats.
        $this->assertSame('[1.5, 2.0, 3.14]', Stringify::stringify([1.5, 2.0, 3.14]));
    }

    /**
     * Test stringifying associative arrays.
     */
    public function testStringifyAssociativeArray(): void
    {
        // Test simple associative array.
        $this->assertSame('{"name": "John", "age": 30}', Stringify::stringify(['name' => 'John', 'age' => 30]));

        // Test associative array with integer keys (not sequential from 0).
        $this->assertSame('{1: "a", 3: "b", 5: "c"}', Stringify::stringify([1 => 'a', 3 => 'b', 5 => 'c']));

        // Test associative array with mixed key types.
        $this->assertSame('{"key": "value", 0: 42}', Stringify::stringify(['key' => 'value', 0 => 42]));
    }

    /**
     * Test stringifying nested arrays without pretty print.
     */
    public function testStringifyNestedArray(): void
    {
        // Test nested list.
        $this->assertSame('[[1, 2], [3, 4]]', Stringify::stringify([[1, 2], [3, 4]]));

        // Test nested associative array.
        $this->assertSame('{"user": {"name": "John", "age": 30}}', Stringify::stringify(['user' => ['name' => 'John',
                                                                                                    'age' => 30]]));

        // Test mixed nesting.
        $this->assertSame('[1, ["a", "b"], 3]', Stringify::stringify([1, ['a', 'b'], 3]));
    }

    /**
     * Test stringifying arrays with pretty print.
     */
    public function testStringifyArrayPrettyPrint(): void
    {
        // Test simple list with pretty print.
        $expected = "[\n    1,\n    2,\n    3\n]";
        $this->assertSame($expected, Stringify::stringify([1, 2, 3], true));

        // Test associative array with pretty print.
        $expected = "{\n    \"name\": \"John\",\n    \"age\": 30\n}";
        $this->assertSame($expected, Stringify::stringify(['name' => 'John', 'age' => 30], true));

        // Test nested array with pretty print.
        $expected = "[\n    [\n        1,\n        2\n    ],\n    [\n        3,\n        4\n    ]\n]";
        $this->assertSame($expected, Stringify::stringify([[1, 2], [3, 4]], true));
    }

    /**
     * Test that circular references in arrays throw ValueError.
     */
    public function testStringifyArrayCircularReference(): void
    {
        // Create an array with circular reference.
        $array = ['foo' => 'bar'];
        $array['self'] = &$array;

        // Test that circular reference throws ValueError.
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage("Cannot stringify arrays containing circular references.");
        Stringify::stringify($array);
    }

    /**
     * Test stringifying resources.
     */
    public function testStringifyResource(): void
    {
        // Create a resource (file handle).
        $resource = fopen('php://memory', 'r');

        // Test that resource is stringified in tag-like format.
        $result = Stringify::stringify($resource);
        $this->assertStringStartsWith('<resource type: "stream"', $result);
        $this->assertStringContainsString('id:', $result);
        $this->assertStringEndsWith('>', $result);

        // Clean up.
        fclose($resource);
    }

    /**
     * Test stringifying resource with non-resource value throws TypeError.
     */
    public function testStringifyResourceWithNonResource(): void
    {
        // Test that non-resource throws TypeError.
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage("Value is not a resource.");
        Stringify::stringifyResource('not a resource');
    }

    /**
     * Test stringifying simple objects.
     */
    public function testStringifyObject(): void
    {
        // Create a simple object with public properties.
        $obj = new class {
            public string $name = 'John';
            public int $age = 30;
        };

        // Test that object is stringified in tag-like format.
        $result = Stringify::stringify($obj);
        $this->assertStringStartsWith('<', $result);
        $this->assertStringEndsWith('>', $result);
        $this->assertStringContainsString('+name: "John"', $result);
        $this->assertStringContainsString('+age: 30', $result);
    }

    /**
     * Test stringifying objects with different visibility modifiers.
     */
    public function testStringifyObjectVisibility(): void
    {
        // Create object with different visibility levels.
        $obj = new class {
            public string $publicProp = 'public';
            protected string $protectedProp = 'protected';
            private string $privateProp = 'private';
        };

        $result = Stringify::stringify($obj);

        // Test that public properties use + symbol.
        $this->assertStringContainsString('+publicProp: "public"', $result);

        // Test that protected properties use # symbol.
        $this->assertStringContainsString('#protectedProp: "protected"', $result);

        // Test that private properties use - symbol.
        $this->assertStringContainsString('-privateProp: "private"', $result);
    }

    /**
     * Test stringifying empty objects.
     */
    public function testStringifyEmptyObject(): void
    {
        // Create empty object.
        $obj = new class {};

        // Test that empty object is rendered as self-closing tag.
        $result = Stringify::stringify($obj);
        $this->assertStringStartsWith('<', $result);
        $this->assertStringEndsWith('>', $result);
        $this->assertStringNotContainsString(':', $result);
        $this->assertStringNotContainsString(',', $result);
        $this->assertStringNotContainsString('+', $result);
        $this->assertStringNotContainsString('#', $result);
        $this->assertStringNotContainsString('-', $result);
    }

    /**
     * Test stringifying objects with pretty print.
     */
    public function testStringifyObjectPrettyPrint(): void
    {
        // Create object with properties.
        $obj = new class {
            public string $name = 'John';
            public int $age = 30;
        };

        $result = Stringify::stringify($obj, true);

        // Test that pretty print adds newlines.
        $this->assertStringContainsString("\n", $result);
        $this->assertStringStartsWith('<', $result);
        $this->assertStringEndsWith('>', $result);
    }

    /**
     * Test abbrev method with short strings.
     */
    public function testAbbrevShortString(): void
    {
        // Test that short strings are not truncated.
        $this->assertSame('"hello"', Stringify::abbrev('hello'));
        $this->assertSame('42', Stringify::abbrev(42));
        $this->assertSame('true', Stringify::abbrev(true));
    }

    /**
     * Test abbrev method with long strings.
     */
    public function testAbbrevLongString(): void
    {
        // Test that long strings are truncated with ellipsis.
        $longString = 'this is a very long string that should be truncated';
        $result = Stringify::abbrev($longString);

        $this->assertLessThanOrEqual(20, strlen($result));
        $this->assertStringEndsWith('...', $result);
    }

    /**
     * Test abbrev method with custom max length.
     */
    public function testAbbrevCustomLength(): void
    {
        // Test with custom max length.
        $string = 'hello world';
        $result = Stringify::abbrev($string, 10);

        $this->assertLessThanOrEqual(10, strlen($result));
        $this->assertStringEndsWith('...', $result);
    }

    /**
     * Test abbrev method with arrays.
     */
    public function testAbbrevArray(): void
    {
        // Test that arrays are abbreviated.
        $array = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $result = Stringify::abbrev($array);

        $this->assertLessThanOrEqual(20, strlen($result));
        $this->assertStringEndsWith('...', $result);
    }

    /**
     * Test stringify with float value calls stringifyFloat.
     */
    public function testStringifyFloatIntegration(): void
    {
        // Test that stringify correctly handles floats.
        $this->assertSame('5.0', Stringify::stringify(5.0));
        $this->assertSame('3.14', Stringify::stringify(3.14));
        $this->assertSame('NaN', Stringify::stringify(NAN));
        $this->assertSame('∞', Stringify::stringify(INF));
    }

    /**
     * Test stringifying nested structures with objects and arrays.
     */
    public function testStringifyComplexNesting(): void
    {
        // Create complex nested structure.
        $obj = new class {
            public array $items = [1, 2, 3];
            public string $name = 'test';
        };

        $array = ['object' => $obj, 'numbers' => [4, 5, 6]];

        $result = Stringify::stringify($array);

        // Test that result contains expected elements.
        $this->assertStringContainsString('"object"', $result);
        $this->assertStringContainsString('"numbers"', $result);
        $this->assertStringContainsString('+items:', $result);
        $this->assertStringContainsString('+name: "test"', $result);
    }
}
