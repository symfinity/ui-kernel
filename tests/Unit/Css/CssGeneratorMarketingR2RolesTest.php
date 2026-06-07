<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

/**
 * symfinity/ux-blocks-marketing R2 roles (048 T026–T027).
 */
final class CssGeneratorMarketingR2RolesTest extends TestCase
{
    /** @return list<string> */
    private static function r2Roles(): array
    {
        return [
            'comparison-section',
            'integrations-section',
            'cookie-consent',
            'status-band',
        ];
    }

    #[Test]
    public function schemaTwoIncludesMarketingR2RoleSelectors(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V2_0);

        foreach (self::r2Roles() as $role) {
            self::assertStringContainsString(
                '[data-ui-role="' . $role . '"]',
                $css,
                sprintf('Missing kernel CSS for marketing R2 role "%s"', $role),
            );
        }
    }

    #[Test]
    public function statusBandAndStatsBandRulesAreDistinct(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V2_0);

        self::assertStringContainsString('[data-ui-role="stats-band"]', $css);
        self::assertStringContainsString('[data-ui-role="status-band"]', $css);
        self::assertStringContainsString('[data-ui-role="status-band"][data-ui-status-tone="operational"]', $css);
        self::assertStringNotContainsString('[data-ui-role="stats-band"][data-ui-status-tone', $css);
    }
}
