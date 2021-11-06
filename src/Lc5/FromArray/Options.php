<?php

declare(strict_types=1);

namespace Lc5\FromArray;

class Options
{
    public const DEFAULT = self::VALIDATE_MISSING | self::VALIDATE_REDUNDANT | self::VALIDATE_TYPES;
    public const VALIDATE_MISSING = 1;
    public const VALIDATE_REDUNDANT = 2;
    public const VALIDATE_TYPES = 4;
}
