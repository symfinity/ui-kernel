<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\Tests\Unit\DataCollector;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfinity\UiKernel\DataCollector\UiKernelDataCollector;
use Symfinity\UiKernel\Theme\ActiveThemeContext;
use Symfinity\UiKernel\Token\ThemeTokenSchema;
use Symfinity\UiKernel\Theme\ThemePreferenceCookies;
use Symfinity\UiKernel\Theme\ThemePreferenceResolver;
use Symfinity\UiKernel\Theme\ThemeRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class UiKernelDataCollectorTest extends TestCase
{
    private ActiveThemeContext $activeThemeContext;
    private ThemePreferenceResolver $resolver;
    private ThemeRegistry $themeRegistry;
    private UrlGeneratorInterface&MockObject $router;

    protected function setUp(): void
    {
        $this->themeRegistry = new ThemeRegistry();
        $this->resolver = new ThemePreferenceResolver($this->themeRegistry, 'semantic');
        $this->activeThemeContext = new ActiveThemeContext(
            new ThemePreferenceCookies(),
            $this->resolver,
            $this->themeRegistry,
        );
        $this->router = $this->createMock(UrlGeneratorInterface::class);
    }

    #[Test]
    public function collectReturnsThemePayloadFromActiveThemeContext(): void
    {
        $request = Request::create('/demo');
        $request->cookies->set(ThemePreferenceCookies::LINEAGE, 'utility');
        $request->cookies->set(ThemePreferenceCookies::SCHEME, 'dark');

        $this->router->expects($this->exactly(2))
            ->method('generate')
            ->willReturnCallback(static function (string $route): string {
                return match ($route) {
                    'ui_kernel_showcase' => throw new RouteNotFoundException(),
                    'ux_blocks_demo_kernel' => '/kernel',
                    default => throw new RouteNotFoundException(),
                };
            });

        $collector = $this->createCollector();
        $collector->collect($request, new Response());

        self::assertTrue($collector->isEnabled());
        self::assertSame('utility-dark', $collector->getThemeId());
        self::assertSame('utility', $collector->getLineage());
        self::assertSame('dark', $collector->getScheme());
        self::assertFalse($collector->isSystemPrefersDark());
        self::assertSame('/kernel', $collector->getShowcaseUrl());
        self::assertGreaterThan(0, $collector->getThemeCount());
        self::assertGreaterThan(0, $collector->getTokenCount());
        self::assertSame('utility-dark', $collector->getActiveTheme()['id']);
        self::assertNotSame('', $collector->getActiveTheme()['label']);
        self::assertNotEmpty($collector->getThemes());
        self::assertSame($collector->getThemeCount(), \count($collector->getThemes()));
        self::assertCount(\count(ThemeTokenSchema::COLOR_KEYS), $collector->getColorPalette());
        self::assertSame('--ui-color-primary', $collector->getColorPalette()[0]['cssVar']);
        self::assertNotSame('', $collector->getColorPalette()[0]['value']);
        self::assertSame('primary', $collector->getColorPalette()[0]['shortName']);
    }

    #[Test]
    public function collectUsesRequestCssBytesAttribute(): void
    {
        $request = Request::create('/demo');
        $request->attributes->set(UiKernelDataCollector::CSS_BYTES_REQUEST_ATTR, 2048);

        $this->router->method('generate')->willThrowException(new RouteNotFoundException());

        $collector = $this->createCollector();
        $collector->collect($request, new Response());

        self::assertSame(2048, $collector->getCssBytes());
    }

    #[Test]
    public function collectLeavesShowcaseUrlNullWhenRoutesMissing(): void
    {
        $this->router->method('generate')->willThrowException(new RouteNotFoundException());

        $collector = $this->createCollector();
        $collector->collect(Request::create('/'), new Response());

        self::assertNull($collector->getShowcaseUrl());
    }

    #[Test]
    public function collectDoesNotThrowOnProfilerMetaRequests(): void
    {
        $this->router->expects($this->never())->method('generate');

        $collector = $this->createCollector();
        $collector->collect(Request::create('/_wdt/abc123'), new Response());

        self::assertTrue($collector->isEnabled());
        self::assertNull($collector->getThemeId());
    }

    #[Test]
    public function resetClearsCollectedData(): void
    {
        $request = Request::create('/');
        $request->cookies->set(ThemePreferenceCookies::LINEAGE, 'semantic');

        $this->router->method('generate')->willReturn('/kernel');

        $collector = $this->createCollector();
        $collector->collect($request, new Response());
        $collector->reset();

        self::assertFalse($collector->isEnabled());
        self::assertNull($collector->getThemeId());
        self::assertSame(0, $collector->getCssBytes());
    }

    #[Test]
    public function collectResolvesAutoSchemeFromClientHint(): void
    {
        $request = Request::create('/demo');
        $request->headers->set('Sec-CH-Prefers-Color-Scheme', 'dark');

        $this->router->method('generate')->willThrowException(new RouteNotFoundException());

        $collector = $this->createCollector();
        $collector->collect($request, new Response());

        self::assertTrue($collector->isSystemPrefersDark());
        self::assertSame('auto', $collector->getScheme());
    }

    private function createCollector(): UiKernelDataCollector
    {
        return new UiKernelDataCollector(
            $this->activeThemeContext,
            $this->resolver,
            $this->themeRegistry,
            $this->router,
        );
    }
}
