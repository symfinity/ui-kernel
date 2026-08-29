<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

/** 065 — tier role CSS lives in ux-blocks packages; kernel emits tokens + profile globals only. */
final class KernelSlimCoreTierCssTest extends TestCase
{
    #[Test]
    public function generatorEmitsNoDataUiRoleSelectors(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V1_0);

        self::assertDoesNotMatchRegularExpression('/\[data-ui-role="/', $css);
    }

    /** @return list<string> */
    private static function coreRolesMovedToPackage(): array
    {
        return [
            'button', 'card', 'alert', 'field', 'avatar', 'badge', 'figure', 'image',
            'grid', 'stack', 'skeleton', 'typography', 'separator',
        ];
    }

    #[Test]
    public function generatorOmitsCoreTierRoleSelectors(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V1_0);

        foreach (self::coreRolesMovedToPackage() as $role) {
            self::assertStringNotContainsString('[data-ui-role="' . $role . '"]', $css, $role);
        }

        self::assertStringContainsString('--ui-z-dropdown:', $css);
        self::assertStringContainsString('@keyframes ui-shimmer', $css);
    }

    /** @return list<string> */
    private static function extendedRolesMovedToPackage(): array
    {
        return [
            'tabs', 'tabs-trigger', 'dropdown-menu', 'drawer', 'drawer-content',
            'sheet', 'sheet-content', 'context-menu', 'hover-card',
        ];
    }

    #[Test]
    public function generatorOmitsExtendedTierRoleSelectors(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V1_0);

        foreach (self::extendedRolesMovedToPackage() as $role) {
            self::assertStringNotContainsString('[data-ui-role="' . $role . '"]', $css, $role);
        }
    }

    /** @return list<string> */
    private static function interactiveOverlayRolesMovedToPackage(): array
    {
        return ['modal', 'dialog', 'popover', 'menu', 'alert-dialog-content'];
    }

    #[Test]
    public function generatorOmitsInteractiveOverlayRoleSelectors(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V1_0);

        foreach (self::interactiveOverlayRolesMovedToPackage() as $role) {
            self::assertStringNotContainsString('[data-ui-role="' . $role . '"]', $css, $role);
        }
    }
}
