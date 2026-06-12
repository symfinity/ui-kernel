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
        $default = ThemeConfig::get('default')->paletteRecipe();
        $semantic = ThemeConfig::get('semantic')->paletteRecipe();

        self::assertSame('#0d6efd', $this->generator->resolveToCss('blue.500', $semantic));
        self::assertSame('#1c77fe', $this->generator->resolveToCss('blue.500', $default));

        $generatorOnly = ThemePaletteRecipe::fromPaletteDefinition(
            $default->hueBase(),
            $default->monoTones(),
        );
        $baselineTuple = $this->generator->resolveToOklch('blue.500', $generatorOnly);
        self::assertSame(258.0, $baselineTuple->h);
        self::assertLessThanOrEqual(PaletteCatalog::hueChroma('blue') + 0.001, $baselineTuple->c);
        self::assertNotSame(
            strtolower($this->generator->resolve('blue.500', $default)),
            strtolower($this->generator->resolveToCss('blue.500', $semantic)),
        );
    }

    #[Test]
    public function eachBuiltInLineageDefinesAllContractHues(): void
    {
        $expected = PaletteCatalog::hueFamilies();

        foreach (['default', 'semantic', 'utility'] as $lineage) {
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
