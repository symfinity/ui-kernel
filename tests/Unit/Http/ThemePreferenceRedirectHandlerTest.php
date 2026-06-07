<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\Http;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\Http\ThemePreferenceRedirectHandler;
use Symfinity\UiKernel\Theme\ActiveThemeContext;
use Symfinity\UiKernel\Theme\ThemePreferenceCookies;
use Symfinity\UiKernel\Theme\ThemePreferenceResolver;
use Symfinity\UiKernel\Theme\ThemeRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ThemePreferenceRedirectHandlerTest extends TestCase
{
    #[Test]
    public function itRedirectsAndStripsThemeQueryParam(): void
    {
        $request = Request::create('/showcase/button?theme=semantic-dark&foo=bar');
        $request->attributes->set('_route', 'showcase_button');
        $request->attributes->set('_route_params', []);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('https://example.test/showcase/button?foo=bar');
        $handler = new ThemePreferenceRedirectHandler(
            new ThemePreferenceCookies(),
            new ThemePreferenceResolver(new ThemeRegistry(), 'default'),
            new ActiveThemeContext(
                new ThemePreferenceCookies(),
                new ThemePreferenceResolver(new ThemeRegistry(), 'default'),
                new ThemeRegistry(),
            ),
            $urlGenerator,
        );

        $response = $handler->createRedirectIfPreferenceQueryPresent($request);

        self::assertNotNull($response);
        self::assertSame(302, $response->getStatusCode());
        self::assertSame('https://example.test/showcase/button?foo=bar', $response->headers->get('Location'));

        $cookieNames = array_map(
            static fn (\Symfony\Component\HttpFoundation\Cookie $cookie): string => $cookie->getName(),
            $response->headers->getCookies(),
        );
        self::assertContains('symfinity_ui_kernel_scheme', $cookieNames);
        self::assertContains('symfinity_ui_kernel_lineage', $cookieNames);

        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === 'symfinity_ui_kernel_scheme') {
                self::assertSame('dark', $cookie->getValue());
            }
            if ($cookie->getName() === 'symfinity_ui_kernel_lineage') {
                self::assertSame('semantic', $cookie->getValue());
            }
        }
    }

    #[Test]
    public function itReturnsNullWhenNoPreferenceQueryPresent(): void
    {
        $handler = $this->handler();
        $request = Request::create('/showcase/button');

        self::assertNull($handler->createRedirectIfPreferenceQueryPresent($request));
    }

    private function handler(): ThemePreferenceRedirectHandler
    {
        return new ThemePreferenceRedirectHandler(
            new ThemePreferenceCookies(),
            new ThemePreferenceResolver(new ThemeRegistry(), 'default'),
            new ActiveThemeContext(
                new ThemePreferenceCookies(),
                new ThemePreferenceResolver(new ThemeRegistry(), 'default'),
                new ThemeRegistry(),
            ),
            $this->createMock(UrlGeneratorInterface::class),
        );
    }
}
