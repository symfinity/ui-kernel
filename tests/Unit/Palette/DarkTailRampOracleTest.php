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
    /** @var array<int, float> SSOT base L — dark-tail-ramp-correction rev 2 */
    private const DARK_TAIL_BASE_L = [
        500 => 0.461,
        600 => 0.446,
        700 => 0.402,
        800 => 0.328,
        900 => 0.224,
        950 => 0.112,
    ];

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
    public function blue900And950UseMidpointToBlack(): void
    {
        $l900 = $this->generator->resolveToOklch('blue.900', $this->recipe)->l;
        $l950 = $this->generator->resolveToOklch('blue.950', $this->recipe)->l;

        self::assertEqualsWithDelta($l900 / 2.0, $l950, 0.001, '950 must sit midway between 900 and black');
        self::assertGreaterThanOrEqual(0.017, $l900 - $l950, '900/950 must remain perceptually distinct');
    }

    #[Test]
    public function darkTailBaseLightnessMatchesSsotAnchors(): void
    {
        foreach (self::DARK_TAIL_BASE_L as $level => $expected) {
            $actual = $this->math->lightnessForLevel($level);
            self::assertEqualsWithDelta($expected, $actual, 0.001, 'level ' . $level);
        }
    }

    #[Test]
    public function level600UsesDarkSegmentNotLinearBaseline(): void
    {
        $levels = PaletteCatalog::levels();
        $index600 = array_search(600, $levels, true);
        self::assertNotFalse($index600);

        $linearL600 = $this->linearLightnessAtIndex($index600);
        $actualL600 = $this->math->lightnessForLevel(600);

        self::assertEqualsWithDelta(self::DARK_TAIL_BASE_L[600], $actualL600, 0.001);
        self::assertGreaterThan($linearL600, $actualL600, '600 must be lighter than pre-079 linear');
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
