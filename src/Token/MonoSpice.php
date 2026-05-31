<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

enum MonoSpice: string
{
    case Pure = 'pure';
    case Cool = 'cool';
    case Warm = 'warm';
    case Wood = 'wood';
    case Pope = 'pope';

    public function hue(): float
    {
        return match ($this) {
            self::Pure => 0.0,
            self::Cool => 220.0,
            self::Warm => 35.0,
            self::Wood => 130.0,
            self::Pope => 290.0,
        };
    }

    public function saturation(): float
    {
        return match ($this) {
            self::Pure => 0.0,
            default => 8.0,
        };
    }
}
