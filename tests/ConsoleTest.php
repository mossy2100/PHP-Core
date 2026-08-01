<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests;

use ArgumentCountError;
use DomainException;
use OceanMoon\Core\Console;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Stringable;

use const OceanMoon\Core\RECURSION;

/**
 * Test class for Console utility class.
 */
#[CoversClass(Console::class)]
final class ConsoleTest extends TestCase
{
    /** The Console instance. */
    private static Console $console;

    /**
     * Console is a singleton, so its style state persists across tests unless reset here first.
     *
     * Deliberately not initialized in setUpBeforeClass(): that method runs outside any single test's code
     * coverage recording window, so the singleton's one-time construction inside getInstance() would never be
     * attributed to any test. Initializing here instead, on every test, keeps it inside a covered test's window.
     */
    protected function setUp(): void
    {
        self::$console = Console::getInstance();

        ob_start();
        self::$console->resetStyle();
        ob_end_clean();
    }

    #region Method getInstance() tests.

    /**
     * Test getInstance() returns the same instance on repeated calls, since Console is a singleton.
     */
    public function testGetInstanceReturnsSameInstance(): void
    {
        $this->assertSame(Console::getInstance(), Console::getInstance());
    }

    #endregion

    #region Method setBackground() tests.

    /**
     * Test setBackground() emits the background code (foreground code + 10) and tracks it, leaving the
     * foreground untouched.
     */
    public function testSetBackground(): void
    {
        $this->expectOutputString("\033[41m");
        self::$console->setBackground(Console::RED);
        $this->assertSame(Console::RED, self::$console->getStyle()['background']);
        $this->assertSame(Console::DEFAULT, self::$console->getStyle()['foreground']);
    }

    #endregion

    #region Method setColor() tests.

    /**
     * Test setColor() with only a foreground emits just the foreground code, leaving the background untouched.
     */
    public function testSetColorForegroundOnly(): void
    {
        $this->expectOutputString("\033[97m");
        self::$console->setColor(Console::WHITE);
        $style = self::$console->getStyle();
        $this->assertSame(Console::WHITE, $style['foreground']);
        $this->assertSame(Console::DEFAULT, $style['background']);
    }

    /**
     * Test setColor() with both foreground and background emits two separate escape sequences (foreground via
     * emit(), background via setBackground()) and tracks both.
     */
    public function testSetColorForegroundAndBackground(): void
    {
        $this->expectOutputString("\033[97m\033[41m");
        self::$console->setColor(Console::WHITE, Console::RED);
        $style = self::$console->getStyle();
        $this->assertSame(Console::WHITE, $style['foreground']);
        $this->assertSame(Console::RED, $style['background']);
    }

    /**
     * Test setColor() returns $this for chaining.
     */
    public function testSetColorReturnsSelf(): void
    {
        $this->expectOutputRegex('/.+/');
        $this->assertSame(self::$console, self::$console->setColor(Console::WHITE));
    }

    #endregion

    #region Method resetColor() tests.

    /**
     * Test resetColor() emits the console's default fg/bg codes (39/49) and resets tracked colours.
     */
    public function testResetColor(): void
    {
        ob_start();
        self::$console->setColor(Console::WHITE, Console::RED);
        ob_end_clean();

        $this->expectOutputString("\033[39m\033[49m");
        self::$console->resetColor();
        $style = self::$console->getStyle();
        $this->assertSame(Console::DEFAULT, $style['foreground']);
        $this->assertSame(Console::DEFAULT, $style['background']);
    }

    #endregion

    #region Bold/dim toggle tests.

    /**
     * Test bold()/dim() emit their own SGR codes (1/2) and track independent boolean state.
     */
    public function testBoldAndDimOn(): void
    {
        $this->expectOutputString("\033[1m\033[2m");
        self::$console->bold()->dim();
        $style = self::$console->getStyle();
        $this->assertTrue($style['bold']);
        $this->assertTrue($style['dim']);
    }

    /**
     * Test boldOff() while dim is still on: since SGR 22 clears both bold and dim, boldOff() must re-emit dim
     * (SGR 2) afterward so dim is left untouched from the caller's perspective.
     */
    public function testBoldOffPreservesDim(): void
    {
        ob_start();
        self::$console->bold()->dim();
        ob_end_clean();

        $this->expectOutputString("\033[22m\033[2m");
        self::$console->boldOff();
        $style = self::$console->getStyle();
        $this->assertFalse($style['bold']);
        $this->assertTrue($style['dim']);
    }

    /**
     * Test dimOff() while bold is still on: mirrors testBoldOffPreservesDim(), re-emitting bold (SGR 1) after
     * clearing intensity.
     */
    public function testDimOffPreservesBold(): void
    {
        ob_start();
        self::$console->bold()->dim();
        ob_end_clean();

        $this->expectOutputString("\033[22m\033[1m");
        self::$console->dimOff();
        $style = self::$console->getStyle();
        $this->assertTrue($style['bold']);
        $this->assertFalse($style['dim']);
    }

    /**
     * Test boldOff()/dimOff() with neither the other attribute active just emits SGR 22 once, with no
     * re-emitted attribute.
     */
    public function testBoldOffWithDimAlreadyOff(): void
    {
        ob_start();
        self::$console->bold();
        ob_end_clean();

        $this->expectOutputString("\033[22m");
        self::$console->boldOff();
        $style = self::$console->getStyle();
        $this->assertFalse($style['bold']);
        $this->assertFalse($style['dim']);
    }

    #endregion

    #region Simple attribute toggle tests.

    /**
     * Test italic()/italicOff() emit SGR 3/23 and track state.
     */
    public function testItalicToggle(): void
    {
        $this->expectOutputString("\033[3m\033[23m");
        self::$console->italic();
        $this->assertTrue(self::$console->getStyle()['italic']);
        self::$console->italicOff();
        $this->assertFalse(self::$console->getStyle()['italic']);
    }

    /**
     * Test underline()/underlineOff() emit SGR 4/24 and track state.
     */
    public function testUnderlineToggle(): void
    {
        $this->expectOutputString("\033[4m\033[24m");
        self::$console->underline();
        $this->assertTrue(self::$console->getStyle()['underline']);
        self::$console->underlineOff();
        $this->assertFalse(self::$console->getStyle()['underline']);
    }

    /**
     * Test strikethrough()/strikethroughOff() emit SGR 9/29 and track state.
     */
    public function testStrikethroughToggle(): void
    {
        $this->expectOutputString("\033[9m\033[29m");
        self::$console->strikethrough();
        $this->assertTrue(self::$console->getStyle()['strikethrough']);
        self::$console->strikethroughOff();
        $this->assertFalse(self::$console->getStyle()['strikethrough']);
    }

    /**
     * Test reverse()/reverseOff() emit SGR 7/27 and track state.
     */
    public function testReverseToggle(): void
    {
        $this->expectOutputString("\033[7m\033[27m");
        self::$console->reverse();
        $this->assertTrue(self::$console->getStyle()['reverse']);
        self::$console->reverseOff();
        $this->assertFalse(self::$console->getStyle()['reverse']);
    }

    #endregion

    #region Style snapshot tests.

    /**
     * Test getStyle() returns the current tracked state as a plain array.
     */
    public function testGetStyleReturnsCurrentState(): void
    {
        ob_start();
        self::$console->setColor(Console::GREEN, Console::BLACK)->bold()->underline();
        ob_end_clean();

        $this->assertSame([
            'foreground'    => Console::GREEN,
            'background'    => Console::BLACK,
            'bold'          => true,
            'dim'           => false,
            'italic'        => false,
            'underline'     => true,
            'strikethrough' => false,
            'reverse'       => false,
        ], self::$console->getStyle());
    }

    /**
     * Test setStyle() round-trips cleanly through getStyle(): capture a style, reset, re-apply, and confirm the
     * state matches exactly.
     */
    public function testSetStyleRoundTrip(): void
    {
        ob_start();
        self::$console->setColor(Console::GREEN, Console::BLACK)->bold()->underline();
        $snapshot = self::$console->getStyle();
        self::$console->resetStyle();
        ob_end_clean();

        $this->assertNotSame($snapshot, self::$console->getStyle());

        ob_start();
        self::$console->setStyle($snapshot);
        ob_end_clean();
        $this->assertSame($snapshot, self::$console->getStyle());
    }

    /**
     * Test setStyle() only applies the keys present, leaving unspecified attributes untouched.
     */
    public function testSetStylePartialArray(): void
    {
        ob_start();
        self::$console->bold();
        ob_end_clean();

        ob_start();
        self::$console->setStyle([
            'italic' => true,
        ]);
        ob_end_clean();

        $this->assertTrue(
            self::$console->getStyle()['bold'],
            'bold should be untouched since it was not in the partial array'
        );
        $this->assertTrue(self::$console->getStyle()['italic']);
    }

    /**
     * Test setStyle() with an explicit false value turns the attribute off (as opposed to an absent key, which
     * leaves it unchanged).
     */
    public function testSetStyleExplicitFalseTurnsOff(): void
    {
        ob_start();
        self::$console->bold();
        ob_end_clean();

        ob_start();
        self::$console->setStyle([
            'bold' => false,
        ]);
        ob_end_clean();

        $this->assertFalse(self::$console->getStyle()['bold']);
    }

    #endregion

    #region Method resetStyle() tests.

    /**
     * Test resetStyle() emits SGR 0 and clears every tracked colour and attribute back to its default.
     */
    public function testResetStyle(): void
    {
        ob_start();
        self::$console->setColor(
            Console::WHITE,
            Console::RED
        )->bold()->italic()->underline()->strikethrough()->reverse();
        ob_end_clean();

        $this->expectOutputString("\033[0m");
        self::$console->resetStyle();

        $this->assertSame([
            'foreground'    => Console::DEFAULT,
            'background'    => Console::DEFAULT,
            'bold'          => false,
            'dim'           => false,
            'italic'        => false,
            'underline'     => false,
            'strikethrough' => false,
            'reverse'       => false,
        ], self::$console->getStyle());
    }

    #endregion

    #region Method message() tests.

    /**
     * Test message() with no colour arguments: bolds, re-emits the current (default) colour, prints the padded
     * text, restores every attribute, and appends a newline.
     */
    public function testMessageWithNoColor(): void
    {
        $this->expectOutputString(
            "\033[1m" // message() always bolds
            . "\033[39m\033[49m" // setColor() with no override re-emits the current (default) colour
            . ' hi '
            // The trailing newline comes from println() and is emitted here, before the style restore below —
            // print()'s per-line background-bleed fix doesn't apply since the background is still DEFAULT.
            . "\n"
            . "\033[39m\033[49m" // restore colour
            . "\033[22m" // restore bold off
            . "\033[22m" // restore dim off
            . "\033[23m" // restore italic off
            . "\033[24m" // restore underline off
            . "\033[29m" // restore strikethrough off
            . "\033[27m" // restore reverse off
        );
        self::$console->message('hi');
    }

    /**
     * Test message() with explicit colours applies them for the message only, then restores the prior colour.
     */
    public function testMessageWithColor(): void
    {
        $this->expectOutputString(
            "\033[1m"
            . "\033[97m\033[41m" // WHITE on RED
            . ' hi '
            // println()'s trailing newline is embedded in the same print() call as the text, and since the
            // background is non-default here, print()'s per-line bleed-prevention fix wraps that embedded
            // newline with a background reset/reapply pair, before message()'s own final style restore runs.
            . "\033[49m" // reset background to default around the embedded newline
            . "\n"
            . "\033[41m" // reapply the message's background after the newline
            . "\033[39m\033[49m" // message()'s own restore to the prior (default) colour
            . "\033[22m"
            . "\033[22m"
            . "\033[23m"
            . "\033[24m"
            . "\033[29m"
            . "\033[27m"
        );
        self::$console->message('hi', Console::WHITE, Console::RED);
    }

    /**
     * Test message() returns $this for chaining.
     */
    public function testMessageReturnsSelf(): void
    {
        $this->expectOutputRegex('/.+/');
        $this->assertSame(self::$console, self::$console->message('hi'));
    }

    #endregion

    #region Badge method tests.

    /**
     * Test success() prints white-on-green text with a checkmark glyph.
     */
    public function testSuccess(): void
    {
        $this->expectOutputRegex('/\033\[97m\033\[42m ✓ ok /');
        self::$console->success('ok');
    }

    /**
     * Test error() prints white-on-red text with a cross glyph.
     */
    public function testError(): void
    {
        $this->expectOutputRegex('/\033\[97m\033\[41m ✗ bad /');
        self::$console->error('bad');
    }

    /**
     * Test warn() prints black-on-bright-yellow text with a warning glyph.
     */
    public function testWarn(): void
    {
        $this->expectOutputRegex('/\033\[30m\033\[103m ⚠ careful /');
        self::$console->warn('careful');
    }

    /**
     * Test info() prints white-on-blue text with an info glyph.
     */
    public function testInfo(): void
    {
        $this->expectOutputRegex('/\033\[97m\033\[44m ℹ fyi /');
        self::$console->info('fyi');
    }

    #endregion

    #region Method link() tests.

    /**
     * Test link() emits the OSC 8 hyperlink sequence with the given label, styled bright blue and underlined on
     * the default background, restoring the prior style afterward.
     */
    public function testLinkWithLabel(): void
    {
        $this->expectOutputString(
            "\033[94m\033[49m" // bright blue on default background
            . "\033[4m" // underline on
            . "\033]8;;https://example.com\033\\" // OSC 8 open
            . 'Example'
            . "\033]8;;\033\\" // OSC 8 close
            . "\033[39m\033[49m" // restore colour
            . "\033[22m"
            . "\033[22m"
            . "\033[23m"
            . "\033[24m"
            . "\033[29m"
            . "\033[27m"
        );
        self::$console->link('https://example.com', 'Example');
    }

    /**
     * Test link() with no label falls back to using the URL itself as the clickable label.
     */
    public function testLinkWithoutLabelUsesUrl(): void
    {
        $this->expectOutputRegex('#\033]8;;https://example\.com\033\\\\https://example\.com\033]8;;#');
        self::$console->link('https://example.com');
    }

    /**
     * Test link() returns $this for chaining.
     */
    public function testLinkReturnsSelf(): void
    {
        $this->expectOutputRegex('/.+/');
        $this->assertSame(self::$console, self::$console->link('https://example.com'));
    }

    #endregion

    #region Method bell() tests.

    /**
     * Test bell() emits exactly the BEL control character (0x07).
     */
    public function testBell(): void
    {
        $this->expectOutputString("\x07");
        self::$console->bell();
    }

    #endregion

    #region Method print() tests.

    /**
     * Test print() with no argument prints nothing.
     */
    public function testPrintWithNoArgument(): void
    {
        $this->expectOutputString('');
        self::$console->print();
    }

    /**
     * Test print() with a string.
     */
    public function testPrintWithString(): void
    {
        $this->expectOutputString('Hello');
        self::$console->print('Hello');
    }

    /**
     * Test print() with an integer.
     */
    public function testPrintWithInt(): void
    {
        $this->expectOutputString('42');
        self::$console->print(42);
    }

    /**
     * Test print() with a float. Uses Stringify::toString(), which prefers PHP's raw string conversion, so a
     * whole-number float doesn't get a distinguishing ".0" suffix the way Stringify::stringifyFloat() would add.
     */
    public function testPrintWithFloat(): void
    {
        $this->expectOutputString('3.14');
        self::$console->print(3.14);
    }

    /**
     * Test print() with true.
     */
    public function testPrintWithTrue(): void
    {
        $this->expectOutputString('1');
        self::$console->print(true);
    }

    /**
     * Test print() with false. PHP casts false to an empty string.
     */
    public function testPrintWithFalse(): void
    {
        $this->expectOutputString('');
        self::$console->print(false);
    }

    /**
     * Test print() with null. PHP casts null to an empty string.
     */
    public function testPrintWithNull(): void
    {
        $this->expectOutputString('');
        self::$console->print(null);
    }

    /**
     * Test print() with a Stringable object uses __toString().
     */
    public function testPrintWithStringableObject(): void
    {
        $this->expectOutputString('custom');
        self::$console->print(new StringableThing());
    }

    /**
     * Test print() with a non-Stringable object doesn't throw, since it converts via Stringify::toString(),
     * which falls back to Stringify::stringify() rather than a raw cast.
     */
    public function testPrintWithNonStringableObjectDoesNotThrow(): void
    {
        $this->expectOutputRegex('/^OceanMoon\\\\Core\\\\Tests\\\\Foo #\d+ \{\+a => 1, #b => 2, -c => 3\}$/');
        self::$console->print(new Foo());
    }

    /**
     * Test print() with a default background prints a multi-line value unchanged.
     */
    public function testPrintMultilineWithDefaultBackground(): void
    {
        $this->expectOutputString("a\nb");
        self::$console->print("a\nb");
    }

    /**
     * Test print() with a non-default background resets the background at each embedded newline, then
     * re-applies it, so the background doesn't bleed into the next line.
     */
    public function testPrintMultilineWithNonDefaultBackground(): void
    {
        ob_start();
        self::$console->setBackground(Console::RED);
        ob_end_clean();

        $this->expectOutputString(
            'a'
            . "\033[49m" // reset background to default before the embedded newline
            . "\n"
            . "\033[41m" // re-apply the RED background after the newline
            . 'b'
        );
        self::$console->print("a\nb");
    }

    /**
     * Test print() returns $this for chaining.
     */
    public function testPrintReturnsSelf(): void
    {
        $this->expectOutputRegex('/.+/');
        $this->assertSame(self::$console, self::$console->print('x'));
    }

    #endregion

    #region Method println() tests.

    /**
     * Test println() with no argument prints just a newline.
     */
    public function testPrintlnWithNoArgument(): void
    {
        $this->expectOutputString(PHP_EOL);
        self::$console->println();
    }

    /**
     * Test println() appends a newline after the converted value. Value conversion itself (every type, plus
     * non-Stringable objects) is covered by print()'s own tests, above, since println() delegates to it.
     */
    public function testPrintlnAppendsNewline(): void
    {
        $this->expectOutputString('Hello' . PHP_EOL);
        self::$console->println('Hello');
    }

    /**
     * Test println() returns $this for chaining.
     */
    public function testPrintlnReturnsSelf(): void
    {
        $this->expectOutputRegex('/.+/');
        $this->assertSame(self::$console, self::$console->println('x'));
    }

    #endregion

    #region Method dump() tests.

    /**
     * Test dump() prints the value pretty-printed via Stringify::stringify(), prefixed with its type and wrapped
     * in the type's color (see Console::TYPE_COLOR), restoring the prior style afterward.
     */
    public function testDumpVarWithArray(): void
    {
        $this->expectOutputString(
            "\033[95m\033[49m" // BRIGHT_MAGENTA on DEFAULT background (Console::TYPE_COLOR['array'])
            . 'array: [1, 2, 3]'
            . "\n"
            . "\033[39m\033[49m\033[22m\033[22m\033[23m\033[24m\033[29m\033[27m" // restore prior style
        );
        self::$console->dump([1, 2, 3]);
    }

    /**
     * Test dump() handles a circular reference instead of erroring, and pretty-prints (multi-line) since dump()
     * always pretty-prints, unlike Stringify::stringify()'s own default.
     */
    public function testDumpVarHandlesRecursion(): void
    {
        $arr = [
            'x' => 1,
        ];
        $arr['self'] = &$arr;

        $this->expectOutputString(
            "\033[95m\033[49m"
            . "array: [\n    'x'    => 1,\n    'self' => " . RECURSION . ",\n]"
            . "\n"
            . "\033[39m\033[49m\033[22m\033[22m\033[23m\033[24m\033[29m\033[27m"
        );
        self::$console->dump($arr);
    }

    /**
     * Test dump() with an object shows the class name and properties, pretty-printed, prefixed with 'object:',
     * and wrapped in BRIGHT_CYAN (Console::TYPE_COLOR['object']).
     */
    public function testDumpVarWithObject(): void
    {
        $this->expectOutputRegex(
            '/^\033\[96m\033\[49mobject: OceanMoon\\\\Core\\\\Tests\\\\Foo #\d+ \{\n'
            . '    \+a => 1,\n    #b => 2,\n    -c => 3,\n\}\n'
            . '\033\[39m\033\[49m\033\[22m\033\[22m\033\[23m\033\[24m\033\[29m\033\[27m$/'
        );
        self::$console->dump(new Foo());
    }

    /**
     * Test dump() with no argument throws.
     */
    public function testDumpVarWithNoArgumentThrows(): void
    {
        $this->expectException(ArgumentCountError::class);
        self::$console->dump(); // @phpstan-ignore arguments.count
    }

    /**
     * Test dump() always returns $this for chaining (unlike the old inspect(), it has no $return option to get
     * the string back instead of printing).
     */
    public function testDumpReturnsSelf(): void
    {
        $this->expectOutputRegex('/.+/');
        $this->assertSame(self::$console, self::$console->dump([1, 2, 3]));
    }

    #endregion

    #region Method hr() tests.

    /**
     * Test self::$console->hr() with default arguments prints an 80-character line of dashes.
     */
    public function testHrWithDefaults(): void
    {
        $this->expectOutputString(str_repeat('-', 80) . PHP_EOL);
        self::$console->hr();
    }

    /**
     * Test self::$console->hr() with a custom character.
     */
    public function testHrWithCustomCharacter(): void
    {
        $this->expectOutputString(str_repeat('=', 80) . PHP_EOL);
        self::$console->hr('=');
    }

    /**
     * Test self::$console->hr() with a custom length.
     */
    public function testHrWithCustomLength(): void
    {
        $this->expectOutputString(str_repeat('-', 20) . PHP_EOL);
        self::$console->hr(length: 20);
    }

    /**
     * Test self::$console->hr() with a multi-character string repeats and truncates to exactly $length characters, not
     * $length repetitions of the string.
     */
    public function testHrWithMultiCharacterString(): void
    {
        $this->expectOutputString('ababa' . PHP_EOL);
        self::$console->hr('ab', 5);
    }

    /**
     * Test self::$console->hr() with a multibyte character repeats and truncates by character count, not byte count.
     */
    public function testHrWithMultibyteCharacter(): void
    {
        $this->expectOutputString('★★★' . PHP_EOL);
        self::$console->hr('★', 3);
    }

    /**
     * Test self::$console->hr() with a length of zero prints just a newline.
     */
    public function testHrWithZeroLength(): void
    {
        $this->expectOutputString(PHP_EOL);
        self::$console->hr(length: 0);
    }

    /**
     * Test self::$console->hr() with an empty string for $ch throws.
     */
    public function testHrWithEmptyStringThrows(): void
    {
        $this->expectException(DomainException::class);
        self::$console->hr('', 5);
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
