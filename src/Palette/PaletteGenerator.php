<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Palette;

use InvalidArgumentException;
use Symfinity\UiKernel\Dtcg\DtcgDocument;
use Symfinity\UiKernel\Dtcg\DtcgTreeBuilder;
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

        $anchors = $this->anchorsFor($recipe);
        if (isset($anchors[$base])) {
            $hex = $anchors[$base];

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

    public function resolveToCss(string $ref, ThemePaletteRecipe $recipe): string
    {
        PaletteRefGrammar::assertValid($ref);

        $base = $ref;
        $alpha = null;
        if (str_contains($ref, '@')) {
            [$base, $alphaToken] = explode('@', $ref, 2);
            $alpha = (int) $alphaToken;
        }

        $anchors = $this->anchorsFor($recipe);
        if (isset($anchors[$base])) {
            $hex = $this->normalizeOpaqueColour($anchors[$base]);

            return $alpha !== null ? $this->applyAlpha($hex, $alpha) : $hex;
        }

        $tuple = $this->resolveToOklch($ref, $recipe);
        if ($alpha !== null) {
            $tuple = $tuple->withAlpha(max(0, min(100, $alpha)) / 100);
        }

        return $this->colorSpace->toCss($tuple);
    }

    public function resolveToOklch(string $ref, ThemePaletteRecipe $recipe): OklchTuple
    {
        PaletteRefGrammar::assertValid($ref);

        $base = $ref;
        if (str_contains($ref, '@')) {
            [$base] = explode('@', $ref, 2);
        }

        $anchors = $this->anchorsFor($recipe);
        if (isset($anchors[$base])) {
            return $this->colorSpace->fromHex($this->normalizeOpaqueColour($anchors[$base]));
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
     * Materialize palette ramps as a W3C DTCG document for the base layer (077).
     */
    public function materializeDtcgDocument(
        ThemePaletteRecipe $recipe,
        string $lineageId = 'default',
        ?string $anchorProfile = null,
    ): DtcgDocument {
        $colorTree = [];

        foreach (PaletteCatalog::hueFamilies() as $hue) {
            foreach (PaletteCatalog::levels() as $level) {
                $ref = sprintf('%s.%d', $hue, $level);
                $colorTree[$hue][(string) $level] = [
                    '$type' => 'color',
                    '$value' => $this->resolveToCss($ref, $recipe),
                ];
            }
        }

        $monoTree = [];
        foreach (PaletteCatalog::monoTones() as $toneName) {
            foreach (PaletteCatalog::levels() as $level) {
                $ref = sprintf('mono.%s.%d', $toneName, $level);
                $monoTree[$toneName][(string) $level] = [
                    '$type' => 'color',
                    '$value' => $this->resolveToCss($ref, $recipe),
                ];
            }
        }

        $colorTree['mono'] = $monoTree;

        $extensions = [
            'generation' => 'live',
            'revision' => PaletteCatalog::revision(),
            'lineage' => $lineageId,
        ];
        if ($anchorProfile !== null && $anchorProfile !== '') {
            $extensions['generation'] = 'frozen';
            $extensions['freeze'] = 'COLOR_FREEZE_v1';
            $extensions['anchor_profile'] = $anchorProfile;
        }

        $tree = [
            'color' => $colorTree,
            '$extensions' => [
                'symfinity' => $extensions,
            ],
        ];

        return (new DtcgTreeBuilder())->build($tree);
    }

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
        $baseChroma = $recipe->hueChromaBase($hue);
        $chroma = $this->hueChromaForLevel($level, $baseChroma);
        $hueDegrees = $recipe->hueDegrees($hue);
        $chroma = $this->colorSpace->maxInGamutChroma($lightness, $hueDegrees, $chroma);

        return new OklchTuple($lightness, $chroma, $hueDegrees);
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
        // Tinted neutrals must read at surface steps (mono.100–200); prior 0.04× floor 0.15 was ~imperceptible.
        return ($saturationPercent / 100.0) * 0.24 * max(0.40, $edgeFactor);
    }

    private function hueChromaForLevel(int $level, float $baseChroma): float
    {
        $distance = abs($level - 500) / 450.0;
        // Floor 0.48 keeps 100–300 ramps vivid enough for BS/TW warning + success refs.
        $scale = max(0.48, 1.0 - $distance * 0.55);

        return $baseChroma * $scale;
    }

    /**
     * @return array<string, string>
     */
    private function anchorsFor(ThemePaletteRecipe $recipe): array
    {
        return array_merge(
            PaletteScaleAnchors::all(),
            $recipe->scaleAnchors(),
            $this->scaleAnchors,
        );
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
