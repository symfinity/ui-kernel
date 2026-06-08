<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Palette;

use Symfinity\UiKernel\Token\ThemePaletteRecipe;

/**
 * Public port for enumerating grammar-valid palette refs with OKLCH tuples.
 *
 * Consumers (055 NearestPaletteRefResolver) MUST use this — no duplicate ramp tables.
 */
interface PaletteRampSamplerInterface
{
    /** @return iterable<string, OklchTuple> ref => tuple */
    public function sampleAll(): iterable;

    public function resolveToOklch(string $ref, ?ThemePaletteRecipe $recipe = null): OklchTuple;

    public function resolveToSrgb(string $ref, ?ThemePaletteRecipe $recipe = null): string;
}
