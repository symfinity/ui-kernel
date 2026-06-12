<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Token\PaletteCatalog;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

final class ThemePaletteRecipeTest extends TestCase
{
    #[Test]
    public function fromPaletteDefinitionBuildsFullRecipe(): void
    {
        $baseline = ThemePaletteRecipe::baseline();
        $hueBase = $baseline->hueBase();
        $hueBase['blue'] = 217.0;
        $monoTones = $baseline->monoTones();
        $monoTones['warm']['saturation'] = 9.0;

        $recipe = ThemePaletteRecipe::fromPaletteDefinition($hueBase, $monoTones);

        self::assertSame(217.0, $recipe->hueDegrees('blue'));
        self::assertSame(9.0, $recipe->monoTones()['warm']['saturation']);
        self::assertSame($baseline->hueDegrees('red'), $recipe->hueDegrees('red'));
    }

    #[Test]
    public function hueChromaOverrideFallsBackToBundleCatalog(): void
    {
        PaletteCatalog::reset();
        $baseline = ThemePaletteRecipe::baseline();
        $recipe = ThemePaletteRecipe::fromPaletteDefinition(
            $baseline->hueBase(),
            $baseline->monoTones(),
            ['yellow' => 0.42],
        );

        self::assertSame(0.42, $recipe->hueChromaBase('yellow'));
        self::assertSame(PaletteCatalog::hueChroma('blue'), $recipe->hueChromaBase('blue'));
    }
}
