<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Palette;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Palette\PaletteRampMath;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

/**
 * Computed ramp policy — gamut-relative chroma, no bundle hue_chroma map (079).
 */
final class PaletteGeneratorVibrancyRetuneTest extends TestCase
{
    private PaletteGenerator $generator;

    protected function setUp(): void
    {
        PaletteCatalog::reset();
        $this->generator = new PaletteGenerator();
    }

    protected function tearDown(): void
    {
        PaletteCatalog::reset();
    }

    #[Test]
    public function blue500UsesComputedMidpointLightness(): void
    {
        $recipe = ThemePaletteRecipe::fromPaletteDefinition(
            ThemePaletteRecipe::baseline()->hueBase(),
            ThemePaletteRecipe::baseline()->monoTones(),
        );
        $tuple = $this->generator->resolveToOklch('blue.500', $recipe);
        [$min, $max] = PaletteCatalog::lBounds();

        self::assertEqualsWithDelta(($min + $max) / 2.0, $tuple->l, 1e-6);
    }

    #[Test]
    public function hue500RampsDoNotChannelClipToPureRed(): void
    {
        $recipe = ThemePaletteRecipe::baseline();

        foreach (PaletteCatalog::hueFamilies() as $hue) {
            $hex = strtolower($this->generator->resolve($hue . '.500', $recipe));
            self::assertNotSame(
                '#ff0000',
                $hex,
                sprintf('%s.500 must not channel-clip to pure red.', $hue),
            );
        }
    }

    #[Test]
    public function lineageChromaOverrideLowersBlue500ChromaVersusDefault(): void
    {
        $baseline = ThemePaletteRecipe::fromPaletteDefinition(
            ThemePaletteRecipe::baseline()->hueBase(),
            ThemePaletteRecipe::baseline()->monoTones(),
        );
        $overridden = ThemePaletteRecipe::fromPaletteDefinition(
            ThemePaletteRecipe::baseline()->hueBase(),
            ThemePaletteRecipe::baseline()->monoTones(),
            ['blue' => 0.05],
        );

        $defaultTuple = $this->generator->resolveToOklch('blue.500', $baseline);
        $overrideTuple = $this->generator->resolveToOklch('blue.500', $overridden);

        self::assertGreaterThan($overrideTuple->c, $defaultTuple->c);
    }

    #[Test]
    public function chromaPercentScalesGamutRelativeVividness(): void
    {
        $full = new PaletteGenerator(null, null, new PaletteRampMath(chromaPercent: 100.0));
        $muted = new PaletteGenerator(null, null, new PaletteRampMath(chromaPercent: 50.0));
        $recipe = ThemePaletteRecipe::fromPaletteDefinition(
            ThemePaletteRecipe::baseline()->hueBase(),
            ThemePaletteRecipe::baseline()->monoTones(),
        );

        $fullTuple = $full->resolveToOklch('green.500', $recipe);
        $mutedTuple = $muted->resolveToOklch('green.500', $recipe);

        self::assertGreaterThan($mutedTuple->c, $fullTuple->c);
        self::assertEqualsWithDelta($fullTuple->c / 2.0, $mutedTuple->c, 0.02);
    }
}
