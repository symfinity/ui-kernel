<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Css\CssCacheKeyPolicy;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Profile\SystemProfile;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class SystemProfileCssTest extends TestCase
{
    #[Test]
    public function schemaOneIncludesProfileGlobalsOnly(): void
    {
        $css = (new CssGenerator())->forTheme(
            ThemeCatalog::get('semantic'),
            ThemeTokenSchema::V1_0,
        );

        self::assertStringContainsString('--ui-z-dropdown: 1000', $css);
        self::assertStringContainsString('--ui-z-toast: 1080', $css);
        self::assertStringContainsString('@keyframes ui-shimmer', $css);
        self::assertStringContainsString('@keyframes ui-pulse', $css);
        self::assertStringNotContainsString('[data-ui-role="grid"]', $css);
        self::assertStringNotContainsString('var(--ui-breakpoint', $css);
    }

    #[Test]
    public function cacheKeyPartsIncludeProfileIdAndHash(): void
    {
        $profile = SystemProfile::chameleonDefault();
        $preset = \Symfinity\UiKernel\Token\ThemeConfig::get('semantic')->presetHash();
        $parts = CssGenerator::cacheKeyParts('semantic', 'abc', ThemeTokenSchema::V1_0, $profile, $preset);

        self::assertSame('semantic', $parts['themeId']);
        self::assertSame('chameleon-default', $parts['systemProfileId']);
        self::assertSame($profile->hash(), $parts['profileHash']);
        self::assertSame($preset, $parts['presetHash']);
        self::assertSame('tokens-only:1', $parts['roleRulesVersion']);
        self::assertSame(CssCacheKeyPolicy::roleRulesVersion(), $parts['roleRulesVersion']);
    }
}
