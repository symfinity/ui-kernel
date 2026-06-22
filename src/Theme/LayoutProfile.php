<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

use Symfinity\UiKernel\Token\PresetRegistry;
use Symfinity\UiKernel\Token\ThemeTokenSchema;
use Symfinity\UiKernel\Theme\PhysicsRegistry;

/**
 * Design preset identifier — spacing, radius, type, motion presets.
 */
enum LayoutProfile
{
    case Semantic;
    case Utility;

    /**
     * @return array<string, string>
     */
    public function layout(string $schemaVersion = ThemeTokenSchema::V1_0): array
    {
        return [
            ...(new PresetRegistry())->tokensFor($this, $schemaVersion),
            ...(new PhysicsRegistry())->flatResolveTokens(),
        ];
    }
}
