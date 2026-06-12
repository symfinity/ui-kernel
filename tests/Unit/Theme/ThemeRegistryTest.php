<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Theme;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Theme\ThemeRegistry;
use Symfinity\UiKernel\Token\DesignTokenSet;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class ThemeRegistryTest extends TestCase
{
    #[Test]
    public function itRejectsIncompleteTokenMaps(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        DesignTokenSet::fromArray(['--ui-color-primary' => '#000']);
    }

    #[Test]
    public function itRegistersSixThemesIncludingForeignDarkVariants(): void
    {
        $registry = new ThemeRegistry();
        self::assertCount(6, $registry->all());
        self::assertSame('Balanced', $registry->get('default')->label());
        self::assertSame('semantic-dark', $registry->get('semantic-dark')->id());
        self::assertSame('utility-dark', $registry->get('utility-dark')->id());
    }

    #[Test]
    public function unknownThemeIdThrows(): void
    {
        $registry = new ThemeRegistry();
        $this->expectException(\InvalidArgumentException::class);
        $registry->resolve('not-a-theme');
    }

    #[Test]
    public function emptyThemeIdResolvesToDefault(): void
    {
        $registry = new ThemeRegistry();
        self::assertSame('default', $registry->resolve(null)->id());
        self::assertSame('default', $registry->resolve('')->id());
    }

    #[Test]
    public function defaultThemeHasAllSchemaKeys(): void
    {
        $tokens = ThemeCatalog::get('default')->tokens()->all();
        foreach (ThemeTokenSchema::REQUIRED_KEYS as $key) {
            self::assertArrayHasKey($key, $tokens);
        }
    }

    #[Test]
    public function catalogRejectsIncompleteTokenMaps(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new \Symfinity\UiKernel\Theme\DefinedTheme(
            'broken',
            'Broken',
            \Symfinity\UiKernel\Token\ThemeTokenSchema::V1_0,
            \Symfinity\UiKernel\Token\DesignTokenSet::fromArray(['--ui-color-primary' => '#000']),
        );
    }

    #[Test]
    public function defaultThemeResolvesGeneratedPaletteRefs(): void
    {
        $tokens = ThemeCatalog::get('default')->tokens()->all();

        self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $tokens['--ui-color-primary']);
        self::assertMatchesRegularExpression('/^(oklch\(|#[0-9a-f]{6}$)/', $tokens['--ui-color-secondary']);
        self::assertMatchesRegularExpression('/^(oklch\(|#[0-9a-f]{6}$)/', $tokens['--ui-color-surface']);
        self::assertNotSame($tokens['--ui-color-primary'], $tokens['--ui-color-secondary']);
    }
}
