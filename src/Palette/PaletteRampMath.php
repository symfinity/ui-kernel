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

        return $this->lightnessForIndex($index, count($levels), $pure);
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
