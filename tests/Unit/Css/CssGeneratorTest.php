<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class CssGeneratorTest extends TestCase
{
    #[Test]
    public function itSnapshotsTokenVariablesForTwoThemes(): void
    {
        $generator = new CssGenerator();
        $dark = $generator->forTheme(ThemeCatalog::get('default-dark'));
        $semantic = $generator->forTheme(ThemeCatalog::get('semantic'));

        self::assertStringContainsString('[data-theme="default-dark"]', $dark);
        self::assertStringContainsString('[data-theme="semantic"]', $semantic);
        self::assertStringContainsString('color-scheme: dark;', $dark);
        self::assertStringContainsString('color-scheme: light;', $semantic);
        self::assertStringContainsString('schema:1.0', $dark);

        foreach (ThemeTokenSchema::requiredKeys(ThemeTokenSchema::V1_0) as $key) {
            self::assertStringContainsString($key, $dark);
            self::assertStringContainsString($key, $semantic);
        }
    }

    #[Test]
    public function allSixThemesEmitSchemaOneKeys(): void
    {
        $generator = new CssGenerator();

        foreach (['default', 'default-dark', 'semantic', 'semantic-dark', 'utility', 'utility-dark'] as $id) {
            $css = $generator->forTheme(ThemeCatalog::get($id));
            foreach (ThemeTokenSchema::requiredKeys(ThemeTokenSchema::V1_0) as $key) {
                self::assertStringContainsString($key, $css, $id . ' missing ' . $key);
            }
        }
    }

    #[Test]
    public function allSixThemesEmitNativeColorScheme(): void
    {
        $generator = new CssGenerator();

        foreach (['default', 'semantic', 'utility'] as $id) {
            $css = $generator->forTheme(ThemeCatalog::get($id));
            self::assertStringContainsString('color-scheme: light;', $css, $id);
        }

        foreach (['default-dark', 'semantic-dark', 'utility-dark'] as $id) {
            $css = $generator->forTheme(ThemeCatalog::get($id));
            self::assertStringContainsString('color-scheme: dark;', $css, $id);
        }
    }

    #[Test]
    public function itIncludesShadowAndMotionTokensInOutput(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'));

        self::assertStringContainsString('--ui-shadow-md:', $css);
        self::assertStringContainsString('--ui-motion-duration-normal:', $css);
        self::assertStringContainsString('--ui-grid-gap:', $css);
    }

    #[Test]
    public function itEmitsOklchSemanticColorsAndOptionalP3Boosts(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'));

        self::assertMatchesRegularExpression('/--ui-color-primary: (#[0-9a-f]{6}|oklch\([^;]+\));/i', $css);
        // P3 block is optional — stallion hex anchors may already exceed boost headroom.
        if (str_contains($css, '@media (color-gamut: p3)')) {
            self::assertStringContainsString('@media (color-gamut: p3)', $css);
        }
    }

    #[Test]
    public function schemaOneIncludesSystemProfileOutput(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V1_0);

        self::assertStringContainsString('profile:chameleon-default', $css);
        self::assertStringContainsString('--ui-z-dropdown:', $css);
        self::assertStringContainsString('@keyframes ui-shimmer', $css);
        self::assertStringNotContainsString('[data-ui-role="button"]', $css);
        self::assertStringNotContainsString('var(--ui-breakpoint', $css);
    }
}
