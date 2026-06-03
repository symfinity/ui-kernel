<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Theme;

use Symfinity\UiKernel\Token\PresetRegistry;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

/**
 * Design preset identifier — spacing, radius, type, motion presets.
 */
enum LayoutProfile
{
    case Kiroshi;
    case Semantic;
    case Utility;

    /**
     * @return array<string, string>
     */
    public function layout(string $schemaVersion = ThemeTokenSchema::V1_0): array
    {
        return (new PresetRegistry())->tokensFor($this, $schemaVersion);
    }
}
