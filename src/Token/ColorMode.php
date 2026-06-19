<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

enum ColorMode: string
{
    case Light = 'light';
    case Dark = 'dark';

    public static function fromThemeMode(string $mode): self
    {
        return $mode === 'dark' ? self::Dark : self::Light;
    }
}
