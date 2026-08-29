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
        GeneratorPaletteConfigValidator::validateThemeMetaPalette($palette);

        $hues = $palette['hues'] ?? null;
        $chroma = $palette['chroma'] ?? [];
        $anchors = $palette['anchors'] ?? [];
        $anchorProfile = $palette['anchor_profile'] ?? null;
        $monoSaturation = TypeGuard::numericFloat($palette['mono_saturation'] ?? 0);

        if (!is_array($hues) || $hues === []) {
            throw new \InvalidArgumentException('Theme palette must define hues.');
        }

        $hueBase = [];
        foreach ($hues as $name => $value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException(sprintf('Palette hue "%s" must be numeric.', (string) $name));
            }
            $hueBase[(string) $name] = (float) $value;
        }

        $bundleHues = PaletteCatalog::monoHues();
        $monoTones = [];
        foreach (PaletteCatalog::monoTones() as $tone) {
            if (!isset($bundleHues[$tone])) {
                throw new \InvalidArgumentException(sprintf('Bundle mono_hues missing tone "%s".', $tone));
            }
            $monoTones[$tone] = [
                'hue' => $bundleHues[$tone],
                'saturation' => $monoSaturation,
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
