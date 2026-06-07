<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

/**
 * MVP wave from symfinity/ux-blocks-extended (025 T009/T024).
 */
final class CssGeneratorShippedExtendedRolesTest extends TestCase
{
    /** @return list<string> */
    private static function shippedRootRoles(): array
    {
        return [
            'tabs',
            'dropdown-menu',
            'alert-dialog-enhanced',
            'drawer',
            'sheet',
            'context-menu',
            'hover-card',
        ];
    }

    /** @return list<string> */
    private static function shippedSubRoles(): array
    {
        return [
            'tabs-list',
            'tabs-trigger',
            'tabs-content',
            'dropdown-menu-trigger',
            'dropdown-menu-content',
            'dropdown-menu-item',
            'alert-dialog-trigger',
            'alert-dialog-content',
            'alert-dialog-title',
            'alert-dialog-description',
            'alert-dialog-footer',
            'alert-dialog-action',
            'alert-dialog-cancel',
            'drawer-trigger',
            'drawer-content',
            'drawer-close',
            'drawer-title',
            'drawer-header',
            'sheet-trigger',
            'sheet-content',
            'sheet-close',
            'sheet-title',
            'sheet-header',
            'context-menu-trigger',
            'context-menu-content',
            'context-menu-item',
            'hover-card-trigger',
            'hover-card-content',
        ];
    }

    #[Test]
    public function schemaTwoIncludesShippedExtendedRootRoles(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V2_0);

        foreach (self::shippedRootRoles() as $role) {
            self::assertStringContainsString(
                '[data-ui-role="' . $role . '"]',
                $css,
                sprintf('Missing kernel CSS for shipped extended root role "%s"', $role),
            );
        }
    }

    #[Test]
    public function schemaTwoIncludesShippedExtendedSubRoles(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V2_0);

        foreach (self::shippedSubRoles() as $role) {
            self::assertStringContainsString(
                '[data-ui-role="' . $role . '"]',
                $css,
                sprintf('Missing kernel CSS for shipped extended sub-role "%s"', $role),
            );
        }
    }

    #[Test]
    public function overlayPanelRulesCoverDrawerAndSheetSides(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V2_0);

        self::assertStringContainsString('[data-ui-role="drawer-content"][data-ui-side="bottom"]', $css);
        self::assertStringContainsString('[data-ui-role="sheet-content"][data-ui-side="right"]', $css);
        self::assertStringContainsString('[data-ui-role="context-menu-content"]:not([hidden])', $css);
        self::assertStringContainsString('[data-ui-role="hover-card-content"]:not([hidden])', $css);
    }
}
