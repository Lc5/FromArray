<?php

declare(strict_types=1);

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
