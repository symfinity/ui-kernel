<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Palette;

use InvalidArgumentException;
use Symfinity\UiKernel\Token\PaletteCatalog;

/**
 * Computed OKLCH ramp lightness and chroma policy (079).
 */
final class PaletteRampMath
{
    /** Dark-tail SSOT — see dark-tail-ramp-correction contract (in-place at generator.palette.revision 1). */
    private const DARK_TAIL_L_AT_600 = 0.446;

    private const DARK_TAIL_DROP_FIRST = 0.044;

    private const DARK_TAIL_DROP_ACCEL = 0.030;

    private const DARK_TAIL_MAX_STEPS_FROM_600 = 4;

    private const DARK_TAIL_STEPS_900 = 3;

    public function __construct(
        private readonly float $lMin = 0.0025,
        private readonly float $lMax = 0.92,
        private readonly float $pureAtStart = 1.0,
        private readonly float $pureAtEnd = 0.0,
        private readonly float $chromaPercent = 100.0,
    ) {
    }

    public static function fromCatalog(): self
    {
        [$lMin, $lMax] = PaletteCatalog::lBounds();
        [$pureAtStart, $pureAtEnd] = PaletteCatalog::pureLBounds();

        return new self(
            $lMin,
            $lMax,
            $pureAtStart,
            $pureAtEnd,
            PaletteCatalog::chromaPercent(),
        );
    }

    public function lightnessForIndex(int $index, int $count, bool $pure = false): float
    {
        if ($count < 2) {
            throw new InvalidArgumentException('Level count must be at least 2.');
        }

        if ($index < 0 || $index >= $count) {
            throw new InvalidArgumentException(sprintf('Level index %d out of range for count %d.', $index, $count));
        }

        if ($pure) {
            $atStart = $this->pureAtStart;
            $atEnd = $this->pureAtEnd;
        } else {
            $atStart = $this->lMax;
            $atEnd = $this->lMin;
        }

        return $atStart + ($index / ($count - 1)) * ($atEnd - $atStart);
    }

    public function lightnessForLevel(int $level, bool $pure = false): float
    {
        $levels = PaletteCatalog::levels();
        $index = array_search($level, $levels, true);
        if ($index === false) {
            throw new InvalidArgumentException(sprintf('Unknown level %d.', $level));
        }

        $count = count($levels);
        $linear = $this->lightnessForIndex($index, $count, $pure);

        if ($pure) {
            return $linear;
        }

        $index600 = array_search(600, $levels, true);
        $index950 = array_search(950, $levels, true);
        if ($index600 === false || $index950 === false || $index < $index600) {
            return $linear;
        }

        $stepsFrom600 = $index - $index600;

        return $this->darkTailLightnessAtStep(
            $stepsFrom600,
            self::DARK_TAIL_L_AT_600,
            PaletteCatalog::darkTailLEnd(),
        );
    }

    /**
     * Dark-tail L for step s from 600. Step 4 (950) = midpoint between step 3 (900) and black (L=0).
     */
    public function darkTailLightnessAtStep(int $stepsFrom600, float $l600Anchor, float $lEnd): float
    {
        if ($stepsFrom600 === self::DARK_TAIL_MAX_STEPS_FROM_600) {
            $l900 = $this->darkSegmentLightnessFromAnchor(self::DARK_TAIL_STEPS_900, $l600Anchor, $lEnd);

            return $l900 / 2.0;
        }

        return $this->darkSegmentLightnessFromAnchor($stepsFrom600, $l600Anchor, $lEnd);
    }

    /**
     * Quadratic-step dark tail from a per-hue L₆₀₀ anchor (500→600 bridge sets anchor).
     *
     * When {@see PaletteCatalog::darkTailLEnd()} differs from the default 0.09, remap along the same relative curve.
     */
    public function darkSegmentLightnessFromAnchor(int $stepsFrom600, float $l600Anchor, float $lEnd): float
    {
        if ($stepsFrom600 < 0) {
            throw new InvalidArgumentException('stepsFrom600 must be non-negative.');
        }

        $lDefault = $l600Anchor
            - $stepsFrom600 * self::DARK_TAIL_DROP_FIRST
            - self::DARK_TAIL_DROP_ACCEL * $stepsFrom600 * ($stepsFrom600 - 1) / 2.0;

        $lDefaultEnd = $l600Anchor
            - self::DARK_TAIL_MAX_STEPS_FROM_600 * self::DARK_TAIL_DROP_FIRST
            - self::DARK_TAIL_DROP_ACCEL * self::DARK_TAIL_MAX_STEPS_FROM_600
                * (self::DARK_TAIL_MAX_STEPS_FROM_600 - 1) / 2.0;

        $range = $l600Anchor - $lDefaultEnd;
        if ($range <= 0.0) {
            return $lEnd;
        }

        $fraction = ($lDefault - $lDefaultEnd) / $range;

        return $lEnd + $fraction * ($l600Anchor - $lEnd);
    }

    public function stepsFrom600(int $level): int
    {
        $levels = PaletteCatalog::levels();
        $index600 = array_search(600, $levels, true);
        $index = array_search($level, $levels, true);
        if ($index600 === false || $index === false) {
            throw new InvalidArgumentException(sprintf('Unknown level %d.', $level));
        }

        return $index - $index600;
    }

    public function chromaForHueStep(
        int $level,
        float $lightness,
        float $hueDegrees,
        OklchColorSpace $colorSpace,
        ?float $lineageOverride = null,
    ): float {
        $scale = self::levelChromaScale($level);

        if ($lineageOverride !== null) {
            return $colorSpace->maxInGamutChroma($lightness, $hueDegrees, $lineageOverride * $scale);
        }

        $maxChroma = $colorSpace->maxInGamutChroma($lightness, $hueDegrees, 0.4);

        return ($this->chromaPercent / 100.0) * $maxChroma * $scale;
    }

    /**
     * Distance-from-500 vividness floor — keeps 50–300 ramps readable (pre-079 hueChromaForLevel policy).
     */
    public static function levelChromaScale(int $level): float
    {
        $distance = abs($level - 500) / 450.0;

        return max(0.48, 1.0 - $distance * 0.55);
    }
}
