<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Palette;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Palette\PaletteRampMath;
use Symfinity\UiKernel\Palette\WarmHueRampPolicy;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

final class WarmHueRampOracleTest extends TestCase
{
    private PaletteGenerator $generator;

    private ThemePaletteRecipe $recipe;

    protected function setUp(): void
    {
        PaletteCatalog::reset();
        $this->generator = new PaletteGenerator();
        $this->recipe = ThemePaletteRecipe::baseline();
    }

    protected function tearDown(): void
    {
        PaletteCatalog::reset();
    }

    #[Test]
    public function yellow500ReadsWarmNotMud(): void
    {
        $tuple = $this->generator->resolveToOklch('yellow.500', $this->recipe);

        self::assertGreaterThanOrEqual(85.0, $tuple->h);
        self::assertLessThanOrEqual(110.0, $tuple->h);
        self::assertGreaterThanOrEqual(0.08, $tuple->c);
    }

    #[Test]
    public function amber500ReadsWarmNotMud(): void
    {
        $tuple = $this->generator->resolveToOklch('amber.500', $this->recipe);

        self::assertGreaterThanOrEqual(60.0, $tuple->h);
        self::assertLessThanOrEqual(95.0, $tuple->h);
        self::assertGreaterThanOrEqual(0.08, $tuple->c);
    }

    #[Test]
    public function lime500IsDistinctFromYellow(): void
    {
        $yellow = $this->generator->resolveToOklch('yellow.500', $this->recipe);
        $lime = $this->generator->resolveToOklch('lime.500', $this->recipe);

        self::assertNotEquals($yellow->h, $lime->h);
        self::assertGreaterThanOrEqual(0.08, $lime->c);
    }

    #[Test]
    public function green500DeltaEWithinBaselineBound(): void
    {
        $math = PaletteRampMath::fromCatalog();
        $policy = new WarmHueRampPolicy();
        $baseL = $math->lightnessForLevel(500);

        self::assertFalse($policy->isWarmFamily('green'));
        self::assertEqualsWithDelta($baseL, $policy->adjustLightness('green', 500, $baseL), 0.0001);

        $green = $this->generator->resolveToOklch('green.500', $this->recipe);
        self::assertEqualsWithDelta($baseL, $green->l, 0.01);
    }
}
