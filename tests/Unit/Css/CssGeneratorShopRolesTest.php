<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

/** 065 W6 — ecommerce tier CSS lives in symfinity/ux-blocks-ecommerce. */
final class CssGeneratorShopRolesTest extends TestCase
{
    /** @return list<string> */
    private static function shopRootRoles(): array
    {
        return [
            'product-overview', 'product-card', 'shopping-cart-layout',
            'cart-drawer-quickview', 'price',
        ];
    }

    #[Test]
    public function schemaTwoOmitsShopRoleSelectors(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V2_0);

        foreach (self::shopRootRoles() as $role) {
            self::assertStringNotContainsString(
                '[data-ui-role="' . $role . '"]',
                $css,
                sprintf('Kernel must not ship shop role "%s" after 065 W6', $role),
            );
        }
    }
}
