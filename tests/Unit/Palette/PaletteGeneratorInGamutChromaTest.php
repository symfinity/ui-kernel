<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Palette;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Palette\OklchColorSpace;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Theme\ThemeRegistry;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

final class PaletteGeneratorInGamutChromaTest extends TestCase
{
    private PaletteGenerator $generator;

    private OklchColorSpace $colorSpace;

    protected function setUp(): void
    {
        PaletteCatalog::reset();
        $this->generator = new PaletteGenerator();
        $this->colorSpace = new OklchColorSpace();
    }

    protected function tearDown(): void
    {
        PaletteCatalog::reset();
    }

    #[Test]
    public function generatorRevisionUsesPerHueInGamutChromaTargets(): void
    {
        self::assertSame(1, PaletteCatalog::revision());
        self::assertGreaterThan(0.25, PaletteCatalog::hueChroma('yellow'));
        self::assertGreaterThan(0.2, PaletteCatalog::hueChroma('purple'));
    }

    #[Test]
    public function resolvedHueTuplesStayInsideSrgbGamut(): void
    {
        $recipe = ThemePaletteRecipe::baseline();

        $anchors = $recipe->scaleAnchors();

        foreach (PaletteCatalog::hueFamilies() as $hue) {
            foreach (PaletteCatalog::levels() as $level) {
                $ref = $hue . '.' . $level;
                $hex = $this->generator->resolve($ref, $recipe);

                if (isset($anchors[$ref])) {
                    self::assertSame(strtolower($anchors[$ref]), strtolower($hex), $ref . ' anchor');
                    continue;
                }

                $tuple = $this->generator->resolveToOklch($ref, $recipe);

                self::assertSame(
                    $hex,
                    $this->colorSpace->toSrgb($tuple),
                    sprintf('%s round-trip', $ref),
                );
                self::assertLessThanOrEqual(
                    $tuple->c + 1e-6,
                    $this->colorSpace->maxInGamutChroma($tuple->l, $tuple->h, $tuple->c),
                    sprintf('%s.%d chroma cap', $hue, $level),
                );
            }
        }
    }

    #[Test]
    public function warmHueRampsDoNotClipToPureRed(): void
    {
        $recipe = ThemePaletteRecipe::baseline();

        foreach (['yellow', 'lime', 'orange'] as $hue) {
            foreach ([400, 500] as $level) {
                $hex = strtolower($this->generator->resolve($hue . '.' . $level, $recipe));
                self::assertNotSame('#ff0000', $hex, $hue . '.' . $level);

                $parts = sscanf(ltrim($hex, '#'), '%2x%2x%2x');
                self::assertIsArray($parts);
                [$r, $g, $b] = $parts;
                self::assertIsInt($r);
                self::assertIsInt($g);
                self::assertIsInt($b);
                self::assertGreaterThan($b, $g, $hue . '.' . $level);
            }
        }
    }

    #[Test]
    public function defaultThemeSurfaceIsNotPureRedClip(): void
    {
        PaletteCatalog::reset();
        $registry = new ThemeRegistry();
        $css = (new CssGenerator())->forTheme($registry->resolve('default'));

        self::assertStringContainsString('--ui-color-surface:', $css);
        self::assertStringNotContainsString('--ui-color-surface: #ff0000;', $css);
        self::assertMatchesRegularExpression('/--ui-color-surface: (#[0-9a-f]{6}|oklch\([^;]+\));/', $css);
        self::assertMatchesRegularExpression('/--ui-color-primary: (oklch\([^;]+\)|#[0-9a-f]{6});/', $css);
        self::assertStringContainsString('@media (color-gamut: p3)', $css);
    }
}
