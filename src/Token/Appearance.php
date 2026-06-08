<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Orthogonal surface mode for Button / Link ({@code data-ui-appearance}, 060).
 */
final class Appearance
{
    public const SOLID = 'solid';

    public const OUTLINE = 'outline';

    public const LINK = 'link';

    public const DEFAULT = self::SOLID;

    /** @var list<string> */
    public const ALL = [
        self::SOLID,
        self::OUTLINE,
        self::LINK,
    ];

    public static function isValid(string $value): bool
    {
        return \in_array($value, self::ALL, true);
    }
}
