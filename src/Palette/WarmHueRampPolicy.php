<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Palette;

/**
 * Perceptual L lift for warm hue families (081) — partial blend toward elevated midtone band.
 *
 * Strength tapers by family × level to avoid a cliff at 500→600.
 */
final class WarmHueRampPolicy
{
    /** @var list<string> */
    private const WARM_FAMILIES = ['orange', 'amber', 'yellow', 'lime'];

    private const CHROMA_FLOOR = 0.08;

    /** @var array<int, float> target OKLCH L anchors (interpolated between) */
    private const LIGHTNESS_TARGETS = [
        50 => 0.90,
        100 => 0.87,
        200 => 0.85,
        300 => 0.82,
        400 => 0.80,
        500 => 0.78,
        600 => 0.76,
    ];

    public function isWarmFamily(string $hue): bool
    {
        return in_array($hue, self::WARM_FAMILIES, true);
    }

    public function adjustLightness(string $hue, int $level, float $baseLightness): float
    {
        $strength = $this->strength($hue, $level);
        if ($strength <= 0.0) {
            return $baseLightness;
        }

        $target = $this->targetLightness($level);

        return $baseLightness + $strength * ($target - $baseLightness);
    }

    public function chromaFloor(string $hue, int $level): float
    {
        $strength = $this->strength($hue, $level);
        if ($strength <= 0.0) {
            return 0.0;
        }

        return self::CHROMA_FLOOR * $strength;
    }

    /**
     * Blend strength 0–1 per operator taper table (081 follow-up).
     */
    public function strength(string $hue, int $level): float
    {
        if (!$this->isWarmFamily($hue)) {
            return 0.0;
        }

        return match ($hue) {
            'orange' => match (true) {
                $level <= 400 => 0.50,
                $level === 500 => 0.25,
                default => 0.0,
            },
            'amber', 'yellow' => match (true) {
                $level <= 400 => 1.00,
                $level === 500 => 0.50,
                $level === 600 => 0.25,
                default => 0.0,
            },
            'lime' => match (true) {
                $level <= 400 => 0.50,
                $level === 500 => 0.25,
                default => 0.0,
            },
            default => 0.0,
        };
    }

    private function targetLightness(int $level): float
    {
        if (isset(self::LIGHTNESS_TARGETS[$level])) {
            return self::LIGHTNESS_TARGETS[$level];
        }

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
                $span = $high - $low;
                if ($span <= 0) {
                    return self::LIGHTNESS_TARGETS[$low];
                }
                $t = ($level - $low) / $span;

                return self::LIGHTNESS_TARGETS[$low]
                    + $t * (self::LIGHTNESS_TARGETS[$high] - self::LIGHTNESS_TARGETS[$low]);
            }
        }

        return self::LIGHTNESS_TARGETS[$last];
    }
}
