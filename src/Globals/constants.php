<?php

/**
 * @file
 * Useful constants.
 */

declare(strict_types=1);

namespace OceanMoon\Core\Globals;

// @codeCoverageIgnoreStart

/**
 * The circle constant tau τ (tau) = 2π. Equal to the number of radians in a circle.
 *
 * To use it without requiring the namespace every time, include the following line:
 * ```php
 * use const OceanMoon\Core\Globals\M_TAU;
 * ```
 */
const M_TAU = 2 * M_PI;

/**
 * The marker used by Arrays and Stringify to represent a circular reference.
 *
 * This is intended to match the recursion marker text ("*RECURSION*") used by the print_r() function.
 */
const RECURSION = '*RECURSION*';

/**
 * Regex for numbers.
 *
 * To use it without requiring the namespace every time, include the following line:
 * ```php
 * use const OceanMoon\Core\Globals\NUMBER_REGEX;
 * ```
 */
const NUMBER_REGEX = '-?(?:\d+(?:\.\d+)?|\.\d+)(?:[eE][+-]?\d+)?';

// @codeCoverageIgnoreEnd
