<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

final class ThemePaletteRecipeTest extends TestCase
{
    #[Test]
    public function fromBaselineMergesOverrides(): void
    {
        $recipe = ThemePaletteRecipe::fromBaseline(
            hueOverrides: ['blue' => 217.0],
            monoOverrides: ['warm' => ['saturation' => 9.0]],
        );

        self::assertSame(217.0, $recipe->hueDegrees('blue'));
        self::assertSame(9.0, $recipe->monoTones()['warm']['saturation']);
        self::assertSame(0.0, $recipe->hueDegrees('red'));
    }
}
