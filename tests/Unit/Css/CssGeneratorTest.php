<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Flavour\FlavourCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class CssGeneratorTest extends TestCase
{
    #[Test]
    public function itSnapshotsTokenVariablesForTwoFlavours(): void
    {
        $generator = new CssGenerator();
        $dark = $generator->forFlavour(FlavourCatalog::get('dark'));
        $semantic = $generator->forFlavour(FlavourCatalog::get('semantic'));

        self::assertStringContainsString('[data-theme="dark"]', $dark);
        self::assertStringContainsString('[data-theme="semantic"]', $semantic);
        self::assertStringContainsString('schema:2.0', $dark);

        foreach (ThemeTokenSchema::requiredKeys(ThemeTokenSchema::V2_0) as $key) {
            self::assertStringContainsString($key, $dark);
            self::assertStringContainsString($key, $semantic);
        }
    }

    #[Test]
    public function allSixFlavoursEmitSchemaTwoKeys(): void
    {
        $generator = new CssGenerator();

        foreach (['default', 'dark', 'semantic', 'semantic-dark', 'utility', 'utility-dark'] as $id) {
            $css = $generator->forFlavour(FlavourCatalog::get($id));
            foreach (ThemeTokenSchema::requiredKeys(ThemeTokenSchema::V2_0) as $key) {
                self::assertStringContainsString($key, $css, $id . ' missing ' . $key);
            }
        }
    }

    #[Test]
    public function schemaVersionOneOmitsFocusRingRules(): void
    {
        $flavour = FlavourCatalog::get('semantic');
        $cssV1 = (new CssGenerator())->forFlavour($flavour, ThemeTokenSchema::V1_0);
        $cssV2 = (new CssGenerator())->forFlavour($flavour, ThemeTokenSchema::V2_0);

        self::assertStringContainsString('schema:1.0', $cssV1);
        self::assertStringNotContainsString(':focus-visible', $cssV1);
        self::assertStringContainsString(':focus-visible', $cssV2);
    }

    #[Test]
    public function itIncludesShadowAndMotionTokensInOutput(): void
    {
        $css = (new CssGenerator())->forFlavour(FlavourCatalog::get('semantic'));

        self::assertStringContainsString('--ui-shadow-md:', $css);
        self::assertStringContainsString('--ui-motion-duration-normal:', $css);
        self::assertStringContainsString('--ui-grid-gap:', $css);
    }

    #[Test]
    public function itIncludesDangerAndSuccessButtonVariants(): void
    {
        $css = (new CssGenerator())->forFlavour(FlavourCatalog::get('default'));

        self::assertStringContainsString('[data-ui-role="button"][data-ui-variant="danger"]', $css);
        self::assertStringContainsString('[data-ui-role="button"][data-ui-variant="success"]', $css);
        self::assertStringContainsString('background: var(--ui-color-danger)', $css);
        self::assertStringContainsString('background: var(--ui-color-success)', $css);
    }

    #[Test]
    public function itIncludesAllV0UxPrimitiveRoles(): void
    {
        $css = (new CssGenerator())->forFlavour(FlavourCatalog::get('default'));

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

        self::assertStringContainsString('[data-ui-role="button"][data-ui-variant="outline"]', $css);
        self::assertStringContainsString('[data-ui-role="alert"][data-ui-variant="warning"]', $css);
    }
}
