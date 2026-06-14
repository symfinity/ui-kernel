<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Palette;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Palette\PaletteRampMath;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

final class DarkTailRampOracleTest extends TestCase
{
    private PaletteRampMath $math;

    private PaletteGenerator $generator;

    private ThemePaletteRecipe $recipe;

    protected function setUp(): void
    {
        PaletteCatalog::reset();
        $this->math = PaletteRampMath::fromCatalog();
        $this->generator = new PaletteGenerator();
        $this->recipe = ThemePaletteRecipe::baseline();
    }

    protected function tearDown(): void
    {
        PaletteCatalog::reset();
    }

    #[Test]
    public function blue900And950ArePerceptuallySeparated(): void
    {
        $l900 = $this->generator->resolveToOklch('blue.900', $this->recipe)->l;
        $l950 = $this->generator->resolveToOklch('blue.950', $this->recipe)->l;

        self::assertGreaterThanOrEqual(0.017, $l900 - $l950, '900/950 must be perceptually distinct at default l_bounds');
        self::assertEqualsWithDelta(PaletteCatalog::darkTailLEnd(), $l950, 0.001);
    }

    #[Test]
    public function level600MatchesLinearBaseline(): void
    {
        $levels = PaletteCatalog::levels();
        $index600 = array_search(600, $levels, true);
        self::assertNotFalse($index600);

        $linearL600 = $this->linearLightnessAtIndex($index600);
        $actualL600 = $this->math->lightnessForLevel(600);

        self::assertEqualsWithDelta($linearL600, $actualL600, 0.001);
    }

    #[Test]
    public function level500UnchangedFromLinearBaseline(): void
    {
        $levels = PaletteCatalog::levels();
        $index500 = array_search(500, $levels, true);
        self::assertNotFalse($index500);

        $linearL500 = $this->linearLightnessAtIndex($index500);
        $actualL500 = $this->math->lightnessForLevel(500);

        self::assertEqualsWithDelta($linearL500, $actualL500, 0.001);
    }

    #[Test]
    public function monoNeutral950RemainsNearBlack(): void
    {
        $l = $this->generator->resolveToOklch('mono.neutral.950', $this->recipe)->l;

        self::assertLessThanOrEqual(0.05, $l);
    }

    private function linearLightnessAtIndex(int $index): float
    {
        $levels = PaletteCatalog::levels();
        [$lMin, $lMax] = PaletteCatalog::lBounds();

        return $lMax + ($index / (count($levels) - 1)) * ($lMin - $lMax);
    }
}
