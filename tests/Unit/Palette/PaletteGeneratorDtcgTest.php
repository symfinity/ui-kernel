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
        $document = (new PaletteGenerator())->materializeDtcgDocument($recipe, 'default');
        $flat = $document->flatten();

        self::assertArrayHasKey('color.blue.600', $flat);
        self::assertArrayHasKey('color.mono.slate.500', $flat);
        self::assertSame('color', $flat['color.blue.600']->type()->value);
    }

    #[Test]
    public function materializeDtcgDocumentIncludesLiveGenerationExtensions(): void
    {
        $recipe = ThemeConfig::get('semantic')->paletteRecipe();
        $document = (new PaletteGenerator())->materializeDtcgDocument($recipe, 'semantic');
        $extensions = $document->extensions()['symfinity'] ?? null;

        self::assertIsArray($extensions);
        self::assertSame('live', $extensions['generation'] ?? null);
        self::assertSame(1, $extensions['revision'] ?? null);
        self::assertSame('semantic', $extensions['lineage'] ?? null);
        self::assertArrayNotHasKey('anchor_profile', $extensions);
    }

    #[Test]
    public function materializeDtcgDocumentUsesLiveBlue600ForDefaultLineage(): void
    {
        $recipe = ThemeConfig::get('default')->paletteRecipe();
        $flat = (new PaletteGenerator())->materializeDtcgDocument($recipe)->flatten();

        $tokenValue = $flat['color.blue.600']->value();
        self::assertIsString($tokenValue);
        self::assertMatchesRegularExpression('/^(oklch\([^)]+\)|#[0-9a-f]{6})$/', $tokenValue);
        self::assertNotSame('#105be3', $tokenValue);
    }
}
