<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Palette;

use Symfinity\UiKernel\Token\PaletteCatalog;

/**
 * Hybrid (C) midtone correction: gamut headroom × hue archetype (085).
 *
 * Supersedes WarmHueRampPolicy for all hue families at levels 400–600.
 */
final class PerceptualMidtoneRampPolicy
{
    /** Fixed OKLCH L drop from resolved level 500 → 600 (Balanced palette bridge). */
    public const LEVEL_500_TO_600_DELTA = 0.07;

    private const CHROMA_REFERENCE = 0.4;

    /** @var array<int, float> Default midtone L targets (levels 50–500 only). */
    private const LIGHTNESS_TARGETS = [
        100 => 0.87,
        200 => 0.85,
        300 => 0.82,
        400 => 0.80,
        500 => 0.67,
    ];

    /**
     * Narrow-warm hue overrides — strength tapers amber &lt; yellow; lime &lt; amber (50–500 only).
     *
     * @var array<string, array<int, float>>
     */
    private const NARROW_WARM_LIGHTNESS_TARGETS = [
        'amber' => [
            100 => 0.87,
            200 => 0.86,
            300 => 0.86,
            400 => 0.80,
            500 => 0.71,
        ],
        'yellow' => [
            100 => 0.87,
            200 => 0.86,
            300 => 0.90,
            400 => 0.82,
            500 => 0.74,
        ],
        'lime' => [
            100 => 0.87,
            200 => 0.85,
            300 => 0.84,
            400 => 0.78,
            500 => 0.70,
        ],
    ];

    private const ENVELOPE_LEVEL_LOW = 50;

    public function __construct(
        private readonly HueArchetypeRegistry $archetypes = new HueArchetypeRegistry(),
        private readonly float $gamma = 1.35,
        private readonly float $globalGain = 1.0,
        private readonly float $chromaFloorBase = 0.10,
    ) {
    }

    public static function fromCatalog(): self
    {
        return new self(
            HueArchetypeRegistry::forCatalog(),
            PaletteCatalog::midtoneGamma(),
            PaletteCatalog::midtoneGain(),
            PaletteCatalog::midtoneChromaFloor(),
        );
    }

    public function adjustLightness(string $hue, int $level, float $baseLightness, float $hueDegrees, OklchColorSpace $colorSpace): float
    {
        $strength = $this->strength($hue, $level, $baseLightness, $hueDegrees, $colorSpace);
        if ($strength <= 0.0) {
            return $baseLightness;
        }

        $target = $this->targetLightness($level, $hue);

        return $baseLightness + $strength * ($target - $baseLightness);
    }

    public function strength(string $hue, int $level, float $baseLightness, float $hueDegrees, OklchColorSpace $colorSpace): float
    {
        $envelope = $this->envelope($level);
        $strength = 0.0;

        if ($envelope > 0.0) {
            $headroom = $this->headroom($baseLightness, $hueDegrees, $colorSpace);
            $deficit = 1.0 - $headroom;
            if ($deficit > 0.0) {
                $archetype = $this->archetypes->multiplier($hue);
                $raw = $envelope * ($deficit ** $this->gamma) * $archetype;
                $strength = max(0.0, min(1.0, $raw * $this->globalGain));
            }
        }

        return $strength;
    }

    public function applyChromaFloor(float $chromaGamut, float $strength): float
    {
        if ($strength <= 0.0) {
            return $chromaGamut;
        }

        $floor = $this->chromaFloorBase * $strength;

        return max($chromaGamut, $floor);
    }

    public function envelope(int $level): float
    {
        if ($level <= self::ENVELOPE_LEVEL_LOW || $level >= 600) {
            return 0.0;
        }

        if ($level === 500) {
            return 1.0;
        }

        if ($level < 500) {
            return ($level - self::ENVELOPE_LEVEL_LOW) / (500 - self::ENVELOPE_LEVEL_LOW);
        }

        return (600 - $level) / (600 - 500);
    }

    private function headroom(float $lightness, float $hueDegrees, OklchColorSpace $colorSpace): float
    {
        $maxChroma = $colorSpace->maxInGamutChroma($lightness, $hueDegrees, self::CHROMA_REFERENCE);

        return max(0.0, min(1.0, $maxChroma / self::CHROMA_REFERENCE));
    }

    private function targetLightness(int $level, string $hue): float
    {
        $table = self::NARROW_WARM_LIGHTNESS_TARGETS[$hue] ?? self::LIGHTNESS_TARGETS;

        if (isset($table[$level])) {
            return $table[$level];
        }

        $keys = array_keys($table);
        sort($keys);

        if ($level <= $keys[0]) {
            return $table[$keys[0]];
        }

        $last = $keys[count($keys) - 1];
        if ($level >= $last) {
            return $table[$last];
        }

        for ($i = 0; $i < count($keys) - 1; ++$i) {
            $low = $keys[$i];
            $high = $keys[$i + 1];
            if ($level >= $low && $level <= $high) {
                $span = $high - $low;
                if ($span <= 0) {
                    return $table[$low];
                }
                $t = ($level - $low) / $span;

                return $table[$low]
                    + $t * ($table[$high] - $table[$low]);
            }
        }

        return $table[$last];
    }
}
