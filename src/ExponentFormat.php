<?php

declare(strict_types=1);

namespace OceanMoon\Core;

/**
 * Rendering styles for the exponent portion of scientific-notation float output.
 */
enum ExponentFormat
{
    /**
     * ASCII lower-case E notation (e.g. 'e+23'). The exponent is not padded to a fixed width.
     */
    case AsciiLowerCaseE;

    /**
     * ASCII upper-case E notation (e.g. 'E+23'). The exponent is not padded to a fixed width.
     */
    case AsciiUpperCaseE;

    /**
     * ASCII mathematical notation (e.g. '*10^23'). Uses ASCII operators and digits.
     */
    case AsciiMath;

    /**
     * Unicode mathematical notation (e.g. '×10²³'). Uses the multiplication sign and superscript digits.
     */
    case UnicodeMath;

    /**
     * HTML mathematical notation (e.g. '&times;10<sup>23</sup>').
     */
    case HtmlMath;

    /**
     * Render a signed exponent as a string, ready to append directly after a (possibly trimmed) mantissa.
     *
     * AsciiLowerCaseE and AsciiUpperCaseE always include an explicit sign ('+' or '-'), matching sprintf()'s own
     * 'e'/'E' conventions. The three mathematical notations (AsciiMath, UnicodeMath, HtmlMath) omit the '+' for
     * positive exponents and show only '-' for negative ones, matching how scientific notation is conventionally
     * written by hand (e.g. '×10³', not '×10+3').
     *
     * @param int $exponent The exponent (may be negative, zero, or positive).
     * @return string The rendered exponent, e.g. 'e+23', 'E-23', '*10^23', '×10²³', '&times;10<sup>23</sup>'.
     */
    public function format(int $exponent): string
    {
        return match ($this) {
            self::AsciiLowerCaseE => sprintf('e%+d', $exponent),
            self::AsciiUpperCaseE => sprintf('E%+d', $exponent),
            self::AsciiMath => '*10^' . $exponent,
            self::UnicodeMath => '×10' . Integers::toSuperscript($exponent),
            self::HtmlMath => '&times;10<sup>' . $exponent . '</sup>',
        };
    }
}
