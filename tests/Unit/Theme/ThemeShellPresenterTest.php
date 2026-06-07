<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Theme;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Theme\ActiveThemeContext;
use Symfinity\UiKernel\Theme\ThemeColorScheme;
use Symfinity\UiKernel\Theme\ThemeLineageCatalog;
use Symfinity\UiKernel\Theme\ThemePreferenceCookies;
use Symfinity\UiKernel\Theme\ThemePreferenceResolver;
use Symfinity\UiKernel\Theme\ThemeRegistry;
use Symfinity\UiKernel\Theme\ThemeShellPresenter;
use Symfinity\UiKernel\Theme\ThemeShellView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ThemeShellPresenterTest extends TestCase
{
    private ThemeShellPresenter $presenter;

    protected function setUp(): void
    {
        $registry = new ThemeRegistry();
        $resolver = new ThemePreferenceResolver($registry, 'default');
        $context = new ActiveThemeContext(new ThemePreferenceCookies(), $resolver, $registry);

        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->method('generate')->willReturnCallback(
            static fn (string $name, array $params = []): string => '/'.$name.'?'.http_build_query($params),
        );

        $this->presenter = new ThemeShellPresenter($context, $registry, $router);
    }

    #[Test]
    public function itBuildsSchemeSwitcherLinksForCurrentRoute(): void
    {
        $request = Request::create('/room');
        $request->attributes->set('_route', 'poker_room');
        $request->attributes->set('_route_params', ['uuid' => 'abc']);

        $shell = $this->presenter->forRequest($request);

        self::assertSame('auto', $shell->scheme);
        self::assertSame(ThemeShellView::SCHEME_ENDPOINT, $shell->schemeEndpoint);
        self::assertCount(3, $shell->schemeSwitcherLinks);
        self::assertSame('auto', $shell->schemeSwitcherLinks[0]['scheme']);
        self::assertTrue($shell->schemeSwitcherLinks[0]['active']);
        self::assertStringContainsString('scheme=light', $shell->schemeSwitcherLinks[1]['url']);
        self::assertStringContainsString('uuid=abc', $shell->schemeSwitcherLinks[1]['url']);
    }

    #[Test]
    public function itResolvesColorSchemeFromActiveTheme(): void
    {
        $request = Request::create('/?scheme=dark');
        $request->attributes->set('_route', 'app_home');
        $request->cookies->set(ThemePreferenceCookies::SCHEME, ThemeColorScheme::Dark->value);

        $shell = $this->presenter->forRequest($request);

        self::assertSame('dark', $shell->scheme);
        self::assertSame('dark', $shell->colorScheme);
        self::assertNotNull($shell->activeTheme);
        self::assertTrue(ThemeLineageCatalog::isDarkThemeId($shell->activeTheme->id()));
    }

    #[Test]
    public function itUsesFallbackRouteWhenAttributeMissing(): void
    {
        $request = Request::create('/');

        $shell = $this->presenter->forRequest($request, 'app_home');

        self::assertCount(3, $shell->schemeSwitcherLinks);
        self::assertStringStartsWith('/app_home?', $shell->schemeSwitcherLinks[0]['url']);
    }

    #[Test]
    public function itReturnsEmptyShellWithoutRoute(): void
    {
        $shell = $this->presenter->forRequest(Request::create('/'));

        self::assertNull($shell->activeTheme);
        self::assertSame([], $shell->schemeSwitcherLinks);
    }

    #[Test]
    public function itBuildsThemeSwitcherLinksForShowcase(): void
    {
        $request = Request::create('/');
        $request->attributes->set('_route', 'app_home');

        $links = $this->presenter->themeSwitcherLinks($request);

        self::assertNotEmpty($links);
        self::assertArrayHasKey('id', $links[0]);
        self::assertArrayHasKey('url', $links[0]);
        self::assertArrayHasKey('active', $links[0]);
    }
}
