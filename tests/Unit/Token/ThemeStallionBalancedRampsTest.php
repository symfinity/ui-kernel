<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Token\BuiltinThemeCatalog;
use Symfinity\UiKernel\Token\PaletteAnchorProfiles;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\SemanticColorMap;
use Symfinity\UiKernel\Token\ThemeConfig;

final class ThemeStallionBalancedRampsTest extends TestCase
{
    private PaletteGenerator $generator;

    protected function setUp(): void
    {
        BuiltinThemeCatalog::reset();
        $this->generator = new PaletteGenerator();
    }

    #[Test]
    public function defaultLoadsBalancedAnchorProfile(): void
    {
        $recipe = ThemeConfig::get('default')->paletteRecipe();

        self::assertCount(190, $recipe->scaleAnchors());
        self::assertSame(PaletteAnchorProfiles::get('balanced')['blue.500'], $recipe->scaleAnchors()['blue.500']);
        self::assertSame('#2377f3', $recipe->scaleAnchors()['blue.500']);
    }

    #[Test]
    public function balancedMonoRampsMatchProfileAtEveryContractLevel(): void
    {
        $targets = PaletteAnchorProfiles::get('balanced');
        $recipe = ThemeConfig::get('default')->paletteRecipe();

        foreach (PaletteCatalog::monoTones() as $tone) {
            foreach (PaletteCatalog::levels() as $level) {
                $ref = 'mono.' . $tone . '.' . $level;
                self::assertArrayHasKey($ref, $targets, $ref);
                self::assertSame(
                    $targets[$ref],
                    $this->generator->resolveToCss($ref, $recipe),
                    $ref,
                );
            }
        }
    }

    #[Test]
    public function balancedHueRampsMatchProfileAtEveryContractLevel(): void
    {
        $targets = PaletteAnchorProfiles::get('balanced');
        $recipe = ThemeConfig::get('default')->paletteRecipe();

        foreach (PaletteCatalog::hueFamilies() as $hue) {
            foreach (PaletteCatalog::levels() as $level) {
                $ref = $hue . '.' . $level;
                self::assertArrayHasKey($ref, $targets, $ref);

                self::assertSame(
                    $targets[$ref],
                    $this->generator->resolveToCss($ref, $recipe),
                    $ref,
                );
            }
        }
    }

    #[Test]
    public function balancedBlue500SitsBetweenTailwindAndBootstrap(): void
    {
        $tw = PaletteAnchorProfiles::get('tailwind-v4')['blue.500'];
        $bs = PaletteAnchorProfiles::get('bootstrap-5.3')['blue.500'];
        $balanced = PaletteAnchorProfiles::get('balanced')['blue.500'];

        self::assertNotSame($tw, $balanced);
        self::assertNotSame($bs, $balanced);
        self::assertSame('#2377f3', $balanced);
    }

    #[Test]
    public function defaultThemeStateColoursUseBalancedProfessionalSteps(): void
    {
        $recipe = ThemeConfig::get('default')->paletteRecipe();
        $targets = PaletteAnchorProfiles::get('balanced');
        $map = new SemanticColorMap($this->generator);
        $resolved = $map->resolve(ThemeConfig::get('default')->colorRefs(), $recipe);

        self::assertSame($targets['blue.600'], $resolved['--ui-color-primary']);
        self::assertSame($targets['green.500'], $resolved['--ui-color-success']);
        self::assertSame($targets['orange.500'], $resolved['--ui-color-warning']);
        self::assertSame($targets['teal.500'], $resolved['--ui-color-info']);
        self::assertSame($targets['red.500'], $resolved['--ui-color-danger']);
    }

    #[Test]
    public function defaultInfoIsTealNotPrimaryBlue(): void
    {
        $recipe = ThemeConfig::get('default')->paletteRecipe();
        $targets = PaletteAnchorProfiles::get('balanced');

        $primary = $this->generator->resolveToCss('blue.600', $recipe);
        $info = $this->generator->resolveToCss('teal.500', $recipe);

        self::assertSame($targets['blue.600'], $primary);
        self::assertSame($targets['teal.500'], $info);
        self::assertNotSame($primary, $info);
    }

    #[Test]
    public function balancedBrandTertiaryIsPurpleNotPrimaryBlue(): void
    {
        $targets = PaletteAnchorProfiles::get('balanced');
        $map = new SemanticColorMap($this->generator);

        foreach (['default', 'default-dark'] as $themeId) {
            $config = ThemeConfig::get($themeId);
            $resolved = $map->resolve($config->colorRefs(), $config->paletteRecipe());

            self::assertSame($targets['purple.500'], $resolved['--ui-color-tertiary'], $themeId);
            self::assertNotSame($resolved['--ui-color-primary'], $resolved['--ui-color-tertiary'], $themeId);
        }
    }
}
