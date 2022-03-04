<?php

declare(strict_types=1);
/*
 * This file is part of the lc5/from-array package.
 *
 * (c) Łukasz Krzyszczak <lukasz.krzyszczak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lc5\FromArray;

final class Options
{
    /**
     * @var int
     */
    public const DEFAULT = self::VALIDATE_MISSING | self::VALIDATE_REDUNDANT | self::VALIDATE_TYPES;

    /**
     * @var int
     */
    public const VALIDATE_MISSING = 1;

    /**
     * @var int
     */
    public const VALIDATE_REDUNDANT = 2;

    /**
     * @var int
     */
    public const VALIDATE_TYPES = 4;
}
