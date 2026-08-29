<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

enum ShadowTier: string
{
    case Sm = 'sm';
    case Md = 'md';
    case Lg = 'lg';

    public function cssKey(): string
    {
        return '--ui-shadow-' . $this->value;
    }
}
