<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Profile\SystemProfile;
use Symfinity\UiKernel\Token\ButtonStateDerivation;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class SystemProfileCssTest extends TestCase
{
    #[Test]
    public function schemaTwoIncludesProfileGlobalsAndLayoutRoles(): void
    {
        $css = (new CssGenerator())->forTheme(
            ThemeCatalog::get('semantic'),
            ThemeTokenSchema::V2_0,
        );

        self::assertStringContainsString('--ui-z-dropdown: 1000', $css);
        self::assertStringContainsString('--ui-z-toast: 1080', $css);
        self::assertStringContainsString('@keyframes ui-shimmer', $css);
        self::assertStringContainsString('@keyframes ui-pulse', $css);
        self::assertStringContainsString('[data-ui-role="grid"]', $css);
        self::assertStringContainsString('[data-ui-role="grid-cell"]', $css);
        self::assertStringContainsString('[data-ui-role="grid-container"]', $css);
        self::assertStringContainsString('[data-ui-role="stack"]', $css);
        self::assertStringContainsString('[data-ui-role="skeleton"]', $css);
        self::assertStringContainsString('@media (min-width: 768px)', $css);
        self::assertStringContainsString('@media (min-width: 1024px)', $css);
        self::assertStringContainsString('data-ui-span-lg="6"', $css);
        self::assertStringContainsString('max-width: 1140px', $css);
        self::assertStringNotContainsString('var(--ui-breakpoint', $css);
    }

    #[Test]
    public function schemaOneOmitsProfileGlobalsAndLayoutRoles(): void
    {
        $css = (new CssGenerator())->forTheme(
            ThemeCatalog::get('semantic'),
            ThemeTokenSchema::V1_0,
        );

        self::assertStringNotContainsString('--ui-z-modal:', $css);
        self::assertStringNotContainsString('@keyframes ui-shimmer', $css);
        self::assertStringNotContainsString('[data-ui-role="grid"]', $css);
        self::assertStringNotContainsString('[data-ui-role="skeleton"]', $css);
    }

    #[Test]
    public function customProfileUsesLiteralPxInMediaQueries(): void
    {
        $profile = SystemProfile::fromConfig(['breakpoints' => ['md' => 800]]);
        $theme = ThemeCatalog::get('default');
        $css = (new CssGenerator())->forResolvedTokens(
            $theme->id(),
            $theme->tokens(),
            ThemeTokenSchema::V2_0,
            $profile,
        );

        self::assertStringContainsString('@media (max-width: 799px)', $css);
        self::assertStringContainsString('@media (min-width: 800px)', $css);
    }

    #[Test]
    public function reducedMotionDisablesSkeletonShimmer(): void
    {
        $css = (new CssGenerator())->forTheme(
            ThemeCatalog::get('default'),
            ThemeTokenSchema::V2_0,
        );

        self::assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        self::assertStringContainsString('animation: none', $css);
        self::assertStringContainsString('[data-ui-role="grid"]', $css);
    }

    #[Test]
    public function cacheKeyPartsIncludeProfileIdAndHash(): void
    {
        $profile = SystemProfile::chameleonDefault();
        $preset = \Symfinity\UiKernel\Token\ThemeConfig::get('semantic')->presetHash();
        $parts = CssGenerator::cacheKeyParts('semantic', 'abc', ThemeTokenSchema::V2_0, $profile, $preset);

        self::assertSame('semantic', $parts['themeId']);
        self::assertSame('chameleon-default', $parts['systemProfileId']);
        self::assertSame($profile->hash(), $parts['profileHash']);
        self::assertSame($preset, $parts['presetHash']);
        self::assertSame(ButtonStateDerivation::ALGORITHM_VERSION, $parts['roleRulesVersion']);
    }
}
