<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

enum ThemeColorScheme: string
{
    case Auto = 'auto';
    case Light = 'light';
    case Dark = 'dark';

    public static function tryFromString(string $value): ?self
    {
        return self::tryFrom(strtolower($value));
    }
}
