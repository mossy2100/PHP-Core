<?php

declare(strict_types=1);

namespace OceanMoon\Core\Tests;

use Locale;
use OceanMoon\Core\Environment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Environment utility class.
 *
 * is64Bit() and require64Bit() are excluded from coverage in the source (see @codeCoverageIgnoreStart/End there)
 * since their 32-bit branches can't be exercised without running PHP on a 32-bit system.
 */
#[CoversClass(Environment::class)]
final class EnvironmentTest extends TestCase
{
    #region Method getLocale() tests.

    /**
     * Test getLocale() falls back to PHP's default locale when no Accept-Language header is present.
     */
    public function testGetLocaleFallsBackToDefault(): void
    {
        $original = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
        unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);

        try {
            $this->assertSame(Locale::getDefault(), Environment::getLocale());
        } finally {
            if ($original !== null) {
                $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $original;
            }
        }
    }

    /**
     * Test getLocale() detects the locale from the Accept-Language header when present.
     */
    public function testGetLocaleDetectsFromAcceptLanguageHeader(): void
    {
        $original = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-DE,de;q=0.9,en;q=0.8';

        try {
            $this->assertSame('de_DE', Environment::getLocale());
        } finally {
            if ($original === null) {
                unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            } else {
                $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $original;
            }
        }
    }

    /**
     * Test getLocale() always returns a non-empty string, regardless of environment.
     */
    public function testGetLocaleReturnsNonEmptyString(): void
    {
        $this->assertNotSame('', Environment::getLocale());
    }

    #endregion
}
