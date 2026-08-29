<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

/**
 * Emits compound inset + drop {@code --ui-shadow-*} strings at resolve time (098).
 */
final class CompoundShadowBuilder
{
    public const FALLBACK_SURFACE_LIGHT = 'oklch(0.98 0.005 260)';

    public const FALLBACK_SURFACE_DARK = 'oklch(0.22 0.01 260)';

    public function compose(ShadowTier $tier, LineageId $lineage, ColorMode $mode, string $resolvedSurface): string
    {
        [$offsetY, $blur, $spread] = match ($tier) {
            ShadowTier::Sm => ['2px', '4px', '0'],
            ShadowTier::Md => ['4px', '8px', '0'],
            ShadowTier::Lg => ['8px', '24px', '0'],
        };

        $alpha = $this->dropAlpha($tier, $lineage, $mode);
        $highlight = $this->highlightColor($mode, $resolvedSurface);
        $drop = sprintf('color-mix(in oklch, black %.1f%%, transparent)', $alpha * 100);

        return sprintf(
            'inset 0 1px 0 %s, 0 %s %s %s %s',
            $highlight,
            $offsetY,
            $blur,
            $spread,
            $drop,
        );
    }

    /**
     * @param array<string, string> $tokens
     *
     * @return array<string, string>
     */
    public function applyToTokenMap(array $tokens, LineageId $lineage, ColorMode $mode): array
    {
        $surface = $tokens['--ui-color-surface-elevated']
            ?? ($mode === ColorMode::Dark ? self::FALLBACK_SURFACE_DARK : self::FALLBACK_SURFACE_LIGHT);

        foreach (ShadowTier::cases() as $tier) {
            $tokens[$tier->cssKey()] = $this->compose($tier, $lineage, $mode, $surface);
        }

        return $tokens;
    }

    private function highlightColor(ColorMode $mode, string $resolvedSurface): string
    {
        $mix = $mode === ColorMode::Light ? '85%' : '90%';

        return sprintf('color-mix(in oklch, %s %s, white)', $resolvedSurface, $mix);
    }

    private function dropAlpha(ShadowTier $tier, LineageId $lineage, ColorMode $mode): float
    {
        $mdLight = match ($lineage) {
            LineageId::Kiroshi => 0.11,
            LineageId::Semantic => 0.14,
            LineageId::Utility => 0.09,
        };

        $tierScale = match ($tier) {
            ShadowTier::Sm => 0.75,
            ShadowTier::Md => 1.0,
            ShadowTier::Lg => 1.35,
        };

        $alpha = $mdLight * $tierScale;

        if ($mode === ColorMode::Dark) {
            $alpha *= match ($lineage) {
                LineageId::Kiroshi => 1.30,
                LineageId::Semantic => 1.35,
                LineageId::Utility => 1.25,
            };
        }

        return $alpha;
    }
}
