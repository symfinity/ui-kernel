<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\SemanticVariant;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class CssGeneratorSemanticVariantTest extends TestCase
{
    #[Test]
    public function buttonSolidAndOutlineAppearanceSelectorsArePresent(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V1_0);

        self::assertStringContainsString(
            '[data-ui-role="button"][data-ui-variant="danger"][data-ui-appearance="outline"]',
            $css,
        );
        self::assertStringContainsString(
            '[data-ui-role="button"][data-ui-variant="primary"]:not([data-ui-appearance])',
            $css,
        );
    }

    #[Test]
    public function accentRolesExposeAllEightSemanticVariants(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V1_0);

        foreach (SemanticVariant::ALL as $variant) {
            self::assertStringContainsString(
                '[data-ui-role="slider"][data-ui-variant="' . $variant . '"]',
                $css,
                'slider missing ' . $variant,
            );
        }
    }
}
