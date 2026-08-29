<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Token;

/**
 * Supported W3C DTCG `$type` set for the kernel resolution core (076).
 *
 * `unknown` is forward-compat; `composite` holds preserved sub-structure.
 */
enum TokenType: string
{
    case Color = 'color';
    case Dimension = 'dimension';
    case Number = 'number';
    case FontFamily = 'fontFamily';
    case Duration = 'duration';
    case CubicBezier = 'cubicBezier';
    case Composite = 'composite';
    case Unknown = 'unknown';

    /**
     * Map a DTCG `$type` string (or null when omitted) to a known type.
     */
    public static function fromDtcg(?string $type): self
    {
        if ($type === null || $type === '') {
            return self::Unknown;
        }

        return self::tryFrom($type) ?? self::Unknown;
    }
}
