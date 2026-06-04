<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;
final class CssGeneratorExtendedRolesTest extends TestCase
{
    /** @return list<string> — mirror symfinity/ux-blocks ExtendedRoleCatalog */
    private static function extendedRoles(): array
    {
        return [
            'tabs', 'alert-dialog-enhanced', 'drawer', 'sheet', 'dropdown-menu', 'combobox',
            'slider', 'toggle', 'toggle-group', 'calendar', 'date-picker', 'input-otp',
            'sidebar', 'stacked-layout-interactive', 'command-palette', 'toast', 'context-menu',
            'hover-card', 'resizable', 'menubar', 'navigation-menu', 'data-table-chrome',
            'carousel-interactive', 'rating', 'filter-chips',
        ];
    }

    #[Test]
    public function schemaTwoIncludesAllExtendedRoleSelectors(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V1_0);

        foreach (self::extendedRoles() as $role) {
            self::assertStringContainsString(
                '[data-ui-role="' . $role . '"]',
                $css,
                sprintf('Missing kernel CSS for extended role "%s"', $role),
            );
        }
    }
}
