<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

/** 065 W5 — marketing tier CSS lives in symfinity/ux-blocks-marketing. */
final class CssGeneratorMarketingR2RolesTest extends TestCase
{
    /** @return list<string> */
    private static function marketingRoles(): array
    {
        return [
            'hero', 'comparison-section', 'integrations-section', 'cookie-consent',
            'status-band', 'stats-band',
        ];
    }

    #[Test]
    public function schemaTwoOmitsMarketingRoleSelectors(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V2_0);

        foreach (self::marketingRoles() as $role) {
            self::assertStringNotContainsString(
                '[data-ui-role="' . $role . '"]',
                $css,
                sprintf('Kernel must not ship marketing role "%s" after 065 W5', $role),
            );
        }
    }
}
