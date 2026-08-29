<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Token\ThemeConfig;
use Symfinity\UiKernel\Token\ThemePaletteRecipe;

final class ThemePaletteRecipeWithoutAnchorsTest extends TestCase
{
    #[Test]
    public function builtInLineagesShipWithoutFrozenScaleAnchors(): void
    {
        $recipe = ThemeConfig::get('semantic')->paletteRecipe();
        self::assertSame([], $recipe->scaleAnchors());
    }

    #[Test]
    public function withoutScaleAnchorsIsIdempotentForBuiltIns(): void
    {
        $live = ThemePaletteRecipe::baseline()->withoutScaleAnchors();
        self::assertSame([], $live->scaleAnchors());
        self::assertSame($live, $live->withoutScaleAnchors());
    }
}
