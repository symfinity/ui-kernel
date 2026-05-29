<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Flavour;

/**
 * Non-color token scales per design lineage — spacing, radius, type, motion.
 *
 * These values drive component layout via {@see \Symfinity\UiKernel\Css\CssGenerator}
 * (padding, gaps, border-radius, font-size). They MUST differ materially per profile
 * so inspired-by flavours are not colour-only skins.
 */
enum LayoutProfile
{
    case Kiroshi;
    case Semantic;
    case Utility;

    /**
     * @return array<string, string>
     */
    public function layout(): array
    {
        return match ($this) {
            // Cyberpunk inspired
            self::Kiroshi => [
                '--ui-space-xs' => '0.125rem',
                '--ui-space-sm' => '0.375rem',
                '--ui-space-md' => '0.625rem',
                '--ui-space-lg' => '1.25rem',
                '--ui-space-xl' => '2rem',
                '--ui-radius-sm' => '0',
                '--ui-radius-md' => '0',
                '--ui-radius-lg' => '0',
                '--ui-font-family-sans' => '"Aptos", "Segoe UI", system-ui, sans-serif',
                '--ui-font-size-sm' => '0.8125rem',
                '--ui-font-size-md' => '0.9375rem',
                '--ui-font-size-lg' => '1.125rem',
                '--ui-transition-duration' => '150ms',
            ],
            // Bootstrap inspired
            self::Semantic => [
                '--ui-space-xs' => '0.25rem',
                '--ui-space-sm' => '0.5rem',
                '--ui-space-md' => '1rem',
                '--ui-space-lg' => '1.5rem',
                '--ui-space-xl' => '3rem',
                '--ui-radius-sm' => '0.25rem',
                '--ui-radius-md' => '0.375rem',
                '--ui-radius-lg' => '0.5rem',
                '--ui-font-family-sans' => 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
                '--ui-font-size-sm' => '0.875rem',
                '--ui-font-size-md' => '1rem',
                '--ui-font-size-lg' => '1.25rem',
                '--ui-transition-duration' => '200ms',
            ],
            // Tailwind inspired
            self::Utility => [
                '--ui-space-xs' => '0.25rem',
                '--ui-space-sm' => '0.5rem',
                '--ui-space-md' => '0.75rem',
                '--ui-space-lg' => '1.5rem',
                '--ui-space-xl' => '2.5rem',
                '--ui-radius-sm' => '0.125rem',
                '--ui-radius-md' => '0.25rem',
                '--ui-radius-lg' => '0.5rem',
                '--ui-font-family-sans' => 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, sans-serif',
                '--ui-font-size-sm' => '0.875rem',
                '--ui-font-size-md' => '0.875rem',
                '--ui-font-size-lg' => '1.125rem',
                '--ui-transition-duration' => '150ms',
            ],
        };
    }
}
