<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfinity\UiKernel\Theme\LayoutProfile;

/**
 * Presets — typography, rhythm, motion fallback when a theme omits appearance tokens.
 */
final class PresetRegistry
{
    public function __construct(
        private readonly CompoundShadowBuilder $compoundShadowBuilder = new CompoundShadowBuilder(),
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function tokensFor(
        LayoutProfile $preset,
        string $schemaVersion = ThemeTokenSchema::V1_0,
        ColorMode $mode = ColorMode::Light,
    ): array {
        ThemeTokenSchema::requiredKeys($schemaVersion);

        $tokens = match ($preset) {
            LayoutProfile::Semantic => [
                '--ui-space-xs' => '0.25rem',
                '--ui-space-sm' => '0.5rem',
                '--ui-space-md' => '1rem',
                '--ui-space-lg' => '1.5rem',
                '--ui-space-xl' => '3rem',
                '--ui-space-2xl' => '4rem',
                '--ui-radius-xs' => '0.125rem',
                '--ui-radius-sm' => '0.25rem',
                '--ui-radius-md' => '0.375rem',
                '--ui-radius-lg' => '0.5rem',
                '--ui-radius-full' => '9999px',
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
                '--ui-motion-duration-fast' => '120ms',
                '--ui-motion-duration-normal' => '200ms',
                '--ui-motion-duration-slow' => '300ms',
                '--ui-motion-duration-skeleton' => '1.75s',
                '--ui-motion-easing-standard' => 'cubic-bezier(0.4, 0, 0.2, 1)',
                '--ui-motion-easing-linear' => 'linear',
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
                '--ui-radius-xs' => '0.125rem',
                '--ui-radius-sm' => '0.125rem',
                '--ui-radius-md' => '0.25rem',
                '--ui-radius-lg' => '0.5rem',
                '--ui-radius-full' => '9999px',
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
                '--ui-motion-duration-fast' => '100ms',
                '--ui-motion-duration-normal' => '150ms',
                '--ui-motion-duration-slow' => '280ms',
                '--ui-motion-duration-skeleton' => '1.6s',
                '--ui-motion-easing-standard' => 'cubic-bezier(0.4, 0, 0.2, 1)',
                '--ui-motion-easing-linear' => 'linear',
                '--ui-focus-ring-width' => '0.25rem',
                '--ui-focus-ring-opacity' => '0.25',
                '--ui-focus-ring-blur' => '0',
            ],
        };

        return $this->compoundShadowBuilder->applyToTokenMap(
            $tokens,
            LineageId::fromLayoutProfile($preset),
            $mode,
        );
    }
}
