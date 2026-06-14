<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Contract\Layer;

/**
 * Token layer roles and their merge precedence (low to high): base < design_system < theme (076).
 */
enum LayerRole: string
{
    case Base = 'base';
    case DesignSystem = 'design_system';
    case Theme = 'theme';

    public function precedence(): int
    {
        return match ($this) {
            self::Base => 0,
            self::DesignSystem => 1,
            self::Theme => 2,
        };
    }
}
