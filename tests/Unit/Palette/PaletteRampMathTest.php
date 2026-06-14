<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Palette;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\OklchColorSpace;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Palette\PaletteRampMath;
use Symfinity\UiKernel\Palette\PaletteRampSampler;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\PaletteRefGrammar;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

final class PaletteRampMathTest extends TestCase
{
    private PaletteRampMath $math;

    private OklchColorSpace $colorSpace;

    protected function setUp(): void
    {
        PaletteCatalog::reset();
        $this->math = PaletteRampMath::fromCatalog();
        $this->colorSpace = new OklchColorSpace();
    }

    protected function tearDown(): void
    {
        PaletteCatalog::reset();
    }

    #[Test]
    public function lightnessDecreasesAsIndexIncreasesToward950(): void
    {
        $levels = PaletteCatalog::levels();
        $count = count($levels);
        $previous = null;

        for ($index = 0; $index < $count; ++$index) {
            $lightness = $this->math->lightnessForIndex($index, $count);
            if ($previous !== null) {
                self::assertGreaterThan($lightness, $previous, 'index ' . $index);
            }
            $previous = $lightness;
        }
    }

    #[Test]
    public function level500IsMidpointBetweenBounds(): void
    {
        [$min, $max] = PaletteCatalog::lBounds();
        $midpoint = ($min + $max) / 2.0;
        $lightness = $this->math->lightnessForLevel(500);

        self::assertEqualsWithDelta($midpoint, $lightness, 1e-6);
    }

    #[Test]
    public function pureMonoHasZeroChroma(): void
    {
        $generator = new PaletteGenerator();
        $recipe = ThemePaletteRecipe::baseline();

        foreach (PaletteCatalog::levels() as $level) {
            $tuple = $generator->resolveToOklch(sprintf('mono.neutral.%d', $level), $recipe);
            self::assertSame(0.0, $tuple->c, 'level ' . $level);
        }
    }

    #[Test]
    public function everyHueLevelResolvesInGamut(): void
    {
        $generator = new PaletteGenerator();
        $recipe = ThemePaletteRecipe::fromPaletteDefinition(
            ThemePaletteRecipe::baseline()->hueBase(),
            ThemePaletteRecipe::baseline()->monoTones(),
        );

        foreach (PaletteCatalog::hueFamilies() as $hue) {
            foreach (PaletteCatalog::levels() as $level) {
                $ref = sprintf('%s.%d', $hue, $level);
                PaletteRefGrammar::assertValid($ref);
                $tuple = $generator->resolveToOklch($ref, $recipe);
                self::assertSame(
                    $generator->resolve($ref, $recipe),
                    $this->colorSpace->toSrgb($tuple),
                    $ref,
                );
            }
        }
    }

    #[Test]
    public function level50RetainsVividnessFloor(): void
    {
        self::assertEqualsWithDelta(0.48, PaletteRampMath::levelChromaScale(50), 1e-6);
        self::assertEqualsWithDelta(1.0, PaletteRampMath::levelChromaScale(500), 1e-6);
    }

    #[Test]
    public function samplerAndGeneratorAgreeOnBlue500(): void
    {
        $sampler = new PaletteRampSampler(new PaletteGenerator());
        $generator = new PaletteGenerator();
        $recipe = ThemePaletteRecipe::fromPaletteDefinition(
            ThemePaletteRecipe::baseline()->hueBase(),
            ThemePaletteRecipe::baseline()->monoTones(),
        );

        self::assertEquals(
            $generator->resolveToOklch('blue.500', $recipe),
            $sampler->resolveToOklch('blue.500'),
        );
    }
}
