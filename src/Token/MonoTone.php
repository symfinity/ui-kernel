<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

enum MonoTone: string
{
    case Slate = 'slate';
    case Stone = 'stone';
    case Sage = 'sage';
    case Mauve = 'mauve';
    case Rust = 'rust';
    case Neutral = 'neutral';

    public function isAchromatic(): bool
    {
        return $this === self::Neutral;
    }
}
