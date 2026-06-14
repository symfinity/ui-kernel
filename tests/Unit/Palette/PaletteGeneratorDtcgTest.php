<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Palette;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Palette\PaletteGenerator;
use Symfinity\UiKernel\Token\ThemeConfig;

final class PaletteGeneratorDtcgTest extends TestCase
{
    #[Test]
    public function materializeDtcgDocumentEmitsNativeColorPrimitives(): void
    {
        $recipe = ThemeConfig::get('default')->paletteRecipe();
        $document = (new PaletteGenerator())->materializeDtcgDocument($recipe, 'default', 'balanced');
        $flat = $document->flatten();

        self::assertArrayHasKey('color.blue.600', $flat);
        self::assertArrayHasKey('color.mono.cool.500', $flat);
        self::assertSame('color', $flat['color.blue.600']->type()->value);
    }

    #[Test]
    public function materializeDtcgDocumentIncludesFreezeExtensions(): void
    {
        $recipe = ThemeConfig::get('semantic')->paletteRecipe();
        $document = (new PaletteGenerator())->materializeDtcgDocument($recipe, 'semantic', 'bootstrap-5.3');
        $extensions = $document->extensions()['symfinity'] ?? null;

        self::assertIsArray($extensions);
        self::assertSame('COLOR_FREEZE_v1', $extensions['freeze'] ?? null);
        self::assertSame('semantic', $extensions['lineage'] ?? null);
        self::assertSame('bootstrap-5.3', $extensions['anchor_profile'] ?? null);
    }

    #[Test]
    public function materializeDtcgDocumentMatchesKnownBalancedAnchorCss(): void
    {
        $recipe = ThemeConfig::get('default')->paletteRecipe();
        $flat = (new PaletteGenerator())->materializeDtcgDocument($recipe)->flatten();

        self::assertSame('#105be3', $flat['color.blue.600']->value());
    }
}
