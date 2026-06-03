<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Token;

use InvalidArgumentException;

/**
 * Internal palette SSOT — mono+spice ramps, hue families, alpha modifier.
 *
 * Generation parameters come from {@see ThemePaletteRecipe} (per theme).
 */
final class PaletteGenerator
{
    /** @var array<string, string> */
    private readonly array $scaleAnchors;

    /**
     * @param array<string, string>|null $scaleAnchors ref => hex/rgb (optional overrides)
     */
    public function __construct(?array $scaleAnchors = null)
    {
        $this->scaleAnchors = $scaleAnchors ?? PaletteScaleAnchors::all();
    }

    public function resolve(string $ref, ThemePaletteRecipe $recipe): string
    {
        PaletteRefGrammar::assertValid($ref);

        $base = $ref;
        $alpha = null;
        if (str_contains($ref, '@')) {
            [$base, $alphaToken] = explode('@', $ref, 2);
            $alpha = (int) $alphaToken;
        }

        if (isset($this->scaleAnchors[$base])) {
            $hex = $this->scaleAnchors[$base];

            return $alpha !== null ? $this->applyAlpha($this->normalizeOpaqueColour($hex), $alpha) : $hex;
        }

        if (preg_match('/^mono\.([a-z]+)\.(\d+)$/', $base, $matches) === 1) {
            $spice = MonoTone::from($matches[1]);
            $level = (int) $matches[2];
            $hex = $this->monoHex($spice, $level, $recipe);

            return $alpha !== null ? $this->applyAlpha($hex, $alpha) : $hex;
        }

        if (preg_match('/^([a-z]+)\.(\d+)$/', $base, $matches) === 1) {
            $hue = $matches[1];
            $level = (int) $matches[2];
            $hex = $this->hueHex($hue, $level, $recipe);

            return $alpha !== null ? $this->applyAlpha($hex, $alpha) : $hex;
        }

        throw new InvalidArgumentException(sprintf('Invalid palette ref "%s".', $ref));
    }

    /**
     * @return array<int, string> level => resolved colour
     */
    public function rampPreview(string $family, ThemePaletteRecipe $recipe, ?MonoTone $spice = null): array
    {
        if ($family === 'mono') {
            $spice ??= MonoTone::Pure;
            $steps = [];
            foreach (PaletteCatalog::rampLevels() as $level) {
                $steps[$level] = $this->monoHex($spice, $level, $recipe);
            }

            return $steps;
        }

        if (!in_array($family, PaletteCatalog::hueFamilies(), true)) {
            throw new InvalidArgumentException(sprintf('Unknown ramp family "%s".', $family));
        }

        $steps = [];
        foreach (PaletteCatalog::rampLevels() as $level) {
            $steps[$level] = $this->hueHex($family, $level, $recipe);
        }

        return $steps;
    }

    public function monoHex(MonoTone $spice, int $level, ThemePaletteRecipe $recipe): string
    {
        $curve = $spice === MonoTone::Pure ? PaletteCatalog::levelLightnessPure() : PaletteCatalog::levelLightness();
        $lightness = $curve[$level] ?? throw new InvalidArgumentException(sprintf('Unknown level %d.', $level));

        return $this->hslToHex($recipe->monoHue($spice), $recipe->monoSaturation($spice), $lightness);
    }

    public function hueHex(string $hue, int $level, ThemePaletteRecipe $recipe): string
    {
        $lightness = PaletteCatalog::levelLightness()[$level] ?? throw new InvalidArgumentException(sprintf('Unknown level %d.', $level));
        $saturation = $level >= 500 ? 72.0 : 85.0;

        return $this->hslToHex($recipe->hueDegrees($hue), $saturation, $lightness);
    }

    public function applyAlpha(string $hex, int $alphaPercent): string
    {
        $rgb = $this->hexToRgb($hex);
        $alpha = max(0, min(100, $alphaPercent)) / 100;

        return sprintf('rgba(%d, %d, %d, %s)', $rgb[0], $rgb[1], $rgb[2], rtrim(rtrim(sprintf('%.2f', $alpha), '0'), '.'));
    }

    private function normalizeOpaqueColour(string $colour): string
    {
        if (str_starts_with($colour, '#')) {
            return $colour;
        }

        if (preg_match('/^rgba?\((\d+),\s*(\d+),\s*(\d+)/', $colour, $matches) === 1) {
            return sprintf('#%02x%02x%02x', (int) $matches[1], (int) $matches[2], (int) $matches[3]);
        }

        throw new InvalidArgumentException(sprintf('Cannot apply alpha to colour "%s".', $colour));
    }

    private function hslToHex(float $h, float $s, float $l): string
    {
        $h /= 360;
        $s /= 100;
        $l /= 100;

        if ($s <= 0.0) {
            $v = (int) round($l * 255);

            return sprintf('#%02x%02x%02x', $v, $v, $v);
        }

        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;

        $r = (int) round($this->hueToRgb($p, $q, $h + 1 / 3) * 255);
        $g = (int) round($this->hueToRgb($p, $q, $h) * 255);
        $b = (int) round($this->hueToRgb($p, $q, $h - 1 / 3) * 255);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    private function hueToRgb(float $p, float $q, float $t): float
    {
        if ($t < 0) {
            $t += 1;
        }
        if ($t > 1) {
            $t -= 1;
        }
        if ($t < 1 / 6) {
            return $p + ($q - $p) * 6 * $t;
        }
        if ($t < 1 / 2) {
            return $q;
        }
        if ($t < 2 / 3) {
            return $p + ($q - $p) * (2 / 3 - $t) * 6;
        }

        return $p;
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }
}
