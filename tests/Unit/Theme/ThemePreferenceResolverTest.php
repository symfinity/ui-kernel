<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Theme;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Theme\ThemeColorScheme;
use Symfinity\UiKernel\Theme\ThemePreference;
use Symfinity\UiKernel\Theme\ThemePreferenceResolver;
use Symfinity\UiKernel\Theme\ThemeRegistry;

final class ThemePreferenceResolverTest extends TestCase
{
    private ThemePreferenceResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new ThemePreferenceResolver(new ThemeRegistry(), 'semantic');
    }

    #[Test]
    public function itResolvesAutoSchemeFromSystemPreference(): void
    {
        $preference = new ThemePreference('semantic', ThemeColorScheme::Auto);

        self::assertSame('semantic', $this->resolver->resolveThemeId($preference, false));
        self::assertSame('semantic-dark', $this->resolver->resolveThemeId($preference, true));
    }

    #[Test]
    public function itResolvesExplicitLightAndDarkWithinLineage(): void
    {
        $semantic = new ThemePreference('semantic', ThemeColorScheme::Light);
        $dark = new ThemePreference('default', ThemeColorScheme::Dark);

        self::assertSame('semantic', $this->resolver->resolveThemeId($semantic, true));
        self::assertSame('default-dark', $this->resolver->resolveThemeId($dark, false));
    }

    #[Test]
    public function itMapsFullThemeIdToLineageAndScheme(): void
    {
        $preference = $this->resolver->preferenceFromThemeId('utility-dark');

        self::assertSame('utility', $preference->lineage);
        self::assertSame(ThemeColorScheme::Dark, $preference->scheme);
    }

    #[Test]
    public function itReadsClientHintHeaderForSystemDark(): void
    {
        $request = \Symfony\Component\HttpFoundation\Request::create('/');
        $request->headers->set('Sec-CH-Prefers-Color-Scheme', 'dark');

        self::assertTrue($this->resolver->systemPrefersDark($request));
        self::assertTrue($this->resolver->resolveSystemPrefersDark($request));
    }

    #[Test]
    public function itReadsSystemPrefersDarkFromPatchBodyWhenHeaderAbsent(): void
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/',
            'PATCH',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['scheme' => 'auto', 'systemPrefersDark' => true], \JSON_THROW_ON_ERROR),
        );

        self::assertFalse($this->resolver->systemPrefersDark($request));
        self::assertTrue($this->resolver->resolveSystemPrefersDark($request));
    }

    #[Test]
    public function itPrefersClientHintHeaderOverPatchBody(): void
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/',
            'PATCH',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['scheme' => 'auto', 'systemPrefersDark' => true], \JSON_THROW_ON_ERROR),
        );
        $request->headers->set('Sec-CH-Prefers-Color-Scheme', 'light');

        self::assertFalse($this->resolver->resolveSystemPrefersDark($request));
    }

    #[Test]
    public function itAppliesSchemeQueryWithoutChangingLineage(): void
    {
        $request = \Symfony\Component\HttpFoundation\Request::create('/?scheme=light');
        $current = new ThemePreference('utility', ThemeColorScheme::Auto);

        $preference = $this->resolver->applyQueryOverrides($request, $current);

        self::assertSame('utility', $preference->lineage);
        self::assertSame(ThemeColorScheme::Light, $preference->scheme);
    }
}
