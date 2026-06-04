<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Token;

use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Token\BuiltinThemeCatalog;
use Symfinity\UiKernel\Token\PaletteCatalog;

final class BuiltinThemeCatalogTest extends TestCase
{
    protected function tearDown(): void
    {
        BuiltinThemeCatalog::reset();
        PaletteCatalog::reset();
        parent::tearDown();
    }

    public function testLoadsEightBuiltInThemesFromBundleConfig(): void
    {
        $themes = BuiltinThemeCatalog::themes();

        self::assertCount(8, $themes);

        $ids = array_map(static fn (array $t): string => $t['id'], $themes);
        self::assertContains('default', $ids);
        self::assertContains('default-dark', $ids);
        self::assertContains('kiroshi-dark', $ids);

        $lineages = BuiltinThemeCatalog::lineageDonors();
        self::assertCount(4, $lineages);
        self::assertSame('default', $lineages['default']);
        self::assertSame('semantic', $lineages['semantic']);
    }

    public function testGroupedThemesExposeShortTokensPerVariant(): void
    {
        foreach (BuiltinThemeCatalog::themes() as $theme) {
            self::assertNotEmpty($theme['tokens'], $theme['id']);
            self::assertArrayHasKey('space-md', $theme['tokens'], $theme['id']);
            self::assertStringNotContainsString('--ui-', array_key_first($theme['tokens']) ?? '', $theme['id']);
        }
    }

    public function testReferenceThemeFileIsNotLoaded(): void
    {
        $ids = array_map(static fn (array $t): string => $t['id'], BuiltinThemeCatalog::themes());
        self::assertNotContains('ref', $ids);
    }

    public function testPaletteCatalogThemesDelegatesToBuiltinCatalog(): void
    {
        self::assertSame(BuiltinThemeCatalog::themes(), PaletteCatalog::themes());
    }
}
