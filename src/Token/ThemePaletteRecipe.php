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
     */
    public function __construct(
        private readonly array $hueBase,
        private readonly array $monoTones,
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
     * Shared baseline — used as template for built-in themes.
     */
    public static function baseline(): self
    {
        $baseline = PaletteCatalog::presets()['baseline'] ?? null;
        if (!is_array($baseline)) {
            throw new InvalidArgumentException('Missing baseline preset in palette catalog.');
        }

        return new self(
            hueBase: $baseline['hue_base'] ?? [],
            monoTones: $baseline['mono_tones'] ?? [],
        );
    }

    /**
     * @param array<string, float> $hueOverrides
     * @param array<string, array{hue?: float, saturation?: float}> $monoOverrides
     */
    public static function fromBaseline(array $hueOverrides = [], array $monoOverrides = []): self
    {
        $base = self::baseline();
        $hueBase = $base->hueBase;
        foreach ($hueOverrides as $hue => $degrees) {
            $hueBase[$hue] = $degrees;
        }

        $monoTones = $base->monoTones;
        foreach ($monoOverrides as $spice => $params) {
            $monoTones[$spice] = [
                'hue' => $params['hue'] ?? $monoTones[$spice]['hue'],
                'saturation' => $params['saturation'] ?? $monoTones[$spice]['saturation'],
            ];
        }

        return new self($hueBase, $monoTones);
    }
}
