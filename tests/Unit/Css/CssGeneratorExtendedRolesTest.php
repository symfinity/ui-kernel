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
    /**
     * symfinity/ux-blocks-live — blocks.live stl roles (054).
     *
     * @return list<string>
     */
    private static function liveRoles(): array
    {
        return [
            'tabs', 'alert-dialog-enhanced', 'drawer', 'sheet', 'dropdown-menu', 'combobox',
            'slider', 'toggle', 'toggle-group', 'calendar', 'date-picker', 'input-otp',
            'sidebar', 'stacked-layout-interactive', 'command-palette', 'toast', 'context-menu',
            'hover-card', 'resizable', 'menubar', 'navigation-menu', 'data-table-chrome-interactive',
            'carousel-interactive', 'rating', 'filter-chips',
            'date-range-picker', 'tags-input', 'tree-view',
        ];
    }

    #[Test]
    public function schemaTwoIncludesAllLiveRoleSelectors(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V2_0);

        foreach (self::liveRoles() as $role) {
            self::assertStringContainsString(
                '[data-ui-role="' . $role . '"]',
                $css,
                sprintf('Missing kernel CSS for live role "%s"', $role),
            );
        }
    }
}
