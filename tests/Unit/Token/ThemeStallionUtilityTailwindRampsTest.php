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

final class ThemeStallionUtilityTailwindRampsTest extends TestCase
{
    private PaletteGenerator $generator;

    protected function setUp(): void
    {
        BuiltinThemeCatalog::reset();
        $this->generator = new PaletteGenerator();
    }

    #[Test]
    public function utilityLoadsTailwindV4AnchorProfile(): void
    {
        $recipe = ThemeConfig::get('utility')->paletteRecipe();
        $anchors = $recipe->scaleAnchors();

        self::assertCount(190, $anchors);
        self::assertSame(PaletteAnchorProfiles::get('tailwind-v4')['orange.500'], $anchors['orange.500']);
    }

    #[Test]
    public function utilityHueRampsMatchTailwindV4AtEveryContractLevel(): void
    {
        $targets = PaletteAnchorProfiles::get('tailwind-v4');
        $recipe = ThemeConfig::get('utility')->paletteRecipe();

        foreach (PaletteCatalog::hueFamilies() as $hue) {
            foreach (PaletteCatalog::levels() as $level) {
                $ref = $hue . '.' . $level;
                self::assertArrayHasKey($ref, $targets, $ref);

                $resolved = $this->generator->resolveToCss($ref, $recipe);
                self::assertSame(
                    strtolower($targets[$ref]),
                    strtolower($resolved),
                    $ref,
                );
            }
        }
    }

    #[Test]
    public function utilityThemeStateColoursMatchYamlRefs(): void
    {
        $recipe = ThemeConfig::get('utility')->paletteRecipe();
        $targets = PaletteAnchorProfiles::get('tailwind-v4');
        $map = new SemanticColorMap($this->generator);
        $resolved = $map->resolve(ThemeConfig::get('utility')->colorRefs(), $recipe);

        self::assertSame($targets['emerald.600'], $resolved['--ui-color-success']);
        self::assertSame($targets['orange.400'], $resolved['--ui-color-warning']);
        self::assertSame($targets['red.500'], $resolved['--ui-color-danger']);
        self::assertSame($targets['teal.600'], $resolved['--ui-color-info']);
    }

    #[Test]
    public function utilityInfoIsTealNotPrimaryBlue(): void
    {
        $recipe = ThemeConfig::get('utility')->paletteRecipe();
        $targets = PaletteAnchorProfiles::get('tailwind-v4');

        $primary = $this->generator->resolveToCss('blue.400', $recipe);
        $info = $this->generator->resolveToCss('teal.600', $recipe);

        self::assertSame($targets['blue.400'], $primary);
        self::assertSame($targets['teal.600'], $info);
        self::assertNotSame($primary, $info);
    }
}
