<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Css;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Css\CssGenerator;
use Symfinity\UiKernel\Theme\ThemeCatalog;
use Symfinity\UiKernel\Token\ThemeTokenSchema;

final class CssGeneratorR2RolesTest extends TestCase
{
    #[Test]
    public function collapsibleRoleSelectorsArePresent(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V2_0);

        self::assertStringContainsString('[data-ui-role="collapsible"]', $css);
        self::assertStringContainsString('[data-ui-role="collapsible-trigger"]', $css);
        self::assertStringContainsString('[data-ui-role="collapsible-content"]', $css);
        self::assertStringContainsString('[data-ui-state="closed"]', $css);
    }

    #[Test]
    public function spinnerVariantSelectorsArePresent(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V2_0);

        self::assertStringContainsString('[data-ui-role="spinner"][data-ui-size="sm"]', $css);
        self::assertStringContainsString('[data-ui-role="spinner"][data-ui-size="lg"]', $css);
        self::assertStringContainsString('[data-ui-role="spinner"][data-ui-density="block"]', $css);
    }

    #[Test]
    public function imageRoleSelectorsArePresent(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('semantic'), ThemeTokenSchema::V2_0);

        self::assertStringContainsString('[data-ui-role="image"]', $css);
        self::assertStringContainsString('[data-ui-role="image"][data-ui-variant="thumbnail"]', $css);
        self::assertStringContainsString('max-width: 12.5rem', $css);
        self::assertStringContainsString('[data-ui-role="image"][data-ui-variant="rounded"]', $css);
    }

    #[Test]
    public function imageRoleSelectorsArePresentOnDefaultV1Lineage(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('default'));

        self::assertStringContainsString(
            '[data-ui-role="image"][data-ui-variant="thumbnail"] {
  width: auto;
  max-width: 12.5rem;
  height: auto;
}',
            $css,
        );
    }

    #[Test]
    public function avatarRoleSelectorsArePresentOnDefaultV1Lineage(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('default'));

        self::assertStringContainsString('[data-ui-role="avatar"]', $css);
        self::assertStringContainsString('border-radius: var(--ui-radius-full)', $css);
        self::assertStringContainsString('[data-ui-role="avatar"] [data-ui-role="image"]', $css);
        self::assertStringContainsString('[data-ui-role="avatar"][data-ui-size="sm"]', $css);
        self::assertStringContainsString('[data-ui-role="avatar"][data-ui-size="lg"]', $css);
        foreach (['primary', 'secondary', 'tertiary', 'destructive', 'success', 'info', 'warning'] as $variant) {
            self::assertStringContainsString('[data-ui-role="avatar"][data-ui-variant="' . $variant . '"]', $css);
        }
    }

    #[Test]
    public function authLayoutStacksDirectChildrenWithSpacingOnDefaultV1Lineage(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('default'));

        self::assertStringContainsString('[data-ui-role="auth-layout"]', $css);
        self::assertStringContainsString('flex-direction: column', $css);
        self::assertStringContainsString('[data-ui-role="auth-layout"] > *:last-child', $css);
        self::assertStringContainsString('margin-block-end: 0.5rem', $css);
    }

    #[Test]
    public function badgeRoleSelectorsArePresentOnDefaultV1Lineage(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('default'));

        self::assertStringContainsString('[data-ui-role="badge"]', $css);
        self::assertStringContainsString('border-radius: var(--ui-radius-full)', $css);
        foreach (['primary', 'secondary', 'outline', 'destructive', 'success', 'info', 'warning', 'ghost'] as $variant) {
            self::assertStringContainsString('[data-ui-role="badge"][data-ui-variant="' . $variant . '"]', $css);
        }
    }

    #[Test]
    public function breadcrumbRoleSelectorsArePresentOnDefaultV1Lineage(): void
    {
        $css = (new CssGenerator())->forTheme(ThemeCatalog::get('default'));

        self::assertStringContainsString('[data-ui-role="breadcrumb"]', $css);
        self::assertStringContainsString('--ui-breadcrumb-divider: "/"', $css);
        self::assertStringContainsString('[data-ui-role="breadcrumb"] ol', $css);
        self::assertStringContainsString('[data-ui-role="breadcrumb"][data-ui-divider="chevron"]', $css);
        self::assertStringContainsString('[data-ui-role="breadcrumb"][data-ui-divider="gt"]', $css);
        self::assertStringContainsString('[data-ui-role="breadcrumb"][data-ui-divider="none"]', $css);
        self::assertStringContainsString('[data-ui-role="breadcrumb"] ol > :not(:first-child)::before', $css);
    }
}
