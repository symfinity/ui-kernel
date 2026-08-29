<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Cross-cutting glass surface substrate tokens (symfinity 109).
 *
 * Panel blur ({@code --ui-glass-blur}) is independent of {@code --ui-backdrop-blur}.
 */
final class GlassSurfaceTokens
{
    /**
     * @param array<string, string> $merged Resolved theme variables before glass merge
     *
     * @return array<string, string>
     */
    public static function resolve(array $merged): array
    {
        return [
            '--ui-glass-blur' => '12px',
            '--ui-glass-saturate' => '1.6',
            '--ui-glass-tint' => 'color-mix(in oklch, var(--ui-color-surface) 65%, transparent)',
            '--ui-glass-border' => 'color-mix(in oklch, white 18%, transparent)',
            '--ui-glass-fallback-surface' => $merged['--ui-color-surface-elevated'],
        ];
    }
}
