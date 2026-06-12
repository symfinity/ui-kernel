<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Palette;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Palette\PaletteRampSampler;
use Symfinity\UiKernel\Token\MonoTone;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\ThemeConfig;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

final class PaletteGeneratorOklchTest extends TestCase
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
    public function bundleUsesOklchInterpolation(): void
    {
        self::assertSame('oklch', PaletteCatalog::interpolation());
        self::assertSame(1, PaletteCatalog::revision());
    }

    #[Test]
    public function generatorModuleContainsNoHsl(): void
    {
        $path = dirname(__DIR__, 3) . '/src/Palette/PaletteGenerator.php';
        $source = file_get_contents($path);

        self::assertIsString($source);
        self::assertStringNotContainsString('hsl(', strtolower($source));
        self::assertStringNotContainsString('hsltohex', strtolower($source));
    }

    #[Test]
    public function fullMonoCoolRampResolvesWithoutException(): void
    {
        $recipe = ThemePaletteRecipe::baseline();

        foreach (PaletteCatalog::levels() as $level) {
            $hex = $this->generator->resolve(sprintf('mono.cool.%d', $level), $recipe);
            self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $hex, 'level ' . $level);
        }
    }

    #[Test]
    public function eachContractHueLevel500Resolves(): void
    {
        $recipe = ThemePaletteRecipe::baseline();

        foreach (PaletteCatalog::hueFamilies() as $hue) {
            $hex = $this->generator->resolve($hue . '.500', $recipe);
            self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $hex, $hue);
        }
    }

    #[Test]
    public function sparseAnchorOverrideIsHonoured(): void
    {
        $generator = new PaletteGenerator(['blue.500' => '#112233']);
        $recipe = ThemePaletteRecipe::baseline();

        self::assertSame('#112233', $generator->resolve('blue.500', $recipe));
    }

    #[Test]
    public function alphaModifierResolvesToRgba(): void
    {
        $recipe = ThemePaletteRecipe::baseline();

        self::assertMatchesRegularExpression(
            '/^rgba\(\d+, \d+, \d+, 0\.4\)$/',
            $this->generator->resolve('mono.cool.900@40', $recipe),
        );
    }

    #[Test]
    public function goldenOklchTuplesAreStable(): void
    {
        $default = ThemeConfig::get('default')->paletteRecipe();
        $recipe = ThemePaletteRecipe::fromPaletteDefinition(
            $default->hueBase(),
            $default->monoTones(),
        );

        $cool500 = $this->generator->resolveToOklch('mono.cool.500', $recipe);
        $blue600 = $this->generator->resolveToOklch('blue.600', $recipe);

        self::assertGreaterThan(0.0, $cool500->l);
        self::assertLessThan(1.0, $cool500->l);
        self::assertGreaterThan(0.0, $cool500->c);
        self::assertSame(240.0, $cool500->h);

        self::assertGreaterThan(0.0, $blue600->l);
        self::assertGreaterThan(0.0, $blue600->c);
        self::assertSame(258.0, $blue600->h);
    }

    #[Test]
    public function pureMonoSpansWhiteToBlack(): void
    {
        $recipe = ThemePaletteRecipe::baseline();

        self::assertSame('#ffffff', $this->generator->monoHex(MonoTone::Pure, 100, $recipe));
        self::assertSame('#000000', $this->generator->monoHex(MonoTone::Pure, 950, $recipe));
    }
}
