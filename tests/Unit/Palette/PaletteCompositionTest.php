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
    public function themeHueChangeAltersResolvedRamp(): void
    {
        $default = ThemeConfig::get('default')->paletteRecipe();
        $semantic = ThemeConfig::get('semantic')->paletteRecipe();

        self::assertNotSame(
            strtolower($this->generator->resolveToCss('blue.500', $default)),
            strtolower($this->generator->resolveToCss('blue.500', $semantic)),
        );

        $generatorOnly = ThemePaletteRecipe::fromPaletteDefinition(
            $default->hueBase(),
            $default->monoTones(),
        );
        $baselineTuple = $this->generator->resolveToOklch('blue.500', $generatorOnly);
        self::assertSame(250.0, $baselineTuple->h);
        self::assertGreaterThan(0.0, $baselineTuple->c);
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
