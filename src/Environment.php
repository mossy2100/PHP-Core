<?php

declare(strict_types=1);

namespace OceanMoon\Core;

use RuntimeException;

/**
 * Utility class for detecting runtime environment characteristics.
 */
final class Environment
{
    /**
     * Private constructor to prevent instantiation.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    // @codeCoverageIgnoreStart
    // We can't test these methods properly without running PHP on a 32-bit system; unnecessary for such a simple class.

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
