<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Palette;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Palette\PaletteRampSampler;
use Symfinity\UiKernel\Token\PaletteCatalog;

final class PaletteRampSamplerTest extends TestCase
{
    private PaletteRampSampler $sampler;

    protected function setUp(): void
    {
        PaletteCatalog::reset();
        $this->sampler = new PaletteRampSampler(new PaletteGenerator());
    }

    protected function tearDown(): void
    {
        PaletteCatalog::reset();
    }

    #[Test]
    public function sampleAllCountMatchesContractCrossProduct(): void
    {
        $expected = (count(PaletteCatalog::monoTones()) + count(PaletteCatalog::hueFamilies()))
            * count(PaletteCatalog::levels());

        $samples = iterator_to_array($this->sampler->sampleAll());

        self::assertCount($expected, $samples);
    }

    #[Test]
    public function resolveToOklchMatchesGenerator(): void
    {
        $generator = new PaletteGenerator();
        $ref = 'mono.cool.600';

        self::assertEquals(
            $generator->resolveToOklch($ref, \Symfinity\UiKernel\Token\ThemePaletteRecipe::baseline()),
            $this->sampler->resolveToOklch($ref),
        );
    }

    #[Test]
    public function sampleForUsesProvidedRecipeNotBaseline(): void
    {
        $semantic = \Symfinity\UiKernel\Token\ThemeConfig::get('semantic')->paletteRecipe()->withoutScaleAnchors();
        $utility = \Symfinity\UiKernel\Token\ThemeConfig::get('utility')->paletteRecipe()->withoutScaleAnchors();

        $semanticBlue = iterator_to_array($this->sampler->sampleFor($semantic))['blue.500'];
        $utilityBlue = iterator_to_array($this->sampler->sampleFor($utility))['blue.500'];

        self::assertNotEquals($semanticBlue, $utilityBlue);
    }

    #[Test]
    public function twoRunsAreDeterministic(): void
    {
        $first = array_keys(iterator_to_array($this->sampler->sampleAll()));
        $second = array_keys(iterator_to_array($this->sampler->sampleAll()));

        self::assertSame($first, $second);
    }

    #[Test]
    public function cssGeneratorOutputUsesOklchForSemanticColorVars(): void
    {
        $css = (new \Symfinity\UiKernel\Css\CssGenerator())->forTheme(
            \Symfinity\UiKernel\Theme\ThemeCatalog::get('semantic'),
        );

        self::assertMatchesRegularExpression('/--ui-color-primary: (#[0-9a-f]{6}|oklch\([^;]+\));/i', $css);

        if (preg_match_all('/--ui-color-(?:primary|secondary|surface|danger)-hover:\s*([^;]+);/', $css, $matches)) {
            foreach ($matches[1] as $value) {
                self::assertStringStartsWith('oklch(from var(--ui-color-', trim($value));
            }
        }
    }
}
