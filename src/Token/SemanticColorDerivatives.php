<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfinity\UiKernel\Palette\OklchColorSpace;
use Symfinity\UiKernel\Palette\OklchTuple;

/**
 * Derived semantic colour tokens (contrast, hover, active) from resolved --ui-color-* SSOT.
 */
final class SemanticColorDerivatives
{
    /** @var list<string> */
    private const INTERACTIVE_ROLES = [
        'primary',
        'secondary',
        'tertiary',
        'danger',
        'success',
        'warning',
        'info',
    ];

    private const ON_LIGHT_THRESHOLD = 0.58;

    private const HOVER_L_MULTIPLIER = 0.88;

    private const ACTIVE_L_MULTIPLIER = 0.76;

    private const P3_CHROMA_BOOST = 1.12;

    private const P3_CHROMA_CEILING = 0.36;

    public function __construct(
        private readonly OklchColorSpace $colorSpace = new OklchColorSpace(),
    ) {
    }

    /**
     * @param array<string, string> $tokens resolved theme tokens including base colours
     *
     * @return array<string, string>
     */
    public function derive(array $tokens): array
    {
        $derived = [];

        foreach (self::INTERACTIVE_ROLES as $role) {
            $baseKey = '--ui-color-' . $role;
            if (!isset($tokens[$baseKey])) {
                continue;
            }

            $tuple = $this->colorSpace->parseColor($tokens[$baseKey]);
            $derived['--ui-color-on-' . $role] = $this->contrastForeground($tuple, $tokens);
            $derived['--ui-color-' . $role . '-hover'] = $this->relativeLightness($baseKey, self::HOVER_L_MULTIPLIER);
            $derived['--ui-color-' . $role . '-active'] = $this->relativeLightness($baseKey, self::ACTIVE_L_MULTIPLIER);
        }

        if (isset($tokens['--ui-color-text-muted'])) {
            $mutedTuple = $this->colorSpace->parseColor($tokens['--ui-color-text-muted']);
            $derived['--ui-color-on-muted'] = $this->contrastForeground($mutedTuple, $tokens);
        }

        if (isset($tokens['--ui-color-text'])) {
            $derived['--ui-color-on-ghost'] = $tokens['--ui-color-text'];
        }

        return $derived;
    }

    /**
     * @param array<string, string> $tokens
     *
     * @return list<array{key: string, css: string}>
     */
    public function p3Boosts(array $tokens): array
    {
        $boosts = [];

        foreach ($tokens as $key => $value) {
            if (!is_string($key) || !str_starts_with($key, '--ui-color-')) {
                continue;
            }
            if (!is_string($value) || str_contains($value, 'from var(')) {
                continue;
            }

            // Frozen #hex anchors are canonical sRGB SSOT — OKLCH round-trip skews hue on P3 displays.
            if (str_starts_with($value, '#')) {
                continue;
            }

            try {
                $tuple = $this->colorSpace->capToSrgbGamut($this->colorSpace->parseColor($value));
            } catch (\InvalidArgumentException) {
                continue;
            }

            if ($tuple->c <= 0.0) {
                continue;
            }

            $boosted = new OklchTuple(
                $tuple->l,
                min($tuple->c * self::P3_CHROMA_BOOST, self::P3_CHROMA_CEILING),
                $tuple->h,
                $tuple->alpha,
            );

            if ($boosted->c <= $tuple->c + 0.008) {
                continue;
            }

            $boosts[] = [
                'key' => $key,
                'css' => $this->colorSpace->toCss($boosted),
            ];
        }

        return $boosts;
    }

    /**
     * @param array<string, string> $tokens
     */
    private function contrastForeground(OklchTuple $background, array $tokens): string
    {
        if ($background->l >= self::ON_LIGHT_THRESHOLD) {
            if ($background->c >= 0.04 && $background->l < 0.75) {
                return 'oklch(1 0 0)';
            }

            return $tokens['--ui-color-text'];
        }

        return 'oklch(1 0 0)';
    }

    private function relativeLightness(string $baseKey, float $multiplier): string
    {
        $factor = rtrim(rtrim(sprintf('%.4f', $multiplier), '0'), '.');

        return sprintf('oklch(from var(%s) calc(l * %s) c h)', $baseKey, $factor);
    }
}
