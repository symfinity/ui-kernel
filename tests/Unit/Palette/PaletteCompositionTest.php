<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Palette;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Palette\PaletteRampSampler;
use Symfinity\UiKernel\Token\BuiltinThemeCatalog;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\ThemeConfig;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

final class PaletteCompositionTest extends TestCase
{
    private PaletteGenerator $generator;

    protected function setUp(): void
    {
        PaletteCatalog::reset();
        BuiltinThemeCatalog::reset();
        $this->generator = new PaletteGenerator();
    }

    protected function tearDown(): void
    {
        PaletteCatalog::reset();
        BuiltinThemeCatalog::reset();
    }

    #[Test]
    public function themeHueChangeAltersHNotC(): void
    {
        $baseline = ThemePaletteRecipe::baseline();
        $semantic = ThemeConfig::get('semantic')->paletteRecipe();

        $baselineTuple = $this->generator->resolveToOklch('blue.500', $baseline);
        $semanticTuple = $this->generator->resolveToOklch('blue.500', $semantic);

        self::assertSame($baselineTuple->l, $semanticTuple->l);
        self::assertSame($baselineTuple->c, $semanticTuple->c);
        self::assertNotSame($baselineTuple->h, $semanticTuple->h);
        self::assertSame(240.0, $baselineTuple->h);
        self::assertSame(215.0, $semanticTuple->h);
    }

    #[Test]
    public function eachBuiltInLineageDefinesAllContractHues(): void
    {
        $expected = PaletteCatalog::hueFamilies();

        foreach (['default', 'semantic', 'utility', 'kiroshi'] as $lineage) {
            $recipe = ThemeConfig::get($lineage)->paletteRecipe();
            self::assertSame($expected, array_keys($recipe->hueBase()), $lineage);
        }
    }

    #[Test]
    public function blue600SamplerIsDeterministic(): void
    {
        $sampler = new PaletteRampSampler(new PaletteGenerator());

        $first = $sampler->resolveToOklch('blue.600');
        $second = $sampler->resolveToOklch('blue.600');

        self::assertEquals($first, $second);
    }
}
