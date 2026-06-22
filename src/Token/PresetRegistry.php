<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfinity\UiKernel\Theme\LayoutProfile;

/**
 * Presets — typography and spacing rhythm only (111: material tokens owned by PhysicsRegistry).
 */
final class PresetRegistry
{
    /**
     * @return array<string, string>
     */
    public function tokensFor(
        LayoutProfile $preset,
        string $schemaVersion = ThemeTokenSchema::V1_0,
        ColorMode $mode = ColorMode::Light,
    ): array {
        ThemeTokenSchema::requiredKeys($schemaVersion);

        return match ($preset) {
            LayoutProfile::Semantic => [
                '--ui-space-xs' => '0.25rem',
                '--ui-space-sm' => '0.5rem',
                '--ui-space-md' => '1rem',
                '--ui-space-lg' => '1.5rem',
                '--ui-space-xl' => '3rem',
                '--ui-space-2xl' => '4rem',
                '--ui-grid-gap' => '1rem',
                '--ui-font-family-sans' => 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
                '--ui-font-family-mono' => 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace',
                '--ui-font-size-sm' => '0.875rem',
                '--ui-font-size-md' => '1rem',
                '--ui-font-size-lg' => '1.25rem',
                '--ui-font-weight-normal' => '400',
                '--ui-font-weight-medium' => '500',
                '--ui-font-weight-semibold' => '600',
                '--ui-line-height-tight' => '1.25',
                '--ui-line-height-normal' => '1.5',
                '--ui-line-height-relaxed' => '1.625',
                '--ui-focus-ring-width' => '0.25rem',
                '--ui-focus-ring-opacity' => '0.25',
                '--ui-focus-ring-blur' => '0',
            ],
            LayoutProfile::Utility => [
                '--ui-space-xs' => '0.25rem',
                '--ui-space-sm' => '0.5rem',
                '--ui-space-md' => '0.75rem',
                '--ui-space-lg' => '1.5rem',
                '--ui-space-xl' => '2.5rem',
                '--ui-space-2xl' => '3.5rem',
                '--ui-grid-gap' => '0.75rem',
                '--ui-font-family-sans' => 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, sans-serif',
                '--ui-font-family-mono' => 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace',
                '--ui-font-size-sm' => '0.875rem',
                '--ui-font-size-md' => '0.875rem',
                '--ui-font-size-lg' => '1.125rem',
                '--ui-font-weight-normal' => '400',
                '--ui-font-weight-medium' => '500',
                '--ui-font-weight-semibold' => '600',
                '--ui-line-height-tight' => '1.25',
                '--ui-line-height-normal' => '1.5',
                '--ui-line-height-relaxed' => '1.625',
                '--ui-focus-ring-width' => '0.25rem',
                '--ui-focus-ring-opacity' => '0.25',
                '--ui-focus-ring-blur' => '0',
            ],
        };
    }
}
