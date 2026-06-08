<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

/**
 * symfinity/ux-blocks-ecommerce shop roles (060 T007).
 */
final class CssGeneratorShopRolesTest extends TestCase
{
    /** @return list<string> */
    private static function shopRootRoles(): array
    {
        return [
            'product-overview',
            'product-list-section',
            'product-card',
            'shopping-cart-layout',
            'checkout-form-section',
            'category-filters-static',
            'order-summary',
            'order-history',
            'promo-incentives',
            'cart-drawer-quickview',
        ];
    }

    #[Test]
    public function schemaTwoIncludesAllShopRootRoleSelectors(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V2_0);

        foreach (self::shopRootRoles() as $role) {
            self::assertStringContainsString(
                '[data-ui-role="' . $role . '"]',
                $css,
                sprintf('Missing kernel CSS for shop role "%s"', $role),
            );
        }
    }

    #[Test]
    public function shopSubRoleRulesArePresent(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V2_0);

        self::assertStringContainsString('[data-ui-role="price"]', $css);
        self::assertStringContainsString('[data-ui-role="product-card"] img', $css);
        self::assertStringContainsString('[data-ui-role="shopping-cart-layout"]', $css);
        self::assertStringContainsString('[data-ui-role="cart-drawer-quickview"]', $css);
        self::assertStringContainsString('z-index: var(--ui-z-modal)', $css);
    }
}
