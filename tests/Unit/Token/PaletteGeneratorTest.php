<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Token\MonoTone;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\ThemeConfig;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

final class PaletteGeneratorTest extends TestCase
{
    #[Test]
    public function blue500UsesComputedMidpointLightness(): void
    {
        $generator = new PaletteGenerator();
        $recipe = ThemePaletteRecipe::fromPaletteDefinition(
            ThemePaletteRecipe::baseline()->hueBase(),
            ThemePaletteRecipe::baseline()->monoTones(),
        );
        $tuple = $generator->resolveToOklch('blue.500', $recipe);
        [$min, $max] = PaletteCatalog::lBounds();

        self::assertEqualsWithDelta(($min + $max) / 2.0, $tuple->l, 1e-6);
    }

    #[Test]
    public function itResolvesGeneratedMonoAndHueRefs(): void
    {
        $generator = new PaletteGenerator();
        $recipe = ThemePaletteRecipe::baseline();

        self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $generator->resolve('mono.stone.900', $recipe));
        self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $generator->resolve('blue.600', $recipe));
    }

    #[Test]
    public function sameRefResolvesDifferentlyPerThemeRecipe(): void
    {
        $generator = new PaletteGenerator();
        $default = ThemeConfig::get('default')->paletteRecipe();
        $semantic = ThemeConfig::get('semantic')->paletteRecipe();

        self::assertNotSame(
            $generator->resolve('blue.500', $default),
            $generator->resolve('blue.500', $semantic),
        );
    }

    #[Test]
    public function pureMonoSpansWhiteToBlackTintedSpicesUseNarrowCurve(): void
    {
        $generator = new PaletteGenerator();
        $recipe = ThemePaletteRecipe::baseline();

        self::assertSame('#ffffff', $generator->monoHex(MonoTone::Neutral, 50, $recipe));
        self::assertSame('#000000', $generator->monoHex(MonoTone::Neutral, 950, $recipe));

        self::assertNotSame(
            $generator->monoHex(MonoTone::Neutral, 500, $recipe),
            $generator->monoHex(MonoTone::Stone, 500, $recipe),
        );
    }

    #[Test]
    public function monoSpiceLevelsDiffer(): void
    {
        $generator = new PaletteGenerator();
        $recipe = ThemePaletteRecipe::baseline();

        $light = $generator->monoHex(MonoTone::Slate, 100, $recipe);
        $dark = $generator->monoHex(MonoTone::Slate, 950, $recipe);

        self::assertNotSame($light, $dark);
    }

    #[Test]
    public function spicesProduceDistinctMonoRamps(): void
    {
        $generator = new PaletteGenerator();
        $recipe = ThemePaletteRecipe::baseline();

        self::assertNotSame(
            $generator->monoHex(MonoTone::Neutral, 500, $recipe),
            $generator->monoHex(MonoTone::Stone, 500, $recipe),
        );
    }

    #[Test]
    public function itAppliesAlphaModifier(): void
    {
        $generator = new PaletteGenerator();
        $recipe = ThemeConfig::get('semantic')->paletteRecipe();

        self::assertMatchesRegularExpression(
            '/^rgba\(\d+, \d+, \d+, 0\.4\)$/',
            $generator->resolve('mono.slate.900@40', $recipe),
        );
    }

    #[Test]
    public function generatedHueOutputIsHex(): void
    {
        $generator = new PaletteGenerator();
        $recipe = ThemePaletteRecipe::baseline();
        $hex = $generator->hueHex('blue', 600, $recipe);

        self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $hex);
        self::assertStringNotContainsString('oklch', $hex);
    }

    #[Test]
    public function rampPreviewReturnsTenSteps(): void
    {
        $generator = new PaletteGenerator();
        $recipe = ThemePaletteRecipe::baseline();

        $mono = $generator->rampPreview('mono', $recipe, MonoTone::Stone);
        $blue = $generator->rampPreview('blue', $recipe);

        self::assertCount(11, $mono);
        self::assertCount(11, $blue);
        self::assertArrayHasKey(500, $mono);
        self::assertArrayHasKey(950, $blue);
    }

    #[Test]
    public function emptyScaleAnchorsUseGeneratorOnly(): void
    {
        $generator = new PaletteGenerator([]);
        $recipe = ThemePaletteRecipe::baseline();

        $first = $generator->resolve('green.500', $recipe);
        $second = $generator->resolve('green.500', $recipe);

        self::assertSame($first, $second);
    }
}
