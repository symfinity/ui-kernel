<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Palette;

/**
 * Perceptual L lift for warm hue families (081) — scoped to midtones 200–500.
 */
final class WarmHueRampPolicy
{
    /** @var list<string> */
    private const WARM_FAMILIES = ['orange', 'amber', 'yellow', 'lime'];

    /** @var array<int, float> target L at warm midtone anchors */
    private const LIGHTNESS_TARGETS = [
        200 => 0.85,
        300 => 0.82,
        400 => 0.80,
        500 => 0.78,
    ];

    public function isWarmFamily(string $hue): bool
    {
        return in_array($hue, self::WARM_FAMILIES, true);
    }

    public function adjustLightness(string $hue, int $level, float $baseLightness): float
    {
        if (!$this->isWarmFamily($hue) || $level < 200 || $level > 500) {
            return $baseLightness;
        }

        $target = self::LIGHTNESS_TARGETS[$level] ?? null;
        if ($target === null) {
            $target = $this->interpolateTarget($level);
        }

        return max($baseLightness, $target);
    }

    public function chromaFloor(int $level): float
    {
        if ($level >= 200 && $level <= 500) {
            return 0.08;
        }

        return 0.0;
    }

    private function interpolateTarget(int $level): float
    {
        $keys = array_keys(self::LIGHTNESS_TARGETS);
        sort($keys);

        if ($level <= $keys[0]) {
            return self::LIGHTNESS_TARGETS[$keys[0]];
        }

        $last = $keys[count($keys) - 1];
        if ($level >= $last) {
            return self::LIGHTNESS_TARGETS[$last];
        }

        for ($i = 0; $i < count($keys) - 1; ++$i) {
            $low = $keys[$i];
            $high = $keys[$i + 1];
            if ($level >= $low && $level <= $high) {
                $t = ($level - $low) / ($high - $low);

                return self::LIGHTNESS_TARGETS[$low]
                    + $t * (self::LIGHTNESS_TARGETS[$high] - self::LIGHTNESS_TARGETS[$low]);
            }
        }

        return self::LIGHTNESS_TARGETS[$last];
    }
}
