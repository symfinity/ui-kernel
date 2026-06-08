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
    public function schemaOneIncludesFocusRingRules(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'));

        self::assertStringContainsString('schema:1.0', $css);
        self::assertStringContainsString(':focus-visible', $css);
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
    public function itIncludesSemanticFilledButtonVariants(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('default'));

        foreach (['tertiary', 'danger', 'success', 'info', 'warning'] as $variant) {
            self::assertStringContainsString(
                '[data-ui-role="button"][data-ui-variant="'.$variant.'"]',
                $css,
            );
        }
        self::assertStringContainsString('background: var(--ui-color-danger)', $css);
        self::assertStringContainsString('background: var(--ui-color-success)', $css);
        self::assertStringContainsString('background: var(--ui-color-info)', $css);
        self::assertStringContainsString('background: var(--ui-color-warning)', $css);
    }

    #[Test]
    public function filledButtonVariantsUseWhiteLabelText(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'));

        foreach (['primary', 'secondary', 'tertiary', 'danger', 'success', 'info', 'warning'] as $variant) {
            self::assertMatchesRegularExpression(
                '/\[data-ui-role="button"\]\[data-ui-variant="'.$variant.'"\](?:\[data-ui-appearance="solid"\]|:not\(\[data-ui-appearance\]\))[^{]*\{[^}]*color: #fff;/s',
                $css,
            );
        }
    }

    #[Test]
    public function secondaryButtonUsesSecondaryColorToken(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'));

        self::assertStringContainsString(
            '[data-ui-role="button"][data-ui-variant="secondary"]',
            $css,
        );
        self::assertMatchesRegularExpression(
            '/\[data-ui-role="button"\]\[data-ui-variant="secondary"\][^{]*\{[^}]*background: var\(--ui-color-secondary\)/s',
            $css,
        );
    }

    #[Test]
    public function itIncludesAllV0UxPrimitiveRoles(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('default'));

        foreach ([
            'separator',
            'typography',
            'label',
            'input',
            'textarea',
            'select',
            'field',
            'checkbox',
            'radio-group',
            'empty',
            'table',
        ] as $role) {
            self::assertStringContainsString('[data-ui-role="' . $role . '"]', $css, $role);
        }

        self::assertStringContainsString('[data-ui-role="button"][data-ui-variant="primary"][data-ui-appearance="outline"]', $css);
        self::assertStringContainsString('[data-ui-role="alert"][data-ui-variant="warning"]', $css);
    }

    #[Test]
    public function schemaOneIncludesSystemProfileOutput(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V1_0);

        self::assertStringContainsString('profile:chameleon-default', $css);
        self::assertStringContainsString('--ui-z-dropdown:', $css);
        self::assertStringContainsString('@keyframes ui-shimmer', $css);
        self::assertStringContainsString('[data-ui-role="grid"]', $css);
        self::assertStringContainsString('[data-ui-role="button"][data-ui-size="sm"]', $css);
        self::assertStringContainsString('[data-ui-role="button"][data-ui-layout="block"]', $css);
        self::assertStringContainsString('[data-ui-role="grid"] > [data-ui-role="button"]', $css);
        self::assertStringContainsString('[data-ui-role="nav"]', $css);
        self::assertStringContainsString('[data-ui-role="alert"][data-ui-variant="info"]', $css);
        self::assertStringContainsString('[data-ui-role="alert"][data-ui-variant="info"] [data-ui-role="button"]', $css);
        self::assertStringNotContainsString('var(--ui-breakpoint', $css);
    }
}
