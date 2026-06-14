<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Token\BuiltinThemeCatalog;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\ThemeConfig;

/** Guards palette-freeze lift — built-in lineages use live OKLCH ramps. */
final class PaletteFreezeTest extends TestCase
{
    private const GENERATOR_REVISION = 1;

    protected function setUp(): void
    {
        BuiltinThemeCatalog::reset();
        PaletteCatalog::reset();
    }

    protected function tearDown(): void
    {
        BuiltinThemeCatalog::reset();
        PaletteCatalog::reset();
    }

    #[Test]
    public function generatorRevisionIsLiveAfterLift(): void
    {
        self::assertSame(self::GENERATOR_REVISION, PaletteCatalog::revision());
    }

    #[Test]
    public function shippedLineagesHaveNoFrozenScaleAnchors(): void
    {
        foreach (['default', 'semantic', 'utility'] as $themeId) {
            self::assertSame([], ThemeConfig::get($themeId)->paletteRecipe()->scaleAnchors(), $themeId);
        }
    }

    #[Test]
    public function builtInLineagesResolveRampsViaLiveOklch(): void
    {
        $generator = new PaletteGenerator();

        foreach (['default', 'semantic', 'utility'] as $themeId) {
            $recipe = ThemeConfig::get($themeId)->paletteRecipe();
            $css = $generator->resolveToCss('blue.500', $recipe);

            self::assertMatchesRegularExpression('/^(oklch\([^)]+\)|#[0-9a-f]{6})$/', $css, $themeId);
        }
    }

    #[Test]
    public function materializeDtcgDocumentMarksLiveGeneration(): void
    {
        $recipe = ThemeConfig::get('default')->paletteRecipe();
        $extensions = (new PaletteGenerator())
            ->materializeDtcgDocument($recipe, 'default')
            ->extensions()['symfinity'] ?? null;

        self::assertIsArray($extensions);
        self::assertSame('live', $extensions['generation'] ?? null);
        self::assertSame(self::GENERATOR_REVISION, $extensions['revision'] ?? null);
        self::assertArrayNotHasKey('anchor_profile', $extensions);
    }
}
