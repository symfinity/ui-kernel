<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Flavour;

use Symfinity\UiKernel\Token\LineagePresetRegistry;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

/**
 * Design lineage identifier — spacing, radius, type, motion presets.
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
        return (new LineagePresetRegistry())->tokensFor($this, $schemaVersion);
    }
}
