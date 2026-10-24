<?php

declare(strict_types=1);

namespace Mossy2100\Core;

use RuntimeException;

/**
 * Utility class for detecting runtime environment characteristics.
 */
final class Environment
{
    /**
     * Invariant locale, matching the root locale in the Unix Common Locale Data Repository (CLDR).
     */
    public const string INVARIANT_LOCALE = 'en_US_POSIX';

    /**
     * Private constructor to prevent instantiation.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    // @codeCoverageIgnoreStart
    // Ignore these two methods for code coverage. We can't test them properly without running PHP on a 32-bit system,
    // which is unnecessary for such a simple class.

    /**
     * Check if the system is 64-bit.
     *
     * @return bool True if the system is 64-bit, false otherwise.
     */
    public static function is64Bit(): bool
    {
        return PHP_INT_SIZE === 8;
    }

    /**
     * Require that the system is 64-bit.
     *
     * @return void
     * @throws RuntimeException If the system is not 64-bit.
     */
    public static function require64Bit(): void
    {
        if (!self::is64Bit()) {
            throw new RuntimeException('This operation requires a 64-bit system.');
        }
    }

    // @codeCoverageIgnoreEnd
}
