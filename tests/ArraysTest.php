<?php

declare(strict_types=1);

namespace Mossy2100\Core\Tests;

use InvalidArgumentException;
use LengthException;
use Mossy2100\Core\Arrays;
use Mossy2100\Core\Types;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

use const Mossy2100\Core\RECURSION;

/**
 * Test class for Arrays utility class.
 */
#[CoversClass(Arrays::class)]
final class ArraysTest extends TestCase
{
    #region Method quoteValues() tests.

    /**
     * Test quoteValues with single quotes (default).
     */
    public function testQuoteValuesWithSingleQuotes(): void
    {
        $input = ['foo', 'bar', 'baz'];
        $expected = ["'foo'", "'bar'", "'baz'"];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues with double quotes.
     */
    public function testQuoteValuesWithDoubleQuotes(): void
    {
        $input = ['foo', 'bar', 'baz'];
        $expected = ['"foo"', '"bar"', '"baz"'];
        $result = Arrays::quoteValues($input, true);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues with empty array.
     */
    public function testQuoteValuesWithEmptyArray(): void
    {
        $result = Arrays::quoteValues([]);

        $this->assertEquals([], $result);
    }

    /**
     * Test quoteValues with single element.
     */
    public function testQuoteValuesWithSingleElement(): void
    {
        $input = ['hello'];
        $expected = ["'hello'"];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues preserves array keys.
     */
    public function testQuoteValuesPreservesKeys(): void
    {
        $input = ['first' => 'apple', 'second' => 'banana', 'third' => 'cherry'];
        $expected = ['first' => "'apple'", 'second' => "'banana'", 'third' => "'cherry'"];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues with values containing quotes.
     */
    public function testQuoteValuesWithQuotesInValues(): void
    {
        // Single quotes in values with single quote wrapping.
        $input = ["it's", "can't", "won't"];
        $expected = ["'it's'", "'can't'", "'won't'"];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues with values containing double quotes.
     */
    public function testQuoteValuesWithDoubleQuotesInValues(): void
    {
        // Double quotes in values with double quote wrapping.
        $input = ['say "hello"', 'the "word"'];
        $expected = ['"say "hello""', '"the "word""'];
        $result = Arrays::quoteValues($input, true);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues with empty strings.
     */
    public function testQuoteValuesWithEmptyStrings(): void
    {
        $input = ['', 'foo', '', 'bar'];
        $expected = ["''", "'foo'", "''", "'bar'"];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues with whitespace strings.
     */
    public function testQuoteValuesWithWhitespace(): void
    {
        $input = [' ', '  spaces  ', "\t", "\n"];
        $expected = ["' '", "'  spaces  '", "'\t'", "'\n'"];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues with special characters.
     */
    public function testQuoteValuesWithSpecialCharacters(): void
    {
        $input = ["hello\nworld", "tab\there", 'back\\slash'];
        $expected = ["'hello\nworld'", "'tab\there'", "'back\\slash'"];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues with numeric strings.
     */
    public function testQuoteValuesWithNumericStrings(): void
    {
        $input = ['123', '45.67', '0', '-999'];
        $expected = ["'123'", "'45.67'", "'0'", "'-999'"];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues throws InvalidArgumentException for non-string values (integers).
     */
    public function testQuoteValuesThrowsExceptionForIntegers(): void
    {
        $input = ['foo', 123, 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid array value type: int. Must be string.');

        Arrays::quoteValues($input); // @phpstan-ignore argument.type
    }

    /**
     * Test quoteValues throws InvalidArgumentException for non-string values (floats).
     */
    public function testQuoteValuesThrowsExceptionForFloats(): void
    {
        $input = ['foo', 3.14, 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid array value type: float. Must be string.');

        Arrays::quoteValues($input); // @phpstan-ignore argument.type
    }

    /**
     * Test quoteValues throws InvalidArgumentException for non-string values (booleans).
     */
    public function testQuoteValuesThrowsExceptionForBooleans(): void
    {
        $input = ['foo', true, 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid array value type: bool. Must be string.');

        Arrays::quoteValues($input); // @phpstan-ignore argument.type
    }

    /**
     * Test quoteValues throws InvalidArgumentException for non-string values (null).
     */
    public function testQuoteValuesThrowsExceptionForNull(): void
    {
        $input = ['foo', null, 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid array value type: null. Must be string.');

        Arrays::quoteValues($input); // @phpstan-ignore argument.type
    }

    /**
     * Test quoteValues throws InvalidArgumentException for non-string values (arrays).
     */
    public function testQuoteValuesThrowsExceptionForArrays(): void
    {
        $input = ['foo', ['nested'], 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid array value type: array. Must be string.');

        Arrays::quoteValues($input); // @phpstan-ignore argument.type
    }

    /**
     * Test quoteValues throws InvalidArgumentException for non-string values (objects).
     */
    public function testQuoteValuesThrowsExceptionForObjects(): void
    {
        $input = ['foo', new stdClass(), 'bar'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid array value type: stdClass. Must be string.');

        Arrays::quoteValues($input); // @phpstan-ignore argument.type
    }

    /**
     * Test quoteValues with unicode strings.
     */
    public function testQuoteValuesWithUnicode(): void
    {
        $input = ['hello', '世界', 'emoji 😀', 'Ñoño'];
        $expected = ["'hello'", "'世界'", "'emoji 😀'", "'Ñoño'"];
        $result = Arrays::quoteValues($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test quoteValues does not modify original array.
     */
    public function testQuoteValuesDoesNotModifyOriginal(): void
    {
        $input = ['foo', 'bar', 'baz'];
        $original = $input;
        Arrays::quoteValues($input);

        $this->assertEquals($original, $input);
    }

    #endregion

    #region Method toSerialList() tests.

    /**
     * Test toSerialList with empty array returns empty string.
     */
    public function testToSerialListEmpty(): void
    {
        $this->assertSame('', Arrays::toSerialList([]));
    }

    /**
     * Test toSerialList with one item returns just that item.
     */
    public function testToSerialListOneItem(): void
    {
        $this->assertSame('apples', Arrays::toSerialList(['apples']));
    }

    /**
     * Test toSerialList with two items uses conjunction without Oxford comma.
     */
    public function testToSerialListTwoItems(): void
    {
        $this->assertSame('apples and oranges', Arrays::toSerialList(['apples', 'oranges']));
    }

    /**
     * Test toSerialList with three items uses Oxford comma.
     */
    public function testToSerialListThreeItems(): void
    {
        $this->assertSame('apples, oranges, and bananas', Arrays::toSerialList(['apples', 'oranges', 'bananas']));
    }

    /**
     * Test toSerialList with four items.
     */
    public function testToSerialListFourItems(): void
    {
        $this->assertSame(
            'apples, oranges, bananas, and grapes',
            Arrays::toSerialList(['apples', 'oranges', 'bananas', 'grapes'])
        );
    }

    /**
     * Test toSerialList with custom conjunction.
     */
    public function testToSerialListCustomConjunction(): void
    {
        $this->assertSame('apples or oranges', Arrays::toSerialList(['apples', 'oranges'], 'or'));
        $this->assertSame(
            'apples, oranges, or bananas',
            Arrays::toSerialList(['apples', 'oranges', 'bananas'], 'or')
        );
    }

    /**
     * Test toSerialList throws InvalidArgumentException for non-string values.
     */
    public function testToSerialListThrowsExceptionForNonStrings(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid array value type: int. Must be string.');

        Arrays::toSerialList(['foo', 42, 'bar']); // @phpstan-ignore argument.type
    }

    /**
     * Test toSerialList with oxford set to false omits the Oxford comma.
     */
    public function testToSerialListWithoutOxfordComma(): void
    {
        $this->assertSame(
            'apples, oranges and bananas',
            Arrays::toSerialList(['apples', 'oranges', 'bananas'], oxford: false)
        );
        $this->assertSame(
            'apples, oranges, bananas and grapes',
            Arrays::toSerialList(['apples', 'oranges', 'bananas', 'grapes'], oxford: false)
        );

        // Oxford comma has no effect with fewer than three items.
        $this->assertSame('apples and oranges', Arrays::toSerialList(['apples', 'oranges'], oxford: false));
    }

    /**
     * Test toSerialList with an associative array normalizes to a list before formatting.
     */
    public function testToSerialListAssociativeArray(): void
    {
        $this->assertSame('apples', Arrays::toSerialList(['a' => 'apples']));

        $this->assertSame(
            'apples and oranges',
            Arrays::toSerialList(['a' => 'apples', 'b' => 'oranges'])
        );

        $this->assertSame(
            'apples, oranges, and bananas',
            Arrays::toSerialList(['a' => 'apples', 'b' => 'oranges', 'c' => 'bananas'])
        );
    }

    #endregion

    #region Method first() tests.

    /**
     * Test first() returns first value of a list array.
     */
    public function testFirstWithListArray(): void
    {
        $this->assertEquals(1, Arrays::first([1, 2, 3]));
        $this->assertEquals('apple', Arrays::first(['apple', 'banana', 'cherry']));
    }

    /**
     * Test first() returns first value of an associative array.
     */
    public function testFirstWithAssociativeArray(): void
    {
        $arr = ['a' => 'alpha', 'b' => 'beta', 'c' => 'gamma'];
        $this->assertEquals('alpha', Arrays::first($arr));
    }

    /**
     * Test first() works with a single-element array.
     */
    public function testFirstWithSingleElement(): void
    {
        $this->assertEquals(42, Arrays::first([42]));
        $this->assertEquals('only', Arrays::first(['key' => 'only']));
    }

    /**
     * Test first() works with various value types.
     */
    public function testFirstWithVariousTypes(): void
    {
        $this->assertNull(Arrays::first([null, 'foo']));
        $this->assertTrue(Arrays::first([true, false]));
        $this->assertEquals(3.14, Arrays::first([3.14, 2.71]));

        $obj = new stdClass();
        $this->assertSame($obj, Arrays::first([$obj, 'other']));
    }

    /**
     * Test first() throws LengthException for empty array.
     */
    public function testFirstThrowsExceptionForEmptyArray(): void
    {
        $this->expectException(LengthException::class);
        $this->expectExceptionMessage('Cannot get the first element of an empty array.');

        Arrays::first([]); // @phpstan-ignore argument.type
    }

    #endregion

    #region Method last() tests.

    /**
     * Test last() returns last value of a list array.
     */
    public function testLastWithListArray(): void
    {
        $this->assertEquals(3, Arrays::last([1, 2, 3]));
        $this->assertEquals('cherry', Arrays::last(['apple', 'banana', 'cherry']));
    }

    /**
     * Test last() returns last value of an associative array.
     */
    public function testLastWithAssociativeArray(): void
    {
        $arr = ['a' => 'alpha', 'b' => 'beta', 'c' => 'gamma'];
        $this->assertEquals('gamma', Arrays::last($arr));
    }

    /**
     * Test last() works with a single-element array.
     */
    public function testLastWithSingleElement(): void
    {
        $this->assertEquals(42, Arrays::last([42]));
        $this->assertEquals('only', Arrays::last(['key' => 'only']));
    }

    /**
     * Test last() works with various value types.
     */
    public function testLastWithVariousTypes(): void
    {
        $this->assertNull(Arrays::last(['foo', null]));
        $this->assertFalse(Arrays::last([true, false]));
        $this->assertEquals(2.71, Arrays::last([3.14, 2.71]));

        $obj = new stdClass();
        $this->assertSame($obj, Arrays::last(['other', $obj]));
    }

    /**
     * Test last() throws LengthException for empty array.
     */
    public function testLastThrowsExceptionForEmptyArray(): void
    {
        $this->expectException(LengthException::class);
        $this->expectExceptionMessage('Cannot get the last element of an empty array.');

        Arrays::last([]); // @phpstan-ignore argument.type
    }

    /**
     * Test first() and last() return same value for single-element array.
     */
    public function testFirstAndLastSameForSingleElement(): void
    {
        $arr = ['only' => 'value'];
        $this->assertEquals(Arrays::first($arr), Arrays::last($arr));
    }

    #endregion

    #region Method removeValue() tests.

    /**
     * Test removeValue removes a value that exists.
     */
    public function testRemoveValueExisting(): void
    {
        $this->assertSame([0 => 1, 2 => 3], Arrays::removeValue([1, 2, 3], 2));
    }

    /**
     * Test removeValue removes all instances of a value.
     */
    public function testRemoveValueMultipleInstances(): void
    {
        $this->assertSame([1 => 'b', 3 => 'b'], Arrays::removeValue(['a', 'b', 'a', 'b'], 'a'));
    }

    /**
     * Test removeValue with value not present returns same array.
     */
    public function testRemoveValueNotPresent(): void
    {
        $this->assertSame([0 => 1, 1 => 2, 2 => 3], Arrays::removeValue([1, 2, 3], 99));
    }

    /**
     * Test removeValue with empty array returns empty array.
     */
    public function testRemoveValueEmptyArray(): void
    {
        $this->assertSame([], Arrays::removeValue([], 'anything'));
    }

    /**
     * Test removeValue preserves keys.
     */
    public function testRemoveValuePreservesKeys(): void
    {
        $input = ['a' => 1, 'b' => 2, 'c' => 3];
        $this->assertSame(['a' => 1, 'c' => 3], Arrays::removeValue($input, 2));
    }

    /**
     * Test removeValue requires types to match.
     */
    public function testRemoveValueTypesMustMatch(): void
    {
        // '0' (string) should not match 0 (int).
        $result = Arrays::removeValue([0, '0', false, null], 0);
        $this->assertSame([1 => '0', 2 => false, 3 => null], $result);
    }

    /**
     * Test removeValue can remove null.
     */
    public function testRemoveValueNull(): void
    {
        $this->assertSame([0 => 1, 2 => 3], Arrays::removeValue([1, null, 3], null));
    }

    /**
     * Test removeValue can remove NAN.
     */
    public function testRemoveValueNan(): void
    {
        $this->assertSame([0 => 1.0, 2 => 2.0], Arrays::removeValue([1.0, NAN, 2.0], NAN));
    }

    /**
     * Test removeValue distinguishes 0.0 from -0.0.
     */
    public function testRemoveValueSignedZero(): void
    {
        $this->assertTrue(Types::identical([1 => -0.0], Arrays::removeValue([0.0, -0.0], 0.0)));
    }

    #endregion

    #region Method removeRecursion() tests.

    /**
     * Test that a simple array without recursion is returned unchanged.
     */
    public function testRemoveRecursionSimpleArray(): void
    {
        $arr = ['a' => 1, 'b' => 2, 'c' => [1, 2, 3]];
        $this->assertSame($arr, Arrays::removeRecursion($arr));
    }

    /**
     * Test that an empty array is returned unchanged.
     */
    public function testRemoveRecursionEmptyArray(): void
    {
        $this->assertSame([], Arrays::removeRecursion([]));
    }

    /**
     * Test that a direct self-reference is replaced with the RECURSION marker.
     */
    public function testRemoveRecursionDirectReference(): void
    {
        $arr = ['foo' => 'bar'];
        $arr['self'] = &$arr;

        $result = Arrays::removeRecursion($arr);

        $this->assertSame('bar', $result['foo']);
        $this->assertSame(RECURSION, $result['self']);
    }

    /**
     * Test that indirect (mutual) recursion between two arrays is replaced with the RECURSION
     * marker.
     */
    public function testRemoveRecursionIndirectReference(): void
    {
        $arr1 = ['name' => 'array1'];
        $arr2 = ['name' => 'array2'];
        $arr1['child'] = &$arr2;
        $arr2['parent'] = &$arr1;

        $result1 = Arrays::removeRecursion($arr1);
        $this->assertSame('array1', $result1['name']);
        $child1 = $result1['child'];
        $this->assertIsArray($child1);
        $this->assertSame('array2', $child1['name']);
        $this->assertSame(RECURSION, $child1['parent']);

        $result2 = Arrays::removeRecursion($arr2);
        $this->assertSame('array2', $result2['name']);
        $parent2 = $result2['parent'];
        $this->assertIsArray($parent2);
        $this->assertSame('array1', $parent2['name']);
        $this->assertSame(RECURSION, $parent2['child']);
    }

    /**
     * Test that recursion nested several levels deep is detected and replaced.
     */
    public function testRemoveRecursionNestedReference(): void
    {
        $arr = ['level1' => ['level2' => ['level3' => []]]];
        $arr['level1']['level2']['level3']['back'] = &$arr;

        $result = Arrays::removeRecursion($arr);

        $level1 = $result['level1'];
        $this->assertIsArray($level1);
        $level2 = $level1['level2'];
        $this->assertIsArray($level2);
        $level3 = $level2['level3'];
        $this->assertIsArray($level3);
        $this->assertSame(RECURSION, $level3['back']);
    }

    /**
     * Test that a shared (but non-circular) reference to a sub-array is not mistaken for
     * recursion and is left untouched.
     */
    public function testRemoveRecursionSubArrayReferenceNotRecursive(): void
    {
        $subArray = ['x' => 1, 'y' => 2];
        $arr = ['original' => $subArray, 'reference' => &$subArray];

        $this->assertSame($arr, Arrays::removeRecursion($arr));
    }

    /**
     * Test that two unrelated sub-arrays with identical contents are not mistaken for recursion.
     */
    public function testRemoveRecursionIdenticalButUnrelatedSubArrays(): void
    {
        $arr = ['a' => [1, 2, 3], 'b' => [1, 2, 3]];

        $this->assertSame($arr, Arrays::removeRecursion($arr));
    }

    /**
     * Test with an array containing various data types alongside recursion.
     */
    public function testRemoveRecursionComplexArray(): void
    {
        $arr = [
            'null' => null,
            'bool' => true,
            'int' => 42,
            'float' => 3.14,
            'string' => 'hello',
            'array' => [1, 2, 3],
        ];
        $arr['self'] = &$arr;

        $result = Arrays::removeRecursion($arr);

        $this->assertNull($result['null']);
        $this->assertTrue($result['bool']);
        $this->assertSame(42, $result['int']);
        $this->assertSame(3.14, $result['float']);
        $this->assertSame('hello', $result['string']);
        $this->assertSame([1, 2, 3], $result['array']);
        $this->assertSame(RECURSION, $result['self']);
    }

    /**
     * Test that the result of removeRecursion() is safe to json_encode(), which would otherwise
     * fail on a genuinely recursive array.
     */
    public function testRemoveRecursionResultIsJsonEncodable(): void
    {
        $arr = ['foo' => 'bar'];
        $arr['self'] = &$arr;

        $result = Arrays::removeRecursion($arr);

        $json = json_encode($result, JSON_THROW_ON_ERROR);
        $this->assertSame('{"foo":"bar","self":"*RECURSION*"}', $json);
    }

    /**
     * Test that removeRecursion() does not modify the original array.
     */
    public function testRemoveRecursionDoesNotModifyOriginal(): void
    {
        $arr = ['foo' => 'bar'];
        $arr['self'] = &$arr;

        Arrays::removeRecursion($arr);

        // The original is still genuinely recursive: writing through 'self' changes the array itself.
        $arr['self']['foo'] = 'baz';
        $this->assertSame('baz', $arr['foo']);
    }

    /**
     * Test that an empty sub-array in an array with recursion is kept as an empty array.
     */
    public function testRemoveRecursionKeepsEmptySubArrayWithRecursion(): void
    {
        $arr = ['empty' => []];
        $arr['self'] = &$arr;

        $expected = ['empty' => [], 'self' => RECURSION];
        $this->assertSame($expected, Arrays::removeRecursion($arr));
    }

    /**
     * Test that invalid UTF-8 and NAN values don't hide recursion, or cause a false positive.
     */
    public function testRemoveRecursionWithInvalidUtf8AndNan(): void
    {
        $plain = ["\xff\xfe", NAN, [1, 2]];
        $result = Arrays::removeRecursion($plain);
        $this->assertSame("\xff\xfe", $result[0]);
        $this->assertNan($result[1]);
        $this->assertSame([1, 2], $result[2]);

        $recursive = ["\xff\xfe", NAN];
        $recursive[] = &$recursive;
        $result = Arrays::removeRecursion($recursive);
        $this->assertSame("\xff\xfe", $result[0]);
        $this->assertSame(RECURSION, $result[2]);
    }

    /**
     * Test that removeRecursion() restores the caller's error handler. It temporarily sets its own to detect recursion.
     */
    public function testRemoveRecursionRestoresErrorHandler(): void
    {
        $arr = ['foo' => 'bar'];
        $arr['self'] = &$arr;

        $handler = static fn (): bool => true;
        set_error_handler($handler);
        try {
            Arrays::removeRecursion($arr);

            // set_error_handler() returns the handler it replaces.
            $this->assertSame($handler, set_error_handler(null));
        } finally {
            restore_error_handler();
            restore_error_handler();
        }
    }

    /**
     * Test that a string that looks like print_r() output doesn't affect an array without recursion. This was a false
     * positive of the print_r() parser, which now only runs for arrays that contain recursion.
     */
    public function testRemoveRecursionLookalikeStringWithoutRecursion(): void
    {
        $arr = ['c' => [1, 2], 'a' => "text\n    [c] => Array\n *RECURSION*"];

        $this->assertSame($arr, Arrays::removeRecursion($arr));
    }

    /**
     * Test that an array containing itself without a PHP reference element has the cycle replaced.
     */
    public function testRemoveRecursionSelfContainingWithoutReference(): void
    {
        // After this, $arr[0] is $arr itself, but it isn't a reference element.
        $arr = ['x' => 1];
        $alias = &$arr;
        $arr[] = $alias;

        $expected = ['x' => 1, 0 => RECURSION];
        $this->assertSame($expected, Arrays::removeRecursion($arr));
    }

    /**
     * Test that a deeply nested array without recursion is copied unchanged.
     */
    public function testRemoveRecursionDeepNestingNotRecursive(): void
    {
        $arr = [1];
        for ($i = 0; $i < 300; $i++) {
            $arr = [$arr];
        }

        $this->assertSame($arr, Arrays::removeRecursion($arr));
    }

    /**
     * Test that objects are kept as the same instances, with and without recursion in the array.
     */
    public function testRemoveRecursionKeepsObjects(): void
    {
        $obj = new stdClass();

        // No recursion.
        $result = Arrays::removeRecursion(['o' => $obj, 'n' => [1, 2]]);
        $this->assertSame($obj, $result['o']);

        // With recursion elsewhere in the array.
        $arr = ['o' => $obj];
        $arr['self'] = &$arr;
        $result = Arrays::removeRecursion($arr);
        $this->assertSame($obj, $result['o']);
        $this->assertSame(RECURSION, $result['self']);
    }

    /**
     * Test that cycles through objects are left alone, since objects aren't followed.
     */
    public function testRemoveRecursionLeavesObjectCycles(): void
    {
        // An object that contains itself.
        $obj = new stdClass();
        $obj->self = $obj;
        $result = Arrays::removeRecursion([$obj]);
        $this->assertSame($obj, $result[0]);

        // A cycle from an array, through an object, back to the array.
        $arr = ['k' => 1];
        $holder = new stdClass();
        $holder->arr = &$arr;
        $arr['o'] = $holder;
        $result = Arrays::removeRecursion($arr);
        $this->assertSame(1, $result['k']);
        $this->assertSame($holder, $result['o']);
    }

    /**
     * Test that the result of an array without recursion is detached from the original's references, so writing to it
     * doesn't change the referenced variables.
     */
    public function testRemoveRecursionResultIsDetachedFromReferences(): void
    {
        $value = 1;
        $arr = ['ref' => &$value, 'nested' => ['ref' => &$value]];

        $result = Arrays::removeRecursion($arr);
        $result['ref'] = 99;

        // Copying an array keeps its reference elements, so writing to this copy would still reach $value if the
        // result weren't detached.
        $nested = $result['nested'];
        $this->assertIsArray($nested);
        $nested['ref'] = 98;

        $this->assertSame(1, $value);
    }

    #endregion
}
