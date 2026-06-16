<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Palette;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\OklchColorSpace;
use Symfinity\UiKernel\Palette\OklchTuple;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Palette\PaletteRampMath;
use Symfinity\UiKernel\Palette\PerceptualMidtoneRampPolicy;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

final class PerceptualMidtoneRampOracleTest extends TestCase
{
    private PaletteGenerator $generator;

    private ThemePaletteRecipe $recipe;

    private PerceptualMidtoneRampPolicy $policy;

    private OklchColorSpace $colorSpace;

    /** @var array<string, array{l: float, c: float, h: float}> */
    private array $baseline;

    protected function setUp(): void
    {
        PaletteCatalog::reset();
        $this->generator = new PaletteGenerator();
        $this->recipe = ThemePaletteRecipe::baseline();
        $this->policy = PerceptualMidtoneRampPolicy::fromCatalog();
        $this->colorSpace = new OklchColorSpace();
        $this->baseline = self::loadBaseline();
    }

    protected function tearDown(): void
    {
        PaletteCatalog::reset();
    }

    #[Test]
    public function red500MeetsBrandGate(): void
    {
        $tuple = $this->generator->resolveToOklch('red.500', $this->recipe);

        self::assertGreaterThanOrEqual(0.58, $tuple->l);
        self::assertGreaterThanOrEqual(20.0, $tuple->h);
        self::assertLessThanOrEqual(40.0, $tuple->h);
        self::assertGreaterThanOrEqual(0.05, $tuple->c);
    }

    #[Test]
    public function yellow500MeetsBrandGate(): void
    {
        $tuple = $this->generator->resolveToOklch('yellow.500', $this->recipe);

        self::assertGreaterThanOrEqual(0.58, $tuple->l);
        self::assertGreaterThanOrEqual(85.0, $tuple->h);
        self::assertLessThanOrEqual(110.0, $tuple->h);
        self::assertGreaterThanOrEqual(0.05, $tuple->c);
    }

    #[Test]
    public function orange500MeetsBrandGate(): void
    {
        $tuple = $this->generator->resolveToOklch('orange.500', $this->recipe);

        self::assertGreaterThanOrEqual(0.56, $tuple->l);
        self::assertGreaterThanOrEqual(0.04, $tuple->c);
    }

    #[Test]
    public function narrowWarmRampsMonotonicAndCleanerMidtones(): void
    {
        foreach (['amber', 'yellow', 'lime'] as $hue) {
            $previousL = null;
            foreach ([50, 100, 200, 300, 400, 500, 600, 700] as $level) {
                $tuple = $this->generator->resolveToOklch(sprintf('%s.%d', $hue, $level), $this->recipe);
                if ($previousL !== null) {
                    self::assertLessThan($previousL, $tuple->l, $hue . '.' . $level . ' must darken vs lighter step');
                }
                $previousL = $tuple->l;
            }

            $l300 = $this->generator->resolveToOklch($hue . '.300', $this->recipe)->l;
            $l400 = $this->generator->resolveToOklch($hue . '.400', $this->recipe)->l;
            self::assertGreaterThan($l400, $l300, $hue . ' 300/400 plateau resolved');
        }

        $yellow500 = $this->generator->resolveToOklch('yellow.500', $this->recipe);
        self::assertGreaterThanOrEqual(0.70, $yellow500->l);
        self::assertGreaterThanOrEqual(0.14, $yellow500->c);

        $amber500 = $this->generator->resolveToOklch('amber.500', $this->recipe);
        self::assertGreaterThanOrEqual(0.68, $amber500->l);

        $lime500 = $this->generator->resolveToOklch('lime.500', $this->recipe);
        self::assertGreaterThanOrEqual(0.60, $lime500->l);
    }

    #[Test]
    public function level500To600BridgeDeltaLIsFixedForAllHues(): void
    {
        foreach (PaletteCatalog::hueFamilies() as $hue) {
            $l500 = $this->generator->resolveToOklch(sprintf('%s.500', $hue), $this->recipe)->l;
            $l600 = $this->generator->resolveToOklch(sprintf('%s.600', $hue), $this->recipe)->l;

            self::assertEqualsWithDelta(
                PerceptualMidtoneRampPolicy::LEVEL_500_TO_600_DELTA,
                $l500 - $l600,
                0.001,
                $hue . ' 500→600 ΔL',
            );
        }
    }

    #[Test]
    public function yellow500To600GradualDrop(): void
    {
        $l500 = $this->generator->resolveToOklch('yellow.500', $this->recipe)->l;
        $l600 = $this->generator->resolveToOklch('yellow.600', $this->recipe)->l;

        self::assertEqualsWithDelta(PerceptualMidtoneRampPolicy::LEVEL_500_TO_600_DELTA, $l500 - $l600, 0.001);
    }

    #[Test]
    public function headroomOrderingAt500(): void
    {
        $math = PaletteRampMath::fromCatalog();
        $baseL = $math->lightnessForLevel(500);

        $yellowH = $this->recipe->hueDegrees('yellow');
        $blueH = $this->recipe->hueDegrees('blue');

        $yellowStrength = $this->policy->strength('yellow', 500, $baseL, $yellowH, $this->colorSpace);
        $blueStrength = $this->policy->strength('blue', 500, $baseL, $blueH, $this->colorSpace);

        self::assertGreaterThan($blueStrength, $yellowStrength);
    }

    #[Test]
    public function blue500DeltaLCapped(): void
    {
        $tuple = $this->generator->resolveToOklch('blue.500', $this->recipe);
        $baseline = $this->baselineRef('blue.500');

        self::assertLessThanOrEqual(0.08, abs($tuple->l - $baseline['l']));
    }

    #[Test]
    public function indigo500DeltaLCapped(): void
    {
        $tuple = $this->generator->resolveToOklch('indigo.500', $this->recipe);
        $baseline = $this->baselineRef('indigo.500');

        self::assertLessThanOrEqual(0.08, abs($tuple->l - $baseline['l']));
    }

    #[Test]
    public function coolVividEffectiveStrengthBounded(): void
    {
        $math = PaletteRampMath::fromCatalog();
        $baseL = $math->lightnessForLevel(500);

        foreach (['blue', 'indigo'] as $hue) {
            $h = $this->recipe->hueDegrees($hue);
            $strength = $this->policy->strength($hue, 500, $baseL, $h, $this->colorSpace);
            self::assertLessThanOrEqual(0.17, $strength, $hue . ' effective strength at 500');
        }
    }

    #[Test]
    public function green500DeltaEWithinBudget(): void
    {
        $tuple = $this->generator->resolveToOklch('green.500', $this->recipe);
        $baseline = $this->baselineRef('green.500');
        $baselineTuple = new OklchTuple($baseline['l'], $baseline['c'], $baseline['h']);

        self::assertLessThanOrEqual(0.08, $this->colorSpace->deltaE($tuple, $baselineTuple));
    }

    #[Test]
    public function darkTail950AnchorsAnd700PlusDarken(): void
    {
        foreach (PaletteCatalog::hueFamilies() as $hue) {
            $l600 = $this->generator->resolveToOklch(sprintf('%s.600', $hue), $this->recipe)->l;
            $l700 = $this->generator->resolveToOklch(sprintf('%s.700', $hue), $this->recipe)->l;
            self::assertLessThan($l600, $l700, $hue . ' 700 must be darker than 600');

            $l900 = $this->generator->resolveToOklch(sprintf('%s.900', $hue), $this->recipe)->l;
            $tuple950 = $this->generator->resolveToOklch(sprintf('%s.950', $hue), $this->recipe);
            self::assertEqualsWithDelta($l900 / 2.0, $tuple950->l, 0.001, $hue . '.950 midway 900/black');
        }
    }

    #[Test]
    public function violetFuchsiaNoRedChannelClip(): void
    {
        foreach (['violet', 'fuchsia'] as $hue) {
            $hex = $this->generator->hueHex($hue, 500, $this->recipe);
            self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/i', $hex);

            $rgb = sscanf(ltrim($hex, '#'), '%2x%2x%2x');
            self::assertIsArray($rgb);
            self::assertLessThan(255, (int) $rgb[0], $hue . ' red channel must not clip at 500');
        }
    }

    /**
     * @return array{l: float, c: float, h: float}
     */
    private function baselineRef(string $ref): array
    {
        if (!isset($this->baseline[$ref])) {
            self::fail(sprintf('Missing baseline ref "%s".', $ref));
        }

        return $this->baseline[$ref];
    }

    /**
     * @return array<string, array{l: float, c: float, h: float}>
     */
    private static function loadBaseline(): array
    {
        $path = dirname(__DIR__, 2) . '/fixtures/palette/pre-085-midtone-baseline.json';
        self::assertFileExists($path);

        $contents = file_get_contents($path);
        self::assertIsString($contents);

        /** @var array{capture_revision?: string, recipe?: string, refs: array<string, array{l: float, c: float, h: float}>} $decoded */
        $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        return $decoded['refs'];
    }
}
