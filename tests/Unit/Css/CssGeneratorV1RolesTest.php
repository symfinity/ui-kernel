<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class CssGeneratorV1RolesTest extends TestCase
{
    /** @return list<string> */
    private static function v1CoreRoles(): array
    {
        return [
            'scroll-area', 'aspect-ratio', 'divider', 'dialog', 'tooltip', 'spinner', 'progress',
            'navbar', 'breadcrumb', 'pagination', 'badge', 'avatar', 'list', 'accordion', 'steps',
            'link', 'switch', 'input-group', 'file-input', 'fieldset', 'description-list', 'stat',
            'timeline', 'carousel', 'kbd', 'button-group', 'page-heading', 'section-heading',
            'auth-layout', 'dashboard-shell',
        ];
    }

    #[Test]
    public function schemaTwoIncludesV1CoreRoleSelectors(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V1_0);

        foreach (self::v1CoreRoles() as $role) {
            self::assertStringContainsString('[data-ui-role="' . $role . '"]', $css, $role);
        }

        self::assertStringContainsString('[data-ui-role="dialog"]', $css);
        self::assertStringContainsString('@keyframes ui-spin', $css);
    }

    #[Test]
    public function tabsTriggerStateSelectorsArePresent(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V1_0);

        self::assertStringContainsString('[data-ui-role="tabs-trigger"][aria-selected="true"]', $css);
        self::assertStringContainsString('[data-ui-role="tabs-trigger"][data-ui-state="linked"]', $css);
        self::assertStringContainsString('[data-ui-role="tabs-trigger"][data-ui-state="disabled"]', $css);
    }

    #[Test]
    public function badgeAndAvatarVariantSelectorsArePresent(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V1_0);

        self::assertStringContainsString('[data-ui-role="badge"][data-ui-variant="secondary"]', $css);
        self::assertStringContainsString('[data-ui-role="badge"][data-ui-variant="ghost"]', $css);
        self::assertStringContainsString('[data-ui-role="avatar"][data-ui-variant="primary"]', $css);
        self::assertStringContainsString('[data-ui-role="avatar"][data-ui-size="sm"]', $css);
    }
}
