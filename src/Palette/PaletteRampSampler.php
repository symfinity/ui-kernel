<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Palette;

use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

final class PaletteRampSampler implements PaletteRampSamplerInterface
{
    public function __construct(
        private readonly PaletteGenerator $generator = new PaletteGenerator(),
    ) {
    }

    public function sampleAll(): iterable
    {
        $recipe = ThemePaletteRecipe::baseline();

        foreach (PaletteCatalog::monoTones() as $tone) {
            foreach (PaletteCatalog::levels() as $level) {
                $ref = sprintf('mono.%s.%d', $tone, $level);
                yield $ref => $this->generator->resolveToOklch($ref, $recipe);
            }
        }

        foreach (PaletteCatalog::hueFamilies() as $hue) {
            foreach (PaletteCatalog::levels() as $level) {
                $ref = sprintf('%s.%d', $hue, $level);
                yield $ref => $this->generator->resolveToOklch($ref, $recipe);
            }
        }
    }

    public function resolveToOklch(string $ref, ?ThemePaletteRecipe $recipe = null): OklchTuple
    {
        return $this->generator->resolveToOklch($ref, $recipe ?? ThemePaletteRecipe::baseline());
    }

    public function resolveToSrgb(string $ref, ?ThemePaletteRecipe $recipe = null): string
    {
        $resolvedRecipe = $recipe ?? ThemePaletteRecipe::baseline();

        return $this->generator->resolve($ref, $resolvedRecipe);
    }
}
