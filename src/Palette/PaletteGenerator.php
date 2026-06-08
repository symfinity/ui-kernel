<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Palette;

use InvalidArgumentException;
use Symfinity\UiKernel\Token\MonoTone;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\PaletteRefGrammar;
use Symfinity\UiKernel\Token\PaletteScaleAnchors;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

/**
 * Internal palette SSOT — mono+spice ramps, hue families, alpha modifier.
 *
 * Generation uses OKLCH interpolation per bundle generator.palette.interpolation.
 */
final class PaletteGenerator
{
    private readonly OklchColorSpace $colorSpace;

    /** @var array<string, string> */
    private readonly array $scaleAnchors;

    /**
     * @param array<string, string>|null $scaleAnchors ref => hex/rgb (optional overrides)
     */
    public function __construct(
        ?array $scaleAnchors = null,
        ?OklchColorSpace $colorSpace = null,
    ) {
        $this->scaleAnchors = $scaleAnchors ?? PaletteScaleAnchors::all();
        $this->colorSpace = $colorSpace ?? new OklchColorSpace();
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

    public function resolveToOklch(string $ref, ThemePaletteRecipe $recipe): OklchTuple
    {
        PaletteRefGrammar::assertValid($ref);

        $base = $ref;
        if (str_contains($ref, '@')) {
            [$base] = explode('@', $ref, 2);
        }

        if (isset($this->scaleAnchors[$base])) {
            return $this->colorSpace->fromHex($this->normalizeOpaqueColour($this->scaleAnchors[$base]));
        }

        if (preg_match('/^mono\.([a-z]+)\.(\d+)$/', $base, $matches) === 1) {
            return $this->monoOklch(MonoTone::from($matches[1]), (int) $matches[2], $recipe);
        }

        if (preg_match('/^([a-z]+)\.(\d+)$/', $base, $matches) === 1) {
            return $this->hueOklch($matches[1], (int) $matches[2], $recipe);
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
            foreach (PaletteCatalog::levels() as $level) {
                $steps[$level] = $this->monoHex($spice, $level, $recipe);
            }

            return $steps;
        }

        if (!in_array($family, PaletteCatalog::hueFamilies(), true)) {
            throw new InvalidArgumentException(sprintf('Unknown ramp family "%s".', $family));
        }

        $steps = [];
        foreach (PaletteCatalog::levels() as $level) {
            $steps[$level] = $this->hueHex($family, $level, $recipe);
        }

        return $steps;
    }

    public function monoHex(MonoTone $spice, int $level, ThemePaletteRecipe $recipe): string
    {
        return $this->colorSpace->toSrgb($this->monoOklch($spice, $level, $recipe));
    }

    public function hueHex(string $hue, int $level, ThemePaletteRecipe $recipe): string
    {
        return $this->colorSpace->toSrgb($this->hueOklch($hue, $level, $recipe));
    }

    public function monoOklch(MonoTone $spice, int $level, ThemePaletteRecipe $recipe): OklchTuple
    {
        $curveKey = $spice === MonoTone::Pure ? 'pure' : 'default';
        $lightness = PaletteCatalog::oklchLightnessCurve($curveKey)[$level]
            ?? throw new InvalidArgumentException(sprintf('Unknown level %d.', $level));

        if ($spice === MonoTone::Pure) {
            return new OklchTuple($lightness, 0.0, 0.0);
        }

        $hue = $recipe->monoHue($spice);
        $saturation = $recipe->monoSaturation($spice);
        $chroma = $this->monoSpiceChroma($saturation, $lightness);

        return new OklchTuple($lightness, $chroma, $hue);
    }

    public function hueOklch(string $hue, int $level, ThemePaletteRecipe $recipe): OklchTuple
    {
        $lightness = PaletteCatalog::oklchLightnessCurve('default')[$level]
            ?? throw new InvalidArgumentException(sprintf('Unknown level %d.', $level));
        $baseChroma = PaletteCatalog::hueChroma($hue);
        $chroma = $this->hueChromaForLevel($level, $baseChroma);

        return new OklchTuple($lightness, $chroma, $recipe->hueDegrees($hue));
    }

    public function applyAlpha(string $hex, int $alphaPercent): string
    {
        $rgb = $this->hexToRgb($hex);
        $alpha = max(0, min(100, $alphaPercent)) / 100;

        return sprintf('rgba(%d, %d, %d, %s)', $rgb[0], $rgb[1], $rgb[2], rtrim(rtrim(sprintf('%.2f', $alpha), '0'), '.'));
    }

    private function monoSpiceChroma(float $saturationPercent, float $lightness): float
    {
        if ($saturationPercent <= 0.0) {
            return 0.0;
        }

        $edgeFactor = 1.0 - abs(2.0 * $lightness - 1.0);

        return ($saturationPercent / 100.0) * 0.04 * max(0.15, $edgeFactor);
    }

    private function hueChromaForLevel(int $level, float $baseChroma): float
    {
        $distance = abs($level - 500) / 450.0;
        $scale = max(0.35, 1.0 - $distance * 0.65);

        return $baseChroma * $scale;
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
