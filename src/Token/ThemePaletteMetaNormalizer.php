<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use Symfinity\UiKernel\Internal\TypeGuard;

/**
 * Normalizes {@code theme.meta.yaml} palette blocks for built-in DTCG catalog load (077).
 */
final class ThemePaletteMetaNormalizer
{
    /**
     * @param array<string, mixed> $palette
     *
     * @return array{
     *     hue_base: array<string, float>,
     *     mono_tones: array<string, array{hue: float, saturation: float}>,
     *     hue_chroma: array<string, float>,
     *     scale_anchors: array<string, string>
     * }
     */
    public static function normalize(array $palette): array
    {
        $hues = $palette['hues'] ?? null;
        $mono = $palette['mono'] ?? null;
        $chroma = $palette['chroma'] ?? [];
        $anchors = $palette['anchors'] ?? [];
        $anchorProfile = $palette['anchor_profile'] ?? null;

        if (!is_array($hues) || !is_array($mono) || $hues === [] || $mono === []) {
            throw new \InvalidArgumentException('Theme palette must define hues and mono.');
        }

        $hueBase = [];
        foreach ($hues as $name => $value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException(sprintf('Palette hue "%s" must be numeric.', (string) $name));
            }
            $hueBase[(string) $name] = (float) $value;
        }

        $monoTones = [];
        foreach ($mono as $name => $tone) {
            if (!is_array($tone)) {
                throw new \InvalidArgumentException(sprintf('Mono tone "%s" must be a mapping with hue and saturation.', (string) $name));
            }
            $monoTones[(string) $name] = [
                'hue' => TypeGuard::numericFloat($tone['hue'] ?? 0),
                'saturation' => TypeGuard::numericFloat($tone['saturation'] ?? 0),
            ];
        }

        $hueChroma = [];
        if ($chroma !== []) {
            if (!is_array($chroma)) {
                throw new \InvalidArgumentException('Theme palette.chroma must be a mapping when present.');
            }
            foreach ($chroma as $name => $value) {
                if (!is_numeric($value)) {
                    throw new \InvalidArgumentException(sprintf('Palette chroma "%s" must be numeric.', (string) $name));
                }
                $hueChroma[(string) $name] = (float) $value;
            }
            $unknown = array_diff(array_keys($hueChroma), PaletteCatalog::hueFamilies());
            if ($unknown !== []) {
                throw new \InvalidArgumentException(sprintf(
                    'Palette chroma has unknown hue families: %s.',
                    implode(', ', $unknown),
                ));
            }
        }

        $scaleAnchors = [];
        if (is_string($anchorProfile) && $anchorProfile !== '') {
            $scaleAnchors = PaletteAnchorProfiles::get($anchorProfile);
        }
        if ($anchors !== []) {
            if (!is_array($anchors)) {
                throw new \InvalidArgumentException('Theme palette.anchors must be a mapping when present.');
            }
            foreach ($anchors as $ref => $hex) {
                if (!is_string($ref) || !is_string($hex)) {
                    throw new \InvalidArgumentException('Theme palette.anchors entries must be ref => hex strings.');
                }
                PaletteRefGrammar::assertValid($ref);
                if (!preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $hex)) {
                    throw new \InvalidArgumentException(sprintf('Theme palette anchor "%s" must be a #hex colour.', $ref));
                }
                $scaleAnchors[$ref] = strtolower($hex);
            }
        }

        return [
            'hue_base' => $hueBase,
            'mono_tones' => $monoTones,
            'hue_chroma' => $hueChroma,
            'scale_anchors' => $scaleAnchors,
        ];
    }
}
