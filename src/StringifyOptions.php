<?php

declare(strict_types=1);

namespace Mossy2100\Core;

use DomainException;

/**
 * Options for Stringify, fixed for the whole of one stringify() call.
 *
 * The options are passed with each call rather than stored in Stringify, so a call made while another is in progress
 * (e.g. from an object's __toString()) can use different options without affecting the outer one.
 *
 * For example:
 * ```php
 * Stringify::stringify($value);                                                  // compact
 * Stringify::stringify($value, StringifyOptions::pretty());                      // pretty, default layout
 * Stringify::stringify($value, StringifyOptions::pretty(indent: 2, maxLineLength: 80));
 * ```
 */
final readonly class StringifyOptions
{
    #region Constants

    /**
     * The default number of spaces to indent each level.
     */
    public const int DEFAULT_INDENT = 4;

    /**
     * The default maximum line length for pretty-printed output.
     */
    public const int DEFAULT_MAX_LINE_LENGTH = 120;

    #endregion

    #region Constructor

    /**
     * Create a set of options.
     *
     * @param bool $prettyPrint Whether to format arrays and objects over multiple lines, with indentation.
     * @param int $indent The number of spaces to indent each level when pretty printing (must be >= 0).
     * @param int $maxLineLength The maximum line length when pretty printing (must be > 0).
     * @throws DomainException If the indent is negative, or the max line length is not positive.
     */
    public function __construct(
        public bool $prettyPrint = false,
        public int $indent = self::DEFAULT_INDENT,
        public int $maxLineLength = self::DEFAULT_MAX_LINE_LENGTH
    ) {
        if ($indent < 0) {
            throw new DomainException("Invalid indent: $indent. Must not be negative.");
        }
        if ($maxLineLength <= 0) {
            throw new DomainException("Invalid max line length: $maxLineLength. Must be positive.");
        }
    }

    #endregion

    #region Factory methods

    /**
     * Get options for compact output, on a single line. This is the default.
     *
     * @return self The options.
     */
    public static function compact(): self
    {
        return new self();
    }

    /**
     * Get options for pretty-printed output, over multiple lines with indentation.
     *
     * @param int $indent The number of spaces to indent each level (must be >= 0).
     * @param int $maxLineLength The maximum line length (must be > 0).
     * @return self The options.
     * @throws DomainException If the indent is negative, or the max line length is not positive.
     */
    public static function pretty(
        int $indent = self::DEFAULT_INDENT,
        int $maxLineLength = self::DEFAULT_MAX_LINE_LENGTH
    ): self {
        return new self(true, $indent, $maxLineLength);
    }

    #endregion
}
