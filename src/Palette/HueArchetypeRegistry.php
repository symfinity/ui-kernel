<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Palette;

use RuntimeException;
use Symfinity\UiKernel\Token\PaletteCatalog;

/**
 * Per-hue-family strength multipliers for perceptual midtone ramp (085).
 */
final class HueArchetypeRegistry
{
    /** @var array<string, float> */
    private const MULTIPLIERS = [
        // narrow_warm
        'red' => 1.40,
        'orange' => 1.40,
        'amber' => 1.40,
        'yellow' => 1.40,
        'rose' => 1.40,
        // yellow_green
        'lime' => 1.10,
        // balanced — tuned below 1.0 for green ΔE collateral gate (085 T012)
        'green' => 0.50,
        'emerald' => 0.50,
        'teal' => 0.50,
        'cyan' => 0.50,
        'sky' => 0.50,
        // cool_vivid
        'blue' => 0.28,
        'indigo' => 0.28,
        // cool_wide
        'violet' => 0.55,
        'purple' => 0.55,
        'fuchsia' => 0.55,
        'pink' => 0.55,
    ];

    public function multiplier(string $hueFamily): float
    {
        if (!isset(self::MULTIPLIERS[$hueFamily])) {
            throw new RuntimeException(sprintf('Unknown hue family "%s" for archetype lookup.', $hueFamily));
        }

        return self::MULTIPLIERS[$hueFamily];
    }

    /**
     * @return array<string, float>
     */
    public function all(): array
    {
        return self::MULTIPLIERS;
    }

    /**
     * Validates every contract hue family is registered.
     */
    public static function forCatalog(): self
    {
        $registry = new self();
        foreach (PaletteCatalog::hueFamilies() as $hue) {
            $registry->multiplier($hue);
        }

        return $registry;
    }
}
