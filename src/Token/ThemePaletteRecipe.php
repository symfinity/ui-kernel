<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use InvalidArgumentException;

/**
 * Per-theme palette generation recipe (hue ramps + mono tone tints).
 *
 * Ref strings (e.g. blue.600) are resolved in the context of the active theme.
 */
final class ThemePaletteRecipe
{
    /**
     * @param array<string, float>                         $hueBase        hue family => degrees (0–360)
     * @param array<string, array{hue: float, saturation: float}> $monoTones tone => tint
     * @param array<string, float>                         $hueChroma      optional per-family base C @ level 500
     * @param array<string, string>                        $scaleAnchors   sparse ref => hex overrides (lineage stallion ramps)
     */
    public function __construct(
        private readonly array $hueBase,
        private readonly array $monoTones,
        private readonly array $hueChroma = [],
        private readonly array $scaleAnchors = [],
    ) {
        foreach (PaletteCatalog::hueFamilies() as $hue) {
            if (!isset($this->hueBase[$hue])) {
                throw new InvalidArgumentException(sprintf('Palette recipe missing hue base for "%s".', $hue));
            }
        }

        foreach (PaletteCatalog::monoTones() as $spice) {
            if (!isset($this->monoTones[$spice])) {
                throw new InvalidArgumentException(sprintf('Palette recipe missing mono tone "%s".', $spice));
            }
        }
    }

    public function hueDegrees(string $hue): float
    {
        if (!isset($this->hueBase[$hue])) {
            throw new InvalidArgumentException(sprintf('Unknown hue family "%s".', $hue));
        }

        return $this->hueBase[$hue];
    }

    public function hueChromaBase(string $hue): float
    {
        if (isset($this->hueChroma[$hue])) {
            return $this->hueChroma[$hue];
        }

        return PaletteCatalog::hueChroma($hue);
    }

    /**
     * @return array<string, float>
     */
    public function hueChromaOverrides(): array
    {
        return $this->hueChroma;
    }

    /**
     * @return array<string, string>
     */
    public function scaleAnchors(): array
    {
        return $this->scaleAnchors;
    }

    /**
     * Live OKLCH generation — no stallion-frozen ramp hex overrides.
     *
     * Used by ui-themer custom packs (061 lift): built-in lineages keep anchors;
     * author/export paths for brand packs MUST use this recipe shape.
     */
    public function withoutScaleAnchors(): self
    {
        if ($this->scaleAnchors === []) {
            return $this;
        }

        return new self($this->hueBase, $this->monoTones, $this->hueChroma, []);
    }

    public function monoHue(MonoTone $spice): float
    {
        return $this->monoTones[$spice->value]['hue'];
    }

    public function monoSaturation(MonoTone $spice): float
    {
        return $this->monoTones[$spice->value]['saturation'];
    }

    /**
     * @return array<string, float>
     */
    public function hueBase(): array
    {
        return $this->hueBase;
    }

    /**
     * @return array<string, array{hue: float, saturation: float}>
     */
    public function monoTones(): array
    {
        return $this->monoTones;
    }

    /**
     * Shared baseline recipe from the balanced lineage (default variant).
     */
    public static function baseline(): self
    {
        return ThemeConfig::get('default')->paletteRecipe();
    }

    /**
     * @param array<string, float> $hueBase full hue_base map (replaces baseline entirely)
     * @param array<string, array{hue?: float, saturation?: float}> $monoTones full mono_tones map
     * @param array<string, float>  $hueChroma optional per-family chroma overrides
     * @param array<string, string> $scaleAnchors optional sparse ramp overrides
     */
    public static function fromPaletteDefinition(
        array $hueBase,
        array $monoTones,
        array $hueChroma = [],
        array $scaleAnchors = [],
    ): self {
        $normalized = [];
        foreach ($monoTones as $tone => $params) {
            $normalized[$tone] = [
                'hue' => (float) ($params['hue'] ?? 0.0),
                'saturation' => (float) ($params['saturation'] ?? 0.0),
            ];
        }

        return new self($hueBase, $normalized, $hueChroma, $scaleAnchors);
    }
}
