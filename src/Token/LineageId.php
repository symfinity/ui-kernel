<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfinity\UiKernel\Theme\LayoutProfile;

enum LineageId: string
{
    case Kiroshi = 'kiroshi';
    case Semantic = 'semantic';
    case Utility = 'utility';

    public static function fromThemeLineage(string $lineage): self
    {
        return match ($lineage) {
            'utility' => self::Utility,
            'semantic' => self::Semantic,
            default => self::Kiroshi,
        };
    }

    public static function fromLayoutProfile(LayoutProfile $layout): self
    {
        return match ($layout) {
            LayoutProfile::Utility => self::Utility,
            LayoutProfile::Semantic => self::Semantic,
        };
    }
}
