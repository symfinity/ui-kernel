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

final class ThemeStallionSemanticBootstrapRampsTest extends TestCase
{
    private PaletteGenerator $generator;

    protected function setUp(): void
    {
        BuiltinThemeCatalog::reset();
        $this->generator = new PaletteGenerator();
    }

    #[Test]
    public function semanticLoadsBootstrap53AnchorProfile(): void
    {
        $recipe = ThemeConfig::get('semantic')->paletteRecipe();

        self::assertCount(190, $recipe->scaleAnchors());
        self::assertSame('#fd7e14', $recipe->scaleAnchors()['orange.500']);
        self::assertSame('#0d6efd', $recipe->scaleAnchors()['blue.500']);
    }

    #[Test]
    public function semanticHueRampsMatchBootstrap53AtEveryContractLevel(): void
    {
        $targets = PaletteAnchorProfiles::get('bootstrap-5.3');
        $recipe = ThemeConfig::get('semantic')->paletteRecipe();

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
    public function semanticThemeStateColoursMatchBootstrap53Roles(): void
    {
        $recipe = ThemeConfig::get('semantic')->paletteRecipe();
        $targets = PaletteAnchorProfiles::get('bootstrap-5.3');
        $map = new SemanticColorMap($this->generator);
        $resolved = $map->resolve(ThemeConfig::get('semantic')->colorRefs(), $recipe);

        self::assertSame($targets['blue.500'], $resolved['--ui-color-primary']);
        self::assertSame($targets['green.500'], $resolved['--ui-color-success']);
        self::assertSame($targets['orange.400'], $resolved['--ui-color-warning']);
        self::assertSame($targets['cyan.500'], $resolved['--ui-color-info']);
        self::assertSame($targets['red.500'], $resolved['--ui-color-danger']);
    }
}
