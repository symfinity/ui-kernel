<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

/**
 * Material axis identifier — glass, flat, retro (symfinity 111).
 */
enum PhysicsId: string
{
    case Glass = 'glass';
    case Flat = 'flat';
    case Retro = 'retro';

    public static function fromString(?string $value): self
    {
        if ($value === null || $value === '') {
            return self::Flat;
        }

        return self::tryFrom($value) ?? self::Flat;
    }
}
