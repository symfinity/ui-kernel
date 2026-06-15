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
        self::assertGreaterThanOrEqual(0.04, $tuple->c, '50% strength → C floor 0.04 at 500');
    }

    #[Test]
    public function amber500ReadsWarmNotMud(): void
    {
        $tuple = $this->generator->resolveToOklch('amber.500', $this->recipe);

        self::assertGreaterThanOrEqual(60.0, $tuple->h);
        self::assertLessThanOrEqual(95.0, $tuple->h);
        self::assertGreaterThanOrEqual(0.04, $tuple->c, '50% strength → C floor 0.04 at 500');
    }

    #[Test]
    public function lime500IsDistinctFromYellow(): void
    {
        $yellow = $this->generator->resolveToOklch('yellow.500', $this->recipe);
        $lime = $this->generator->resolveToOklch('lime.500', $this->recipe);

        self::assertNotEquals($yellow->h, $lime->h);
        self::assertGreaterThanOrEqual(0.02, $lime->c, '25% strength → C floor 0.02 at 500');
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

    #[Test]
    public function yellow500To600LightnessDropIsGradual(): void
    {
        $l500 = $this->generator->resolveToOklch('yellow.500', $this->recipe)->l;
        $l600 = $this->generator->resolveToOklch('yellow.600', $this->recipe)->l;

        $yellowDrop = $l500 - $l600;

        self::assertLessThan(0.30, $yellowDrop, '500→600 drop softer than pre-taper cliff (~0.41)');
        self::assertGreaterThan(0.40, $l600, '600 retains 25% warm blend lift over base ~0.37');
    }

    #[Test]
    public function strengthTableMatchesOperatorTaper(): void
    {
        $policy = new WarmHueRampPolicy();

        self::assertEqualsWithDelta(0.50, $policy->strength('orange', 400), 0.001);
        self::assertEqualsWithDelta(0.25, $policy->strength('orange', 500), 0.001);
        self::assertSame(0.0, $policy->strength('orange', 600));

        self::assertEqualsWithDelta(1.00, $policy->strength('yellow', 200), 0.001);
        self::assertEqualsWithDelta(0.50, $policy->strength('yellow', 500), 0.001);
        self::assertEqualsWithDelta(0.25, $policy->strength('yellow', 600), 0.001);

        self::assertEqualsWithDelta(0.50, $policy->strength('lime', 300), 0.001);
        self::assertSame(0.0, $policy->strength('lime', 600));
    }
}
