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
    public function withoutScaleAnchorsStripsFrozenRefs(): void
    {
        $recipe = ThemeConfig::get('semantic')->paletteRecipe();
        self::assertNotSame([], $recipe->scaleAnchors());

        $live = $recipe->withoutScaleAnchors();
        self::assertSame([], $live->scaleAnchors());
        self::assertSame($recipe->hueBase(), $live->hueBase());
        self::assertSame($recipe->monoTones(), $live->monoTones());
    }

    #[Test]
    public function withoutScaleAnchorsIsIdempotent(): void
    {
        $live = ThemePaletteRecipe::baseline()->withoutScaleAnchors();
        self::assertSame($live, $live->withoutScaleAnchors());
    }
}
