<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

/** 065 W2 — extended tier chrome lives in symfinity/ux-blocks-extended, not kernel. */
final class CssGeneratorExtendedRolesTest extends TestCase
{
    /**
     * @return list<string>
     */
    private static function extendedRolesInPackage(): array
    {
        return [
            'tabs', 'tabs-trigger', 'tabs-content', 'dropdown-menu', 'drawer', 'drawer-content',
            'sheet', 'sheet-content', 'context-menu', 'hover-card', 'alert-dialog-enhanced',
            'combobox', 'menubar', 'navigation-menu', 'filter-chips',
        ];
    }

    #[Test]
    public function schemaTwoOmitsExtendedTierRoleSelectors(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V2_0);

        foreach (self::extendedRolesInPackage() as $role) {
            self::assertStringNotContainsString(
                '[data-ui-role="' . $role . '"]',
                $css,
                sprintf('Kernel must not ship extended role "%s" after 065 W2', $role),
            );
        }
    }
}
