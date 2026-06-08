<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class CssGeneratorAccentVariantTest extends TestCase
{
    #[Test]
    public function accentSemanticVariantSelectorsArePresent(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V1_0);

        foreach (['slider', 'progress', 'switch', 'checkbox'] as $role) {
            self::assertStringContainsString(
                '[data-ui-role="' . $role . '"][data-ui-variant="success"]',
                $css,
                $role,
            );
        }

        self::assertStringContainsString('[data-ui-role="toggle"][data-ui-variant="danger"][aria-pressed="true"]', $css);
        self::assertStringContainsString('[data-ui-role="rating"][data-ui-variant="info"] button[aria-pressed="true"]', $css);
        self::assertStringContainsString('[data-ui-role="accordion"][data-ui-variant="success"] details', $css);
    }
}
